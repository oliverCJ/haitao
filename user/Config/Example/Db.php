<?php
namespace Config;
/**
 * 数据库配置.
 *
 * @author oliver <cgjp123@163.com>
 */
class Db
{

    public $DEBUG = TRUE;
    public $DEBUG_LEVEL = 1;
    
    public $read = array(
            'default' => array(
                    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=haitao',
                    'user' => 'root',
                    'password' => 'root',
                    'confirm_link' => true,//required to set to TRUE in daemons.
                    'options'  => array(
                            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                            \PDO::ATTR_TIMEOUT=>3
                    )
            ),
        );
    
    public $write = array(
            'default' => array(
                    'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=haitao',
                    'user' => 'root',
                    'password' => 'root',
                    'confirm_link' => true,//required to set to TRUE in daemons.
                    'options'  => array(
                            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'utf8\'',
                            \PDO::ATTR_TIMEOUT=>3
                    )
            ),
        );

}
