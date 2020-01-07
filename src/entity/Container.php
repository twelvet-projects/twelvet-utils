<?php

namespace twelvet\utils\entity;

/**
 * ============================================================================
 * TwelveT 工具箱
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * 容器
 */

use InvalidArgumentException;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use twelvet\utils\exception\UtilsException;

class Container
{
    /**
     * 容器对象实例
     * @var Container
     */
    protected static $instance;

    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];

    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [
        'HTTP'                      => HTTP::class,
        'Random'                    => Random::class,
        'File'                      => File::class,
    ];

    /**
     * 容器标识别名
     * @var array
     */
    protected $name = [];

    /**
     * 获取当前容器的实例（单例）
     * 
     * @return static
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * 
     * @param  string  $abstract    类标识、接口
     * @param  mixed   $concrete    要绑定的类、闭包或者实例
     * @return Container
     */
    public static function set($abstract, $concrete = null)
    {
        return static::getInstance()->bindTo($abstract, $concrete);
    }

    /**
     * 获取容器中的对象实例
     * 
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
     */
    public static function get($abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract, $vars, $newInstance);
    }

    /**
     * 移除容器中的对象实例
     * @access public
     * @param  string  $abstract    类标识、接口
     * @return void
     */
    public static function remove($abstract)
    {
        return static::getInstance()->delete($abstract);
    }

    /**
     * 清除容器中的对象实例
     * @access public
     * @return void
     */
    public static function clear()
    {
        return static::getInstance()->flush();
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     * @access public
     * @param  string|array  $abstract    类标识、接口
     * @param  mixed         $concrete    要绑定的类、闭包或者实例
     * @return $this
     */
    public function bindTo($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } elseif ($concrete instanceof \Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            if (isset($this->bind[$abstract])) {
                $abstract = $this->bind[$abstract];
            }
            $this->instances[$abstract] = $concrete;
        } else {
            $this->bind[$abstract] = $concrete;
        }

        return $this;
    }

    /**
     * 创建类的实例
     * 
     * @param  string        $abstract       类名或者标识
     * @param  array|true    $vars           变量
     * @param  bool          $newInstance    是否每次创建新的实例
     * @return object
     */
    public function make($abstract, $vars = [], $newInstance = false)
    {
        if ($vars === true) {
            // 总是创建新的实例化对象
            $newInstance = true;
            $vars        = [];
        }

        // 获取类名
        $abstract = isset($this->name[$abstract]) ? $this->name[$abstract] : $abstract;
        // 容器存在实例以及不需要新的实例立即返回
        if (isset($this->instances[$abstract]) && !$newInstance) {
            return $this->instances[$abstract];
        }

        // 是否已注册类
        if (isset($this->bind[$abstract])) {
            $concrete = $this->bind[$abstract];
            // 是否是匿名函数
            if ($concrete instanceof \Closure) {
                // 执行匿名函数
                $object = $this->invokeFunction($concrete, $vars);
            } else {
                // 写进容器别名标识
                $this->name[$abstract] = $concrete;
                // 重新执行
                return $this->make($concrete, $vars, $newInstance);
            }
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        if (!$newInstance) {
            // 写入容器
            $this->instances[$abstract] = $object;
        }
        // 返回实例
        return $object;
    }

    /**
     * 通过反射实例化指定类（支持依赖注入）
     * @param  string    $class 类名
     * @param  array     $vars  参数
     * @return mixed
     */
    public function invokeClass($class, $vars = [])
    {
        try {
            // 类信息反射
            $reflect = new \ReflectionClass($class);

            // 判断是否存在方法__make
            if ($reflect->hasMethod('__make')) {
                // 方法信息反射
                $method = new \ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    // 对方法进行参数依赖注入
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }
            // 获取构造方法
            $constructor = $reflect->getConstructor();
            // 对构造函数的参数进行依赖注入
            $args = $constructor ? $this->bindParams($constructor, $vars) : [];
            // 实例化类并返回
            return $reflect->newInstanceArgs($args);
        } catch (\ReflectionException $e) {
            throw new UtilsException('Utils not exists: ' . $class, 500);
        }
    }

    /**
     * 绑定参数
     * @param  \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param  array                                 $vars    参数
     * @return array
     */
    protected function bindParams($reflect, $vars = [])
    {
        // 构造函数是否存在参数
        if ($reflect->getNumberOfParameters() == 0)  return [];
        // 重置指针
        reset($vars);
        // 获取当前键值的位置([1,2,3]数组时true)
        $type   = key($vars) === 0 ? true : false;
        // 获取构造方法的参数
        $params = $reflect->getParameters();

        foreach ($params as $param) {
            // 获取参数名称
            $name      = $param->getName();
            // 转换命名风格使其一致
            $lowerName = $this->parseName($name);
            // 获取依赖注入类信息
            $class     = $param->getClass();
            // 遇上类进行注入
            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            }
            // 数字数组，且不为空参数
            elseif ($type == true && !empty($vars)) {
                $args[] = array_shift($vars);
            }
            // 非纯数字数组,且存在此风格命名的值
            elseif ($type == false && isset($vars[$name])) {
                $args[] = $vars[$name];
            }
            // 存在此风格命名的值
            elseif ($type == false && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            }
            // 是否存在默认值
            elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('Utils Method Param Miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @access protected
     * @param  string   $className  类名
     * @param  array    $vars       参数
     * @return mixed
     */
    protected function getObjectParam($className, &$vars)
    {
        $array = $vars;
        // 删除first并返回元素
        $value = array_shift($array);
        // 判断是否属于此类
        if ($value instanceof $className) {
            $result = $value;
            // 删除元素
            array_shift($vars);
        } else {
            // 获取类实例
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * 删除容器中的对象实例
     * @access public
     * @param  string|array    $abstract    类名或者标识
     * @return void
     */
    public function delete($abstract)
    {
        foreach ((array) $abstract as $name) {
            $name = isset($this->name[$name]) ? $this->name[$name] : $name;

            if (isset($this->instances[$name])) {
                unset($this->instances[$name]);
            }
        }
    }

    /**
     * 获取容器中的对象实例
     * @access public
     * @return array
     */
    public function all()
    {
        return $this->instances;
    }

    /**
     * 清除容器中的对象实例
     * @access public
     * @return void
     */
    public function flush()
    {
        $this->instances = [];
        $this->bind      = [];
        $this->name      = [];
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * 
     * @param  mixed  $function 函数或者闭包
     * @param  array  $vars     参数
     * @return mixed
     */
    public function invokeFunction($function, $vars = [])
    {
        try {
            // 反射函数信息
            $reflect = new ReflectionFunction($function);
            // 进行参数绑定
            $args = $this->bindParams($reflect, $vars);
            // 执行方法并传参
            return call_user_func_array($function, $args);
        } catch (ReflectionException $e) {
            throw new UtilsException('function not exists: ' . $function . '()', 500);
        }
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * 
     * @param  string  $name 字符串
     * @param  integer $type 转换类型
     * @param  bool    $ucfirst 首字母是否大写（驼峰规则）
     * @return string
     */
    private function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
    }
}
