<?php
namespace Module;
/**
 * 用户操作模块.
 *
 * @author oliver <cgjp123@163.com>
 */
class User extends \Module\ModuleBase
{
    const PASSWORD_KEY = 'password_';
    /**
     * 获取实例.
     * 
     * @return multitype:
     */
    public static function instance()
    {
        return parent::instance();
    }
    
    /**
     * 用户登录
     * 
     * @param unknown_type $username
     * @param unknown_type $passwd
     * @return boolean|unknown
     */
    public function memberLogin($username, $passwd)
    {
        $passwd = substr(hash('sha512', self::PASSWORD_KEY . $passwd), 0, 32);
        $cond = array(
                'username' => $username,
                'password' => $passwd,
                );
        $result = \Model\User::instance()->getUserInfoByCond($cond);
        if (empty($result)) {
            return false;
        }
        // 登录成功,要更新一下最后登录时间
        $updateCond = array(
                'userid' => $result['userid'],
                );
        $updateParam = array(
                'lastlogintime' => time(),
                );
        \Model\User::instance()->updateUserInfo($updateParam, $updateCond);
        return $result;
    }
    
    /**
     * 根据用户ID获取用户信息.
     * 
     * @param unknown_type $uid
     * @return boolean|unknown
     */
    public function getUserInfoByUid($uid)
    {
        $cond = array(
                'userid' => $uid
                );
        $result = \Model\User::instance()->getUserInfoByCond($cond);
        if (empty($result)) {
            return false;
        }
        return $result;
    }
    
    /**
     * 检查注册数据完整性.
     * 
     * @param array $registerData
     * @throws \Exception\UserException
     * @return boolean
     */
    public function checkRegisterData(array $registerData)
    {
        $theNeedElement = array('username', 'password', 'name', 'sex', 'client_platform', 'client_system');
        $checkResult = true;
        $checkResultString = '';        
        foreach ($theNeedElement as $v) {
            if (!array_key_exists($v, $registerData)) {
                $checkResult = false;
                $checkResultString = 'miss element ' . $v;
                break;
            }  
        }
        if (!$checkResult) {
            throw new \Exception\UserException($checkResultString);
        }
        return true;
    }
    
    /**
     * 验证数据正确性.
     * 
     * @param array $registerData
     * @throws \Exception\UserException
     * @return boolean
     */
    public function checkRegisterDataIsRight(array $registerData)
    {
        $checkResult = true;
        foreach ($registerData as $key => $data) {
            switch ($key) {
                case 'name' :
                case 'username' :
                    if (! \Helper\Helper::checkUserName($data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'sex' :
                    if (!ctype_digit((string)$data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'email' :
                    if (!\Helper\Helper::checkEmail($data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'ip' :
                    if (!\Helper\Helper::checkIp($data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'mobile' :
                    if (!\Helper\Helper::checkMobile($data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'client_platform' :
                    if (!ctype_digit((string)$data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
                case 'client_system' :
                    if (!ctype_digit((string)$data)) {
                        $checkResult = false;
                        $checkResultString = 'the ' . $key . ' include illegal character';
                        break(2);
                    }
                    break;
            }
        }
        if (!$checkResult) {
            throw new \Exception\UserException($checkResultString);
        }
        return true;
    }
    
    /**
     * 检查用户名是否已经被使用.
     * 
     * @param unknown_type $userName
     * @param unknown_type $email
     * @param unknown_type $mobile
     */
    public function checkUserNameIsUsed($userName) {
        return \Model\User::instance()->checkColumnValueIsExists('username', $userName);
    }
    
    /**
     * 检查邮箱是否已经被使用.
     *
     * @param unknown_type $userName
     * @param unknown_type $email
     * @param unknown_type $mobile
     */
    public function checkEmailInfoIsUsed($email) {
        return \Model\User::instance()->checkColumnValueIsExists('email', $email);
    }
    
    /**
     * 检查手机是否已经被使用.
     *
     * @param unknown_type $userName
     * @param unknown_type $email
     * @param unknown_type $mobile
     */
    public function checkMobileInfoIsUsed($mobile) {
        return \Model\User::instance()->checkColumnValueIsExists('mobile', $mobile);
    }
    
    /**
     * 注册.
     * 
     * @param unknown_type $registerData
     * @param unknown_type $phoneId
     */
    public function registerMember($registerData, $phoneId)
    {
        $insertData = array(
            'phoneid' => $phoneId,
            'username' => $registerData['username'],
            'password' => substr(hash('sha512', self::PASSWORD_KEY . $registerData['password']), 0, 32),
            'open_type' => isset($registerData['open_type']) ? $registerData['open_type'] : 0,
            'open_secretid' => isset($registerData['open_secretid']) ? $registerData['open_secretid'] : '',
            'name' => $registerData['name'],
            'sex' => $registerData['sex'],
            'mobile' => $registerData['mobile'],
            'email' => $registerData['email'],
            'provinceid' => isset($registerData['provinceid']) ?  $registerData['provinceid'] : 0,
            'cityid' => isset($registerData['cityid']) ?  $registerData['cityid'] : 0,
            'areaid' => isset($registerData['areaid']) ?  $registerData['areaid'] : 0,
            'area' => isset($registerData['area']) ?  $registerData['area'] : '',
            'logo' => isset($registerData['logo']) ?  $registerData['logo'] : '',
            'ip' => $registerData['ip'],
            'points' => isset($registerData['points']) ?  $registerData['points'] : 0,
            'status' => isset($registerData['status']) ?  $registerData['status'] : 0,
            'grade' => isset($registerData['grade']) ?  $registerData['grade'] : 1,
            'update_date' => isset($registerData['update_date']) ?  $registerData['update_date'] : 0,
            'regtime' => isset($registerData['regtime']) ?  $registerData['regtime'] : date('Y-m-d H:i:s'),
            'lastlogintime' => isset($registerData['lastlogintime']) ?  $registerData['lastlogintime'] : 0,
            'chkstatus' => isset($registerData['chkstatus']) ?  $registerData['chkstatus'] : 0, // 0手机邮箱都未验证，1邮箱已验证，2手机已验证，3均已验证
            'client_platform' => isset($registerData['client_platform']) ?  $registerData['client_platform'] : 0, // 注册平台
            'client_system' => isset($registerData['client_system']) ?  $registerData['client_system'] : 0, // 注册系统
            'create_time' => isset($registerData['create_time']) ?  $registerData['create_time'] : date('Y-m-d H:i:s'), // 发布时间
        );
        try {
            $lastId = \Model\User::instance()->insertMember($insertData);
        } catch (\Exception\DbException $e) {
            $lastId = false;
        }
        return $lastId;
    }
}