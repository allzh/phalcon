<?php

class Article extends \Phalcon\Mvc\Model{

    public function getSource()
    {
        return "tb_article";
    }

    public function getArticle($catid,$num=10){
        $db = $this->getDI()->get('db');
        $result = $db->query('SELECT * FROM tb_article');
        $result->setFetchMode(\Phalcon\Db::FETCH_ASSOC);
        return $result->fetchAll();
    }

}