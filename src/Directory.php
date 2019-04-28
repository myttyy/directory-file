<?php
namespace myttyy\tools;

/**
 * 目录操作类
 * 提供目录创建目录、删除目录、复制目录、移动目录、获取指定目录下的树形结构、获取目录大小(可转为带kb等单位数值)
 * @author myttyy 1297942619@qq.com
 */

 class Directory{

    /**
     * 遍历目录
     *
     * @param string $dir
     * @return array
     */
	public function tree(string $dir ):array {
		$all = [];
		if ( empty( $dir ) ) {
			return $list;
        }
        $dir .= substr($dir, -1) == '/' ? '' : '/';
		foreach ( glob( $dir . '*' ) as $id => $v ) {
			$info                       = pathinfo( $v );
			$list['path']      = $v;
			$list['type']      = filetype( $v );
			$list['dirname']   = $info['dirname'];
			$list['basename']  = $info['basename'];
			$list['filename']  = $info['filename'];
			$list['extension'] = isset( $info['extension'] ) ? $info['extension'] : '';
			$list['filemtime'] = filemtime( $v );
			$list['fileatime'] = fileatime( $v );
            $list['size']      = is_file( $v ) ? filesize( $v ) : $this->size( $v );
            $list['sizeformat'] = $this->sizeFormatFielBytes( $list['size'] );
			$list['iswrite']   = is_writeable( $v );
            $list['isread']    = is_readable( $v );
            $info = [];
            $all[] = $list;
            if(is_dir($v)){
                $this->tree($v);
            }
		}
		return $list;
	}
    
    /**
     * 获取目录在小
     *
     * @param string $dir
     * @return integer
     */
	public function size(string $dir ):int {
		$s = 0;
		foreach ( glob( $dir . '/*' ) as $v ) {
			$s += is_file( $v ) ? filesize( $v ) : self::size( $v );
		}
		return $s;
    }

    /**
     * 返回文件大小.带单位
     *
     * @param int $size
     * @return string
     */
    public function sizeFormatFielBytes(int $size): string
    {
        $units = array(' B', ' KB', ' MB', ' GB', ' TB');
        for ($i = 0; $size >= 1024 && $i < 4; ++$i) {
            $size /= 1024;
        }
        return round($size, 2).$units[$i];
    } 
    
    /**
     * 删除目录
     *
     * @param string $dir
     * @return boolean
     */
	public function del(string $dir ):bool {
		if ( ! is_dir( $dir ) ) {
			return true;
		}
		foreach ( glob( $dir . "/*" ) as $v ) {
			is_dir( $v ) ? $this->del( $v ) : unlink( $v );
		}
		return rmdir( $dir );
    }
    
	/**
     * 创建目录
     *
     * @param string $dir 目录名称
     * @param int $auth 目录权限
     * @return boolean
     */
	public function create(string $dir,int $auth = 0755 ):bool {
		if ( ! empty( $dir ) ) {
			return is_dir( $dir ) or mkdir( $dir, $auth, true );
		}
    }
    
	/**
     * 复制目录
     *
     * @param string $old
     * @param string $new
     * @return void
     */
	public function copy(string $old,string $new ):bool {
		is_dir( $new ) or mkdir( $new, 0755, true );
		foreach ( glob( $old . '/*' ) as $v ) {
			$to = $new . '/' . basename( $v );
			is_file( $v ) ? copy( $v, $to ) : $this->copy( $v, $to );
		}
		return true;
	}
	
	/**
     * 移动目录
     *
     * @param string $old
     * @param string $new
     * @return boolean
     */
	public function move(string $old,string $new ):bool {
		if ( $this->copy( $old, $new ) ) {
			return $this->del( $old );
		}
	}
    
 }