<?php
namespace Server\Lib;

/**
 * 服务分发.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class Forward
{

    static $configServer;
    
    static $baseServerDir = 'Handler';
    
    public static function boot()
    {
        // TODO 来路检测,权限校验等
        
        
        
        self::$configServer = \Server\Config\ConfigServer::$server;
        
        self::deliveryApp();
    }
    
    public static function deliveryApp()
    {
        $appName = \Server\Lib\Router::instance()->appName;
        $className = \Server\Lib\Router::instance()->className;
        $functionName = \Server\Lib\Router::instance()->functionName;
        $param = \Server\Lib\Router::instance()->param;
        
        if (isset(self::$configServer[$appName])) {
            require BASE_PATH . self::$configServer[$appName]['rootPath'];
        } else {
            throw new \Exception('Call wrong service, the application not found');
        }
        
        $classPath = '\\'. self::$baseServerDir .'\\' . $className;
        if (class_exists($classPath)) {
            $handler = new $classPath();
            $r = call_user_func_array(array($handler, $functionName), $param);
            if (!$r) {
                throw new \Exception('Call wrong service, no function found');
            }
            self::dispaly($r);
        } else {
            throw new \Exception('Call wrong service, no class found');
        }
    }
    
    public static function dispaly($data)
    {
        \Utility\Output::returnJsonVal($data);
    }

}

