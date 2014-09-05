<?php
namespace Server\Lib;

/**
 * 获取配置参数
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class ServerConfig
{

    protected static $instance = array();
    
    protected $config;
    protected $fileName;
    
    private function __construct($name = 'Config')
    {
        $fileDir = __dir__ . '/../Config/';
        $fileName = $fileDir . $name . '.php';
        if (!file_exists($fileName)) {
            throw new \Exception('Configuration file "' . $fileName . '" not found');
        }
        
        $config = include $fileName;
        if (empty($config) || !is_array($config)) {
            throw new \Exception('Invalid configuration file format');
        }
        $this->config = $config;
        $this->filename = realpath($fileName);
    }
    
    public static function instance($name = 'Config')
    {
        if (empty(self::$instance[$name])) {
            self::$instance[$name] = new self($name);
        }
        return self::$instance[$name];
    }
    
    public static function get($cfgNodeString, $name = 'Config')
    {
        $configArray = self::instance($name)->config;
        $nodeArray = explode('.', $cfgNodeString);
        while (!empty($nodeArray)) {
            $currentNode = array_shift($nodeArray);
            if (!isset($configArray[$currentNode])) {
                return null;
            }
            $nodeVal = $configArray[$currentNode];
        }
        return $nodeVal;
    }
}