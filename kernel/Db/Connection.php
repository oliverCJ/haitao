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
class Connection
{

    protected static $config;
    protected static $instance;
    protected static $writeConnection = array();
    protected static $readConnection = array();
    
    protected $currentCfgTag = 'default';
    protected $connect;
    protected $connectCfg;
    
    protected $select_sql_top;
    protected $select_sql_columns;
    protected $select_sql_from_where;
    protected $select_sql_group_having;
    protected $select_sql_order_limit;
    
    /**
     * 获取对象.
     * 
     * @return resource
     */
    public static function instance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new static();
        }
        return self::$instance;
    }
    
    private function __construct($dsn = null, $username = null, $passwd = null, $options = array())
    {
        if (!self::$config) {
            self::$config =(array) new \Config\Db();
        }
        if (!is_null($dsn)) {
            $this->connect($dsn, $username, $passwd, $options);
        }
    }
    
    /**
     * 设定配置.
     * 
     * @param unknown_type $config
     * @return Ambigous <array, unknown>|boolean
     */
    public function Config($config = null)
    {
        if (empty($config)) {
            return self::$config;
        }
        self::$config = $config;
        return true;
    }
    
    public function setCfgTag($tag)
    {
        $this->currentCfgTag = $tag;
        return true;
    }
    
    /**
     * 链接数据库.
     * 
     * @param unknown_type $dsn
     * @param unknown_type $username
     * @param unknown_type $passwd
     * @param unknown_type $options
     * @throws PDOException
     */
    protected function connect($dsn, $username = null, $passwd = null, $options = array())
    {
        if ($this->connect) return $this->connect;
        try {
            $this->connect = new \PDO($dsn, $username, $passwd, $options);
        } catch (\PDOException $e) {
            // TODO 记录日志
            
            throw $e;
        }
        return $this;
    }
    
    /**
     * 写库操作.
     * 
     * @return resource
     */
    public function write($tag = 'default')
    {
        if ($tag == 'default' && $this->currentCfgTag) {
            $tag = $this->currentCfgTag;
        }
        if (!isset(self::$writeConnection[$tag]) && !$this->addWriteConnection($tag)) {
            throw new \Exception\DbException('No available read connections.');
        }
        
        return self::$writeConnection[$tag];
    }
    
    /**
     * 读库操作.
     * 
     * @return resource
     */
    public function read($tag = 'default')
    {
        if ($tag == 'default' && $this->currentCfgTag) {
            $tag = $this->currentCfgTag;
        }
        if (!isset(self::$readConnection[$tag]) && !$this->addReadConnection($tag)) {
            throw new \Exception\DbException('No available read connections.');
        }
        
        return self::$readConnection[$tag];
    }
    
    /**
     * 增加写链接.
     * 
     * @param unknown_type $tag
     * @throws \Exception\DbException
     */
    private function addWriteConnection($tag = 'default')
    {
        if (empty(self::$config['write'][$tag])) {
            throw new \Exception\DbException('write configuration ' . $tag . ' not found');
        }
        $config = self::$config['write'][$tag];
        $connection = new self($config['dsn'], $config['user'], $config['password'], $config['options']);
        $this->connectCfg = $config;
        self::$writeConnection[$tag] = $connection;
        return $connection;
    }
    
    /**
     * 增加读链接.
     * 
     * @param unknown_type $tag
     * @throws \Exception\DbException
     */
    private function addReadConnection($tag = 'default')
    {
        if (empty(self::$config['read'][$tag])) {
            throw new \Exception\DbException('read configuration ' . $tag . ' not found');
        }
        $config = self::$config['read'][$tag];
        $connection = new self($config['dsn'], $config['user'], $config['password'], $config['options']);
        $this->connectCfg = $config;
        self::$readConnection[$tag] = $connection;
        return $connection;
    }
    
    /**
     * 抛出数据库错误.
     * 
     * @param unknown_type $message
     * @param unknown_type $code
     * @param unknown_type $previous
     * @throws \Exception\DbException
     */
    public function throwException($message = null, $code = null, $previous = null) {
        $errorInfo = $this->connect->errorInfo ();
        $ex = new \Exception\DbException ( $message . ' (DriverCode:'.$errorInfo[1].')'. $errorInfo [2], $code, $previous );
        throw $ex;
    }
    
    public function select($columns = '*')
    {
        $this->select_sql_top = '';
        $this->select_sql_columns = $columns;
        $this->select_sql_from_where = '';
        $this->select_sql_group_having = '';
        $this->select_sql_order_limit = '';
        return $this;
    }
    
    public function from($table)
    {
        $table = $this->quoteObj($table);
        $this->select_sql_from_where .= " FROM $table ";
        return $this;
    }
    
    /**
     * 格式化.
     * 
     * @param unknown_type $objName
     */
    public function quoteObj($objName)
    {
        if (is_array ( $objName ))
        {
            $return = array ();
            foreach ( $objName as $k => $v )
            {
                $return[] = $this->quoteObj($v);
            }
            return $return;
        } else {
            $v = trim($objName);
            $v = str_replace('`', '', $v);
            $v = preg_replace('# +AS +| +#i', ' ', $v);
            $v = explode(' ', $v);
            foreach ($v as $k_1 => $v_1) {
                $v_1 = trim($v_1);
                if ($v_1 == '')
                {
                    unset($v[$k_1]);
                    continue;
                }
                if (strpos($v_1, '.')) {
                    $v_1 = explode('.', $v_1);
                    foreach ($v_1 as $k_2 => $v_2) {
                        $v_1[$k_2] = '`'.trim($v_2).'`';
                    }
                    $v[$k_1] = implode('.', $v_1);
                } else {
                    $v[$k_1] = '`'.$v_1.'`';
                }
            }
            $v = implode(' AS ', $v);
            return $v;
        }
    }

}
