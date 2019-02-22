# pol (php optional library)

## 概述
> - 说明：php可选基础类库(目标是:作为yaf的补充类库使用，也可独立使用)
> - 宗旨：轻量级、模块化、高度解耦
> - 尽可能的接近原生的php，忌过度封装。

##目录结构

    ├── Pol
    │   ├── AutoLoader.php
    │   ├── Db
    │   │   ├── Assist.php
    │   │   ├── Pdo.php
    │   │   └── Statement.php
    │   ├── Exception
    │   │   ├── DiFailed.php
    │   │   ├── EventFailed.php
    │   │   ├── LoggerFailed.php
    │   │   ├── McFailed.php
    │   │   ├── PdoFailed.php
    │   │   ├── RedisFailed.php
    │   │   └── ValidateFailed.php
    │   ├── Log
    │   │   ├── Logger.php
    │   │   ├── Monitor.php
    │   │   ├── PolLog.php
    │   │   └── PolMonitor.php
    │   ├── Manager
    │   │   ├── Di.php
    │   │   └── Event.php
    │   ├── Ns
    │   │   ├── Memcached.php
    │   │   └── Redis.php
    │   ├── Queue
    │   │   └── Beanstalkd.php
    │   ├── README.md
    │   ├── Rpc
    │   │   └── Http
    │   └── Validate
    │       ├── Datalist.php
    │       ├── Enumer.php
    │       ├── Floater.php
    │       ├── Inter.php
    │       ├── Stringer.php
    │       └── Validator.php

## 配置
### nginx的配置

    server {
        listen       8000;
        server_name  localhost;
    
        error_page  404              /404.html;
    
        # redirect server error pages to the static page /50x.html
        error_page   500 502 503 504  /50x.html;
        location = /50x.html {
            root   html;
        }
    
        if (!-e $request_filename) {
            rewrite ^/(.*)  /index.php/$1 last;
        }
    
        location / {
            root           /Users/luweijun/opt/base/phplib/phplib/example/public;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    
        location ~ \.php$ {
            root           /Users/luweijun/opt/base/phplib/phplib/example/public;
            fastcgi_pass   127.0.0.1:9000;
            fastcgi_index  index.php;
            fastcgi_param  SCRIPT_FILENAME  $document_root$fastcgi_script_name;
            include        fastcgi_params;
        }
    }

### php.ini

    [yaf]
    extension=yaf.so
    #yaf.library=""
    yaf.cache_config=1
    yaf.name_separator='_'
    yaf.use_namespace=1
    yaf.use_spl_autoload=1

## 使用说明

    1、基于 yaf 框架 
        application.ini中设置系统类库路径到pol所在目录
        application.system.library=
    
    2、非yaf框架
        require_once \Pol\AutoLoader.php 相当于引入Pol类库


**1. Di 依赖注入**

    单例模式:
    Di::setShared('redis',function() use ($config){
        return new \Pol\Ns\Redis($config);
    });

    普通模式:
    DI::set('redis',function() use ($config){
        return new \Pol\Ns\Redis($config);
    });

**2. Event 事件监听**

    Di::set('event',function() {
        return new \Pol\Manager\Events();
    });
    $event = Di::get('event');
    //添加事件
    $event->attach('name',function () {
        return 'event select is fired!';
    });
    //触发事件
    $event->fire('name');
    //回调消费  callback 必须为匿名函数
    $event->fire('select',function($data){
        //do something
    });

**3. Db 数据库操作**

    //配置示例
    $conf = array(
        'master' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'dbname'   => '',
            'charset'  => 'utf8',
            'options'  => array(\PDO::ATTR_PERSISTENT=>true) //长连接
        ),
        'slave' => array(
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => 'root',
            'password' => '',
            'dbname'   => '',
            'charset'  => 'utf8',
            'options'  => array(\PDO::ATTR_PERSISTENT=>true)
        ),
    );
    
    Di::setShared('db',function () use ($conf){
        return new \Pol\Db\Pdo($conf);
    });
    $db = Di::get('db');

    //使用示例:
    DI::setShared('mysql',function() use ($config){
        return new \Pol\Db\Pdo($config);
    });
    $db = Di::get('mysql');
    $db->forceMaster()->prepare($sql);

    //查询
    $sql = 'SELECT * FROM `cc_account_user` WHERE `id` = :id';
    try{
        $sth = $db->prepare($sql);
        $sth->execute(array(':id'=>1));
        $result = $sth->fetch();
    } catch ( \Exception $e ) {
        //do something
    }

    /*  使用一个数组的值执行一条含有 IN 子句的预处理语句 */
    $params = array(1, 1274738, 1274739, 1274740);
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $placeHolders = implode(',', array_fill(0, count($params), '?'));
    $sql = 'SELECT * FROM `cc_account_user` WHERE `id` in ('.$placeHolders.')';
    try{
        $sth = $db->prepare($sql);
        $sth->execute($params);
        $result = $sth->fetchAll();
    } catch ( \Exception $e ) {
        //do something
    }
 
    //事务
    $sql = 'UPDATE `cc_account_user` SET `gender` = 4 WHERE `id` = ?';
    try{
        $db->beginTransaction();
        $result = $db->prepare($sql);
        $param = array(1);
        $result->execute($param);
        if ( $result !== false ) {
            $db->commit();
        } else {
           $db->rollBack();
        }
    } catch (\Exception $e ) {
        //do something
    }

    //assist类使用
    setAdd insert操作前 准备 sql 及 绑定参数 
    demo:
    $data = $db->assist()->setTable()->setAdd(array('user_id' => 123,'password' => 'test'));
    print_r($data);
    $sth = $db->prepare($data['sql']);
    $result = $sth->execute($data['bindings']);
            
    Array
    (
        [sql] => INSERT INTO `zy_user` (`user_id`,`password`) VALUES (:user_id,:password)
        [bindings] => Array
        (
            [:user_id] => 123
            [:password] => test
        )
      )
      
      setSave update操作前 准备 sql 及 绑定参数         
      demo:
      $data = $db->assist()->setTable()->setSave(array('id'=>1,'password' => 'test'), array('user_id'=>1));
      print_r($data);
      Array
      (
           [sql] => UPDATE `zy_user` SET `id` = :id , `password` = :password WHERE `user_id` = :user_id AND `id` = :id
           [bindings] => Array
               (
                    [:user_id] => 1
                    [:id] => 1
                    [:password] => test
                )
       )

**4. NoSQL**

    demo:
        //配置
        DI::setShared('redis',function() use ($redisConfig){
            return \Pol\Ns\Redis($redisConfig);
        });

        $objRedis = DI::get('redis');
        $key = 'xxx';
        
        try {
            $value = $objRedis->get($key);
            //连接失败则抛出异常~
        } catch ( \Exception $e ) {
            //do something
        }

**5. Rpc 调用协议**

    http调用示例
    DI::setShared('httpClient',function(){
        return new \Pol\Rpc\Http\Client();
    });
    $obj = DI::get('httpClient');
    $url = '';
    $result = $obj->get($url)->execute();
            
    if ( $result['status'] == 0 ) {
     //http_code == 200
    $data = $result['data'];//返回数据，格式未做处理
    } else {
        //http_code != 200
    }

    http批量并发调用示例
    $multi = new \Pol\Rpc\Http\Multi();
    
    $objClient = new \Pol\Rpc\Http\Client();
    $objA = $objClient->get('', array())
    
    $objClient = new \Pol\Rpc\Http\Client();
    $objB = $objClient->post('', array());
    
    $multi->register('aa', $objA)->register('bb', $objB);
    $ret = $multi->execute();

**6. Log**

    


            

**7. 参数校验**

### 配置示例
    protected $_paramInfos = array(
        //签名
        'sign' => array(
            'string',
            'widthMin,10',
            \Pol\Validate\Validator::NEED,
            \Pol\Validate\Validator::RIGHT
        )
    );

### 通用配置
    
    空值限制选项：
    
    /**
     * 空值限制选项：可以为null，不使用默认值
     */
    const OPT_NO_DEFAULT = 1;
    
    /**
     * 空值限制选项：可以为null，使用默认值
     */
    const OPT_USE_DEFAULT = 2;
    
    /**
     * 空值限制选项：不可以为null
     */
    const NEED = 3;
    
    验证限制选项：
    
    /**
     * 验证限制选项：可以错误，不使用默认值
     */
    const WRONG_NO_DEFAULT = 1;
    
    /**
     * 验证限制选项：可以错误，使用默认值
     */
    const WRONG_USE_DEFAULT = 2;
    
    /**
     * 验证限制选项：不可以错误
     */
    const RIGHT = 3;
    
    配置验证规则：
    
    如"max,5;min,-3;"
    
### int类型的规则
    basic
    min,1
    max,20
    range,1,20
    len,1   len,1,5
    
### string类型的规则
    basic/safechars //验证是否仅包含安全字符（字母、数字、运算符、标点符号、回车、换行、删除、tab）
    printable //是否仅包含可打印字符
    min //验证字符串长度是否小于等于某值
    max //证字符串长度是否大于等于某值
    widthMax //验证字符串长度是否小于等于某值（支持宽字符）
    widthMin //验证字符串长度是否大于等于某值（支持宽字符）
    preg //验证是否匹配某正则表达式
    charslist //验证是否是由指定的字符列表的字符组成
    num //验证是否是数字
    alnum //验证是否只包含字母或数字
    alpha //验证是否只包含字母
    lower //验证是否只包含小写字母
    upper //验证是否只包含大写字母
    hex //验证是否符合十六进制字符串规则
    json //验证是否为JSON字符串
    
### float类型的验证规则
    basic
    min
    max
    range
    
### enum类型验证
    enum //验证数据值是否在枚举列表中
    
### datalist验证
    max //验证数据集元素容量是否小于等于某值
    min  //验证数据集元素容量是否大于等于某值
    unique //验证数据集元素是否唯一
    datalist //列表元素挨个检查
    
    protected $_paramInfos = array(
      //dalalist配置示例
        'array' => array(
            'datalist',
            'rule' => array(
                'datalist',
                'datalist,int,\,,min\,1\;max\,100,3,3',
                \Pol\Validate\Validator::NEED,
                \Pol\Validate\Validator::RIGHT
            )
        ),
    );

