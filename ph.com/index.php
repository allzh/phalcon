<?php
/**
 * Created by PhpStorm.
 * User: Disen
 * Date: 2017/12/25
 * Time: 15:29
 */

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
        './app/models/'
    ))->register();


    /**
     * 注册命名空间
     */
    $loader -> registerNamespaces(array(
        'platform' => '../platform_libs',
    )) -> register();

    // Create a DI
    $di = new Phalcon\Di\FactoryDefault();

    // Setup the view component
    $di->set('view', function () {
        $view = new Phalcon\Mvc\View();
        $view->setViewsDir('./app/views/');
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