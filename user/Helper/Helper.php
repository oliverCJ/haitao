<?php
namespace Helper;

class Helper
{
    /**
     * 检查数据是否包含非法字符,只能含字母或数字.
     * 
     * @param string $data
     * 
     * @return boolean
     */
    public static function checkCtypeAlNum($data)
    {
        if (!empty($data) && ctype_alnum((string)$data)) return true;
        return false;
    }
    
    /**
     * 验证邮箱.
     * 
     * @param unknown_type $email
     */
    public static function checkEmail($email)
    {
        if (!empty($email) && preg_match('#^[\w\.\-]+@[\w\-]+(\.[\w\-]+)+$#', $email) !== false) return true;
        return false;
    }
    
    /**
     * 验证用户名,只允许数字,字母,中文,短横线,下划线.
     * 
     * @param unknown_type $userName
     */
    public static function checkUserName($userName)
    {
        if (!empty($userName) && preg_match('#^[A-Za-z0-9\u4E00-\u9FA5\-\_]{4,30}$#', $userName) !== false) return true;
        return false;
    }
    
    /**
     * 检查手机号.格式为:12345678901
     * 
     * @param unknown_type $mobile
     */
    public static function checkMobile($mobile)
    {
        if (!empty($mobile) && preg_match('#^\d{6-12}$#', $mobile) !== false) return true;
        return false;
    }
    
    /**
     * 检查IP. 格式为123.123.123.123
     * 
     * @param unknown_type $ip
     */
    public static function checkIp($ip)
    {
        if (!empty($mobile) && preg_match('#^([\d]{1,3}\.){3}[\d]{1,3}$#', $mobile) !== false) return true;
        return false;
    }
    
    /**
     * 统一封装返回.
     * 
     * @param unknown_type $response
     * @param unknown_type $data
     */
    public static function reponseData($response, $data = array())
    {
        return json_encode(array_merge($response, 'result' => $data));
    }
}