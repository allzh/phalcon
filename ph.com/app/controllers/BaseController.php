<?php
/**
 * 基础控制类
 */
class BaseController extends \Phalcon\Mvc\Controller
{
	
	function initialize(){
		
	}
	
	/**
	 * 定义AJAX返回时的数据，
	 * @param $status 200 操作成功 500 操作失败
	 * @param $msg 回调的消息
	 * @param $info 回调的具体信息
	 * @param $type  显示的格式 支持JSON和JSONP
	 */
	public function ajaxReturn($status,$msg="success",$info="",$type="json"){
		header('Content-Type:application/json; charset=utf-8');
		$message=array('status'=>$status,
				'msg'=>$msg,
				'info'=>$info,
		);
		switch($type){
			case "json":
				exit(json_encode($message));
				break;
			case "jsonp":
				$handler  =   'jsonpReturn';
				exit($handler.'('.json_encode($message).');');
				break;
		}
		
	}

	/**
	 * 输出jsonp
	 *
	 * @param type $callback        	
	 * @param type $data        	
	 */
	public function jsonpOutput($callback = '', $data = null) {
		$json = json_encode($data);
		if (empty($callback)) {
			echo $json;
		} else {
			echo $callback . '(' . $json . ')';
		}
	}
	
	/**
	 * ajax输出---适用于交易线
	 * @static
	 * @access public
	 * @param mixed $data
	 * @param number $code 默认500
	 * @param string $callback
	 *
	 */
	public function ajax_return($data, $code=500, $callback=null){
		if(isset($data['error']) && $data['error'] != 0){  //异常
			$code = !empty($data['error']) ? $data['error'] : $code;
			//$code = intval($code);
			$msg = !empty($data['msg']) ? $data['msg'] : '系统异常！';
	
			$specialCodeArray = array(500);
			if(in_array($code, $specialCodeArray)){ //特殊code码，不进行过滤
	
			}else if(intval($code) <= 100){ //<=100为框架保留异常code码
	
			}else if(intval($code) < 1000){  // code码<1000，进行过滤，报系统异常
				$msg = '系统异常';
			}
			$data['error'] = $code;
			$data['msg'] = $msg;
		}else{ //正常
			$data = self::format_return($data['msg'], 0, $data);
		}
		//header('Content-Type:application/json; charset=utf-8');
// 		if(function_exists('ob_gzhandler')) {
// 			ob_start('ob_gzhandler');
// 		} else {
// 			ob_start();
// 		}
		// 	    	if(isset($_GET['_']) && $_GET['_']){
	
		// 	    	}
		//JSONP支持
		if(!empty($callback)){
			exit("{$callback}(".json_encode($data).")");
		}
		exit(json_encode($data));
	}
	
	/**
	 * 格式化输出格式
	 * @static
	 * @access public
	 * @param string $msg
	 * @param number $code
	 * @param mixed $data
	 * @param string $ver 版本号
	 * @return array
	 *
	 */
	static public function format_return($msg, $code=500, $data=null, $ver=null){
		empty($ver) && $ver = '1.0';
		$result = array(
		'error' => $code,
		'msg' => $msg,
		'data' => $data,
		'ver' => $ver,
		);
		return $result;
	}
	
	
	
	/**
	 * 写操作日志
	 */
	function writeLog(){
		$Log=new Log();
		$data=array();
		$data['bmodule']=$this->router->getControllerName();
		$data['baction']=$this->router->getActionName();
		if(empty($data['baction'])){
			$data['baction']="index";
		}
		$data['querystring']=get_url();
		$data['data']=serialize($_POST);
		$data['userid']=$this->user->name;
		$data['username']=$this->user->employeeName;
		$data['ip']=ip2long($this->request->getClientAddress());
		$data['opertime']=time();
	
		$msg=$Log->dgjFsave($data);
		if(is_array($msg)){
			$this->wirteErrLog($msg,"写入操作日志失败");
		}
	}
	
	
	/**
	 * 写错误日志
	 * @param array $messages	错误信息
	 * @param string $opt		额外描述信息
	 */
	function wirteErrLog($messages,$opt=""){
		if(empty($opt)){
			$opt=$this->router->getControllerName()."/".$this->router->getActionName();
		}
		$logger = new Phalcon\Logger\Adapter\File(check_dir(LOG_PATH."error.log"));
		$msg="";
		foreach($messages as $message){
			$msg.=" ".$message;
		}
		$logger->error($opt.",错误描述如下".$msg);
	}
	
}