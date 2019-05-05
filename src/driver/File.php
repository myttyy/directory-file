<?php
namespace myttyy\driver;

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
	
	/**
	 * 读取文件某行
	 *
	 * @param string $file
	 * @param integer $line
	 * @return string|null
	 */
	public function  getFileLine(string $file,int $line ):?string{
		$n = 0;
		$handle = fopen($fileName,'r');
		if ($handle) {
			while (!feof($handle)) {
					++$n;
					$out = fgets($handle, 4096*5);
					if($line==$n) break;
			}
			fclose($handle);
		}
		if( $line==$n) return $out;
		return "";
	}

}