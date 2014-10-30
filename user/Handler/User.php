<?php
namespace Handler;
/**
 * 用户相关接口
 *
 * @author oliver <cgjp123@163.com>
 */
class User
{
    
    /**
     * 返回码预定义.
     * 
     * @var array
     */
    public static $reponse = array(
            'CATCH_EXCEPTION'         => array('code' => '-2', 'msg' => 'sorry, system catch exception'),
            'SYSTEM_BUSY'                 => array('code' => '-1', 'msg' => 'system busy, please try again later'),
            'SUCCESS'                          => array('code' => '0', 'msg' => 'success'),
            'REGISTER_DATA_ERROR'  => array('code' => '10001', 'msg' => 'register data error: %s'),
            'ILLEGAL_PHONEID'           => array('code' => '10002', 'msg' => 'illegal phone id'),
            'MEMBER_INFO_USED'      => array('code' => '10003', 'msg' => 'register member info had used by others: %s'),
            );
    
    /**
     * 用户登录接口.
     * 
     * @param string $userName 用户名.
     * @param string $password  明文密码.
     * 
     * @return mixed
     */
    public function memberLogin($userName, $password)
    {
        
    }
    
    /**
     * 用户注册接口.
     * 
     * @param array  $registerData 注册数据.
     * @param string $phoneId       用户手机识别ID.
     * 
     * @return mixed
     */
    public function memberRegister(array $registerData, $phoneId = '')
    {
        if (empty($registerData) || !is_array($registerData)) {
            return \Helper\Helper::reponseData(printf(self::$reponse['REGISTER_DATA_ERROR'], 'empty register data'));
        }
        // 检查数据完整性
        try {
            \Module\User::instance()->checkRegisterData($registerData);
        } catch (\Exception\UserException $e) {
            return \Helper\Helper::reponseData(printf(self::$reponse['REGISTER_DATA_ERROR'], $e->getMessage()));
        }
        if (!empty($phoneId)) {
            if (!\Helper\Helper::checkCtypeAlNum($phoneId)) {
                return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_PHONEID']);
            }
        }
        // 检查用户名或邮箱或手机号是否已经被使用
        try {
            \Module\User::instance()->checkMemberInfoIsUsed($registerData['username'], $registerData['email'], $registerData['mobile'])
        } catch (\Exception\UserException $e) {
            return \Helper\Helper::reponseData(printf(self::$reponse['MEMBER_INFO_USED'], $e->getMessage()));
        }
        
        // 返回用户ID或false,false表示注册失败
        $result = \Module\User::instance()->registerMember($registerData, $phoneId);
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
    }
    
}
