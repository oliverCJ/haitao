<?php
/**
 * 数据库操作连接类. 
 *
 * @author oliver <cgjp123@163.com>
 */

namespace Db;

/**
 * 操作类.
 */
class Connection extends \Db\Base
{

    protected static $config;
    protected $connect;
    
    public function setConfig($config)
    {
        if (!empty($config)) {
            self::$config = $config;
        }
        return true;
    }
    
    public function connect($tag = 'default')
    {
        if (empty(self::$config)) {
            self::$config = new \Config\Config();
        }
        if (empty(self::$config[$tag])) {
            throw new \Exception('can not find db config as '.$tag);
        }
        if (empty(self::$config['tag']['dsn'])) {
            throw new \Exception('can not find db dsn');
        }
        $this->connect = '\POD';
        return ;
    }
    
    /**
     * 写库操作.
     * 
     * @return resource
     */
    public function write($tag = 'default')
    {
        $this->connect();
    }
    
    /**
     * 读库操作.
     * 
     * @return resource
     */
    public function read($tag = 'default')
    {
        
    }

}
