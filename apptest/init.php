<?php
require '../kernel/BootStrap/Autoload.php';

$path = dirname(__FILE__).'/';

\BootStrap\Autoload::instance()->setRoot($path)->init();

$app = new \Handler\Test();