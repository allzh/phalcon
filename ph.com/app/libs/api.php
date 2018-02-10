<?php
/**
 * api调用统一接口
 */
use platform\bbg\log\Log;
class api {

    private static $_instance;

	private $_Snoopy;

	private $time_start;

	private $time_end;

	private $time_spent;

	/**
	 * API 列表
	 */
	protected $_api_method = array();

	/**
	 * API 列表
	 * 生产环境
	 */
	protected $_api_method_pro = array(
	    'uploadToKind.methods'=>'http://img.ph.com/api',
        'getImgUrl.methods'=>'http://img.ph.com/api/get'
	);

	/**
	 * API 列表
	 * 开发环境
	 */
	protected $_api_method_dev = array(
	    'uploadToKind.methods'=>'http://img.ph.com/api',
        'getImgUrl.methods'=>'http://img.ph.com/api/get'

	);

	/**
	 * API 列表
	 * 测试环境
	 */
	protected $_api_method_test = array(
        'uploadToKind.methods'=>'http://img.ph.com/api',
        'getImgUrl.methods'=>'http://img.ph.com/api/get'
	);

	/**
	 * API 列表
	 * 预演环境
	 */
	protected $_api_method_pre = array(
        'uploadToKind.methods'=>'http://img.ph.com/api',
        'getImgUrl.methods'=>'http://img.ph.com/api/get'
	);

	/**
	 * 构造函数
	 *
	 * @param array $method
	 */
	private function __construct() {

		switch (RUNTIME) {
			case 'dev':
				// 开发环境配置
				$this->_api_method = $this->_api_method_dev;
				break;
			case 'pro':
				$this->_api_method = $this->_api_method_pro;
				// 生产环境配置
				break;
			case 'pre':
				$this->_api_method = $this->_api_method_pre;
				// 预演环境配置
				break;
			default:
				// 测试环境配置
				$this->_api_method = $this->_api_method_test;
		}
	}

	public function __clone() {
		trigger_error('Clone is not allow!', E_USER_ERROR);
	}

	public static function getInstance() {
		if (!(self::$_instance instanceof self)) {

			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * 获取数据
	 *
	 * @param string $method
	 * @param array $parameter
	 */
	public function get($method, $parameter = array(),$type="json",$rawheaders=array()) {
		$api = $this->_api_method[$method];
		$data = '';
		$request = array();
		if ($api) {
			!is_array($parameter) && $parameter = (array) $parameter;
			$parameter['method']=$method;

			foreach ($parameter as $key => $val) {
				if(is_array($val)){
					foreach ($val as $v){
						$request[] = $key . "=" . $v;
					}
				}else{
					$request[] = $key . "=" . $val;
				}
			}

			if(strpos($api,"?")){
				$url = $api . "&" . implode("&", $request);
			}else{
				$url = $api . "?" . implode("&", $request);
			}

			$rawheaders=self::header($parameter,$rawheaders);

			$data = $this->_get($url,$type,$rawheaders,$parameter);
		}
		return $data;
	}

	/**
	 * get方法取数据
	 * @param string $url
	 * @param string $type
	 * @param array $headers
	 * @return mixed
	 */
	protected function _get($url,$type='json',$rawheaders=array(), $parameter =array()) {
		$this->_Snoopy = new Snoopy();
		$this->_Snoopy->read_timeout=2;	//超时时间2秒
		if(!empty($rawheaders)){
			$this->_Snoopy->rawheaders=$rawheaders;
		}
		$this->time_start=microtime();
		$this->_Snoopy->fetch($url);
		$this->time_end=microtime();
		$msg=array();
		$msg['time_spent']=$this->_spent();
		$msg['url']=$url;
		$msg['results'] = $temp = $this->_Snoopy->results;
		
		
		//日志
		$Log = Log::getInstance();
		$Log ->api_info($parameter['method'],$url,$parameter,time(),$this->_Snoopy->status,$this->_spent(),$this->_Snoopy->results);
		//print_r($msg);exit;
		
		$this->log($msg);
		unset($msg);
		if(empty($temp)){
			return false;
		}
		switch($type){
			case 'json':
				$temp=json_decode($temp,true);
				break;
			case 'serialize':
				$temp=unserialize($temp);
				break;
			default:
				break;
		}
		return $temp;
	}


	public function submit($url,$params=array(),$formfiles = "",$type='json',$rawheaders=array()){
		$this->_Snoopy = new Snoopy();
		$this->_Snoopy->read_timeout=2;	//超时时间2秒
		$rawheaders=self::header($params,$rawheaders);
		if(!empty($rawheaders)){
			$this->_Snoopy->rawheaders=$rawheaders;
		}
		if(!empty($formfiles)){
			$this->_Snoopy->_submit_type = 'multipart/form-data';
		}

		$this->time_start=microtime();
		$this->_Snoopy->submit($url,$params,$formfiles);
		$this->time_end=microtime();
		$msg=array();
		$msg['time_spent']=$this->_spent();
		$msg['url']=$url;
		$this->log($msg);
		unset($msg);
		$temp=$this->_Snoopy->results;
		//print_r($temp);
		//日志
		$Log = Log::getInstance();
		$Log ->api_info($params['method'],$url,$params,time(),$this->_Snoopy->status,$this->_spent(),$this->_Snoopy->results);
		if(empty($temp)){
			return false;
		}
		switch($type){
			case 'json':
				$temp=json_decode($temp,true);
				break;
			case 'serialize':
				$temp=unserialize($temp);
				break;
			default:
				break;
		}
		return $temp;
	}

	/**
	 * 根据method 获取对应的url
	 * @param string $method
	 * @return multitype:
	 */
	public function get_api_url($method){
		return $this->_api_method[$method];
	}

	/**
	 * 计算运行时间差
	 * @return string
	 */
	protected  function _spent(){
        if ($this->time_spent) {
            return $this->time_spent;
        } else {
         list($StartMicro, $StartSecond) = explode(" ", $this->time_start);
         list($StopMicro, $StopSecond) = explode(" ", $this->time_end);
            $start = doubleval($StartMicro) + $StartSecond;
            $stop = doubleval($StopMicro) + $StopSecond;
            $this->time_spent = $stop - $start;
            return substr($this->time_spent,0,8)."秒";//返回获取到的程序运行时间差
        }
    }

	public function log($message) {
		$logger = new Phalcon\Logger\Adapter\File(check_dir(LOG_PATH."api.log"));

		$msg=" 接口".$message['url'].' 耗时:'.$message['time_spent']."返回的结果".$message['results'];
		$logger->info($msg);
	}

	/**
	 * 生成java接口 http头信息
	 * @param array $params
	 * @param array $header
	 * @param null $time
	 * @return array
	 */
	static public function header(array $params=array(),array $header=array(),$time=null) {
		if(null === $time) {
			list($usec, $sec) = explode(' ', microtime());
			$time = number_format(((float)$usec + (float)$sec)*1000,0,'','');
		}

		$params_md5 = self::paramsMd5($params);
		$sign = md5(API_SECRET.$time.$params_md5.API_SECRET);

		return array_merge(
				$header,
				array(
						'sign' => $sign,
						'key' => $params_md5,
						'appId' => API_ID,
						'timestamp' => $time,
				)
		);
	}

	/**
	 * 生成参数的排序字符串
	 * @param array $params
	 * @return string
	 */
	static private function paramsMd5(array $params) {
		ksort($params);
		$str = self::buildQuery($params);
		return md5($str);
	}

	/**
	 * 递规组合url字符串
	 * @param $data
	 * @param string $prefix
	 * @param string $sep
	 * @param string $key
	 * @param bool $urlEncode
	 * @return string
	 */
	static public function buildQuery($data,$prefix='',$sep='',$key='',$urlEncode=false) {
		$ret = array();
		foreach ((array)$data as $k => $v) {
			$k = $urlEncode?urlencode($k):$k;
			if (is_int($k) && $prefix) {
				$k = $prefix . $k;
			}
			if ($key) {
				$k = $key . "[" . $k . "]";
			}
			if (is_array($v) || is_object($v)) {
				array_push($ret, self::buildQuery($v, "", $sep, $k));
			} else {
				array_push($ret, $k . ($urlEncode?urlencode($v):$v));
			}
		}
		return implode($sep, $ret);
	}
}