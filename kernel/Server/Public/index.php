<?php
/**
 * 项目入口.
 * 
 * @author oliverCJ <cgjp123@163.com>
 */
require '../Config/Config.base.php';

require '../../BootStrap/Autoload.php';

// 总消耗
$startTime = microtime(true);

\BootStrap\Autoload::instance()->setRoot(ROOT_PATH)->init();

try {
    \Server\Lib\Forward::boot();
    $endTime = microtime(true);
} catch (\Exception $e) {
    \Utility\Output::returnTextVal($e->getMessage());
}
