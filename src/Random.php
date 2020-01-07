<?php

namespace twelvet\utils;

/**
 * ============================================================================
 * TwelveT 工具箱
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * 随机生成类
 */

use twelvet\utils\entity\Facade;

class Random extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     *
     * @return String
     */
    protected static function getFacadeClass()
    {
        return 'Random';
    }
}
