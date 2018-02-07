<?php 

    /**
     * 重量格式化
     * @category platform
     * @package platform.libs.format
     * @author kuangwenjie@bubugao.com
     * @version $Id
     * @date 2014-12-08 11:16:00
     *
     */

    namespace platform\libs\format;
    
    use platform\config\Config;
    use platform\helpers\MathHelper;
    
    class WeightFormat {
    	
        private static $_instance;
        
        /**
         * 单例
         * @return object
         */
        public static function getInstance(){
            if(!(self::$_instance instanceof self)){
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        
        /**
         * 防止克隆单例对象
         */
        public function __clone() {
            trigger_error('Clone is not allow!', E_USER_ERROR);
        }
        
        private function __construct(){
             
        }
        
        /**
         * 格式化重量
         * @access public
         * @param float $weight
         * @param int $weightAccuracy
         * @return float $weight
         */
        public function format_weight($weight, $weightAccuracy=null){
            if(empty($weightAccuracy)){
                $systemConfig = Config::getInstance('system');
                $weightAccuracy = $systemConfig -> get('weight_accuracy');
            }
            $weightUnit = $this -> get_weight_unit();
            
            $weight = floatval($weight / $weightUnit);
            $weight < 0 && $weight = 0;
            //保留三位小数
            $weight = substr(sprintf('%.10f', $weight), 0, ($weightAccuracy - 9));
            //对千分位小数进行向上取整（产品确认）
            //$weight = MathHelper::float_ceil($weight, $weightAccuracy);
            //格式化2位小数
            $weight = number_format($weight, $weightAccuracy);
            return $weight;
        }
        
        /**
         * 获取重量单位（单位为克时，返回1000；单位为千克时，返回1）
         * @access public
         * @param int $weightUnit
         * @return number
         */
        public function get_weight_unit($weightUnit=null){
        	if(empty($weightUnit)){
        		$systemConfig = Config::getInstance('system');
        		$weightUnit = $systemConfig -> get('weight_unit');
        	}
        	switch($weightUnit){
        		case '克':
        		    $weightUnit = 1000;
        		    break;
        		case '千克':
        		    $weightUnit = 1;
        		    break;
        		default:
        		    $weightUnit = 1000;
        		    break;
        	}
        	return $weightUnit;
        }
    }