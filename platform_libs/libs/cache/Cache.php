<?php

namespace platform\libs\cache;

use platform\config\Config;
use platform\libs\cache\cacheFactory;
use platform\libs\cache\drives\Redis;

class Cache {
	protected static $_instance;
	
	/**
	 * 缓存备份
	 *
	 * @var Boolean
	 */
	protected $_cache_backup = true;
	
	public $cache_short_time = 30;
	
	/**
	 * 主缓存
	 *
	 * @var unknown
	 */
	protected $_Master;
	protected $_Slave;
	
	/**
	 * 防止克隆单例对象
	 */
	public function __clone() {
		trigger_error ( 'Clone is not allow!', E_USER_ERROR );
	}
	
	/**
	 * 获取当前类单例对象
	 *
	 * @static
	 *
	 * @access public
	 * @return object
	 */
	public static function getInstance() {
		if (! (self::$_instance instanceof self)) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	/**
	 * 构造函数
	 */
	private function __construct() {
		$Config = Config::getInstance('api','api_cache');
		$this->_cache_backup = $Config->get ( 'cache_backup' );
		$cache_short_time = $Config->get ( 'cache_short_time' );
		$cache_short_time && $this->cache_short_time = $cache_short_time;
		// 主缓存
		$master_config = json_decode ( get_cfg_var ( 'mall.redis.mall' ), true );
		$this->_Master = new Redis($master_config['redis']);
		// 备用缓存
		if ($this->_cache_backup) {
			$slave_config = json_decode ( get_cfg_var ( 'mall.redis.slave' ), true );
			$this->_Slave = @new Redis($slave_config['redis']);
		}
	}
	
	/**
	 * 设置值
	 *
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param int $timeout 时间
	 * @param int $compression 压缩
	 * @param int $type 序列化类型
	 */
	public function set($key, $value, $timeout = 0, $type = 'json',$compression = true) {
		$return = $this->_Master->set($key, $value, $timeout,$type,$compression);
		$this->_cache_backup && @$this->_Slave->set($key, $value, 0,$type,$compression);
		return $return;
	}
	
	/**
	 * 通过KEY获取数据
	 *
	 * @param string $key KEY名称
	 * @param int $compression 压缩
	 * @param int $type 序列化类型
	 */
	public function get($key, $type = 'json',$compression = true) {
		return $this->_Master->get($key, $type, $compression);
	}
	
	/**
	 * 通过KEY获取备用缓存数据
	 *
	 * @param string $key KEY名称
	 * @param int $compression 压缩
	 * @param int $type 序列化类型
	 */
	public function get_backup($key, $type = 'json',$compression = true) {
		return $this->_Slave->get($key, $type, $compression);
	}

	/**
	 * 删除一条数据
	 *
	 * @param string $key KEY名称
	 */
	public function delete($key) {
		return $this->_Master->delete ( $key );
	}
	
	/**
	 * 删除一条数据
	 *
	 * @param string $key KEY名称
	 */
	public function delete_slave($key) {
		return $this->_Slave->delete ( $key );
	}
	
	/**
	 * 是否启用备用缓存
	 */
	public function is_backup(){
		return $this->_cache_backup;
	}
	
	/**
	 * 取得主缓存类
	 */
	public function get_master(){
		return $this->_Master;
	}
	
	/**
	 * 取得从缓存类
	 */
	public function get_slave(){
		return $this->_Slave;
	}
	
	/**
	 * 清空数据
	 */
	public function flush() {
		return $this->_Master->flushAll();
	}
	
	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value ,$right = true) {
		return $right ? $this->_Master->rPush($key, $value) : $this->_Master->lPush($key, $value);
	}
	
	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key , $left = true) {
		return $left ? $this->_Master->lPop($key) : $this->_Master->rPop($key);
	}
	
	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function increment($key) {
		return $this->_Master->increment($key);
	}
	
	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decrement($key) {
		return $this->_Master->decrement($key);
	}
	
	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) {
		return $this->_Master->exists($key);
	}
	/**
	 * 主缓存过期设置
	 * @param unknown $key
	 * @param unknown $time
	 */
	public function expire($key,$time){
		$this->_Master->expire($key,$time);
	}
	
	/**
	 * 从缓存过期设置
	 * @param unknown $key
	 * @param unknown $time
	 */
	public function expire_slave($key,$time){
		$this->_Slave->expire($key,$time);
	}
	
}
