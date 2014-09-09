<?php
namespace Server\Config;
/**
 * 各应用验证tokey私钥.
 * 
 * @author chengjun <cgjp123@163.com>
 *
 */
class AppSecrectKey
{
    public static $tokenKey = array(
            'apptest' => array(
                'test' => '{1BA19531-F9E6-478D-9965-7EB31A590000}',
                ),
            );
}