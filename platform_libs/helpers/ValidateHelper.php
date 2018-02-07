<?php 

/**
 * 验证相关类
 */

namespace platform\helpers;

class ValidateHelper {
    
        /**
         * 验证手机号码（支持170|176|177|178虚拟号段）
         * @static
         * @access public
         * @param string $mobile
         * @return boolean
         */
        static public function check_mobile($mobile){
            $pattern = '/^((13[0-9])|(15[^4])|(14[57])|(17[0678])|(18[0,0-9]))\\d{8}$/';
            if(preg_match($pattern, $mobile))
                return true;
            return false;
        }
    
        /**
         * 验证固定电话
         * @static
         * @access public
         * @param string $tel
         * @return boolean
         */
        static public function check_tel($tel){
            $pattern = '/\d{7,8}|\d{3}-\d{8}|\d{4}-\d{7,8}/';
            if(preg_match($pattern, $tel))
                return true;
            return false;
        }


    /**
     * 是否是邮箱
     * @param type $email
     * @return boolean
     */
    static public function is_email($email='') {
        $email = trim($email);
        $result = preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $email) ? true : false;
        return $result;
    }
    
    /**
     * 验证是否为URL
     * @param string $url
     * @return boolean
     * @author kuangwenjie@bubugao.com
     * @date 2015-03-25 14:36:00
     */
    static public function check_url($url){
		$pattern = '/^(http)/';
		$url = trim($url);
		if(preg_match($pattern, $url)){
			return true;
		}
		return false;
    }
}