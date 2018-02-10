<?php
/**
 * Created by PhpStorm.
 * User: Disen
 * Date: 2018/1/12
 * Time: 16:08
 */
use platform\libs\cache\cacheFactory;

class TestTask extends \Phalcon\CLI\Task{
    private $_cache;
    private $_cachekey = 'TestListStr';
    private $redis; //redis对象
    private $timeout = 2;
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect(REDIS_HOST, REDIS_PORT, $this->timeout);
        if (defined("REDIS_PASSWORD")){
            $this->redis->auth(REDIS_PASSWORD);
        }
        $this->redis->select(REDIS_DB);
    }

    public function index(){
        $red_rand_num = range(1,33);
        shuffle($red_rand_num);
        $red = array_slice($red_rand_num,0,6);
        $red_str = implode(',',$red);

        $blue_rand_num = range(1,16);
        shuffle($blue_rand_num);
        $blue = array_slice($blue_rand_num,0,1);
        $blue_str = implode(',',$blue);

        echo($red_str."=====".$blue_str."\n");
    }
    public function goAction(){
        for($i=0;$i<30;$i++){
            $this->index();
        }
    }
    public function setpushAction(){
        try{
            for($i=0;$i<50;$i++){
                $res = $this->redis->rpush($this->_cachekey,$i);
                echo "插入一个队列：".$res."\n";
            }
            echo $this->redis->lLen($this->_cachekey)."\n";
        }catch (Exception $e){
            echo $e->getMessage();
        }

    }

    public function getpushAction(){
        $get = $this->redis->lRange($this->_cachekey,0,5);
        while (true){
            try{
                $get = $this->redis->lPop($this->_cachekey,0,5);
                if($get){
                    print_r($get);
                }else{
                    echo '出队完成';
                    break;
                }
            }catch (Exception $e){
                echo $e->getMessage();
            }

        }
    }

}