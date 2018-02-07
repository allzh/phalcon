<?php
/**
 * Created by PhpStorm.
 * User: Disen
 * Date: 2017/12/25
 * Time: 15:31
 */

use Phalcon\Mvc\Controller;

class IndexController extends Controller
{

    public function indexAction()
    {
        echo "<h1>Hello!</h1>";
    }
}