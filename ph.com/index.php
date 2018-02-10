<?php
/*
 * 域名访问入口
 * Created by PhpStorm.
 * User: Disen
 * Date: 2017/12/25
 * Time: 15:29
 */


/**
 * 截获错误代码处理代码 提交日志服务器
 */
register_shutdown_function(
    function () {
        $e = error_get_last();
        if ($e) {
            include_once dirname(__DIR__) .
                '/platform_libs/bbg/log/Log.php';
            $Log = \platform\bbg\log\Log::getInstance();
            $Log->errors_info($e);
            get_cfg_var('mall.runtime') != 'pro' && var_dump($e);//非生产环境输出错误
        }
    });

/**
 * 注册截获用户错误的方法提交到日志服务器
 */
set_error_handler(
    function ($errno, $errstr, $errfile, $errline) {
        include_once dirname(__DIR__) . '/platform_libs/bbg/log/Log.php';
        $Log = \platform\bbg\log\Log::getInstance();
        $data = array('type' => $errno, 'message' => $errstr,
            'file' => $errfile, 'line' => $errline);
        $Log->errors_info($data);
        //get_cfg_var('mall.runtime') != 'pro' && $errno <> 8  && var_dump($data);//非生产环境,非Notice 输出错误
    }, E_ALL);


try {
    define('BBG_PATH', __DIR__ . DIRECTORY_SEPARATOR . "app" .
        DIRECTORY_SEPARATOR);
    define('VIEW_PATH', __DIR__ . DIRECTORY_SEPARATOR); // 静态资源路径

    $runtime = get_cfg_var('mall.runtime');
    define('RUNTIME', $runtime);

    switch (RUNTIME) {
        case 'pro':
            // 生产环境配置
            define('MAST_CACHE', "mall.redis.mall");
            define('DEBUG', 0); // DEBUG
            define('COMPILED_ALWAYS', false); //模板文件实时编译开关
            define('STATIC_HOST', '//ph.com/cms-assets'); //静态资源host
            break;
        case 'test':
            // 测试环境配置
            // 开发环境配置
            define('MAST_CACHE', "mall.redis.mall");
            define('DEBUG', 1); // DEBUG
            define('COMPILED_ALWAYS', true); //模板文件实时编译开关
            define('STATIC_HOST', '//ph.com/cms-assets'); //静态资源host
            break;
        default:
            // 开发环境配置
            define('MAST_CACHE', "mall.redis.mall");
            define('DEBUG', 1); // DEBUG
            define('COMPILED_ALWAYS', true); //模板文件实时编译开关
            define('STATIC_HOST', '//ph.com/cms-assets'); //静态资源host
    }

    // Register an autoloader
    $loader = new Phalcon\Loader();
    $loader->registerDirs(array(
        './app/controllers/',
        './app/models/',
        './app/config/',
        './app/libs/',
        './app/libs/tags/'
    ))->register();


    /**
     * 注册命名空间
     */
    $loader -> registerNamespaces(array(
        'platform' => '../platform_libs',
    )) -> register();

    require_once BBG_PATH . 'base.php';

    // Create a DI
    $di = new Phalcon\Di\FactoryDefault();

    $di->set('voltService', function($view, $di) {
        //模板缓存目录不存在就创建
        if (!is_dir(COMPILED_VIEWS_PATH)) {
            mkdir(COMPILED_VIEWS_PATH, 0777, true);
        }
        $volt = new Phalcon\Mvc\View\Engine\Volt($view, $di);
        $volt->setOptions(array(
            "compiledPath" => COMPILED_VIEWS_PATH,
            "compiledExtension" => ".php",
            "compileAlways" => COMPILED_ALWAYS
        ));
        return $volt;
    });

    // Setup the view component
    $di->set('view',
        function () {
            $view = new \Phalcon\Mvc\View();
            $view->setViewsDir('./app/views/');
            $view->registerEngines(
                array(".phtml" => 'voltService'));

            return $view;
        });

    // Setup a base URI so that all generated URIs include the "tutorial" folder
    $di->set('url', function () {
        $url = new Phalcon\Mvc\Url();
        $url->setBaseUri('/');
        return $url;
    });


    $di->setShared('session',
        function () {
            $session = new Phalcon\Session\Adapter\Files();
            $session->start();
            return $session;
        });

    $di->set('cookies',
        function () {
            $cookies = new Phalcon\Http\Response\Cookies();
            $cookies->useEncryption(false);
            return $cookies;
        });

    // Handle the request
    $application = new Phalcon\Mvc\Application($di);

    echo $application->handle()->getContent();

} catch  (\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
}