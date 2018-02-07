<?php

    /**
     * 价格格式化
     * @category platform
     * @package platform.libs.format
     * @author kuangwenjie@bubugao.com
     * @version $Id
     * @date 2014-12-08 10:55:00
     *
     */

    namespace platform\libs\format;

    use platform\config\Config;

    class PriceFormat {

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
         * 格式化价格数据
         * @access public
         * @param float $price 价格
         * @param int $priceAccracy 小数点精确度，即保留小数点后几位
         * @param boolean $format Format a number with grouped thousands
         * @return float $price
         */
        public function format_price($price, $priceAccracy=null, $format=false){
            if(empty($priceAccracy)){
                $systemConfig = Config::getInstance('system');
                $priceAccracy = $systemConfig -> get('price_accuracy');
            }

            $priceUnit = $this -> get_price_unit();
            $price = floatval($price / $priceUnit);
            $price < 0 && $price = 0;
            $price = substr(sprintf('%.10f', $price), 0, ($priceAccracy - 10));
            if($format){
                $price = number_format($price, $priceAccracy);
            }
            return $price;
        }

        /**
         * 获取价格单位（单位为分，会返回100；单位为角，会返回10）
         * @access public
         * @param int $priceUnit
         * @return number
         */
        public function get_price_unit($priceUnit=null){
        	if(empty($priceUnit)){
        		$systemConfig = Config::getInstance('system');
        		$priceUnit = $systemConfig -> get('price_unit');
        	}
        	switch($priceUnit){
        		case '分':
        		    $priceUnit = 100;
        		    break;
        		case '角':
        		    $priceUnit = 10;
        		    break;
        		case '元':
        		    $priceUnit = 1;
        		    break;
        		default:
        		    $priceUnit = 100;
        		    break;
        	}
        	return $priceUnit;
        }

        /**
         * 显示价格，该处主要用来搜索过滤条件显示
         * @category
         * @author chengbin@bubugao.com
         * @param $price 价格
         * @param
         * @return int 需要显示的价格
         */
        public function format_show_price($price) {
            $priceUnit = $this->get_price_unit ();
            $show_price = intval ( $price / $priceUnit );
            return $show_price;
        }
    }