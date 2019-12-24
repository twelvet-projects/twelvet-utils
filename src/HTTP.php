<?php

namespace twelvet\utils;

/**
 * ============================================================================
 * TwelveT
 * 版权所有 twelvet.cn，并保留所有权利。
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * HTTP工具
 */

class HTTP
{
    /**
     * 发送一个POST请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return array
     */
    public static function post(String $url, array $params = [], array $options = [])
    {
        // 传参请求
        $request = self::request($url, $params, 'POST', $options);
        return $request;
    }

    /**
     * 发送一个GET请求
     * @param string $url     请求URL
     * @param array  $params  请求参数
     * @param array  $options 扩展参数
     * @return array
     */
    public static function get(String $url, array $params = [], array $options = [])
    {
        // 传参请求
        $request = self::request($url, $params, 'GET', $options);
        return $request;
    }


    public static function download(String $url, array $params = [], array $options = [])
    {
        return '下载成功';
    }

    /**
     * CURL发送Request请求,含POST和REQUEST
     * @param string $url     请求的链接
     * @param mixed  $params  传递的请求参数
     * @param string $method  请求的方法
     * @param mixed  $options CURL的设置参数
     * @return array
     */
    private static function request(String $url, array $params = [], String $method = 'POST', array $options = [])
    {
        // 转换大写POST，GET
        $method = strtoupper($method);
        // 获取地址协议
        $protocol = substr($url, 0, 5);
        // 将数组参数转换为字符串参数
        $query_string = is_array($params) ? http_build_query($params) : $params;
        // 初始化curl
        $CURL = curl_init();
        // 定义参数
        $curlParams = [];
        // 判断发送请求类型
        if ($method == 'GET') {
            // 设置url以及参数（允许提前设置参数）
            $geturl = $query_string ? $url . (stripos($url, "?") !== false ? "&" : "?") . $query_string : $url;
            // 设置url
            $curlParams[CURLOPT_URL] = $geturl;
        } else {
            // 设置url
            $curlParams[CURLOPT_URL] = $url;
            // 不是GET\POST时使用掉调用的方法
            if ($method == 'POST') {
                $curlParams[CURLOPT_POST] = true;
            } else {
                $curlParams[CURLOPT_CUSTOMREQUEST] = $method;
            }
            // 设置请求参数
            $curlParams[CURLOPT_POSTFIELDS] = $query_string;
        }
        // 禁止输出header信息
        $curlParams[CURLOPT_HEADER] = false;
        // 设置请求模拟头信息
        $curlParams[CURLOPT_USERAGENT] = "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.98 Safari/537.36";
        // 跟踪重定向
        $curlParams[CURLOPT_FOLLOWLOCATION] = true;
        // 获取的信息以文件流的形式返回，禁止直接输出
        $curlParams[CURLOPT_RETURNTRANSFER] = true;
        // 响应时间
        $curlParams[CURLOPT_CONNECTTIMEOUT] = 3;
        // 超时时间
        $curlParams[CURLOPT_TIMEOUT] = 3;

        // 禁用100-continue
        curl_setopt($CURL, CURLOPT_HTTPHEADER, array('Expect:'));
        // 判断是否为https协议
        if ($protocol == 'https') {
            // 跳过证书检查
            $curlParams[CURLOPT_SSL_VERIFYPEER] = false;
            // 从证书中检查SSL加密算法是否存在
            $curlParams[CURLOPT_SSL_VERIFYHOST] = false;
        }
        // 为cURL传输会话批量设置选项（合并数组，直接抛弃下标相同的后参数而不是覆盖）
        curl_setopt_array($CURL, (array) $options + $curlParams);
        // 接受数据
        $result = curl_exec($CURL);
        // 获取错误信息
        $err = curl_error($CURL);
        // 判断是否有错误
        if (false === $result || !empty($err)) {
            // 获取最后一次错误信息
            $errno = curl_errno($CURL);
            // 获取错误代码
            $info = curl_getinfo($CURL);
            // 关闭资源
            curl_close($CURL);
            return [
                'status'   => false,
                'errno' => $errno,
                'msg'   => $err,
                'info'  => $info,
            ];
        }
        // 关闭
        curl_close($CURL);
        return ['status' => true, 'msg' => $result];
    }

    /**
     * 异步发送一个请求
     * @param string $url    请求的链接
     * @param mixed  $params 请求的参数
     * @param string $method 请求的方法
     * @return boolean TRUE
     */
    public static function asyncRequest(String $url, $params = [], String $method = 'POST')
    {
        // 转换大写
        $method = strtoupper($method);
        // 仅允许POST\GET请求
        $method = $method == 'POST' ? 'POST' : 'GET';
        // 是否数组参数
        if (is_array($params)) {
            // 定义结果
            $post_params = [];
            // 遍历参数
            foreach ($params as $k => &$v) {
                // 判断键值是否为数组（分割为字符串）
                if (is_array($v))  $v = implode(',', $v);
                // 转换参数
                $post_params[] = $k . '=' . urlencode($v);
            }
            // 以&合并参数和字符串
            $post_string = implode('&', $post_params);
        } else {
            // 赋值参数
            $post_string = $params;
        }
        // 解析字符串中的地址
        $parts = parse_url($url);
        // 判断是否为GET请求以及是否存在参数
        if ($method == 'GET' && $post_string) {
            // 判断是否已设置参数（有拼接参数）
            $parts['query'] = isset($parts['query']) ? $parts['query'] . '&' . $post_string : $post_string;
            // 清空参数
            $post_string = '';
        }
        // 判断是否已设置参数（无则清空）
        $parts['query'] = isset($parts['query']) ? '?' . $parts['query'] : '';
        // 发送socket请求,获得连接句柄
        $fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 3);
        // 连接失败立即返回false
        if (!$fp)  return false;

        //设置超时时间
        stream_set_timeout($fp, 3);
        // 设置头信息
        $out = "{$method} {$parts['path']}{$parts['query']} HTTP/1.1\r\n";
        $out .= "Host: {$parts['host']}\r\n";
        $out .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out .= "Content-Length: " . strlen($post_string) . "\r\n";
        $out .= "Connection: Close\r\n\r\n";
        if ($post_string !== '') {
            // 参数不为空继续拼接
            $out .= $post_string;
        }
        // 读取资源
        fwrite($fp, $out);
        // 关闭资源
        fclose($fp);
        return true;
    }

    /**
     * 发送文件到客户端
     * @param string $file
     * @param bool   $exitAfterSend 是否立即退出程序
     */
    public static function toBrowser($file, $exitAfterSend = true)
    {
        // 判断文件是否存在以及可读
        if (file_exists($file) && is_readable($file)) {
            // 设置头部信息
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment;filename = ' . basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check = 0, pre-check = 0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            // 清除输出缓存
            ob_clean();
            // 将缓冲数据发送给予客户端
            flush();
            // 读取文件
            readfile($file);
            // 关闭资源
            unlink($file);
            // 是否需要关闭程序
            if ($exitAfterSend)   exit;
        }
    }
}
