<?php
namespace Module;

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
     * 检查注册数据完整性和正确性.
     * 
     * @param array $registerData
     * @throws \Exception\UserException
     * @return boolean
     */
    public function checkRegisterData(array $registerData)
    {
        $theNeedElement = array('username', 'password', 'name', 'sex', 'mobile', 'email', 'provinceid', 'cityid', 'areaid', 'ip');
        $checkResult = true;
        $checkResultString = '';        
        foreach ($theNeedElement as $v) {
            if (!array_key_exists($v, $registerData)) {
                $checkResult = false;
                $checkResultString = 'miss element ' . $v;
                break;
            } else {
                switch ($v) {
                    case 'name' :
                    case 'username' :
                        if (! \Helper\Helper::checkUserName($registerData[$v])) {
                            $checkResult = false;
                            $checkResultString = 'the ' . $v . ' include illegal character';
                            break(2);
                        }
                        break;
                    case 'sex' :
                        if (!ctype_digit($registerData[$v])) {
                            $checkResult = false;
                            $checkResultString = 'the ' . $v . ' include illegal character';
                            break(2);
                        }
                        break;
                case 'email' :
                        if (!\Helper\Helper::checkEmail($registerData[$v])) {
                            $checkResult = false;
                            $checkResultString = 'the ' . $v . ' include illegal character';
                            break(2);
                        }
                        break;
                    case 'ip' :
                        if (!\Helper\Helper::checkIp($registerData[$v])) {
                            $checkResult = false;
                            $checkResultString = 'the ' . $v . ' include illegal character';
                            break(2);
                        }
                        break;
                    case 'mobile' :
                        if (!\Helper\Helper::checkMobile($registerData[$v])) {
                            $checkResult = false;
                            $checkResultString = 'the ' . $v . ' include illegal character';
                            break(2);
                        }
                        break;
                }
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
            'phoneid' => isset($registerData['phoneid']) ? $registerData['phoneid'] : '',
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
        );
        try {
            $lastId = \Model\User::instance()->insertMember($insertData);
        } catch (\Exception\DbException $e) {
            $lastId = false;
        }
        return $lastId;
    }
}