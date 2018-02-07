<?php
namespace platform\core;
class BaseModel extends \Phalcon\Mvc\Model{
	/**
	 * 通用的设置表名
	 * @param string $tbname
	 */
	public function diySource($tbname){
		
		$this->setSource(bbg_base::load_config("system","prefix").$tbname);
	}
	
	//忽略某些字段
	/**
	 * 忽略某些字段
	 * @param array $arr
	 */
	public function skipAttr($arr=array()){
		$this->skipAttributes($arr);
	}
	
	//test
	public function showtab(){
		return $this->getSource();
	}
	
	/**
	 * 超级创建修改数据方法,成功返回true，失败则返回错误消息数组
	 * @param array $data
	 * @return boolean|multitype:string
	 */
	public function dgjFsave($data){
		if(!is_array($data)){
			return false;
		}
		$skip=array();
		foreach($data as $k=>$v){
			if(mb_strlen($v,'UTF8')<1){
			//	$skip[]=$k;
				unset($data[$k]);
			}
		}
		if(!empty($skip)){
			//$this->skipAttr($skip);
		}
		if($this->save($data)){
			return true;
		}else{
		  
			$msg=array();
			foreach($this->getMessages() as $v){
				$msg[]=$v->getMessage();
			}
			return $msg;
		}
	}
}