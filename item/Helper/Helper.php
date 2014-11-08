<?php
namespace Helper;

class Helper
{
    /**
     * 统一封装返回.
     * 
     * @param unknown_type $response
     * @param unknown_type $data
     */
    public static function reponseData($response, $data = array())
    {
        return array_merge($response, array('result' => $data));
    }
}