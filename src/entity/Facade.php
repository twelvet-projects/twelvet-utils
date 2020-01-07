<?php

namespace twelvet\utils\entity;

use twelvet\utils\exception\UtilsException;

class Facade
{
    protected static $class;

    /**
     * 始终创建新的对象实例
     * @var bool
     */
    protected static $alwaysNewInstance;

    /**
     * 创建Facade实例
     * @static
     * @access protected
     * @param  string    $class          类名或标识
     * @param  array     $args           变量
     * @param  bool      $newInstance    是否每次创建新的实例
     * @return object
     */
    protected static function createFacade($class = '', $args = [], $newInstance = false)
    {
        // 是否已实现属性名称
        if (!static::$class) throw new UtilsException('Utils name not implemented', 500);
        // 是否需要每次获取新的对象
        if (static::$alwaysNewInstance) $newInstance = true;

        return Container::getInstance()->make(static::$class, $args, $newInstance);
    }
    
    /**
     * 调用指定的方法（PHP魔术方法）
     *
     * @param [type] $method
     * @param Array<String, Object> $params
     * @return mixed
     */
    public static function __callStatic(String $method, Array $params)
    {
        return call_user_func_array([
            // 得到Class
            static::createFacade(),
            // 调用method
            $method
        ], $params);
    }
}
