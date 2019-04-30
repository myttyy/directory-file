<?php
namespace myttyy\tools;

use myttyy\tools\driver\FilesFinder as Finder;

/**
 * 文件查找操作类
 * 提供类似著名的 nette/finder 包，查找目录下指定条件下的文件列表
 * 使用直观的API查找文件和目录
 * @author myttyy 1297942619@qq.com
 */

class FilesFinder
{
    private $link =null;

    public function __call( $method, $params ) {
		if ( is_null( $this->link ) ) {
			$this->link = new Finder();
		}
		if ( method_exists( $this->link, $method ) ) {
			return call_user_func_array( [ $this->link, $method ], $params );
		}
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(new Finder(), $name), $arguments);
    }
}