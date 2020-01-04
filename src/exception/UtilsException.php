<?php

namespace twelvet\utils\exception;

/**
 * ============================================================================
 * TwelveT 工具箱
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * 工具箱异常处理类
 */

use twelvet\utils\exception\Exception;

class UtilsException extends Exception
{
    /**
     * 构造方法传参
     *
     * @param [type] $message
     * @param [type] $code
     * @param string $data
     */
    public function __construct($message, $code, $data = '')
    {
        $this->message  = $message;
        $this->code     = $code;
        $this->data     = $data;
    }
}
