<?php
namespace myttyy\driver;

class Directory{
    /**
     * 遍历目录
     *
     * @param string $dir
     * @return array
     */
	public function tree(string $dir ,int $max_level = 0,int $curr_level = 0):?array {
		$trees = [];
		if ( empty( $dir ) ) {
			return [];
        }
        $list = [];
        $dir .= substr($dir, -1) == '/' ? '' : '/';
		foreach ( glob( $dir . '*' ) as $id => $v ) {
            if ($max_level > 0 && $curr_level == $max_level) {
                return $trees;
            }
			$info              = pathinfo( $v );
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
            if(filetype( $v ) != 'file'){
                $list['subdirectory'] = $this->tree($v, $max_level, $curr_level + 1);
            }
            $trees[] = $list;
            $list = [];
		}
		return $trees;
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
     * @param string $dir
     * @return string
     */
    public function sizeFormatFielBytes(string $dir): string
    {
        $size = $this->size($dir);
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