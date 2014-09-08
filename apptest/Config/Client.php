<?php
namespace Config;

class Client
{
    public static $connectTTL = 30;
    public static $clientConfig = array(
            'apptest' => array(
                    'host' => 'http://127.0.0.1:8000/',
                    'user' => 'test',
                    'secrect' => '{1234-1234-1234-1234}'
                    ),
            );
}