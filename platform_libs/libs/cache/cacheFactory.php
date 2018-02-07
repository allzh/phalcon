<?php
namespace platform\libs\cache;

final class cacheFactory {
	
	/**
	 * 当前缓存工厂类静态实例
	 */
	private static $cache_factory;
	
	/**
	 * 缓存配置列表
	 */
	protected $cache_config = array();
	
	/**
	 * 缓存操作实例化列表
	 */
	protected $cache_list = array();
	
	/**
	 * 构造函数
	 */
	public function __construct() {
	}
	
	/**
	 * 返回当前终级类对象的实例
	 * @param $cache_config 缓存配置
	 * @return object
	 */
	public static function get_instance($cache_config = '') {
		if(cacheFactory::$cache_factory == '' || $cache_config !='') {
			cacheFactory::$cache_factory = new cacheFactory();
			if(!empty($cache_config)) {
				cacheFactory::$cache_factory->cache_config = $cache_config;
			}
		}
		return cacheFactory::$cache_factory;
	}
	
	/**
	 * 获取缓存操作实例
	 * @param $cache_name 缓存配置名称
	 */
	public function get_cache($cache_name) {
		if(!isset($this->cache_list[$cache_name]) || !is_object($this->cache_list[$cache_name])) {
			$this->cache_list[$cache_name] = $this->load($cache_name);
		}
		return $this->cache_list[$cache_name];
	}
	
	/**
	 *  加载缓存驱动
	 * @param $cache_name 	缓存配置名称
	 * @return object
	 */
	public function load($cache_name) {
		$object = null;
		if(isset($this->cache_config[$cache_name]['type'])) {
			switch($this->cache_config[$cache_name]['type']) {
				case 'file' :
					$object =new cacheFile();
					break;
				case 'memcache' :
					if(!defined('MEMCACHE_HOST')){
						define('MEMCACHE_HOST', $this->cache_config[$cache_name]['host']);
						define('MEMCACHE_PORT', $this->cache_config[$cache_name]['port']);
						define('MEMCACHE_TIMEOUT', $this->cache_config[$cache_name]['timeout']);
						define('MEMCACHE_DEBUG', $this->cache_config[$cache_name]['debug']);
					}
					$object = new cacheMemcache();
					break;
				case 'redis' :
					if(!defined('REDIS_HOST')){
						define('REDIS_HOST', $this->cache_config[$cache_name]['host']);
						define('REDIS_PORT', $this->cache_config[$cache_name]['port']);
						define('REDIS_DB', $this->cache_config[$cache_name]['db']);
						if ( isset($this->cache_config[$cache_name]['password']) ){
							define('REDIS_PASSWORD',  $this->cache_config[$cache_name]['password']);
						}
					}
						$object = new cacheRedis();
						break;
				default :
					$object = new cacheFile();
			}
		} else {
			$object = new cacheFile();
		}
		return $object;
	}

}
?>