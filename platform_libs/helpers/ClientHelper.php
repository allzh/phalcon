<?php 

    /**
     * 公共辅助类
     */

    namespace platform\helpers;
    
    class ClientHelper {
    	
        /**
         * 获取客户端IP
         * @static
         * @access public
         * @return string
         */
        static public function get_client_ip(){
            if(!empty($_SERVER['HTTP_X_REAL_IP'])){
                $clientIp = $_SERVER['HTTP_X_REAL_IP'];
            }else if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
                $clientIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
            }else if(!empty($_SERVER['HTTP_REMOTE_HOST'])){
                $clientIp = $_SERVER['HTTP_REMOTE_HOST'];
            }else if(!empty($_SERVER['REMOTE_ADDR'])){
                $clientIp = $_SERVER['REMOTE_ADDR'];
            }else{
                $clientIp = '127.0.0.1';
            }
            return $clientIp;
        }
    }