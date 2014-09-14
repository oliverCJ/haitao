<?php
namespace Log;

class Handler
{
    protected static $instance = array();
    
    protected static $config;
    
    protected static $logAvailableType = array('php', 'file', 'jsonfile');
    
    protected $cfgName;
    protected $logcfg;
    
    public static function instance($cfgName = 'default')
    {
        if (!isset(self::$instance[$cfgName])) {
            self::$instance[$cfgName] = new self($cfgName);
        }
        return self::$instance;
    }
    
    protected function __construct($cfgName)
    {
        if (!self::$config) {
            self::$config = (array) new \Config\Log;
        }
        if (empty(self::$config) || empty(self::$config[$cfgName])) {
            throw new \Exception('missing log configuration for ' . $cfgName);
        }
        $cfg = self::$config[$cfgName];
        if (!in_array($cfg['logger'], self::$logAvailableType)) {
            throw new \Exception('missing log configuration for ' . $cfgName);
        }
        $this->cfgName = $cfgName;
    }
    
    public function config($config = array())
    {
        if (empty($config)) {
            return self::$config;
        }
        self::$config = $config;
        return true;
    }
    
    public function log($msg) {
        if (in_array(, $haystack))
    }
}