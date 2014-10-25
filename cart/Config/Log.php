<?php
namespace Config;
/**
 * 日志配置.
 *
 * @author oliver <cgjp123@163.com>
 */
class Log
{
    public $LOG_ROOT = '/tmp/logs/haitao/apptest';
    
    public $db = array(
                    'logger' => 'file',
    );
}