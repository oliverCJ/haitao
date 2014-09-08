<?php
/**
 * 应用入口配置.
 * 
 * @author chengjun <cgjp123@163.com>
 */
$path = dirname(__FILE__).'/';
if (!defined('APP_ROOT_PATH')) define('APP_ROOT_PATH', $path);
include ROOT_PATH . 'Client/RPCClient.php';

\BootStrap\Autoload::instance()->setRoot($path);