<?php
namespace Log;

class Handler
{
    protected static $instance = array();
    
    protected static $config;
    
    protected static $logAvailableType = array('php', 'file', 'jsonfile');
    protected static $phpLogType = array(
                    E_USER_NOTICE => 'notice',
                    E_USER_ERROR => 'Fatal error',
                    E_USER_WARNING => 'warning',
                    E_USER_DEPRECATED => 'deprecated'
    );
    
    protected $cfgName;
    protected $logcfg;
    protected $prefixTime = true;
    protected $rawLog = false;
    
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
        $this->logcfg = $cfg;
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
    
    public function log($msg, $option = array()) {
        if (!$this->logcfg) return false;
        switch ($this->logcfg['logger']) {
            case 'php' :
                $this->logPhp($msg, $option);
                break;
            case 'file' :
                $this->logFile($msg);
                break;
            case 'jsonfile' :
                $this->rawLog = true;
                $this->prefixTime = false;
                $this->logJsonFile($msg);
                break;
        }
    }
    
    /**
     * 文本日志
     * 
     * @param unknown $msg
     * @return boolean
     */
    protected function logFile($msg)
    {
        if (isset($this->logcfg['path'])) {
            $logDir = $this->logcfg['path'];
            $fileName = basename($this->logcfg['path']);
        } else {
            $logDir = self::$config['LOG_ROOT'] . DIRECTORY_SEPARATOR . $this->cfgName . DIRECTORY_SEPARATOR;
            $fileName = $this->cfgName . '-' . date('Ym') . '.log';
        }
        if (!is_dir($logDir) && !@mkdir($logDir, 0777, true) && !is_writeable($logDir)) {
            return false;
        }
        if ($this->rawLog) {
            $log = $this->formatLog($msg);
        }
        file_put_contents($logDir, $log, FILE_APPEND);
        return true;
    }
    
    /**
     * 把日志记录进PHP日志
     * 
     * @param unknown $msg
     * @param unknown $option
     */
    protected function logPhp($msg, $option)
    {
        if (!is_array($option)) {
            $option = array();
        }
        $type = isset($option['type']) && in_array($option['type'], self::$phpLogType) ? $option['type'] : E_USER_NOTICE;
        $traceDepth = isset($option['traceDepth']) ? (int)$option['traceDepth'] : 1;
        $msg = self::$phpLogType[$type] . $msg . "\n";
        $traceStr = '';
        $traceDepth += 1; //需要算上当前调LOG的次数
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $traceDepth);
        } else {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        }
        unset($trace[0]); // 去掉当前调log的trace
        if (count($trace) > 0) {
            foreach ($trace as $k => $v) {
                extract($v);
                $traceStr .= $k . ' IN ' . $file  . ' LINE: ' . $line . "\n";
            }
            error_log($traceStr . $msg);
        }
    }
    
    public function logJsonFile()
    {
        $data['log_time'] = time();
        $msg = json_encode($data, 256);
        return $this->logFile($msg);
    }
    
    protected function formatLog($log)
    {
        $msg = str_replace(array('\n', '\r\n'), '\n', $log);
        if ($this->prefixTime) {
            $msg = '[' . date('Y-m-d H:i:s') . ']' . $msg;
        }
        $msg .= "\n";
        return $msg;
    }
}