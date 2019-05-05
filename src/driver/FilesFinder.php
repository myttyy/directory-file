<?php
namespace myttyy\driver;

use myttyy\driver\Directory;

 class FilesFinder implements \IteratorAggregate
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
    private $onlyChildFirst = false;

    private $directory;

    // 常用文本格式
    private $text = [];
    // 常用图片格式
    private $images = [];
    // 常用视频格式
    private $video = [];

    /**
     * 初始化方法
     */
    public function __construct(){
	  $this->directory = new Directory();
    }
    
    /**
     * 实现接口迭代器
     *
     * @return void
     */
    public function getIterator() {
        if($this->onlyChildFirst){
            return new \ArrayIterator($this->list[0] ?: []);
        }
        return new \ArrayIterator($this->list);
    }

    /**
     * 搜索结果转为数组
     *
     * @return void
     */
    public function toArray():?array{
       return $this->list;
    }

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
     * 设置递归层数防止无限递归
     *
     * @param integer $maxDepth
     * @return self
     */
    public function maxDepth(int $maxDepth):self{
        $this->maxDepth = $maxDepth;
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
     * @return self
     */
    public function select($pattern=null,$paths=null):self{
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
        if($this->maxDepth != 0){
            $dirInfo = [];
            foreach ($this->paths as $k => $v) {
                if($k == $this->maxDepth){
                   continue;
                }
                $dirInfo = array_merge($dirInfo, $this->folderList($v));
            }
            $this->paths = array_merge($this->paths, $dirInfo);
        }

        $list = [];
        foreach ($this->pattern as $index => $item ) {
            foreach ($this->paths as $k => $path) {
              $this->glob($path,$item);
            }
        }
        return $this;
    }

     /**
     * 从递归遍历中排除路径含有指定字符的文件信息
     *
     * @param string ...$mask
     * @return self
     */
    public function exclude(string ...$masks):self
    {
        if(!$this->isSelected){
            $this->select();
        }
        $list = [];
        $masks = $masks && is_array($masks[0]) ? $masks[0] : $masks;
        $pattern = $this->buildPattern($masks);
        if(!empty($this->list)){
            foreach ($this->list as $k => $v) {
                if(!preg_match($pattern, '/' . strtr($v['path'], '\\', '/'))){
                   $list[] = $v;
                }
            }
        }
        $this->list = $list;
        return $this;
    }

    /**
     * 按照文件修改时间查找
     *
     * @param string $operator 比较内容
     * @return self
     */
    public function date(string $operator): self{
        if(!$this->isSelected){
            $this->select();
        }
        $list = [];

        if(empty($size)){
            return $this;
        }
        if (!preg_match('#^(?:([=<>!]=?|<>)\s*)?(.+)\z#i', $operator, $matches)) {
            return $this;
        }
        [, $operator, $date] = $matches;
        $operator = $operator ?: '=';

        if(!empty($this->list)){
            foreach ($this->list as $k => $v) {
                if($this->compare($v['filectime'],$operator,$size)){
                   $list[] = $v;
                }
            }
       }

        $this->list = $list;
        return $this;
    }

    /**
     * 按文件大小筛选
     *
     * @param string $operator
     * @param string|null $size
     * @return array|null
     */
    public function size(string $operator): self{
        if(!$this->isSelected){
            $this->select();
        }
        $list = [];
        if(empty($size)){
            return $this;
        }
        if (!preg_match('#^(?:([=<>!]=?|<>)\s*)?((?:\d*\.)?\d+)\s*(K|M|G|)B?\z#i', $operator, $matches)) {
            // 报异常
            return $this;
        }
        [, $operator, $size, $unit] = $matches;
        static $units = ['' => 1, 'k' => 1e3, 'm' => 1e6, 'g' => 1e9];
        $size *= $units[strtolower($unit)];
        $operator = $operator ?: '=';
            
        if(!empty($this->list)){
             foreach ($this->list as $k => $v) {
                 if($this->compare($v['size'],$operator,$size)){
                    $list[] = $v;
                 }
             }
        }
        $this->list = $list;
        return $this;
    }

    /**
     * 返回上面查找结果的首个
     *
     * @return array
     */
    public function childFirst():array{
        if(!$this->isSelected){
            $this->select();
        }
        $this->onlyChildFirst = true;
        return $this;
    }
    
    /**
     * Undocumented function
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
     * 遍历目录
     *
     * @param [type] $path
     * @param [type] $pattern
     * @return void
     */
    private function glob($path,$pattern){
        $path .= substr($path, -1) == '/' ? '' : '/';
        foreach (glob($path.$pattern) as $v) {
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
                $list['filectime'] = filectime( $v );
                $list['size']      = is_file( $v ) ? filesize( $v ) : $this->directory->size( $v );
                $list['iswrite']   = is_writeable( $v );
                $list['isread']    = is_readable( $v );
                $this->list[] = $list;
                $info = [];
                $list = [];
            }
        }
    }

    /**
     * Compares two values.
     *
     * @param [type] $l 对比值
     * @param string $operator 比较符号
     * @param [type] $r 输入值
     * @return boolean
     */
	public static function compare($l, string $operator, $r): bool
	{
		switch ($operator) {
			case '>':
				return $l > $r;
			case '>=':
				return $l >= $r;
			case '<':
				return $l < $r;
			case '<=':
				return $l <= $r;
			case '=':
			case '==':
				return $l == $r;
			case '!':
			case '!=':
			case '<>':
				return $l != $r;
			default:
                return false;
		}
    }
    
    /**
	 * Converts Finder pattern to regular expression.
	 */
	private function buildPattern(array $masks): ?string
	{
		$pattern = [];
		foreach ($masks as $mask) {
			$mask = rtrim(strtr($mask, '\\', '/'), '/');
			$prefix = '';
			if ($mask === '') {
				continue;
			} elseif ($mask === '*') {
				return null;
			} elseif ($mask[0] === '/') { // absolute fixing
				$mask = ltrim($mask, '/');
				$prefix = '(?<=^/)';
			}
			$pattern[] = $prefix . strtr(preg_quote($mask, '#'),
				['\*\*' => '.*', '\*' => '[^/]*', '\?' => '[^/]', '\[\!' => '[^', '\[' => '[', '\]' => ']', '\-' => '-']);
		}
		return $pattern ? '/(' . implode('|', $pattern) . ')/' : null;
	}
 }
