<?php

/**
 * 上传文件入口
 * Class UeFileController
 */
class UeFileController extends  \Phalcon\Mvc\Controller
{
	protected $_img_url = '';
	
    public function indexAction()
    {
        $action = $_GET['action'];
        $callback = isset($_GET["callback"])?$_GET["callback"]:'';
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("app/config/ueditor.json")), true);
        switch ($action) {

            case 'config':
                $result =  json_encode($CONFIG);
                break;

            /* 上传图片 */
            case 'uploadimage':
                $result = $this->image();
                break;
            /* 抓取远程文件 */
            case 'catchimage':
                $result = $this->catchs();
                break;

            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }

        /* 输出结果 */
        if ($callback) {
            echo $callback . '(' . $result . ')';
        } else {
            echo $result;
        }

    }

    //上传图片
    public function image(){

        $api=api::getInstance();

        $remoteUrl=$api->get_api_url('uploadToKind.methods');

        $data=array(
            'method'=>'uploadToKind.methods'
        );
        $temp=$api->submit($remoteUrl,$data,$_FILES);
        if(!is_array($temp)){
            return json_encode(array(
                'state' => '图片上传失败'
            ));
        }

        if(array_key_exists("_error", $temp)){
            return json_encode(array(
                'state' => '接口报错:'.$temp['_error']['msg']
            ));
        }
        if($temp['v'] && $temp['v'][0]){
            $imgid= $temp['v'][0]['id'];
            return json_encode(array(
                'state'=>'SUCCESS',
                'url'=> $this->_get_img_domain().$imgid
            ));
        }else{
            return json_encode(array(
                'state'=> '接口返回数据格式异常'
            ));
        }
    }
    
    /**
     * 取得图片域名
     * @author zhangyong
     * @return string
     */
    protected function _get_img_domain(){
    	if (empty($this->img_url)) {
    		$api=api::getInstance();
    		$result = $api->get("getImgUrl.methods");
    		$this->img_url = 'http://' . $result['pub'] . '/';
    	}
    	return $this->img_url;
    }

    private function delcache($_files){
        foreach($_files as $file){
            unlink($file);
        }
    }


}