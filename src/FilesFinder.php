<?php
namespace myttyy\tools;

/**
 * 文件查找操作类
 * 提供类似著名的 nette/finder 包，查找目录下指定条件下的文件列表
 * 使用直观的API查找文件和目录
 * @author myttyy 1297942619@qq.com
 */

 class FilesFinder
 {
    // 目标目录地址
    private $paths = [];
    // 递归层数
    private $maxDepth = -1;
    // 查找结果
    private $list = [];
    // 匹配格式
    private $pattern = ["/*"];
    // 仅查找文件
    private $onlyFindFile = false;
    // 返回结果无文件详情
    private $notFilesInfo = false;
    // 是否已执行查询
    private $isSelected = false;
    // 常用文本格式
    private $text = [];
    // 常用图片格式
    private $images = [];
    // 常用视频格式
    private $video = [];


    /**
     * 在指定目录(文件可存在子目录)递归查找某个完整文件名的文件信息
     *
     * @param string|array $pattern
     * @return self
     */
    public function find(...$pattern):self{
        $this->pattern = $pattern && is_array($pattern[0]) ? $pattern[0] : $pattern;
        return $this;
    }

    /**
    * 在指定目录查找指定类型或者文件名带特定字符的文件列表
    *
    * @param string|array $pattern 支持正则 eg: *.log 、 '*[0-9]*.txt 、'*[0-9]* 等等,支持一位数组
    * @return self FilesFinder对象
    */
    public function findFiles(...$pattern):self
    {
        $this->onlyFindFile = true;
        $this->pattern = $pattern && is_array($pattern[0]) ? $pattern[0] : $pattern;
        return $this;
    }

    /**
     * notFilesInfo 结果只生成文件名的一维数组
     *
     * @param boolean $notFilesInfo
     * @return self FilesFinder对象
     */
    public function notFilesInfo():self{
        $this->notFilesInfo = true;
        return $this;
    }

    /**
     * 不递归查找,只在当前目录查找，支持一维数组
     *
     * @param string|array $paths
     * @return self
     */
    public function in(...$paths):self{
        $this->maxDepth = 0;
		return $this->from(...$paths);
    }
    
    /**
     * 递归查找，包含子目录查找，支持一维数组
     *
     * @param array|array $paths
     * @return self
     */
    public function from(...$paths):self{
        $this->paths = is_array($paths[0]) ? $paths[0] : $paths;
        if($this->isSelected){
            $this->select();
            $this->isSelected = false;
        }
		return $this;
    }

    /// 以下为返回结果的方法

    /**
     * 执行搜索
     *
     * @param [type] $paths
     * @param [type] $pattern
     * @return array
     */
    public function select($pattern=null,$paths=null):?array{
        $this->isSelected = true;
        if(!empty($pattern)){
            is_array($pattern) ? $this->pattern = $pattern : $this->pattern[0] = $pattern;
        }

        if(!empty($paths)){
            is_array($paths) ? $this->paths = $paths : $this->paths[0] = $paths;
        }

        if(empty($this->paths)){
            return [];
        }

        // 先遍历出所有目录
        if($this->maxDepth !== 0){
            $dirInfo = [];
            foreach ($this->paths as $k => $v) {
                $dirInfo = array_merge($dirInfo, $this->folderList($v));
            }
            $this->paths = array_merge($this->paths, $dirInfo);
        }
        
        $list = [];
        foreach ($this->pattern as $index => $item ) {
            foreach ($this->paths as $k => $path) {
               $path .= substr($path, -1) == '/' ? '' : '/';
               foreach (glob($path.$item) as $v) {
                    if($this->notFilesInfo){
                        $this->list[] = $v;
                    }else {
                        $info              = pathinfo( $v );
                        $list['path']      = $v;
                        $list['type']      = filetype( $v );
                        $list['dirname']   = $info['dirname'];
                        $list['basename']  = $info['basename'];
                        $list['filename']  = $info['filename'];
                        $list['extension'] = isset( $info['extension'] ) ? $info['extension'] : '';
                        $list['filemtime'] = filemtime( $v );
                        $list['fileatime'] = fileatime( $v );
                        $list['size']      = is_file( $v ) ? filesize( $v ) : $this->getSize( $v );
                        $list['iswrite']   = is_writeable( $v );
                        $list['isread']    = is_readable( $v );
                        $this->list[] = $list;
                        $info = [];
                        $list = [];
                    }
                }
            }
        }

        return $this->list;
    }

    /**
     * 包含查找
     *
     * @param string ...$fileName
     * @return array
     */
    public function exclude(string ...$fileName):?array
    {
        if(!$this->isSelected){
            $this->select();
        }
        $list = [];
        return $list;
    }
    /**
     * 排除查找
     *
     * @param string ...$fileName
     * @return array
     */
    public function filter(string ...$fileName):?array
    {
        if(empty($this->list)){
            $this->select();
        }
        $list = [];
        return $list;
    }

    /**
     * 按照文件修改时间查找
     *
     * @param string $operator 比较方式 
     * @param string $date
     * @return array|null
     */
    public function date(string $operator,string $date = null): ?array{
        if(empty($this->list)){
            $this->select();
        }
        $list = [];
        return $list;
    }

    /**
     * 按文件大小筛选
     *
     * @param string $operator 比较方式
     * @param string $date
     * @return array|null
     */
    public function size(string $operator,string $date = null): ?array{
        if(empty($this->list)){
            $this->select();
        }
        $list = [];
        return $list;
    }

    /**
     * 返回上面查找结果的首个
     *
     * @return array
     */
    public function childFirst():array{
        return $this->list[0] ?? [];
    }
    
    /**
     * 返回上面查找合法的文件数量
     * 
     *
     * @return int
     */
    public function count():int{
        return count($this->list);
    }

    /**
     * 首次遍历出所有的文件夹
     *
     * @param string $dir
     * @return void
     */
    private function folderList(string $dir){
        $dir .= substr($dir, -1) == '/' ? '' : '/';
        $dirInfo = array();
        foreach (glob($dir.'*') as $v) {
          if(is_dir($v)){
            $dirInfo[] = $v; 
            $dirInfo = array_merge($dirInfo, $this->folderList($v));
          }
        }
        return $dirInfo;
   }

    /**
     * 获取目录在小
     *
     * @param string $dir
     * @return integer
     */
	public function getSize(string $dir ):int {
		$s = 0;
		foreach ( glob( $dir . '/*' ) as $v ) {
			$s += is_file( $v ) ? filesize( $v ) : self::size( $v );
		}
		return $s;
    }
 }
