<?php
define('IN_BBGCMS', true);
//缓存文件夹地址
define('CACHE_PATH', '/tmp/ph.com/caches'.DIRECTORY_SEPARATOR);
define('JS_CACHE_PATH',VIEW_PATH."caches".DIRECTORY_SEPARATOR);

//加载公用函数库
bbg_base::load_sys_func('global');
//加载扩展函数库
bbg_base::load_sys_func('ext');

/**
 *
 * 系统时间
 */
define('SYS_TIME', time());
/**
 *
 * 系统开始时间
 */
define('SYS_START_TIME', microtime());

define('MAST_CACHE_SERVER', "redis");

define('SLAVE_CACHE', "mall.memcache");


$sysSet=bbg_base::load_config('system');
/**
 *
 * 应用程序路径
 */
define('APP_PATH',$sysSet['app_path']);

define('WEB_URL',$sysSet['weburl']);


define('MULTI_MODULE',$sysSet['multi_module']);

define('IS_SQL',$sysSet['is_sql']);

/**
 *
 * 图片路径
 */
define('IMG_PATH',$sysSet['imgpath']);
/**
 *
 * JS路径
 */
define('JS_PATH',$sysSet['jspath']);
/**
 *
 * CSS路径
 */
define('CSS_PATH',$sysSet['csspath']);
/**
 *
 * 缓存状态
 */
define('IS_CACHE',$sysSet['is_cache']);
/**
 *
 * 缓存类型
 */
define('CACHE_TYPE',$sysSet['cache_type']);

define('CACHE_SERVER',"memcache");

/**
 * JAVA签名相关设置
 */
define('API_ID',$sysSet['api_id']);
define('API_SECRET',$sysSet['api_secret']);

/**
 *
 * 用户来源
 */
define('HTTP_REFERER', isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');


/**
 * 视图编译文件目录
 */
define('COMPILED_VIEWS_PATH', '/tmp/ph.com/compiled-views'.DIRECTORY_SEPARATOR);

/**
 * 日志目录
 */
define('LOG_PATH', '/apps/logs/php/ph.com'.DIRECTORY_SEPARATOR);

if (!defined('CURRENT_TASK')) {
	if($sysSet['is_gzip'] && function_exists('ob_gzhandler')) {
		ob_start('ob_gzhandler');
	} else {
		ob_start();
	}
}

date_default_timezone_set('Asia/Shanghai');
error_reporting(0);



class bbg_base{
	/**
	 * 加载函数库
	 * @param string $func 函数库名
	 * @param string $path 地址
	 */
	private static function _load_func($func, $path = '') {
		static $funcs = array();
		if (empty($path)) $path ='libs'.DIRECTORY_SEPARATOR.'functions';
		$path .= DIRECTORY_SEPARATOR.$func.'.php';

		$key = md5($path);
		if (isset($funcs[$key])) return true;
		if (file_exists(BBG_PATH.$path)) {
			include BBG_PATH.$path;
		} else {
			$funcs[$key] = false;
			return false;
		}
		$funcs[$key] = true;
		return true;
	}



	/**
	 * 加载系统的函数库
	 * @param string $func 函数库名
	 */
	public static function load_sys_func($func) {
		return self::_load_func($func);
	}


	/**
	 * 设置config文件
	 * @param $config 配属信息
	 * @param $filename 要配置的文件名称
	 */
	function set_config($config, $filename="system") {
		$configfile = BBG_PATH.'config'.DIRECTORY_SEPARATOR.$filename.'.php';
		if(!is_writable($configfile)) exit('Please chmod '.$configfile.' to 0777 !');
		$pattern = $replacement = array();
		foreach($config as $k=>$v) {
			if(in_array($k,array('webname','weburl','is_gzip','is_cache','is_sql','jspath','enablehits','csspath','imgpath','is_operlog','is_errlog','logexp','upload_maxsize','upload_allowext','tpl_name','tpl_app_name','cache_type','cache_server','api_id','api_secret'))) {
				$v = trim($v);
				$configs[$k] = $v;
				$pattern[$k] = "/'".$k."'\s*=>\s*([']?)[^']*([']?)(\s*),/is";
				$replacement[$k] = "'".$k."' => \${1}".$v."\${2}\${3},";
			}
		}
		$str = file_get_contents($configfile);
		$str = preg_replace($pattern, $replacement, $str);

		bbg_base::load_config('system','lock_ex') ? file_put_contents($configfile, $str, LOCK_EX) : file_put_contents($configfile, $str);
		$data=include $configfile;
		redisSet("c_".$filename, $data);

		if(MULTI_MODULE){
			$msg=array();
			$msg['method']='config';
			$msg['param']=$filename;
			assignMsg($msg);
		}
		return true;
	}

	/**
	 * 加载配置文件
	 * @param string $file 配置文件
	 * @param string $key  要获取的配置荐
	 * @param string $default  默认配置。当获取配置项目失败时该值发生作用。
	 * @param boolean $reload 强制重新加载。
	 */
	public static function load_config($file, $key = '', $default = '', $reload = false) {
		static $configs = array();
		if (!$reload && isset($configs[$file])) {
			if (empty($key)) {
				return $configs[$file];
			} elseif (isset($configs[$file][$key])) {
				return $configs[$file][$key];
			} else {
				return $default;
			}
		}

		$configs[$file]=redisGet("c_".$file);

		if(empty($configs[$file])){
			$path = BBG_PATH.'config'.DIRECTORY_SEPARATOR.$file.'.php';
			if (file_exists($path)) {
				$configs[$file] = include $path;
			}
			redisSet("c_".$file, $configs[$file]);
		}

		if (empty($key)) {
			return $configs[$file];
		} elseif (isset($configs[$file][$key])) {
			return $configs[$file][$key];
		} else {
			return $default;
		}
	}
}