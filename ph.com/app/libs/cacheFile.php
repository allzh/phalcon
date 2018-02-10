<?php
/**
 * 文件缓存处理类
 */
class cacheFile {
	
	/*缓存默认配置*/
	protected $_setting = array(
								'suf' => '.cache.php',	/*缓存文件后缀*/
								'type' => 'array',		/*缓存格式：array数组，serialize序列化，null字符串*/
							);
	
	/*缓存路径*/
	protected $filepath = '';

	/**
	 * 构造函数
	 * @param	array	$setting	缓存配置
	 * @return  void
	 */
	public function __construct($setting = '') {
		$this->get_setting($setting);
	}
	
	/**
	 * 写入缓存
	 * @param	string	$name		缓存名称
	 * @param	mixed	$data		缓存数据
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @param	string	$module		所属模型
	 * @return  mixed				缓存路径/false
	 */

	public function set($name, $data, $setting = '', $type = 'data', $module = '') {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = 'Content';
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
	    if(!is_dir($filepath)) {
			mkdir($filepath, 0777, true);
	    }
	    if($this->_setting['type'] == 'array') {
	    	$data = "<?php\nreturn ".var_export($data, true).";\n?>";
	    } elseif($this->_setting['type'] == 'serialize') {
	    	$data = serialize($data);
	    }
	   
	    //是否开启互斥锁
		if(bbg_base::load_config('system', 'lock_ex')) {
			$file_size = file_put_contents($filepath.$filename, $data, LOCK_EX);
		} else {
			$file_size = file_put_contents($filepath.$filename, $data);
		}
	    return $file_size ? $file_size : 'false';
	}
	
	/**
	 * 获取缓存
	 * @param	string	$name		缓存名称
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @param	string	$module		所属模型
	 * @return  mixed	$data		缓存数据
	 */
	public function get($name, $setting = '', $type = 'data', $module = '') {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = 'Content';
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if (!file_exists($filepath.$filename)) {
			return false;
		} else {
		    if($this->_setting['type'] == 'array') {
		    	$data = @require($filepath.$filename);
		    } elseif($this->_setting['type'] == 'serialize') {
		    	$data = unserialize(file_get_contents($filepath.$filename));
		    }
		    
		    return $data;
		}
	}
	
	/**
	 * 删除缓存
	 * @param	string	$name		缓存名称
	 * @param	array	$setting	缓存配置
	 * @param	string	$type		缓存类型
	 * @param	string	$module		所属模型
	 * @return  bool
	 */
	public function delete($name, $setting = '', $type = 'data', $module = '') {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = 'Content';	
		$filepath = CACHE_PATH.'caches_'.$module.'/caches_'.$type.'/';
		$filename = $name.$this->_setting['suf'];
		if(file_exists($filepath.$filename)) {
			return @unlink($filepath.$filename) ? true : false;
		} else {
			return false;
		}
	}
	
	/**
	 * 和系统缓存配置对比获取自定义缓存配置
	 * @param	array	$setting	自定义缓存配置
	 * @return  array	$setting	缓存配置
	 */
	public function get_setting($setting = '') {
		if(is_array($setting)) {
			$this->_setting = array_merge($this->_setting, $setting);
		}
	}

	public function cacheinfo($name, $setting = '', $type = 'data', $module = '') {
		$this->get_setting($setting);
		if(empty($type)) $type = 'data';
		if(empty($module)) $module = 'Content';
		$filepath = CACHE_PATH.'caches_'.$module.DIRECTORY_SEPARATOR.'caches_'.$type.DIRECTORY_SEPARATOR;
		$filename = $filepath.$name.$this->_setting['suf'];
		if(file_exists($filename)) {
			$res['filename'] = $name.$this->_setting['suf'];
			$res['filepath'] = $filepath;
			$res['filectime'] = filectime($filename);
			$res['filemtime'] = filemtime($filename);
			$res['filesize'] = filesize($filename);
			return $res;
		} else {
			return false;
		}
	}
	
	public function delTpkey($path){
		
		if ($handle = opendir ($path))
		{
			$ret=array();
			while (false !== ($file = readdir($handle)))
			{
				if ($file=='.'||$file=='..') {
					continue;
				}
				$nextpath = $path . DIRECTORY_SEPARATOR . $file;
				
				if(is_dir($nextpath)){
					$this->delTpkey($nextpath);
				}else{
					//echo $file;
					$tpkey=substr($file,0,-10);
					//echo $tpkey."<br />";
					$ret[]=$tpkey;
					@unlink($nextpath);
				}
			}
			
			return $ret;
		}else{
			return false;
		}	
	}
	
	public function flush($path){
	if ($handle = opendir ($path))
		{
			while (false !== ($file = readdir($handle)))
			{
				if ($file == 'caches_template'||$file=='.'||$file=='..') {
					continue;
				} 
				$nextpath = $path . DIRECTORY_SEPARATOR . $file;
				if(is_dir($nextpath)){
					$this->flush($nextpath);
				}else{
					@unlink($nextpath);
				}
			}
			return true;
		}else{
			return false;
		}
	}
	
	public function getStatus($path)
	{
		$totalsize = 0;
		$totalcount = 0;
		$dircount = 0;
		if ($handle = opendir ($path))
		{
			while (false !== ($file = readdir($handle)))
			{
				if($file=='caches_template'||$file=='.'||$file=='..'){
					continue;
				}
				$nextpath = $path . DIRECTORY_SEPARATOR . $file;
				if ($file != '.' && $file != '..' && !is_link ($nextpath))
				{
					if (is_dir ($nextpath))
					{
						$dircount++;
						$result = $this->getStatus($nextpath);
						$totalsize += $result['size'];	
						$totalcount += $result['count'];				
						$dircount += $result['dircount'];				
					}
					elseif (is_file ($nextpath))
					{
						$totalsize += filesize ($nextpath);
						$totalcount++;
					}
				}
			}
		}
		closedir ($handle);
		$total['size'] = $this->sizeFormat($totalsize);	//文件夹大小
		$total['count'] = $totalcount;			//文件数
		$total['dircount'] = $dircount;			//目录数
		return $total;
	}
	
	private function sizeFormat($size)
	{
		$sizeStr='';
		if($size<1024)
		{
			return $size." bytes";
		}
		else if($size<(1024*1024))
		{
			$size=round($size/1024,1);
			return $size." KB";
		}
		else if($size<(1024*1024*1024))
		{
			$size=round($size/(1024*1024),1);
			return $size." MB";
		}
		else
		{
			$size=round($size/(1024*1024*1024),1);
			return $size." GB";
		}
	
	}

}

?>