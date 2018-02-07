<?php

    /**
     * Phalcon配置扩展类
     *
     */

    namespace platform\config;

    use platform\bbg\log\Log;

    class Config{
        
        private static $_instance;
        
        /**
         * 配置数组
         * @var array
         */
        private static $configArray;
        
        /**
         * 配置文件
         * @var string
         */
        private $path;
        
        /**
         * 单例
         * @access public
         * @param string $path
         * @param string $file
         * @param boolean $app 是否读取应用程序配置
         * @return object
         */
        public static function getInstance($path, $file=null, $app=false){
        	$key = ($app?"app/":'').$path.($file?"/".$file:'');
        	if(!isset(self::$_instance[$key]) || !(self::$_instance[$key] instanceof self)){
        		self::$_instance[$key] = new self($path, $file, $app);
        	}
        	return self::$_instance[$key];
        }
        
        /**
         * 构造函数
         * @access private
         * @param string $path
         * @param string $file
         * @param boolean $app 是否读取应用程序配置
         */
        private function __construct($path, $file=null, $app=false){
        	$key = ($app?"app/":'').$path.($file?"/".$file:'');
        	if(!isset(self::$configArray[$key]) || !is_array(self::$configArray[$key]) || count(self::$configArray[$key]) == 0){
        		$configArray = $this -> _load_config($path, $file, $app);
        		if(is_array($configArray) && count($configArray) > 0){
        			self::$configArray[$key] = $configArray;
        		}        		
        	}
        	$this -> path = $path;
        	$this -> key = $key;
        }
        
        /**
         * 防止克隆单例对象
         */
        public function __clone() {
            trigger_error('Clone is not allow!', E_USER_ERROR);
        }
        
        /**
         * 获取api配置（自动匹配运行环境）
         * @access public
         * @param string 接收动态参数，以数组下标的先后顺序传递
         *      eg:get_api_config('CART_CENTER', 'URL', ...);
         *      不传任何参数，即获取api整个配置数组
         * @return string $result
         */
        public function get(){
            $result = self::$configArray[$this -> key];
            $argsArray = func_get_args();
            foreach($argsArray as $key=>$value){
                //$value = strtoupper($value);
                //按下标索引取值
                if(isset($result[$value])){
                    $result = $result[$value];
                }else{
                    $result = '';
                }
            }

            //获取配置为空，记录到日志
            if(empty($result) && !is_bool($result)){
                $logArray = array(
                    'file' => __FILE__,
                    'line' => __LINE__,
                    'method' => __METHOD__,
                    'msg' => "配置获取失败，程序读取到的结果为：{$result}",
                    'params' => json_encode($argsArray),
                );
                $this -> _write_log($logArray);
            }
        
            return $result;
        }
        

        /**
         * 获取运行环境
         * @access protected
         * @return string
         */
        protected function _get_runtime_environment(){
            $runTimeEnvironment = get_cfg_var('mall.runtime');
            empty($runTimeEnvironment) && $runTimeEnvironment = 'dev';
            return strtolower($runTimeEnvironment);
        }

        /**
         * 加载配置文件
         * @access protected
         * @param string $path
         * @param string $file
         * @param boolean $app 是否读取应用程序配置
         * @return object
         */
        protected function _load_config($path, $file=null, $app=false){
            try{
                empty($file) && $file = $path;
                $runTimeEnvironment = $this -> _get_runtime_environment();
                if($app){
                	$configFile = dirname(dirname(__DIR__)) . "/{$path}/{$file}_{$runTimeEnvironment}.php";
                }else{
	                $configFile = dirname(__DIR__) . "/config/{$path}/{$file}_{$runTimeEnvironment}.php";
                }
                if(!file_exists($configFile)){
            		throw new \Exception("配置文件：{$configFile}不存在");
            	}
            	$result = new \Phalcon\Config\Adapter\Php($configFile);
            	$result = $result -> toArray();
            	return $result;
            }catch(\Exception $e){
                $logArray = array(
                	'file' => $e -> getFile(),
                    'line' => $e -> getLine(),
                    'method' => __METHOD__,
                    'code' => $e -> getCode(),
                    'msg' => $e -> getMessage(),
                    'traceString' => $e -> getTraceAsString(),
                );
                self::_write_log($logArray);
            }
        }

        /**
         * 日志记录
         * @access protected
         * @param array $logArray
         */
        protected function _write_log(array $logArray){
        	$logger = Log::getInstance();
        	$logger -> errors_info($logArray);
        }

    }