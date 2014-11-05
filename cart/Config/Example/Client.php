<?php
namespace Config;
/**
 * 应用配置
 *
 * @author oliver <cgjp123@163.com>
 */
class Client
{
    public $rpc_secrect_key = '769af463a39f077a0340a189e9c1ec28';
    public $connectTTL = 30;
    // 测试应用.
    public $apptest = array(
                    'host' => 'tcp://127.0.0.1:9527/',
                    'user' => 'test',
                    'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590000}',
                    );
    // 购物流程应用
    public $cart = array(
    		'host' => 'tcp://127.0.0.1:9528/',
    		'user' => 'app',
    		'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
    );
    // 商品应用.
    public $item = array(
    		'host' => 'tcp://127.0.0.1:9529/',
    		'user' => 'app',
    		'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
    );
    // 用户应用
    public $user = array(
    		'host' => 'tcp://127.0.0.1:9530/',
    		'user' => 'app',
    		'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
    );
    // 圈子应用
    public $sns = array(
            'host' => 'tcp://127.0.0.1:9531/',
            'user' => 'app',
            'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590001}',
    );
}