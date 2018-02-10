<?php
class cacheRedis{
	 
	private $redis; //redis对象
	
	private $timeout = 2;

	/**
	 * 初始化Redis
	 * $config = array(
	 * 'server' => '127.0.0.1' 服务器
	 * 'port' => '6379' 端口号
	 * )
	 * 
	 * @param array $config        	
	 */
	public function __construct() {
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT, $this->timeout);
		if (defined("REDIS_PASSWORD")){
		    $this->redis->auth(REDIS_PASSWORD);
		}
		$this->redis->select(REDIS_DB);
	}
	  
	/**
	 * 设置值
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param int $timeOut 时间
	 */
	public function set($key, $value, $timeOut = 0,$type='json') {
		if($type=='serialize')
		{
			$value = serialize($value);
		}
		else
		{
			$value = json_encode($value);
		}
		 
		$retRes = $this->redis->set($key, $value);
		
		if ($timeOut > 0) $this->redis->setTimeout($key, $timeOut);
		return $retRes;
	}
	 
	/**
	 * 通过KEY获取数据
	 * @param string $key KEY名称
	 */
	public function get($key,$type='json') {
		$result = $this->redis->get($key);
		if($type=='serialize')
		{
			return unserialize($result);
		}
		else
		{
			return json_decode($result,true);
		}
	}
	 
	/**
	 * 删除一条数据
	 * @param string $key KEY名称
	 */
	public function delete($key) {
		return $this->redis->delete($key);
	}
	 
	/**
	 * 清空数据
	 */
	public function flush() {
		return $this->redis->flushAll();
	}
	 
	/**
	 * 数据入队列
	 * @param string $key KEY名称
	 * @param string|array $value 获取得到的数据
	 * @param bool $right 是否从右边开始入
	 */
	public function push($key, $value ,$right = true) {
		$value = json_encode($value);
		return $right ? $this->redis->rPush($key, $value) : $this->redis->lPush($key, $value);
	}
	 
	/**
	 * 数据出队列
	 * @param string $key KEY名称
	 * @param bool $left 是否从左边开始出数据
	 */
	public function pop($key , $left = true) {
		$val = $left ? $this->redis->lPop($key) : $this->redis->rPop($key);
		return json_decode($val,true);
	}
	 
	/**
	 * 数据自增
	 * @param string $key KEY名称
	 */
	public function increment($key) {
		return $this->redis->incr($key);
	}
	 
	/**
	 * 数据自减
	 * @param string $key KEY名称
	 */
	public function decrement($key) {
		return $this->redis->decr($key);
	}
	 
	/**
	 * key是否存在，存在返回ture
	 * @param string $key KEY名称
	 */
	public function exists($key) {
		return $this->redis->exists($key);
	}
	 
	/**
	 * 返回redis对象
	 * redis有非常多的操作方法，我们只封装了一部分
	 * 拿着这个对象就可以直接调用redis自身方法
	 */
	public function redis() {
		return $this->redis;
	}
}