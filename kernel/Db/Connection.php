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
    
    // 链接复用池
    protected static $writeConnection = array();
    protected static $readConnection = array();
    
    protected $cacbePerQueries = array();
    protected $currentCfgTag = 'default';
    protected $connect;
    protected $connectCfg;
    protected $allowRealExec;
    protected $withCache = true;
    // null/false:not allow and throw exception; true:allow
    protected $allowGuessConditionOperator = true;
    protected $allowCloseLastStatement = false;
    
    protected $select_sql_columns;
    protected $select_sql_from_where;
    protected $select_sql_group_having;
    protected $select_sql_order_limit;
    protected $lastSql;
    protected $lastStmt;
    protected $memoryUsageBeforeFetch;
    protected $memoryUsageAfterFetch;
    protected $queryBeginTime;
    protected $queryEndTime;
    
    protected static $update_ignore = 'ignore';
    protected static $insert_ignore = 'ignore';
    protected static $insert_duplicate_key_update = 'update';
    
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
        if (is_array($dsn)) {
            extract($dsn);
        }
        if ($this->connect) return $this->connect;
        try {
            $this->connect = new \PDO($dsn, $username, $passwd, $options);
        } catch (\PDOException $e) {
            // TODO 记录日志
            
            throw $e;
        }
        return $this;
    }
    
    protected function reConnect()
    {
        $this->connect = null;
        return $this->connect($this->connectCfg);
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
    
    public function insert($table, array $insertData, $insertTag = null)
    {
        $table = $this->quoteObj($table);
        if (empty($insertData)) throw new \Exception\DbException('miss the insert data');
        $sql_insert_key = '';
        $sql_insert_value = '';
        foreach ($insertData as $key => $val) {
            $sql_insert_key .= $this->quoteObj($key) . ',';
            $sql_insert_value .= is_null($val) ? 'NULL' : $this->quote($val) . ',';
            } 
        $sql_insert_key = substr($sql_insert_key, 0, -1);
        $sql_insert_value = substr($sql_insert_value, 0, -1);
        $sql_ignore = '';
        $sql_update = '';
        if ($insertTag == self::$insert_ignore) {
            // IGNORE关键词在执行语句时出现错误会被当作警告处理，且仍然尝试执行，语句不会失败，http://dev.mysql.com/doc/refman/5.1/zh/sql-syntax.html#insert
            $sql_ignore = 'IGNORE';
        } elseif ($insertTag == self::$insert_duplicate_key_update) {
            if (func_num_args() >= 4) {
                $updateParam = func_get_args(3);
            } else {
                $updateParam = $insertData;
            }
            $update = array();
            foreach ($updateParam as $key => $val) {
                if (is_int($key)) {
                    $update[] = $val;
                } else {
                    $update[] = $this->quoteObj($key) . '=' . is_null($val) ? 'NULL' : $this->quote($val) . ',';
                }
            }
            if (!empty($update)) {
                // ON DUPLICATION KEY UPDATE http://dev.mysql.com/doc/refman/5.1/zh/sql-syntax.html#insert
                $sql_update = 'ON DUPLICATION KEY UPDATE ' . implode(',', $update);
            }
        }
        $sql = 'INSERT ' . $sql_ignore . ' INTO ' . $table. ' (' . $sql_insert_key . ') VALUES (' . $sql_insert_value . ') ' . $sql_update;
        $re = $this->execute($sql);
        if ($re === false) {
            return false;
        }
        $id = $this->connect->lastInsertId();
        if ($id) return $id;
        return !!$re;
    }
    
    /**
     * 更新数据操作.
     * 
     * @param unknown $table
     * @param unknown $params
     * @param unknown $cond
     * @param number $option
     * @param string $order_by_limit
     * @return boolean|unknown
     */
    public function update($table, $params, $cond, $option = 0, $order_by_limit = '')
    {
        if (empty($params)) return false;
        if (is_string($params)) {
            $update_str = $params;
        } else {
            $update_str = '';
            foreach ($params as $column => $value) {
                if (is_int($column)) {
                    $update_str .= $value;
                } else {
                    $column = $this->quoteObj($value);
                    $value = is_null($value) ? 'NULL' : $this->quote($value);
                    $update_str .= $column . '=' . $value . ',';
                }
            }
            $update_str = substr($update_str, 0, strlen($update_str) - 1);
        }
        
        $table = $this->quoteObj($table);
        if (is_numeric($cond)) {
            $cond = $this->quoteObj('id') . '=' . $cond;
        } else {
            $cond = $this->buildCondition($cond);
        }
        $sql = 'UPDATE ';
        if ($option == self::$update_ignore) {
            $sql .= ' IGNORE ';
        }
        $sql .= $table . ' SET ' . $update_str . ' WHERE ' . $cond . $order_by_limit;
        $re = $this->execute($sql);
        return $ret;
    }
    
    public function delete($table, $cond)
    {
        $table = $this->quoteObj($table);
        $cond = $this->buildCondition($cond);
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $cond;
        $re = $this->execute($sql);
        return $re;
    }
    
    public function group($group)
    {
        $this->select_sql_group_having .= ' GROUP BY ' . $group;
        return $this;
    }
    
    public function having($cond)
    {
        $cond = $this->buildCondition($cond);
        $this->select_sql_group_having .= ' HAVING ' . $cond;
        return $this;
    }
    
    public function order($order)
    {
        $this->select_sql_order_limit .= ' ORDER BY ' . $order;
        return $this;
    }
    
    public function queryScalar($sql = null, $default = null)
    {
        $stmt = $this->query($sql);
        $v = $stmt->fetchColumn(0);
        $this->memoryUsageAfterFetch = memory_get_usage();
        if ($v !== false) {
            return $v;
        }
        return $default;
    }
    
    public function querySimple($sql = null, $default = null)
    {
        return $this->queryScalar($sql, $default);
    }
    
    public function queryAll($key = '')
    {
        if ($key) return $this->queryAllAssocKey($key);
        $sql = $this->getSelectSql();
        $stmt = $this->query($sql);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $this->memoryUsageAfterFetch = microtime(true);
        // TODO 日志
        return $data;
    }
    
    public function queryAllAssocKey($key)
    {
        $rows = array();
        $sql = $this->getSelectSql();
        $stmt = $this->query($sql);
        if ($stmt)
        {
            while (($row = $stmt->fetch(\PDO::FETCH_ASSOC)) !== false) {
                $rows[$row[$key]] = $row;
            }
        }
        $this->memoryUsageAfterFetch = microtime(true);
        // TODO 日志
        return $rows;
    }
    
    public function queryRow()
    {
        $sql = $this->getSelectSql();
        $stmt = $this->query($sql);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->memoryUsageAfterFetch = microtime(true);
        // TODO 日志
        return $data;
    }
    
    public function queryExe($sql)
    {
        //return $this->querySimple($sql);
        $stmt = $this->query($sql);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->memoryUsageAfterFetch = microtime(true);
        return $data;
    }
    
    public function exec($sql)
    {
        $this->execute($sql);
    }
    
    public function find($table, $cond, $order = '')
    {
        if (is_numeric($cond)) {
            $cond = array('id' => $cond);
        }
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        if ($order && strncasecmp($order, 'ORDER BY', 8) != 0) {
            $order = 'ORDER BY ' . $order;
        }
        $sql = "SELECT * FROM " . $table . $where . $order;
        return $this->queryRow($sql);
    }
    
    public function findAll($table, $cond, $order = '')
    {
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        if ($order && strncasecmp($order, 'ORDER BY', 8) != 0) {
            $order = 'ORDER BY ' . $order;
        }
        $sql = "SELECT * FROM " . $table . $where . $order;
        return $this->queryAll($sql);
        
    }
    
    public function count($table, $cond, $columns = '*')
    {
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        $sql = 'SELECT COUNT('.$columns.') FROM ' . $table . $where;
        return $this->querySimple($sql);
    }
    
    public function exits($table, $cond)
    {
        $table = $this->quoteObj($table);
        $where = $this->buildWhere($cond);
        $sql = 'SELECT 1 FROM ' . $table . $where . ' LIMIT 1';
        return !!$this->querySimple($sql);
    }
    
    public function limit($a, $b = null)
    {
        if (is_null($b)) {
            $a = intval($a);
            $this->select_sql_order_limit .= ' LIMIT ' . $a;
        } else {
            $a = intval($a);
            $b = intval($b);
            $this->select_sql_order_limit .= ' LIMIT ' . $a . ', ' . $b;
        }
        return $this;
    }
    
    /**
     * 执行SQL，原则上只能内部调用，外部调用都使用queryRow或queryAll或queryExe
     */
    protected function query($sql)
    {
        static $retryCount = 0;
        $this->lastSql = $sql;
        // 缓存设置，每次设置后重置缓存，这样下一个query如果没有使用noCache()则仍然可以使用缓存
        $withCache = $this->withCache;
        $this->withCache = true;
        
        $sqlcmd = strtoupper(substr($this->lastSql, 0, 6));
        if (in_array($sqlcmd, array('UPDATE', 'DELETE')) && stripos($this->lastSql, 'where') === false) {
            throw new \Exception\DbException('no where condition in sql(update, delete) to executed! it is not safe');
        }
        if ($sqlcmd == 'SELECT' || $this->allowRealExec) {
            // 获取缓存结果，如果需要直接获取，可设置不使用缓存
            $cacheKey = md5($this->lastSql);
            if ($withCache && isset($this->cacbePerQueries[$cacheKey])) {
                return $this->cacbePerQueries[$cacheKey];
            }
            // TODO 日志
            $this->queryBeginTime = microtime(true);
            $this->memoryUsageBeforeFetch = memory_get_usage();
            $this->lastStmt = $this->connect->query($this->lastSql);
        } else {
            $this->lastStmt = true;
        }
        $this->queryEndTime = microtime(true);
        if (false === $this->lastStmt) {
            // 重试
            $errorInfo = $this->connect->errorInfo();
            if ($retryCount < 1 && $this->needConfirmConnection() && $errorInfo[1] == 2006) {
                $retryCount++;
                $this->reConnect();
                $re2 = $this->query($sql);
                $retryCount = 0;
                return $retryCount;
            }
            $retryCount = 0;
            $this->throwException('Query failure SQL:' . $this->lastSql . '. (' . $errorInfo[2] . ')');
        }
        // 设置缓存
        if ($withCache && isset($cacheKey)) {
            $this->cacbePerQueries[$cacheKey] = $this->lastStmt;
        }
        return $this->lastStmt;
    }
    
    /**
     * PDO::exec().
     * 
     * @param string $sql
     * @return Ambigous <unknown, number>|number
     */
    protected function execute($sql)
    {
        static $retryCount = 0;
        $this->queryBeginTime = microtime(true);
        //TODO 日志
        $re = $this->connect->exec($sql);
        $this->queryEndTime = microtime(true);
        if ($re === false) {
            $errorInfo = $this->connect->errorInfo();
            // retry
            if ($retryCount < 1 && $this->needConfirmConnection() && $errorInfo[1] == 2006) {
                $retryCount++;
                $this->reConnect();
                $re2 = $this->execute($sql);
                $retryCount = 0;
                return $re2;
            }
            $retryCount = 0;
            $this->throwException('Query failure SQL:' . $sql . '(' . $errorInfo[2] . ')');
        }
        return $re;
    }
    
    public function select($columns = '*')
    {
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
    
    public function where($cond)
    {
        $cond = $this->buildCondition($cond);
        $this->select_sql_from_where .= ' WHERE ' . $cond;
        return $this;
    }
    
    protected function jointTable($join, $table, $cond)
    {
        $table = $this->quoteObj($table);
        $select_sql_from_join = '';
        $select_sql_from_join .= ' ' . $join . ' ' . $table . ' ';
        $cond = $this->buildCondition($cond);
        $select_sql_from_join .= ' ON '. $cond;
        $this->select_sql_from_where .= $select_sql_from_join;
        return $this;
    }
    
    public function join($table, $cond)
    {
        return $this->jointTable('JOIN', $table, $cond);
    }
    
    public function leftjoin($table, $cond)
    {
        return $this->jointTable('LEFT JOIN', $table, $cond);
    }
    
    public function rightjoin($table, $cond)
    {
        return $this->jointTable('RIGHT JOIN', $table, $cond);
    }
    
    public function needConfirmConnection()
    {
        if (isset(self::$config['confirm_link']) && self::$config['confirm_link'] === true) {
            return true;
        }
        return false;
    }
    
    public function getLastSql()
    {
        return $this->lastSql;
    }
    
    public function getSelectSql()
    {
        return 'SELECT ' . $this->select_sql_columns . $this->select_sql_from_where .$this->select_sql_group_having . $this->select_sql_order_limit;
    }
    
    public function noCache()
    {
        $this->withCache = false;
        return $this;
    }
    
    public function setAllowRealExec($v)
    {
        $this->allowRealExec = $v;
    }
    
    public function setAllowGuessConditionOperator($v) {
        $this->allowGuessConditionOperator = $v;
    }
    
    public function bulidWhere($condition = array(), $logic = 'AND')
    {
        $cond = $this->buildCondition($condition, $logic);
        if ($cond) $cond = ' WHERE ' . $cond;
        return $cond;
    }
    
    /**
     * 处理$cond数组条件.
     * 
     * @param unknown $condition
     * @param string $logic
     * @throws \Exception\DbException
     * @return string
     */
    public function buildCondition($condition = array(), $logic = 'AND')
    {
        if (!is_array($condition)) {
            if (is_string($condition)) {
                if (preg_match('#\<|\>|\=|\s#', $condition, $match)) {
                    $condition = explode($match[0], $condition);
                    $condition[0] = $this->quoteObj($condition[0]);
                    $condition = implode($match[0], $condition);
                    return $condition;
                }
            }
        throw new \Exception\DbException('the SQL condition is not valid');
        }
        $logic = strtoupper($logic);
        $content = '';
        foreach ($condition as $k => $v) {
            $v_str = null;
            $v_connect = '';
            if (is_int($k)) {
                if ($content){
                    $content .= $login . ' (' . $this->buildCondition($v) . ') ';
                } else {
                    $content .= ' (' . $this->buildCondition($v) . ') ';
                }
                continue;
            }
            
            $k = trim($k);
            $maybe_logic = strtoupper($k);
            if (in_array($maybe_logic, array('AND', 'OR'))) {
                if ($content) {
                    $content .= $logic . ' (' . $this->buildCondition($v, $maybe_logic) . ') ';
                } else {
                    $content .= ' (' . $this->buildCondition($v, $maybe_logic) . ') ';
                }
                continue;
            }
            
            //处理'`col` >=' => 'sd'类型
            $k_upper = strtoupper($k);
            $maybe_connectors = array('>=', '<=', '<>', '!=', '>', '<', '=', ' NOT BETWEEN', ' BETWEEN', ' NOT LIKE', ' LIKE', ' IS NOT', ' NOT IN', ' IS', ' IN');
            foreach ($maybe_connectors as $maybe_connector) {
                $l = strlen($maybe_connector);
                if (substr($k_upper, -$l) == $maybe_connector) {
                    $k = trim(substr($k, 0, -$l));
                    $v_connect = $maybe_connector;
                    break;
                }
            }
            if (is_null($v)) {
                $v_str = ' NULL';
                if ($v_connect == '') {
                    $v_connect = 'IS';
                }
             } elseif (is_array($v)) {
                 if ($v_connect == ' BETWEEN') {
                     $v_str = $this->quote($v[0]) . ' AND ' . $this->quote($v[1]);
                 } elseif (is_array($v) && !empty($v)) {
                     // 处理key => array($v1,$v2)
                     $v_str = null;
                     foreach ($v as $one) {
                         if (is_array($one)) {
                             $sub_items = '';
                             foreach ($one as $sub_value) {
                                 $sub_items .= ',' . $this->quote($sub_value);
                             }
                             $v_str .= ',(' . substr($sub_items, 1) . ')';
                         } else {
                             $v_str .= ',' .$this->quote($one); 
                         }
                     }
                     $v_str = '(' . substr($v_str, 1) . ')';
                     if (empty($v_connect)) {
                         if ($this->allowGuessConditionOperator = true) {
                             $v_connect = ' IN';
                         } else {
                             throw new \Exception\DbException('guessing condition operator is not allowed, please use \'' . $k . ' IN\'=>array(...)');
                         }
                     }
                 } elseif (empty($v)) {
                     // 'key'=>array()
                     $v_str = $k;
                     $v_connect = '<>';
                 }
             } else {
                 $v_str = $this->quote($v);
             }
             
             // 'key' => 'val'
             if (empty($v_connect)) $v_connect = '=';
             $quoted_k = $this->quoteObj($k);
             if ($content) {
                 $content .=$logic . ' (' . $quoted_k . $v_connect . $v_str . ') ';
             } else {
                 $content = ' (' . $quoted_k . $v_connect . $v_str . ') ';
             }
        }
        return $content;
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
    
    public function quote($data, $paramType = \PDO::PARAM_STR)
    {
        if (is_array($data) || is_object($data)) {
            $return = array();
            foreach ($data as $k => $v) {
                $return[$k] = $this->quote($v);
            }
            return $return;
        } else {
            $data = $this->connect->quote($data);
            if ($data === false) {
                $data = "''";
            }
            return $data;
        }
    }

}
