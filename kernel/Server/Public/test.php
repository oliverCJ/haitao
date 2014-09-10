<?php
/**
 * 测试接口
 * 
 * @author chengjun <cgjp123@163.com>
 */
header("Content-type:text/html;charset=utf-8");
require '../Config/Config.base.php';
require '../../BootStrap/Autoload.php';
require_once('../../Client/RPCClient.php');
\BootStrap\Autoload::instance()->setRoot(ROOT_PATH)->init();
//\Server\Lib\ErrorHandler::instance();
$testHandler = new Test;
if ($_POST) {
    $testHandler->requestParam = $_POST;
}
$testHandler->display();

class Test
{
    protected $application;
    
    public $requestParam = array();
    
    public $callParam;
    
    protected static $config = array(
                    array(
                    'rpc_secrect_key' => '769af463a39f077a0340a189e9c1ec28',
                    'connectTTL' => 30,
                    'apptest' => array(
                            'host' => 'http://127.0.0.1:8010/',
                            'user' => 'test',
                            'secrect' => '{1BA19531-F9E6-478D-9965-7EB31A590000}',
                    ),
                )
            );
    
    public function __construct()
    {
        $this->application = \Server\Lib\ServerConfig::get('appServer');
    }
    
    protected function getService()
    {
        $requestParam = $this->requestParam;
        if (!empty($requestParam)) {
            if (!empty($requestParam['appname'])) {
                $appName = $requestParam['appname'];
            } else {
                throw new \Exception('Missing appname');
            }
            if (!empty($requestParam['class'])) {
                $class = $requestParam['class'];
            } else {
                throw new \Exception('Missing class');
            }
            if (!empty($requestParam['function'])) {
                $function = $requestParam['function'];
            } else {
                throw new \Exception('Missing function');
            }
            $param = array();
            if (!empty($requestParam['param'])) {
                $param = $requestParam['param'];
            }
            
            $call = '\RPCClient_'.$appName.'_'.$class;
            if (is_callable(array($call, 'instance'), true)) {
                $client = call_user_func_array(array($call, 'instance'), self::$config);
                $response = call_user_func_array(array($client, $function), $param);
                if (!empty($response)) {
                    return $response;
                } else {
                    throw new \Exception('connect failure');
                }
            }
        }
        return false;
    }
    
    public function display()
    {
        $getError = false;
        $response = array();
        try {
            $response = $this->getService();
        } catch (\Exception $e) {
            $getError = true;
            $error = $e->getMessage();
        }
        $class = isset($this->requestParam['class']) ? $this->requestParam['class'] : 'Test';
        $function = isset($this->requestParam['function']) ? $this->requestParam['function'] : 'getSomeData';
        
        $html = <<<HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>接口测试</title>
<script type="text/javascript" src="http://lib.sinaapp.com/js/jquery/1.8/jquery.min.js"></script>
</head>
<body>
HTML;
        if ($getError) {
            $html .= '<b>'.$error.'</b><br />';
        }
        $html .= '<b>接口测试</b><br />';
        $html .= '<form action="" method="post">';
        $html .= '<table>';
        $html .= '<tr>';
        $html .= '<td>应用:</td>';
        $html .= '<td><select name="appname">';
        if (!empty($this->application)) {
            foreach ($this->application as $k => $v) {
                $html .= '<option value="'.$k.'">'.$k.'</option>';
            }
        }
        $html .= '</select></td>';
        $html .= '</tr><tr>';
        $html .= '<td>类:</td>';
        $html .= '<td><input name="class" type="text" value="'.$class.'" style="width:400px"/></td>';
        $html .= '</tr><tr>';
        $html .= '<td>方法:</td>';
        $html .= '<td><input name="function" type="text" value="'.$function.'" style="width:400px"/></td>';
        $html .= '</tr><tbody id="parames"><tr>';
        $html .= '<td>参数:</td>';
        $html .= '<td><input name="param[]" type="text" value="" placeholder="数组使用array(..)格式,bool直接使用true/false,null直接写null" style="width:400px"/> <a href="javascript:void(0)" onclick="delParam(this)">删除本行</a></td>';
        $html .= '</tr></tbody><tfoot><tr><td colspan="2"><a href="javascript:void(0)" onclick="addParam()">添加参数</a></td></tr><tr>';
        $html .= '<td colspan="2"><input type="submit" value="提交" /></td>';
        $html .= '</tr></tfoot>';
        $html .= '</table>';
        $html .= '</form>';
        if ($response) {
            $html .= '<pre>'.var_export($response, true).'</pre>';
        }
        
        $html .= <<<HTML
<script type="text/javascript">

    function addParam() {
        $('#parames').append('<tr><td>参数:</td><td><input name="param[]" type="text" value="" placeholder="数组使用array(..)格式,bool直接使用true/false,null直接写null" style="width:400px"/> <a href="javascript:void(0)" onclick="delParam(this)">删除本行</a></td></tr>');
    }
    
    function delParam(obj) {
        $(obj).parent('td').parent('tr').remove();
    }
</script>
</body>
</html>
HTML;
        echo $html;
    }
}
