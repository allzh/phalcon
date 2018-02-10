<?php
/**
 * memcache 缓存处理
 */ 
class cacheMemcache {

	private $memcache = null;

	public function __construct() {
		$this->memcache = new Memcache();
		$this->memcache->connect(MEMCACHE_HOST, MEMCACHE_PORT, MEMCACHE_TIMEOUT);
	}

	public function memcache() {
		$this->__construct();
	}

	public function get($name) {
		$value = $this->memcache->get($name);
		return $value;
	}

	public function set($name, $value, $ttl = 0, $ext1='', $ext2='') {
		return $this->memcache->set($name, $value, false, $ttl);
	}

	public function delete($name) {
		return $this->memcache->delete($name);
	}

	public function flush() {
		return $this->memcache->flush();
	}
	
	public function delTpkey(){
		$ret=array();
		$items=$this->memcache->getExtendedStats ('items');
		$items=$items[MEMCACHE_HOST.":".MEMCACHE_PORT]['items'];
		foreach($items as $k=>$v){
			$number=$k;
			$str=$this->memcache->getExtendedStats ("cachedump",$number,0);
			$line=$str[MEMCACHE_HOST.":".MEMCACHE_PORT];
			if(is_array($line)&&count($line)>0){
				foreach($line as $key=>$val){
					if(substr($key,0,3)=="tp_"){
						$ret[]=$key;
						$this->memcache->delete($key);
					}
				}
			}
		}
		return $ret;
	}
	
	public function getStatus($ext=''){
		$resp = $this->sendMemcacheCommand(MEMCACHE_HOST,MEMCACHE_PORT,'stats');
		return $resp;
	}
	
	function sendMemcacheCommand($server,$port,$command){
	
		$s = @fsockopen($server,$port);
		if (!$s){
			die("Cant connect to:".$server.':'.$port);
		}
	
		fwrite($s, $command."\r\n");
	
		$buf='';
		while ((!feof($s))) {
			$buf .= fgets($s, 256);
			if (strpos($buf,"END\r\n")!==false){ // stat says end
				break;
			}
			if (strpos($buf,"DELETED\r\n")!==false || strpos($buf,"NOT_FOUND\r\n")!==false){ // delete says these
				break;
			}
			if (strpos($buf,"OK\r\n")!==false){ // flush_all says ok
				break;
			}
		}
		fclose($s);
		return $this->parseMemcacheResults($buf);
	}
	function parseMemcacheResults($str){
	
		$res = array();
		$lines = explode("\r\n",$str);
		$cnt = count($lines);
		for($i=0; $i< $cnt; $i++){
			$line = $lines[$i];
			$l = explode(' ',$line,3);
			if (count($l)==3){
				$res[$l[0]][$l[1]]=$l[2];
				if ($l[0]=='VALUE'){ // next line is the value
					$res[$l[0]][$l[1]] = array();
					list ($flag,$size)=explode(' ',$l[2]);
					$res[$l[0]][$l[1]]['stat']=array('flag'=>$flag,'size'=>$size);
					$res[$l[0]][$l[1]]['value']=$lines[++$i];
				}
			}elseif($line=='DELETED' || $line=='NOT_FOUND' || $line=='OK'){
				return $line;
			}
		}
		return $res;
	
	}
}
?>