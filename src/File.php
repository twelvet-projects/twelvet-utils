<?

namespace twelvet\utils;

/**
 * ============================================================================
 * TwelveT 工具箱
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * 文件操作类（所有路径请使用绝对路径，暂不支持相对）
 */

use twelvet\utils\entity\Facade;

class File extends Facade
{
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     *
     * @return String
     */
    protected static function getFacadeClass()
    {
        return 'File';
    }
}
