<?php
	
	/**
	 * 检查目录可写性
	 * @param $dir 目录路径
	 */
	function dir_writeable($dir) {
		$writeable = 0;
		if(is_dir($dir)) {
			if($fp = @fopen("$dir/chkdir.test", 'w')) {
				@fclose($fp);
				@unlink("$dir/chkdir.test");
				$writeable = 1;
			} else {
				$writeable = 0;
			}
		}
		return $writeable;
	}
	
	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	function safe_replace($string) {
		$string = str_replace('%20','',$string);
		$string = str_replace('%27','',$string);
		$string = str_replace('%2527','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace('"','&quot;',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('"','',$string);
		$string = str_replace(';','',$string);
		$string = str_replace('<','&lt;',$string);
		$string = str_replace('>','&gt;',$string);
		$string = str_replace("{",'',$string);
		$string = str_replace('}','',$string);
		$string = str_replace('\\','',$string);
		return $string;
	}
	
	/**
	 * xss过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	function remove_xss($string) {
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $string);
	
		$parm1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	
		$parm2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	
		$parm = array_merge($parm1, $parm2);
	
		for ($i = 0; $i < sizeof($parm); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($parm[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[x|X]0([9][a][b]);?)?';
					$pattern .= '|(&#0([9][10][13]);?)?';
					$pattern .= ')?';
				}
				$pattern .= $parm[$i][$j];
			}
			$pattern .= '/i';
			$string = preg_replace($pattern, ' ', $string);
		}
		return $string;
	}
	
	/**
	 * 将文本格式成适合js输出的字符串
	 * @param string $string 需要处理的字符串
	 * @param intval $isjs 是否执行字符串格式化，默认为执行
	 * @return string 处理后的字符串
	 */
	function format_js($string, $isjs = 1) {
		$string = addslashes(str_replace(array("\r", "\n", "\t"), array('', '', ''), $string));
		return $isjs ? 'document.write("'.$string.'");' : $string;
	}
	
	/**
	 * 获取当前页面完整URL地址
	 */
	function get_url() {
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? safe_replace($_SERVER['PHP_SELF']) : safe_replace($_SERVER['SCRIPT_NAME']);
		$path_info = isset($_SERVER['PATH_INFO']) ? safe_replace($_SERVER['PATH_INFO']) : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? safe_replace($_SERVER['REQUEST_URI']) : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.safe_replace($_SERVER['QUERY_STRING']) : $path_info);
		return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}
	
	function get_curhost(){
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		return $sys_protocal.(isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'] : '');
	}
	/**
	 * 字符截取 支持UTF8/GBK
	 * @param $string
	 * @param $length
	 * @param $dot
	 */
	function str_cut($string, $length, $dot = '...') {
		$strlen = strlen($string);
		if($strlen <= $length) return $string;
		$string = str_replace(array(' ','&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array('∵',' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
		$strcut = '';
		if(strtolower(CHARSET) == 'utf-8') {
			$length = intval($length-strlen($dot)-$length/3);
			$n = $tn = $noc = 0;
			while($n < strlen($string)) {
				$t = ord($string[$n]);
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif(194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif(224 <= $t && $t <= 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif(240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif(248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
			$strcut = str_replace(array('∵', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), array(' ', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), $strcut);
		} else {
			$dotlen = strlen($dot);
			$maxi = $length - $dotlen - 1;
			$current_str = '';
			$search_arr = array('&',' ', '"', "'", '“', '”', '—', '<', '>', '·', '…','∵');
			$replace_arr = array('&amp;','&nbsp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;',' ');
			$search_flip = array_flip($search_arr);
			for ($i = 0; $i < $maxi; $i++) {
				$current_str = ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
				if (in_array($current_str, $search_arr)) {
					$key = $search_flip[$current_str];
					$current_str = str_replace($search_arr[$key], $replace_arr[$key], $current_str);
				}
				$strcut .= $current_str;
			}
		}
		return $strcut.$dot;
	}
	
	/**
	 * 生成纯粹的URL地址无分页参数
	 * @param $actionName 控制器方法名 一般情况下不需要 当分页URL有错时使用
	 */
	function dep_Url($actionName=''){
		$url=get_url();
		
		//过滤掉分页数据
		$url=preg_replace("/(\?)?p=\d+/i","\\1",$url);
		if(!empty($action)&&!strpos($url, $actionName)){
			if(substr($url,-1,1)=="/"){
				$url=substr($url,0,-1);
			}
			$url.="/".$actionName;
		}
		while(substr($url,-1,1)=="&"){
			$url=substr($url,0,-1);
		}
	
		return $url;
	}
	
	/**
	 * 将URL参数转成对应数组，应对REWRITE后request无法获取参数的问题
	 * @param $key 获取的参数名
	 */
	function getParms($key=""){
		$router = new Phalcon\CLI\Router();
		$parms=$router->getParams();
		$newParms=array();
		$keys=array();
		$vals=array();
		foreach($parms as $k=>$v){
			if($k&1){
				//奇数
				$vals[]=$v;
			}else{
				//偶数
				$keys[]=$v;
			}
		}
		foreach($keys as $k=>$v){
			$newParms[$v]=$vals[$k];
		}
		if(empty($key)){
			return $newParms;
		}else{
			return $newParms[$key];
		}
	}
	
	/**
	 * 分页函数
	* $num 总条数,$perpage 一页多少条, $curpage 当前页, $mpurl url, $page = 10 显示几个分页
	*
	*/
	function multi($num, $perpage, $curpage, $mpurl, $page = 10) {
		$multipage = '';
		$realpages = 1;

		if ($num > $perpage){

			$offset = 4;
			$realpages = @ceil($num / $perpage);
			
			if ($page > $realpages) {
				$form = 1;
				$to = $realpages;
			}else {
				$form = $curpage - $offset;
				$to = $form + $page - 1;
				if($to>$realpages) { //add by zhujincong  2016/10/09
					$to = $realpages;
				}
				if ($form < 1) {
					$form = 1;
					//$to = $curpage + 1 - $form;
					if ($to - $form < $page) {
						$to = $page;
					}
				}
			}
	
			$multipage = ($curpage > 1 ? '<li class="prev"><a href="'.$mpurl.'&p='.($curpage - 1).'">prev</a></li> ' : '');
			for ($i = $form; $i <= $to; $i++) {
				$multipage .= $i == $curpage ? '<li class="disable"><a>'.$i.'</a></li> ' :
				'<li><a href="'.$mpurl.'&p='.$i.'">'.$i.'</a></li> ';
			}
			$multipage .= $curpage < $realpages ? '<li class="next"><a href="'.$mpurl.'&p='.($curpage + 1).'">next</a></li> ' : '';
			$multipage = $multipage ? '<div class="dataTables_paginate paging_bootstrap"><ul class="pagination" style="visibility: visible;">'.$multipage.'</ul></div>' : '';
		}
	
		return $multipage;
	}
	
	/**
	 * 将字符串转换为数组
	 *
	 * @param	string	$data	字符串
	 * @return	array	返回数组格式，如果，data为空，则返回空数组
	 */
	function string2array($data) {
		$array=array();
		if($data == '') return array();
		@eval("\$array = $data;");
		return $array;
	}
	/**
	 * 将数组转换为字符串
	 *
	 * @param	array	$data		数组
	 * @param	bool	$isformdata	如果为0，则不使用new_stripslashes处理，可选参数，默认为1
	 * @return	string	返回字符串，如果，data为空，则返回空
	 */
	function array2string($data, $isformdata = 1) {
		if($data == '') return '';
		if($isformdata) $data = new_stripslashes($data);
		return addslashes(var_export($data, TRUE));
	}
	
	/**
	 * 返回经addslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	function new_addslashes($string){
		if(!is_array($string)) return addslashes($string);
		foreach($string as $key => $val) $string[$key] = new_addslashes($val);
		return $string;
	}
	
	/**
	 * 返回经stripslashes处理过的字符串或数组
	 * @param $string 需要处理的字符串或数组
	 * @return mixed
	 */
	function new_stripslashes($string) {
		if(!is_array($string)) return stripslashes($string);
		foreach($string as $key => $val) $string[$key] = new_stripslashes($val);
		return $string;
	}
	
	/**
	 * 加载模板标签缓存
	 * @param string $name 缓存名
	 * @param integer $times 缓存时间 默认一小时
	 */
	function tpl_cache($name,$times = 3600,$filepath='tpl_data',$type=CACHE_TYPE) {

		$redisdata=redisGet($name);
		if(!empty($redisdata)){
			return $redisdata;
		}
		
		if($type=='memcache'){
			return getcache($name);
		}else{
			$info = getcacheinfo($name, $filepath);
			if (SYS_TIME - $info['filemtime'] >= $times) {
				return false;
			} else {
				return getcache($name,$filepath);
			}
		}
	}
	

	function redisSet($name,$data,$timeout=0){
		$name=md5($name);
		$cacheconfig = dgj_get_cfg(MAST_CACHE);
		$cache = cacheFactory::get_instance($cacheconfig)->get_cache(MAST_CACHE_SERVER);
		return $cache->set($name, $data, $timeout);
	}
	
	function redisGet($name){
		$name=md5($name);
		$cacheconfig = dgj_get_cfg(MAST_CACHE);
		$cache = cacheFactory::get_instance($cacheconfig)->get_cache(MAST_CACHE_SERVER);
		return $cache->get($name);
	}
	
	function redisDel($name){
		$name=md5($name);
		$cacheconfig = dgj_get_cfg(MAST_CACHE);
		$cache = cacheFactory::get_instance($cacheconfig)->get_cache(MAST_CACHE_SERVER);
		return $cache->delete($name);
	}
	
	function redisFlush(){
		$cacheconfig = dgj_get_cfg(MAST_CACHE);
		$cache = cacheFactory::get_instance($cacheconfig)->get_cache(MAST_CACHE_SERVER);
		return $cache->flush();
	}
	
	function redisPush($name,$val){
		$cacheconfig = dgj_get_cfg(MAST_CACHE);
		$cache = cacheFactory::get_instance($cacheconfig)->get_cache(MAST_CACHE_SERVER);
		return $cache->push($name,$val);
	}
	
	/**
	 * 写入缓存，默认为文件缓存，不加载缓存配置。
	 * @param $name 缓存名称
	 * @param $data 缓存数据
	 * @param $filepath 数据路径（模块名称） caches/cache_$filepath/
	 * @param $timeout 过期时间
	 */
	function setcache($name, $data, $filepath='tpl_data', $timeout=0) {
		if(empty($data)){
			return false;
		}
		redisSet($name, $data,$timeout);
		redisPush("dgjtpquee",$name."$".$filepath);	//分布式用
		
		if(CACHE_TYPE=='memcache'){
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
			$cache->set($name, $data, $timeout, '', $filepath);
		}
		//文件缓存备份
		$cache_file = cacheFactory::get_instance()->get_cache('file');
		$cache_file->set($name, $data, $timeout, '', $filepath);
		return true;
	}
	
	/**
	 * 读取缓存，默认为文件缓存，不加载缓存配置。
	 * @param string $name 缓存名称
	 * @param $filepath 数据路径（模块名称） caches/cache_$filepath/
	 */
	function getcache($name, $filepath='') {
		$data=redisGet($name);
		if(!empty($data)){
			return $data;
		}
		
		
		if(CACHE_TYPE=='file'){
			$cache = cacheFactory::get_instance()->get_cache('file');
			$data = $cache->get($name, '', '', $filepath);
		}else{
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
			$data = $cache->get($name, '', '', $filepath);
			//读取最后没过期的缓存
			if(!empty($data)){
				return $data;
			}else{
				$cache = cacheFactory::get_instance()->get_cache('file');
				$data = $cache->get($name, '', '', $filepath);
			}
		}
		return $data;
	}
	
	/**
	 * 删除缓存，默认为文件缓存，不加载缓存配置。
	 * @param $name 缓存名称
	 * @param $filepath 数据路径（模块名称） caches/cache_$filepath/
	 */
	function delcache($name, $filepath='tpl_data') {
		@redisDel($name);
		
		if(CACHE_TYPE=='file'){
			$cache = cacheFactory::get_instance()->get_cache('file');
		}else{
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
		}
		return $cache->delete($name, '', '', $filepath);
	}
	
	/**
	 * 读取缓存，默认为文件缓存，不加载缓存配置。
	 * @param string $name 缓存名称
	 * @param $filepath 数据路径（模块名称） caches/cache_$filepath/
	 */
	function getcacheinfo($name, $filepath='') {
		if(CACHE_TYPE=='file'){
			$cache = cacheFactory::get_instance()->get_cache('file');
		}else{
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
		}
		return $cache->cacheinfo($name, '', '', $filepath);
	}
	
	/**
	 * 得到缓存服务器的状态信息
	 */
	function getcachestatus(){
		if(CACHE_TYPE=='file'){
			$filepath=CACHE_PATH;
			$cache = cacheFactory::get_instance()->get_cache('file');
		}else{
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
		}
		return $cache->getStatus($filepath);
	}
	
	/**
	 * 清空标签缓存
	*/
	function clearcache(){
		if(CACHE_TYPE=='file'){
			$filepath=CACHE_PATH."caches_tpl_data".DIRECTORY_SEPARATOR."caches_data";
			$cache = cacheFactory::get_instance()->get_cache('file');
		}else{
			$cacheconfig = dgj_get_cfg(SLAVE_CACHE);
			$cache = cacheFactory::get_instance($cacheconfig)->get_cache(CACHE_SERVER);
		}
		$data=$cache->delTpkey($filepath);
		if(is_array($data)&&!empty($data)){
			foreach($data as $v){
				@redisDel($v);
			}
		}
		return true;
	}
	
	/**
	 * 生成sql语句，如果传入$in_cloumn 生成格式为 IN('a', 'b', 'c')
	 * @param $data 条件数组或者字符串
	 * @param $front 连接符
	 * @param $in_column 字段名称
	 * @return string
	 */
	function to_sqls($data, $front = ' AND ', $in_column = false) {
		if($in_column && is_array($data)) {
			$ids = '\''.implode('\',\'', $data).'\'';
			$sql = "$in_column IN ($ids)";
			return $sql;
		} else {
			if ($front == '') {
				$front = ' AND ';
			}
			if(is_array($data) && count($data) > 0) {
				$sql = '';
				foreach ($data as $key => $val) {
					$sql .= $sql ? " $front `$key` = '$val' " : " `$key` = '$val' ";
				}
				return $sql;
			} else {
				return $data;
			}
		}
	}
	
	
	/**
	 * 模板调用
	 *
	 * @param $module  改为传路径 支持多级目录用
	 * @param $template
	 * @param $style
	 * @return unknown_type
	 */
	function template($module = 'Index', $template = 'index',$style='') {

		$module = str_replace('/', DIRECTORY_SEPARATOR, $module);
		if(!$style) $style = 'default';
		if(substr($template,-4)!="html"&&!empty($template)){
			$template.=".html";
		}
		$template_cache = new template();
		$fpat="";
		if(!empty($template)){
			$fpat=DIRECTORY_SEPARATOR.$template;
		}
		$compiledtplfile = CACHE_PATH.DIRECTORY_SEPARATOR.'caches_template'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat.'.php';
		if(file_exists(BBG_PATH.'templates'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat)) {
			if(!file_exists($compiledtplfile) || (@filemtime(BBG_PATH.'templates'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat) > @filemtime($compiledtplfile))) {
				$template_cache->template_compile($module, $template, $style);
			}
		} else {
			$compiledtplfile = CACHE_PATH.DIRECTORY_SEPARATOR.'caches_template'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat.'.php';
			if(!file_exists($compiledtplfile) || (file_exists(BBG_PATH.'templates'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat) && filemtime(BBG_PATH.'templates'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat) > filemtime($compiledtplfile))) {
				$template_cache->template_compile($module, $template, $style);
			} elseif (!file_exists(BBG_PATH.'templates'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat)) {
				showmessage('Template does not exist.'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.$fpat);
			}
		}
		return $compiledtplfile;
	}
	
	/**
	 * 提示信息页面跳转，跳转地址如果传入数组，页面会提示多个地址供用户选择，默认跳转地址为数组的第一个值，时间为2秒。
	 * showmessage('登录成功', array('默认跳转地址'=>'http://www.aomygod.com/'));
	 * @param string $msg 提示信息
	 * @param mixed(string/array) $url_forward 跳转地址
	 * @param int $ms 跳转等待时间
	 */
	function showmessage($msg, $url_forward = 'goback', $ms = 2000) {
		include(template('public', 'message.html'));
		exit;
	}
	
	/**
	 * 转义 javascript 代码标记
	 *
	 * @param $str
	 * @return mixed
	 */
	function trim_script($str) {
		if(is_array($str)){
			foreach ($str as $key => $val){
				$str[$key] = trim_script($val);
			}
		}else{
			$str = preg_replace ( '/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str );
			$str = preg_replace ( '/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str );
			$str = preg_replace ( '/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str );
			$str = str_replace ( 'javascript:', 'javascript：', $str );
		}
		return $str;
	}
	
	/**
	 * 模板预览
	 * @param $module
	 * @param $template
	 * @param $style
	 * @return PHP运行结果  
	*/
	function preview($module = 'Index', $template = '',$style='',$params=array(),$timeout=30){
		$module = str_replace('/', DIRECTORY_SEPARATOR, $module);
		if(!$style) $style = 'default';
		if(empty($template)){
			$compiledtplfile = CACHE_PATH.DIRECTORY_SEPARATOR.'caches_template'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.'.php';
		}else{
			$compiledtplfile = CACHE_PATH.DIRECTORY_SEPARATOR.'caches_template'.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.$module.DIRECTORY_SEPARATOR.$template.'.php';
		}
		if(!empty($params)){
			extract($params);
		}
			if(IS_CACHE){
				$cachename=md5($module.$template);
				$data=tpl_cache($cachename,$timeout,'Content');
				if(!empty($data)){
					echo $data;
				}else{
					if(!file_exists($compiledtplfile)){
						$str='Template does not exist.'.$compiledtplfile;
						return $str;
					}
					ob_start();
					require_once $compiledtplfile;
					$data = ob_get_contents();
					ob_end_clean();
					setcache($cachename, $data, 'Content', $timeout);
					echo $data;
				}
			}else{
				if(!file_exists($compiledtplfile)){
					$str='Template does not exist.'.$compiledtplfile;
					return $str;
				}
				ob_start ();
				require_once $compiledtplfile;
				$data = ob_get_contents ();
				ob_end_clean ();
				echo $data;
			}
			return true;
	}
	
	/**
	 * 生成上传附件验证
	 * @param $args   参数
	 * @param $operation   操作类型(加密解密)
	 */
	
	function upload_key($args) {
		$pc_auth_key = md5(bbg_base::load_config('system','auth_key').$_SERVER['HTTP_USER_AGENT']);
		$authkey = md5($args.$pc_auth_key);
		return $authkey;
	}
	
	/**
	 * 读取swfupload配置类型
	 * @param array $args flash上传配置信息
	 */
	function getswfinit($args) {
		$site_allowext = bbg_base::load_config('system','upload_allowext');
		$args = explode(',',$args);
		$arr['file_upload_limit'] = intval($args[0]) ? intval($args[0]) : '8';
		$args['1'] = ($args[1]!='') ? $args[1] : $site_allowext;
		$arr_allowext = explode('|', $args[1]);
		foreach($arr_allowext as $k=>$v) {
			$v = '*.'.$v;
			$array[$k] = $v;
		}
		$upload_allowext = implode(';', $array);
		$arr['file_types'] = $upload_allowext;
		$arr['file_types_post'] = $args[1];
		$arr['allowupload'] = intval($args[2]);
		$arr['thumb_width'] = intval($args[3]);
		$arr['thumb_height'] = intval($args[4]);
		$arr['watermark_enable'] = ($args[5]=='') ? 1 : intval($args[5]);
		return $arr;
	}
	/**
	 * 判断是否为图片
	 */
	function is_image($file) {
		$ext_arr = array('jpg','gif','png','bmp','jpeg','tiff');
		$ext = fileext($file);
		return in_array($ext,$ext_arr) ? $ext_arr :false;
	}
	
	/**
	 * 判断是否为视频
	 */
	function is_video($file) {
		$ext_arr = array('rm','mpg','avi','mpeg','wmv','flv','asf','rmvb');
		$ext = fileext($file);
		return in_array($ext,$ext_arr) ? $ext_arr :false;
	}
	
	/**
	 * 取得文件扩展
	 *
	 * @param $filename 文件名
	 * @return 扩展名
	 */
	function fileext($filename) {
		return strtolower(trim(substr(strrchr($filename, '.'), 1, 10)));
	}
	
	/**
	 * 转换字节数为其他单位
	 *
	 *
	 * @param	string	$filesize	字节大小
	 * @return	string	返回大小
	 */
	function sizecount($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize.' Bytes';
		}
		return $filesize;
	}
	

	/**
	 * 获取可用的模板信息
	 * @return multitype:string
	 */
	function get_sys_template(){
		$template_dir        = @opendir(BBG_PATH . 'templates'.DIRECTORY_SEPARATOR);
		$cur_temp=array();
		while($file=readdir($template_dir)){
			if ($file != '.' && $file != '..' && is_dir(BBG_PATH. 'templates'.DIRECTORY_SEPARATOR. $file) && $file != '.svn' && $file != 'index.html')
			{
				$cur_temp[]=$file;
			}
		}
		return $cur_temp;
	}
	/**
	 * 获得模版的信息
	 *
	 * @access  private
	 * @param   string      $template_name      模版名
	 * @return  array
	 */
	function get_template_info($template_name)
	{
		$info = array();
		$ext  = array('png', 'gif', 'jpg', 'jpeg');
	
		$info['code']       = $template_name;
		$info['screenshot'] = '';
	

			foreach ($ext AS $val)
			{
				if (file_exists(BBG_PATH.'templates' .DIRECTORY_SEPARATOR. $template_name .DIRECTORY_SEPARATOR. "screenshot.$val"))
				{
					$info['screenshot'] = '../app/templates/' . $template_name . "/screenshot.$val";
	
					break;
				}
			}
	
		$doc_path = BBG_PATH.'templates/' . $template_name . '/config.php';

		if (file_exists($doc_path) && !empty($template_name))
		{
			$arr=include $doc_path;
			
			
			$info['name']       =$arr['name'];
			$info['uri']        = $arr['homepage'];
			$info['desc']       = $arr['desc'];
			$info['version']    = $arr['version'];
			$info['author']     = $arr['author'];
	
		}
		else
		{
			$info['name']       = '';
			$info['uri']        = '';
			$info['desc']       = '';
			$info['version']    = '';
			$info['author']     = '';
		}
	
		return $info;
	}
	
	/**
	 * 返回经htmlspecialchars处理过的字符串或数组
	 * @param $obj 需要处理的字符串或数组
	 * @return mixed
	 */
	function new_html_special_chars($string) {
		$encoding = 'utf-8';
		if(!is_array($string)) return htmlspecialchars($string,ENT_QUOTES,$encoding);
		foreach($string as $key => $val) $string[$key] = new_html_special_chars($val);
		return $string;
	}
	
	/**
	 * 调取公用模板标签
	 * @param $book 模板变量名
	 */
	function pubtemp($book,$isvis = 0){
		if(empty($book)){
			return false;
		}
		$TemPub=new TemPub();
		$info=$TemPub->findFirst(array(
				"conditions"=>"book='".$book."'",
				"columns"=>"template,id",
		));
		if(!$info){
			return false;
		}else{
			
			if($GLOBALS['url_set']){
				extract($GLOBALS['url_set']);
			}
			$template_cache=new template();
			$data=new_stripslashes($info->template);
			$compfile=$template_cache->template_code_compile($data,'pubdgj'.uniqid(),'default',$isvis);
			ob_start();
			require_once $compfile;
			$data = ob_get_contents();
			ob_end_clean();
			echo $data;
			@unlink($compfile);
			return true;
		}
	}
	

	/**
	 * 缓存页面规则数据
	 * @return boolean|unknown
	 */
	function cacheUrls(){
		$Urlrule=new Urlrule();
		$infos=$Urlrule->find(array('conditions'=>"is_release = '1'", "columns"=>"url", "order" => "id desc"));
		if(!$infos){
			return false;
		}
		$infos=$infos->toArray();
		$Cache = \platform\libs\cache\Cache::getInstance();
		$Cache->set("UrlRule",$infos,86400);
		//setcache("dgjurl", $infos,"");
		return $infos;
	}
	
	/**
	 * 缓存域名规则数据
	 * @return boolean|unknown
	 */
	function cacheHosts(){
		if(IS_CACHE){
			$Hosts=new Hosts();
			$infos=$Hosts->find();
			if(!$infos){
				return false;
			}
			$infos=$infos->toArray();
	
			setcache("dgjhosts", $infos,"");
			return $infos;
		}else{
			return false;
		}
	}
	
	/**
	 * 查找并替换字符串中的重复文字
	 * @param string $need
	 * @param string $rep
	 * @param int $pos
	 * @param string $str
	 * @return string
	 */
	function dgjReplace($need,$rep,$pos,$str){
		$tempArr=explode($need,$str);
		$size=sizeof($tempArr);

		if($size<$pos+1){
			return $str;
		}
		
		$temp="";
		foreach($tempArr as $k=>$v){
			$temp.=$v;
			if($k==$size-1){
				break;
			}
			if($k==$pos){
				$temp.=$rep;
			}else{
				$temp.=$need;
			}
		}
		return $temp;
	}
	
	/**
	 * 获取conf配置信息
	 * @param string $var
	 * @param string $type
	 * @return mixed
	 */
	function dgj_get_cfg($var,$type='json'){
		if(!$var){
			return false;
		}
		$temp=get_cfg_var($var);
		if(!$temp){
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
	 * 分发消息
	 * @param Array $data
	 */
	function assignMsg($data=array()){
		$Apis=new Apis();
		$infos=$Apis->find(array(
			'conditions'=>'disabled=0',
		));
		if(count($infos)>0){
			$infos=$infos->toArray();
			$Snoopy=new Snoopy();
			$Snoopy->read_timeout=20;
			foreach($infos as $k=>$v){
				if( $v['platform']!=$data['page_type'] ) continue; //如果页面对应平台与 API接口对应的网站 不相符的话，不会发起页面请求   2017/8/28  zjc
				$Snoopy->submit($v['url']."/Msgcenter",$data);
// 				echo $Snoopy->results;die;
// 				log2([$Snoopy->results,time()]);
			}
			//刷新CND
			$Chinanetcenter = new Chinanetcenter();
			$url = WEB_URL.$data['url']; 
			$Chinanetcenter->clear_file($url);
		}else{
			return false;
		}
	}
	
	function log2($message) {
	    $logger = new Phalcon\Logger\Adapter\File(check_dir(LOG_PATH . "debug112_".date("Y-m-d").".log"));
	    $logger->info(json_encode($message));
	}
	
	
	function tpuseArray($code,$ttpl,$tdir,$tfile){
		if(preg_match_all("/\{template\s+(.+)\}/i",$code,$match)){
			$match=$match[0];
			$cachekey="pubtemp";
			$cachedata=tpl_cache($cachekey,0,"Content");
			if(!$cachedata){
				$cachedata=array();
			}
			foreach($match as $v){
				$v=str_replace("\"","'",$v);
				preg_match("/'(.+?)'/i",$v,$bs);
				if(isset($bs[1])&&!empty($bs[1])){
					$temp=$ttpl.'|'.$tdir.'|'.$tfile;
					if(isset($cachedata[$bs[1]])){
						if(!in_array($temp,$cachedata[$bs[1]])){
							$cachedata[$bs[1]][]=$temp;
						}
					}else{
						$cachedata[$bs[1]][]=$temp;
					}
				}
			}
			setcache($cachekey, $cachedata,"Content");
		}else{
			return false;
		}
	}

	/**
	 * 标签索引存储
	 * @param unknown $op
	 * @param unknown $cachename
	 * @param unknown $param
	 */
	function tpCacheIndex($op,$cachename,$param,$path){
		if(empty($op)||empty($path)){
			return false;
		}
		//$path路径要支持多级目录
		$path=str_replace("\\","|",$path);
		
		if($op=='product'){
			$cachekey="dgjtpgoods";
			$data=tpl_cache($cachekey,0,"Content");
			if(empty($data)){
				$data=array();
			}
			if(!isset($param['productid'])||empty($param['productid'])){
				return false;
			}
			$goodids=explode(",",$param['productid']);
			foreach($goodids as $v){
				if(array_key_exists($v, $data)){
					$data[$v][]=array($cachename,$path);
					$data[$v]=array_unique_2d($data[$v]);
				}else{
					$data[$v][]=array($cachename,$path);
				}
			}
			setcache($cachekey, $data,"Content");
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * 二维数组去重
	 * @param unknown $array2D
	 * @return multitype:mixed
	 */
	function array_unique_2d($array2D) {
		$temp = $res = array ();
		
		foreach ( $array2D as $v ) {
			
			$v = json_encode ( $v ); // 降维,将一维数组转换字符串
			
			$temp [] = $v;
		}
		
		$temp = array_unique ( $temp ); // 去掉重复的字符串,也就是重复的一维数组
		
		foreach ( $temp as $item ) {
			
			$res [] = json_decode ( $item, true ); // 再将拆开的数组重新组装
		}
		
		return $res;
	}


    /*
     * 验证公司链接
     * */
	function check_com_url($url){
        if(!$url) return false;

        $urlarray = parse_url($url);
        if(!isset($urlarray['host'])){
            return false;
        }
        if(!preg_match('/.*(aomygod)\.com/i',$urlarray['host'])){
            return false;
        }
        return true;
    }

    /*
     * 验证是url
     * */
    function check_url($url){
        if(!$url) return false;

        $urlhttp = strtolower(substr($url,0,7));
        $urlhttps = strtolower(substr($url,0,8));

        if($urlhttp == 'http://' || $urlhttps == 'https://') {
            return true;
        }
        return false;
    }