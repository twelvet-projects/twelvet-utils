<?

/**
 * ============================================================================
 * TwelveT 工具箱
 * 官网地址:www.twelvet.cn
 * QQ:2471835953
 * ============================================================================
 * 文件操作类
 */

class File
{
    /**
     * 创建文件并写入信息
     *
     * @param String $pathName
     * @param String $resource
     * @return void
     */
    public function touch(String $pathName, String $resource)
    {
    }

    /**
     * 创建文件夹
     *
     * @param String $pathName
     * @return void
     */
    public function mkdir(String $pathName)
    {
    }

    /**
     * 移动文件
     *
     * @param String $pathName
     * @param String $newPathName
     * @return void
     */
    public function mv(String $pathName, String $newPathName)
    {
    }

    /**
     * 复制文件
     *
     * @return void
     */
    public function cp(String $pathName, String $newPathName)
    {
    }

    /**
     * 删除文件
     *
     * @return void
     */
    public function rm(String $pathName)
    {
    }

    /**
     * 列出一个文件夹中所有的文件
     *
     * @param String $path
     * @param boolean $child
     * @return void
     */
    public function ll(String $path, bool $child = true)
    {
    }

    /**
     * 文件解压
     *
     * @param String $pathName
     * @param String $unPathName
     * @return void
     */
    public function unzip(String $pathName, String $unPathName)
    {
    }
}
