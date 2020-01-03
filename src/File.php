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

use twelvet\utils\exception\UtilsException;
use ZipArchive;

class File
{
    /**
     * 创建文件并写入信息
     *
     * @param String $dirName
     * @param String $resource
     * @return void
     */
    public static function touch(String $dirName, String $resource)
    {
    }

    /**
     * 创建文件夹
     *
     * @param String $dirName
     * @return void
     */
    public static function mkdir(String $dirName)
    {
    }

    /**
     * 移动文件
     *
     * @param String $dirName
     * @param String $newDirName
     * @return void
     */
    public static function mv(String $dirName, String $newDirName)
    {
    }

    /**
     * 复制文件
     *
     * @return void
     */
    public static function cp(String $dirName, String $newDirName)
    {
    }

    /**
     * 删除文件
     *
     * @param String $dirName 
     * @return void
     */
    public static function rm(String $dirName)
    {
        if (!file_exists($dirName)) {
            throw new UtilsException('无法寻找:' . $dirName, 500);
        }
        try {
            //判断是否为一个目录
            if (is_dir($dirName)) {
                //使用递归计算
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($dirName, \RecursiveDirectoryIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::CHILD_FIRST
                );
                //遍历删除目录中的文件以及目录
                foreach ($files as $fileinfo) {
                    //判断是否是目录
                    $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                    $todo($fileinfo->getRealPath());
                }
            } else if (is_file($dirName)) {
                unlink($dirName);
            }
        } catch (\Exception $e) {
            throw new UtilsException($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 列出一个文件夹中所有的文件
     *
     * @param String $path
     * @param boolean $child
     * @return void
     */
    public static function ll(String $path, bool $child = true)
    {
    }

    /**
     * 文件解压
     *
     * @param String $dirName
     * @param String $unPathName
     * @return void
     */
    public static function unzip(String $dirName, String $unPathName)
    {
        // 判断是否存在zip类
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            // 打开文件
            if ($zip->open($dirName) !== true) {
                throw new UtilsException('Unable to open the zip file', 500);
            }
            // 开始解压
            if (!$zip->extractTo($unPathName)) {
                // 释放
                $zip->close();
                throw new UtilsException('Unable to open the zip file', 500);
            }
            // 释放
            $zip->close();
            return $unPathName;
        }
        throw new UtilsException('无法执行解压操作，请确保ZipArchive安装正确', 500);
    }
}
