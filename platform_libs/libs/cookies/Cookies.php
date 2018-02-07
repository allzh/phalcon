<?php
namespace platform\libs\cookies;

class Cookies {
	protected $_prefix = ''; // cookie prefix
	protected $_expire = 3600; // default expire
	protected $_domain;
	
	/**
	 * 初始化
	 * 
	 * @param String $prefix
	 *        	cookie prefix
	 * @param int $expire
	 *        	过期时间
	 * @param String $securekey
	 *        	cookie secure key
	 */
	public function __construct($prefix = '', $expire = 0, $domain = '') {
		if (is_string($prefix) && $prefix != '') {
			$this->_prefix = $prefix;
		}
		
		if (is_numeric($expire) && $expire > 0) {
			$this->_expire = $expire;
		}
		
		if (is_string($domain) && $domain != '') {
			$this->_domain = $domain;
		}
	}

	/**
	 * 设置cookie
	 * 
	 * @param String $name
	 *        	cookie name
	 * @param mixed $value
	 *        	cookie value 可以是字符串,数组,对象等
	 * @param int $expire
	 *        	过期时间
	 */
	public function set($name, $value, $expire = 0) {
		$cookie_name = $this->getName($name);
		$cookie_expire = time() + ($expire ? $expire : $this->_expire);
		if ($cookie_name && $value && $cookie_expire) {
			setcookie($cookie_name, $value, $cookie_expire, "/", $this->_domain);
		}
	}

	/**
	 * 读取cookie
	 * 
	 * @param String $name
	 *        	cookie name
	 * @return mixed cookie value
	 */
	public function get($name) {
		$cookie_name = $this->getName($name);
		$cookie_value = null;
		if (isset($_COOKIE[$cookie_name])) {
			$cookie_value = $_COOKIE[$cookie_name];
		}
		return $cookie_value;
	}

	/**
	 * 清除cookie
	 * 
	 * @param String $name
	 *        	cookie name
	 */
	public function clear($name) {
		$cookie_name = $this->getName($name);
		setcookie($cookie_name);
	}

	/**
	 * 设置前缀
	 * 
	 * @param String $prefix
	 *        	cookie prefix
	 */
	public function setPrefix($prefix) {
		if (is_string($prefix) && $prefix != '') {
			$this->_prefix = $prefix;
		}
	}

	/**
	 * 设置过期时间
	 * 
	 * @param int $expire
	 *        	cookie expire
	 */
	public function setExpire($expire) {
		if (is_numeric($expire) && $expire > 0) {
			$this->_expire = $expire;
		}
	}

	/**
	 * 获取cookie name
	 * 
	 * @param String $name        	
	 * @return String
	 */
	protected function getName($name) {
		return $this->_prefix ? $this->_prefix . '_' . $name : $name;
	}
}