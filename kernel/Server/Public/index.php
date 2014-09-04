<?php
require '../Config/Config.php';

require '../../BootStrap/Autoload.php';

\BootStrap\Autoload::instance()->setRoot(ROOT_PATH)->init();

$app = new \Server\Lib\Forward();
$app->boot();
