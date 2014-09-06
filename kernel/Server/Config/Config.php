<?php
/**
 * app配置.
 * 
 * @author chengjun <cgjp123@163.com>
 */
return array(

   'appServer' => array(
            'apptest' => array(
                    'rootPath' => "/apptest/init.php",         // [必填] 应用框架入口路径
                    'recv_timeout' => 1,                    // [必填] 接收超时时间
                    'process_timeout' => 2,                 // [必填] 处理超时时间
                    ),
            ),
);
