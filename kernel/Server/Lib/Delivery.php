<?php
namespace Server\Lib;

/**
 * 分发服务类.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class Delivery 
{

    static $configServer;
    
    static $baseServerDir = 'Handler';
    
    public static function instance()
    {
        if (!isset(self::$configServer)) {
            self::$configServer  = \Server\Lib\ServerConfig::get('appServer');
        }
        if (empty(self::$configServer)) {
            throw new \Exception('configuration not found');
        }
        self::deliveryApp();
    }
    
    private static function deliveryApp()
    {
        $appName = \Server\Lib\Router::instance()->appName;
        $className = \Server\Lib\Router::instance()->className;
        $functionName = \Server\Lib\Router::instance()->functionName;
        $param = \Server\Lib\Router::instance()->param;
        
        
        if (isset(self::$configServer[$appName])) {
            // 获取应用路径
            $appPath = self::$configServer[$appName]['rootPath'];
            $filePatn = realpath(BASE_PATH . $appPath);
            if (file_exists($filePatn)) {
                require $filePatn;
            } else {
                throw new \Exception('Call wrong service, the application path not found');
            }
        } else {
            throw new \Exception('Call wrong service, the application configuration not found');
        }
        
        // 获取超时时间
        $processTime = self::$configServer[$appName]['process_timeout'];
        
        $classPath = '\\'. self::$baseServerDir .'\\' . $className;
        if (class_exists($classPath)) {
            $handler = new $classPath;
            $callBack = array($handler, $functionName);
            if (is_callable($callBack)) {
                // TODO 记录请求日志.
                
                set_time_limit($processTime);
                // 接口处理消耗.
                $appProcessStartTime = microtime(true);
                $r = call_user_func_array($callBack, $param);
                $appProcessStartTime = microtime(true);
                if (!$r) {
                    throw new \Exception("method $classPath::{$functionName} call failture");
                }
                
                self::dispaly($r);
                return true;
            } else {
                throw new \Exception("method $classPath::{$functionName} not exist");
            }
        } else {
            throw new \Exception("class $classPath not exist");
        }
    }
    
    private static function dispaly($data)
    {
        \Utility\Output::returnJsonVal($data);
    }

}
