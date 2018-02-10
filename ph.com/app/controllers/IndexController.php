<?php
/**
 * phalcon控制器入口
 * Created by PhpStorm.
 * User: Disen
 * Date: 2017/12/25
 * Time: 15:31
 */

use Phalcon\Mvc\Controller;

class IndexController extends BaseController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
    }

    /**
     * MVC
     * 简单输出
     */
    public function indexAction()
    {
        $tab = $this->request->get('tab', 'trim', '1');

        $setting=bbg_base::load_config("system");

        $this->view->setVar("tab", $tab);
        $this->view->setting = $setting;
        $this->view->qdurl=WEB_URL;
        $this->view->title="测试标题";
        $this->view->pick('web/index');
    }

    /**
     * MVC
     * 连接db输出
     */
    public function dbinfoAction(){
        $page = $this->request->get('tab', 'trim', '1');
        $size = 20;
        $count = Article::count(array("status=1"));
        $info = Article::find(array(
                "status=1",
                "order" => "sort desc,id desc",
                'limit'=> ($page-1)*$size . ',' . $size
            ));
        $url = "/Index/dbinfo";
        $page = bbgtool::multi($count,$size,$page,$url,10);
        $this->view->setVar("page", $page);
        $this->view->pick('web/article');
    }
}