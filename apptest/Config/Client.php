<?php
namespace Config;

class Client
{
    public static $rpc_secrect_key = '769af463a39f077a0340a189e9c1ec28';
    public static $connectTTL = 30;
    public static $clientConfig = array(
            'apptest' => array(
                    'host' => 'http://127.0.0.1:8010/',
                    'user' => 'test',
                    'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590000}'
                    ),
            );
}