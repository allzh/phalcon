<?php
namespace platform\core;
class Common{
    /**
     * 获取当前选择地区id字符串
     * @category
     * @author chengbin@bubugao.com
     * @return 当前用户选择的地区字符串，如：43_430100000000_430102000000_430102010000
     */
    static function getAreaId() {
        // 默认 湖南省-长沙市-芙蓉区-文艺街道 ps：只有用户手动删除cookie才会出现这个情况
        $area_id_str = '43_430100000000_430102000000_430102010000';
        if (isset ( $_COOKIE ['_address'] )) {
            $_address = $_COOKIE ['_address'];
            if (! empty ( $_address )) {
                $_tmp_address = explode ( ':', $_address );
                $area_id_str = array_pop ( $_tmp_address );
            }
        }
        return $area_id_str;
    }

    /**
     * 格式化价格
     * @param number $price
     * @return string
     */
    static public function formatPrice($price = 0) {
        $price = sprintf ( "%.2f", $price / 100 );
        return $price;
    }

    /**
     * 输出jsonp
     * @author chengbin@bubugao.com
     * @param $callback 前端回调方法
     * @param $data 输出的数据
     * @return
     */
    static public function jsonpOutput($callback = '', $data = null) {
        $json = json_encode ( $data );
        if (empty ( $callback )) {
            echo $json;
        } else {
            echo $callback . '(' . $json . ')';
        }
    }

    /**
     * N个2的乘方求和
     * @param array $val_arr
     * @return number $sum
     */
    static public function sumCheckboxValue($val_arr=array()) {
        $sum = 0;
        if(is_array($val_arr)) {
            foreach($val_arr as $val=>$is_true) {
                if($is_true=="true") {
                    $sum += $val;
                }
            }
        }
        return $sum;
    }

}