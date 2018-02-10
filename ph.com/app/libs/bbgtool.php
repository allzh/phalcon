<?php

class bbgtool {

    /*
    * 分页函数
    * $num 总条数,$perpage 一页多少条, $curpage 当前页, $mpurl url, $page = 10 显示几个分页
    */
    public static function multi($num, $perpage, $curpage, $mpurl, $page = 10) {
        $multipage = '';
        $realpages = 1;

        if ($num > $perpage){

            $offset = 4;
            $realpages = @ceil($num / $perpage);
            //echo $realpages;
            if ($page > $realpages) {
                $form = 1;
                $to = $realpages;
            }else {
                $form = $curpage - $offset;
                $to = $form + $page - 1;
                if ($form < 1) {
                    $form = 1;
                    //$to = $curpage + 1 - $form;
                    if ($to - $form < $page) {
                        $to = $page;
                    }
                }
            }

            $multipage = ($curpage > 1 ? '<li class="prev"><a href="'.$mpurl.'&page='.($curpage - 1).'">上一页</a></li> ' : '<li class="prev"><a>上一页</a></li>');
            for ($i = $form; $i <= $to; $i++) {
                $multipage .= $i == $curpage ? '<li class="disable"><a style="background-color:#4b8df8;color:#fff;">'.$i.'</a></li> ' :
                    '<li><a href="'.$mpurl.'&page='.$i.'">'.$i.'</a></li> ';
            }
            $multipage .= $curpage < $realpages ? '<li class="next"><a href="'.$mpurl.'&page='.($curpage + 1).'">下一页</a></li> ' : '<li class="next"><a>下一页</a></li>';
            $multipage = $multipage ? '<div class="dataTables_paginate paging_bootstrap"><ul class="pagination" style="visibility: visible;">'.$multipage.'</ul></div>' : '';
        }

        return array(
            'totalSize'=>$num,
            'pageNo'=>$curpage,
            'totalPage'=>$realpages,
            'pagehtml'=>$multipage
        );
    }

    /*
     * java时间戳转为php时间戳
     * */
    public static function gettime($time){
        $time = strval($time);
        if(strlen($time)==10) return intval($time);
        if(strlen($time)==13) return substr($time,  0 , -3);
        return $time;
    }

    /*
     * java时间戳转为格式化的日期
     * */
    public static function getdate($format ,$time){
        $time = bbgtool::gettime($time);
        return date($format,$time);
    }

    /*
     * 日期转为java时间戳
     * 或获得当前时间的java时间戳
     * */
    public static function getjavatime($time){
        if(!$time) {
            list($usec, $sec) = explode(" ", microtime());
            return number_format(((float)$usec + (float)$sec)*1000,0,'','');
        }else{
            return strtotime($time)*1000;
        }
    }

    /*
     * 商品类型 (product:商品;pkg:捆绑商品;gift:赠品商品;adjunct:配件商品)
     * */
    public static function get_itemtype($val){
        switch ($val)
        {
            case 'product':
                return '商品';
            case 'pkg':
                return '捆绑商品';
            case 'gift':
                return '赠品商品';
            case 'adjunct':
                return '配件商品';
            default:
                return '未知:'.$val;
        }
    }

    /*
     * 获得小时option
     * */
    public static function get_hour_opt($hour=0){
        $select = '';
        for($i=0;$i<24;$i++){
            $tmpNum = str_pad($i,2,'0',STR_PAD_LEFT);
            $select.=($hour==$i?'<option value="'.$tmpNum.'" selected="selected">':'<option value="'.$tmpNum.'">').$tmpNum.'</option>';
        }
        return $select;
    }

    /*
     * 获得分,秒option
     * */
    public static function get_minute_opt($minute=0){
        $select = '';
        for($i=0;$i<60;$i++){
            $tmpNum = str_pad($i,2,'0',STR_PAD_LEFT);
            $select.=($minute==$i?'<option value="'.$tmpNum.'" selected="selected">':'<option value="'.$tmpNum.'">').$tmpNum.'</option>';
        }
        return $select;
    }

    /*
     * 消息并发通知(GET)
     * */
    public static function curl_notice($urls) {
        $queue = curl_multi_init();
        $map = array();

        foreach ($urls as $url) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_NOSIGNAL, true);

            curl_multi_add_handle($queue, $ch);
            $map[(string) $ch] = $url;
        }

        $responses = array();
        do {
            while (($code = curl_multi_exec($queue, $active)) == CURLM_CALL_MULTI_PERFORM) ;

            if ($code != CURLM_OK) { break; }

            // a request was just completed -- find out which one
            while ($done = curl_multi_info_read($queue)) {

                // get the info and content returned on the request
                $info = curl_getinfo($done['handle']);
                $error = curl_error($done['handle']);
                $results = curl_multi_getcontent($done['handle']);
                $responses[$map[(string) $done['handle']]] = compact('info', 'error', 'results');

                // remove the curl handle that just completed
                curl_multi_remove_handle($queue, $done['handle']);
                curl_close($done['handle']);
            }

            // Block for data in / output; error handling is done by curl_multi_exec
            if ($active > 0) {
                curl_multi_select($queue, 0.5);
            }

        } while ($active);

        curl_multi_close($queue);
        return $responses;
    }


}