<?php
namespace platform\bbg\log;

class Log {
	protected static $_instance;
	/**
	 * redis
	 * @var redis 类
	 */
	protected $_redis = null;
	/**
	 * redis 超时时间 默认2秒
	 * @var int
	 */
	protected $_redis_timeout = 2;
	
	/**
	 * redis 是否链接
	 * @var int
	 */
	protected $_redis_connected = false;

	/**
	 * 队列 名
	 * @var string
	 */
	protected $_redis_list_key = 'bbgLogRedisListKey';
	
	/**
	 * 备份队列名
	 * @var string
	 */
	protected $_redis_list_failed_key = 'bbgLogRedisListFailedKey';
	
	/**
	 * 开始管道ID
	 * @var int
	 */
	protected $_conduit_start_id = 1;
	
	/**
	 * 结束管道ID
	 * @var int
	 */
	protected $_conduit_end_id = 5;

	/**
	 * 应用名
	 * @var string
	 */
	protected $_app;
	/**
	 * 服务器ip地址
	 * @var string
	 */
	protected $_ip;
	/**
	 * 应用类型
	 * @var string
	 */
	protected $_type;
	/**
	 * 构造函数 初始化 
	 *
	 * @param array $method        	
	 */
	protected function __construct($config = null) {
		if($config){
			isset($config['app']) && $this->_app = $config['app'];
			isset($config['ip']) && $this->_ip = $config['ip'];
			isset($config['type']) && $this->_type = $config['type'];
		}
		//配置为空的时候写入默认配置
		empty($this->_app) && $this->_app = $_SERVER['SERVER_NAME'];
		empty($this->_ip) && $this->_ip = $_SERVER['SERVER_ADDR'];
		//初始化redis
		$this->_init_redis();
	}
	/**
	 * 禁止克隆
	 */
	public function __clone() {
		trigger_error('Clone is not allow!', E_USER_ERROR);
	}
	/**
	 * 取得单例logs的实例
	 * @param string $config
	 * @return BbgLogs
	 */
	public static function getInstance($config = null) {
		if (!(self::$_instance instanceof self)) {
			self::$_instance = new self($config);
		}
		return self::$_instance;
	}
	
	/**
	 * 初始化队列服务器 redis list
	 */
	protected function _init_redis(){
		$phplog = json_decode(get_cfg_var('mall.phplog'),true);
		if (empty($phplog) || empty($phplog['type']) || $phplog['type'] <= 1) {
			return;
		}

		$config = json_decode(get_cfg_var('mall.redis.logs'),true);
		if (empty($config)) {
			return;
		}

		$this->_redis = new \Redis();

		$result = $this->_redis->connect($config['redis']['host'], $config['redis']['port'], $this->_redis_timeout);
		if (!$result) {
			return;
		}

		if (!empty($config['redis']['password'])){
			$result = $this->_redis->auth($config['redis']['password']);
			if (!$result) {
				return;
			}
		}

		$result = $this->_redis->select($config['redis']['db']);
		if (!$result) {
			return;
		}

		$this->_redis_connected = true;
	}
	
	/**
	 * push数据到redis 队列中
	 * @param sting $app
	 * @param string $ip
	 * @param string $type
	 * @param date $date
	 * @param json $data
	 */
	public function push($data,$type,$app,$ip,$api_method='',$api_run_time=''){
		//$phplog['type'] 1文件日志，2redis日志，3两种方式
		$phplog = json_decode(get_cfg_var('mall.phplog'),true);
		if (empty($phplog) || empty($phplog['type'])) {
			return;
		}

		//接口日志
		//$phplog['api'] 0不记录接口日志，1记录接口状态，2记录详细内容
		if ($type == 'api') {
			if (empty($phplog['api'])) {
				return;
			} elseif ($phplog['api'] == 1) {
				unset($data['apiResults']);
			} elseif (isset($data['apiResults']) && is_array($data['apiResults'])) {
				//兼容elasticsearch的数据格式，apiResult字段不能为数组
				$data['apiResults'] = json_encode($data['apiResults']);
			}
		}

		//记录访问的网址
		if (empty($data['web_url'])) {
			$data['web_url'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}

		$data = array(
			'pid' => 'php',
			"app"=>$app,
			"ip"=>$ip,
			"type"=>$type,
			"date"=>time() * 1000,
			"data"=>$data,
			"apiMethod"=>$api_method,
			"apiRunTime"=>$api_run_time,
		);

		if ($phplog['type'] >= 2 && $this->_redis_connected) {
			$key_index = rand($this->_conduit_start_id,$this->_conduit_end_id);
			$key = $this->_redis_list_key . $key_index;
			$this->_redis->LPUSH($key,serialize($data));
		}

		if ($phplog['type'] == 1 || $phplog['type'] >= 3) {
			$this->log($data,$type);
		}
	}
	
	/**
	 * 从redis list中取数据
	 */
	public function pop(){
		return $this->_redis->RPOPLPUSH($this->_redis_list_key,$this->_redis_list_failed_key);
	}
	
	/**
	 * 从redis备份队列 list 中取数据
	 */
	public function pop_failed(){
		return $this->_redis->RPOPLPUSH($this->_redis_list_failed_key,$this->_redis_list_failed_key);
	}
	
	/**
	 * 处理完毕删除备份队列数据
	 * @param array $data
	 */
	public function delete_failed($data){
		return $this->_redis->LREM($this->_redis_list_failed_key,$data);
	}
	
	/**
	 * 设置应用app名
	 * @param string $app
	 */
	public function set_app($app){
		$this->_app = $app;
	}
	
	/**
	 * 应用IP地址
	 * @param string $ip
	 */
	public function set_ip($ip){
		$this->_ip = $ip;
	}
	
	/**
	 * 设置日志类型
	 * @param string $type
	 */
	public function set_type($type){
		$this->_type = $type;
	}
	
	/**
	 * 写入api日志
	 * @param string $api_method 接口方法
	 * @param string $api_url 接口地址
	 * @param json $api_params 接口参数
	 * @param date $api_request_time 请求接口的当前系统时间
	 * @param enum(success,fail,timeout) $api_status 接口调用结果
	 * @param string $api_run_time 接口运行时间
	 * @param json $api_results 接口返回的结果
	 */
	public function api_info($api_method,$api_url,$api_params,$api_request_time,$api_status,$api_run_time,$api_results){
		$data = array(
				'apiMethod'=>$api_method,
				'apiUrl'   =>$api_url,
				'apiParams'=>$api_params,
				'apiRequestTime'=>$api_request_time,
				'apiStatus'=>$api_status,
				'apiRunTime'=>$api_run_time,
				'apiResults'=>$api_results,
		);
		return $this->push($data, 'api', $this->_app, $this->_ip, $api_method, $api_run_time);
	}
	
	/**
	 * 写入error 日志
	 * @param array $data
	 */
	public function errors_info($data){
		return $this->push($data, 'error', $this->_app, $this->_ip);
	}
	
	/**
	 * 写入debug日志
	 * @param array $data
	 */
	public function debug_info($data){
		return $this->push($data, 'debug', $this->_app, $this->_ip);
	}
	/**
	 * 写入日志到文件
	 * @param unknown $data
	 * @param string $type
	 */
	public function log($data, $type="log"){
		$fileName = date('Ymd', time());
		$logFile = "/apps/logs/php/{$type}.{$fileName}.log";
		$logger = new \Phalcon\Logger\Adapter\File($logFile);
		$logger->info(json_encode($data));
	}
	
	
}