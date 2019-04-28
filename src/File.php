<?php
namespace myttyy\tools;

/**
 * 目录操作类
 * 包含文件创建、删除、复制、获取指定文件的指定行内容、获取文件大小
 * @author myttyy 1297942619@qq.com
 */

class File{

	/**
     * 删除文件
     *
     * @param string $file 文件文件地址
     * @return boolean
     */
	public function delFile(string $file ):bool {
		if ( is_file( $file ) ) {
			return unlink( $file );
		}
		return true;
    }
    
    /**
     * 复制文件
     *
     * @param string $file 原始文件文件地址
     * @param string $to 目标文件地址
     * @return boolean
     */
	public function copyFile(string $file,string $to ):bool {
		if ( ! is_file( $file ) ) {
			return false;
		}
		//创建目录
		$this->create( dirname( $to ) );
		return copy( $file, $to );
    }
    
    /**
	 * 移动文件
	 *
	 * @param string $file 文件
	 * @param string $dir 目录
	 *
	 * @return bool
	 */
	public function moveFile(string $file,string $dir ):bool {
		is_dir( $dir ) or mkdir( $dir, 0755, true );
		if ( is_file( $file ) && is_dir( $dir ) ) {
			copy( $file, $dir . '/' . basename( $file ) );
			return unlink( $file );
		}
    }
    

}