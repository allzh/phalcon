<?php
return array(
//网站路径
'webname' => '测试项目',
'weburl' => 'http://www.ph.com/',
'is_gzip' => 1, //是否Gzip压缩后输出
'is_cache' => 1,	//开启全页缓存
'enablehits' => 0,	//统计广告展示信息
	
//附件相关配置
'upload_path' => BBG_PATH.'uploadfile/',
'upload_allowext' => 'jpg|jpeg|gif|bmp|png|doc|docx|xls|xlsx|ppt|pptx|pdf|txt|rar|zip|swf',
'upload_maxsize' => '2048',	//上传最大限制 KB
		

//模板相关配置
'tpl_root' => 'templates/', //模板保存物理路径
'tpl_name' => 'default', //当前模板方案目录
'tpl_referesh' => 1,
'tpl_edit'=> 0,//是否允许在线编辑模板
'prefix'=>'',	//表前缀
'app_path'=>'http://test.ph.com/',
'jspath' => '/js/', //CDN JS
'csspath' => '/css/', //CDN CSS
'imgpath' => '/img/', //CDN img
'is_operlog' => 1,	//开启后台操作日志
'is_errlog' => 0,	//开启错误日志
'logexp' => 1,	//错误日志保存期限

'charset' => 'utf-8', //网站字符集

'auth_key' => 'hsihafh821hgafH', //密钥
'lock_ex' => '1',  //写入缓存时是否建立文件互斥锁定（如果使用nfs建议关闭）

'admin_founders' => '1', //网站创始人ID，多个ID逗号分隔
'execution_sql' => 0, //EXECUTION_SQL

'html_root' => '/html',//生成静态文件路径
'php_path'=>'C:/xampp/php/php.exe',	//PHP的执行路径 

'cache_type' => 'file',	//缓存类型
'cache_server' => 'file1', //缓存服务器
'multi_module'=>1,		//是否分布式布局
'is_sql' => 0,
'api_id' => 303,
'api_secret' => 'ALAHFH3232JS.!',

);
?>