<?php
/*
 * cli访问入口（用于计划任务脚本执行）
 * Created by PhpStorm.
 * User: Disen
 * Date: 2018/02/10
 * Time: 9:29
 */

/**
 * 截获错误代码处理代码 提交日志服务器
 */
register_shutdown_function(
	function () {
		$e = error_get_last();
		if ($e) {
			print_r($e);
		}
	});
/**
 * 注册截获用户错误的方法提交到日志服务器
 */
set_error_handler(
	function ($errno, $errstr, $errfile, $errline) {
		// 发送错误日志
		// include_once 'app/libs/BbgLogs.php';
		// $logs = \MY\log\BbgLogs::getInstance();
		$data = array('type' => $errno, 'message' => $errstr, 
			'file' => $errfile, 'line' => $errline);
		// $logs->errors_info($data);
		// print_r($data);
		// die();
	}, E_ALL);
if (php_sapi_name() != "cli") {
	exit('disallow');
}

define('BBG_PATH',__DIR__ . DIRECTORY_SEPARATOR);

define('LOG_PATH', '/apps/logs/php/ph.com'.DIRECTORY_SEPARATOR);

/**
 * 处理参数及分发
 */
if ($argc < 3) {
	exit('参数错误！');
}
$args = array('task' => $argv[1], 'action' => $argv[2], 
	'params' => ($argc > 3 ? array_splice($argv, 3) : array()));

define('CURRENT_TASK', $args['task']);
define('CURRENT_ACTION', $args['action']);
define('APPLICATION_PATH', realpath(dirname(__FILE__)));
$runtime = get_cfg_var('mall.runtime');
define('RUNTIME', $runtime);

switch (RUNTIME) {
	case 'pro':
		// 生产环境配置
		define('MAST_CACHE', "mall.redis.mall");
		define('DEBUG', 0); // DEBUG
		define('COMPILED_ALWAYS', false); // 模板文件实时编译开关
		break;
	case 'test':
		// 测试环境配置
		define('MAST_CACHE', "mall.redis.mall");
		define('DEBUG', 0); // DEBUG
		define('COMPILED_ALWAYS', false); // 模板文件实时编译开关
		break;
	case 'pre':
		// 测试环境配置
		define('MAST_CACHE', "mall.redis.mall");
		define('DEBUG', 0); // DEBUG
		define('COMPILED_ALWAYS', false); // 模板文件实时编译开关
		break;
	default:
		// 开发环境配置
		define('MAST_CACHE', "mall.redis.mall");
		define('DEBUG', 1); // DEBUG
		define('COMPILED_ALWAYS', true); // 模板文件实时编译开关
}

/**
 * 初始化CLI容器
 */
$cli = new Phalcon\DI\FactoryDefault\CLI();
/** 注册加载器 */
$loader = new \Phalcon\Loader();  
$loader->registerDirs(  
    array(  
        APPLICATION_PATH.'/tasks',
        APPLICATION_PATH.'/controllers/',
        APPLICATION_PATH.'/models/',
        APPLICATION_PATH.'/config/',
        APPLICATION_PATH.'/libs/',  
        APPLICATION_PATH.'/libs/tags/',
    )  
);
$loader->register(); 
/**
 * 注册命名空间
 */
$loader->registerNamespaces(array('platform' => BBG_PATH .'../../platform_libs'))->register();

/**
 * 创建的控制台
 */
$console = new Phalcon\CLI\Console();
$console->setDI($cli);

require_once APPLICATION_PATH . '/base.php';

// 设置数据库
$cli->setShared('db',
	function () use($di) {
		// 配置数据库
		$dbconfig = json_decode(get_cfg_var('mall.mysql'), true);
		if (empty($dbconfig)) {
			exit("the database config is error");
		}
		$connection = new \Phalcon\Db\Adapter\Pdo\Mysql(
			array("host" => $dbconfig['host'], "port" => $dbconfig['port'], 
				"username" => $dbconfig['user'], 
				"password" => $dbconfig['password'], 
				"dbname" => $dbconfig['dbname'], 
				"charset" => $dbconfig['charset']));
		return $connection;
	});

$cli->setShared('dbext',
    function () use($di) {
        // 配置数据库
        $dbconfig = json_decode(get_cfg_var('mallext.mysql'), true);
        if (empty($dbconfig)) {
            exit("the database config is error");
        }
        $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(
            array("host" => $dbconfig['host'], "port" => $dbconfig['port'],
                "username" => $dbconfig['user'],
                "password" => $dbconfig['password'],
                "dbname" => $dbconfig['dbname'],
                "charset" => $dbconfig['charset']));
        return $connection;
    });



/**
 * 日志
 */
$cli->setShared('logger', 
	function () {
		$logger = new Phalcon\Logger\Adapter\File(
			check_dir(LOG_PATH . date('Ymd') . ".log"));
		return $logger;
	});

try {
	$console->handle($args);
}
catch (\Phalcon\Exception $e) {
	echo $e->getMessage();
	exit();
} 
