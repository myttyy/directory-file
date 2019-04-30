<?php
namespace myttyy\tools;

use myttyy\tools\driver\File as FileDriver;

/**
 * 文件操作类
 * 包含文件创建、删除、复制、获取指定文件的指定行内容、获取文件大小
 * @author myttyy 1297942619@qq.com
 */

class File
{
    private $link =null;

    public function __call( $method, $params ) {
		if ( is_null( $this->link ) ) {
			$this->link = new FileDriver();
		}
		if ( method_exists( $this->link, $method ) ) {
			return call_user_func_array( [ $this->link, $method ], $params );
		}
    }
    
    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(new FileDriver(), $name), $arguments);
    }
}