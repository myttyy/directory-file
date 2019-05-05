
### >=PHP7.1适用的目录&文件工具库类


### composer 安装

```php
composer require myttyy/directory_file
```

### Dome

```php
<?php

namespace app\api\controller;

use think\facade\Env;

use myttyy\tools\FilesFinder;
use myttyy\tools\Directory;
use myttyy\tools\File;

class Test
{
    public function index(){

        $list1 = (new FilesFinder())->select(["*[0-9]*.log"],[Env::get('runtime_path')])->select();
        var_dump($list1->toArray());
        $list2 = (new FilesFinder())->findFiles("*[0-9]*.log")->from(Env::get('runtime_path'))->date(">=2019-04-29 18:07:19");
        var_dump($list2->toArray());
        $list3 = (new FilesFinder())->findFiles("*[0-9]*.log")->from(Env::get('runtime_path'))->size('>2Mb');
        var_dump($list3->toArray());
        $list4 = (new FilesFinder())->select(["*.log"],[Env::get('runtime_path')])->exclude("cli.log");
        foreach ($list4 as $key => $value) {
          var_dump($value);
        }


        $tree = Directory::tree(Env::get('runtime_path'));
        var_dump($tree);


        $line = File::getFileLine(Env::get('runtime_path')."/log/201804/30.log",0);
        var_dump($line);
    }
}
```

### API文档

- 目录创建 

    ```php
    Directory::create(string $dir,int $auth = 0755 )
    ```

- 目录复制 

    ```php
    Directory::copy(string $old,string $new )
    ```
    
- 目录剪贴 

    ```php
    Directory::move(string $old,string $new )
    ```
    
- 目录删除

    ```php
     Directory::del(string $dir )
    ```
    
- 统计目录大小 

    ```php
    Directory::size(string $dir )
    ```
    
- 统计目录大小，返回带单位容量信息 

    ```php
    Directory::sizeFormatFielBytes(string $dir )
    ```

- 文件复制 

    ```php
    File::copyFile(string $file,int $auth = 0755 )
    ```
    
- 文件剪贴 

    ```php
    File::moveFile(string $file,int $auth = 0755 )
    ```
    
- 文件删除 

    ```php
    File::delFile(string $file,int $auth = 0755 )
    ```
    
- 读取文件某行 

    ```php
    File::getFileLine(string $file,int $line )
    ```

- 查找文件

> FilesFinder下列方法都可以链式调用，返回FilesFinder对象可以使用 foreach遍历

```json
[
    {
        "path":"/data/php/pack.xxxx.com/runtime/log",
        "type":"dir",
        "dirname":"/data/php/pack.xxxx.com/runtime",
        "basename":"log",
        "filename":"log",
        "extension":"",
        "filemtime":1556533016,
        "filectime":1556533018,
        "size":24709,
        "iswrite":true,
        "isread":true,
    },
    {
        "path":"/data/php/pack.xxxx.com/runtime/route_list.php",
        "type":"file",
        "dirname":"/data/php/pack.xxxx.com/runtime",
        "basename":"route_list.php",
        "filename":"route_list",
        "extension":"php",
        "filemtime":1556071467,
        "fileatime":1556071467,
        "size":3297,
        "iswrite":true,
        "isread":true
    },
]
```

- 指定目录按正则等格式查找文件 

    - 基础方法 FilesFinder::select($pattern=null,$paths=null)

    ```php
    File::tree(string $dir ,int $max_level = 0)
    ```

    - 基础方法,查找目录所有内容

    ```php
    FilesFinder::find(...$pattern) //
    ```

    - 基础方法,查找目录所有文件

    ```php
    FilesFinder::findFiles(...$pattern);
    ```

    - 查找结果只含文件名的一维数组  

    ```php
    FilesFinder::notFilesInfo()
    ```

    - 递归设置递归层数 
        
    ```php
    FilesFinder::maxDepth(int $maxDepth);
    ```

    - 一级目录遍历 (不递归,仅查找当前一级目录)
        
    ```php
    // $paths:目录路径 支持一维数组 in(["/public","/log"]) 也支持不定参数 in("/public","/log");
    FilesFinder::in(...$paths);
    ```

    - 递归遍历

    ```php
    // $paths:目录路径 支持一维数组 in(["/public","/log"]) 也支持不定参数 in("/public","/log");
    FilesFinder::from(...$paths);
    ```

    - 从递归遍历中排除路径含有指定字符的文件信息 

    ```php
    // eg: FilesFinder::exclude("abc") 将排除文件路径含有abc的文件
    FilesFinder::exclude();

    ```

    - 按照文件大小查找 

    ```php
    // eg: FilesFinder::size(">=100kb") 比较符号键下表
    FilesFinder::size(string $operator); 
    ```

    - 按照文件filectime(范指文件创建)时间查找

    ```php
    // eg: FilesFinder::date(">=2019-04-30 16:46:57")
    FilesFinder::date(string $operator);
    ```

- FilesFinder下列方法不可以链式调用

    ```php
        FilesFinder::toArray()
    ```

- FilesFinder::size()、FilesFinder::date() 支持的比较条件

| 标识符号 | 比较意义 |
| ------ | ------ | 
| > | 同PHP运算符原意 |
| >= | 同PHP运算符原意 |
| < | 同PHP运算符原意 |
| <= | 同PHP运算符原意 |
| ! | 不等于 |
| != | 不等于 |
| <> | 不等于 |
| = | 等于 |
| == | 等于 |

- 支持使用Facade模式可静态调用