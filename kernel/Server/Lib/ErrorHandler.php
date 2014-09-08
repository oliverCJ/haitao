<?php
namespace Server\Lib;

/**
 * 错误控制类.
 *
 * @author chengjun <cgjp123@163.com>
 */
class ErrorHandler {

    static $errorType;

    /**
     * 初始化.
     */
    public static function instance()
    {
        // 注册关闭检查函数
        // 当代码执行发生以下几种情况会调用：
        // (1)页面被用户强制停止
        // (2)程序代码运行超时
        // (3)php代码执行完成
        register_shutdown_function(array('\Server\Lib\ErrorHandler', 'check_for_fatal'));

        // 注册错误替代函数，处理非致命错误
        set_error_handler(array('\Server\Lib\ErrorHandler', 'errorLog'));

        //注册异常处理函数，处理未捕获的异常
        set_exception_handler(array('\Server\Lib\ErrorHandler', 'log_exception'));
    }

    /**
     * 定义错误输出.
     *
     * @param unknown $errno
     * @param unknown $errstr
     * @param unknown $errfile
     * @param unknown $errline
     */
    public static function errorLog($errno, $errstr, $errfile, $errline)
    {
        if (!error_reporting() && !$errno) {
            return;
        }
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
                self::$errorType = 'Notice';
                break;
            case E_WARNING:
            case E_USER_WARNING:
                self::$errorType = 'Warning';
                break;
            case E_ERROR:
            case E_USER_ERROR:
                self::$errorType = 'Error';
                break;
            default:
                self::$errorType = 'Other';
                break;
        }

        self::log_exception(new \ErrorException($errstr, 9999, $errno, $errfile, $errline));
    }

    /**
     * 定义异常输出，只捕获没有捕捉的异常.
     *
     * @param \Exception $e
     */
    public static function log_exception($e)
    {
        if (DEBUG) {
            $array = array(
                    'ErrorType' => self::$errorType,
                    'exceptionType' => get_class( $e ),
                    'Message' => $e->getMessage(),
                    'File' => $e->getFile(),
                    'Line' => $e->getLine(),
                    'code' => $e->getCode()
            );
            \Utility\Output::returnJsonVal($array);
        } else {
            // TODO 记录日志
            $message = '[' . date('Y-m-d H:i:s') . "] ErrorType: " . self::$errorType . "; exceptionType: " . get_class( $e ) . "; Message: {$e->getMessage()}; File: {$e->getFile()}; Line: {$e->getLine()};";
            //file_put_contents( "/tmp/logs/exceptions.log", $message . PHP_EOL, FILE_APPEND );
        }
        exit();
    }

    /**
     * 致命错误输出.
     */
    public static function check_for_fatal()
    {
        $error = error_get_last();
        if ( $error["type"] == E_ERROR )
            self::errorLog( $error["type"], $error["message"], $error["file"], $error["line"] );
    }
}