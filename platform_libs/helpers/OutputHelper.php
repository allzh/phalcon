<?php

	/**
	 * 输出Helper类
	 *
	 */

	namespace platform\helpers;
	
	
	class OutputHelper {
	    
	    /**
	     * 格式化输出格式
	     * @static 
	     * @access public
	     * @param string $msg 
	     * @param number $code
	     * @param mixed $data
	     * @param string $ver 版本号
	     * @return array
	     * @author kuangwenjie@bubugao.com 
	     * @date 2015-02-02 15:25:00
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
	     * ajax输出---适用于交易线
	     * @static
	     * @access public
	     * @param mixed $data
	     * @param number $code 默认500
	     * @param string $callback 
	     * @author kuangwenjie@bubugao.com
	     * @date 2015-02-02 15:26:00
	     * 
	     */
	    static public function ajax($data, $code=0, $callback=null,$format=true,$encrypt=false){
	    	if($format){
		    	if(isset($data['error']) && $data['error'] != 0){  //异常
	                $code = !empty($data['error']) ? $data['error'] : $code;
	                //$code = intval($code); 
	                $msg = !empty($data['msg']) ? $data['msg'] : '系统异常！';
	                
	                $specialCodeArray = array(500);
	                if(in_array($code, $specialCodeArray)){ //特殊code码，不进行过滤
	                    
	                }else if(intval($code) <= 100){ //<=100为框架保留异常code码
	                    
	                }else if(intval($code) < 1000){  // code码<1000，进行过滤，报系统异常
	                	empty($msg) && $msg = '系统异常';
	                }
	                $data['error'] = $code;
	                $data['msg'] = $msg;
		    	}else{ //正常
		    	    $data = self::format_return(null, 0, $data);
		    	}
	    	}
	    	$data = json_encode($data);
	    	$encrypt && $data = '"'.base64_encode($data).'"';
	    	//JSONP支持
	    	if(!empty($callback)){
				header("Content-Type:application/javascript; charset=utf-8");
	    		exit("{$callback}(".$data.")");
	    	}else{
	    		header('Content-Type:application/json; charset=utf-8');
	    		exit($data);
	    	}
	    }
	}
