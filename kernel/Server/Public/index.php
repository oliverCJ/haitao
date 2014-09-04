<?php
/**
 * 项目入口.
 * 
 * @author oliverCJ <cgjp123@163.com>
 */
require '../Config/Config.base.php';
require '../Config/Config.php';

require '../../BootStrap/Autoload.php';

\BootStrap\Autoload::instance()->setRoot(ROOT_PATH)->init();

try {
    \Server\Lib\Forward::boot();
} catch (\Exception $e) {
    \Utility\Output::returnTextVal($e->getMessage());
}
