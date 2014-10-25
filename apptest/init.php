<?php
/**
 * 应用入口配置.
 * 
 * @author chengjun <cgjp123@163.com>
 */
/**
 * 处理开始初始化函数.
 * 
 * @return void
 */
function on_phpserver_request_start()
{
    
}

/**
 * 处理完成回调函数.
 *
 * @return void
 */
function on_phpserver_request_finish()
{
    
}
if (!defined('APP_ROOT_PATH')) define('APP_ROOT_PATH', __DIR__.DIRECTORY_SEPARATOR);

require_once(KERNEL_ROOT_PATH . 'Client/RPCSocketClient.php');
require_once(KERNEL_ROOT_PATH . 'BootStrap/Autoload.php');
\BootStrap\Autoload::instance()->setRoot(APP_ROOT_PATH)->init();