<?php
namespace myttyy;

use myttyy\driver\Directory as Dir;

/**
 * 目录操作类
 * 提供目录创建目录、删除目录、复制目录、移动目录、获取指定目录下的树形结构、获取目录大小(可转为带kb等单位数值)
 * @author myttyy 1297942619@qq.com
 */

class Directory
{
    private $link =null;

    public function __call( $method, $params ) {
		if ( is_null( $this->link ) ) {
			$this->link = new Dir();
		}
		if ( method_exists( $this->link, $method ) ) {
			return call_user_func_array( [ $this->link, $method ], $params );
		}
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array(array(new Dir(), $name), $arguments);
    }
}