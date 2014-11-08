<?php
namespace Handler;

/**
 * 首页接口.
 *
 * @author chengjun <cgjp123@163.com>
 *
 */
class Index
{
    /**
     * 返回码预定义.
     *
     * @var array
     */
    public static $reponse = array(
            'CATCH_EXCEPTION'                 => array('code' => '-2', 'msg' => 'sorry, system catch exception'),
            'SYSTEM_BUSY'                         => array('code' => '-1', 'msg' => 'system busy, please try again later'),
            'SUCCESS'                                  => array('code' => '0', 'msg' => 'success'),
            'REGISTER_DATA_ERROR'          => array('code' => '20001', 'msg' => 'register data error: %s'),
            'ILLEGAL_ADPOSID'                   => array('code' => '20002', 'msg' => 'illegal ad position id'),
            'ILLEGAL_NUM'                          => array('code' => '20003', 'msg' => 'illegal number'),
            'EMAIL_USED'                             => array('code' => '20004', 'msg' => 'email had used'),
            'MOBILE_USED'                          => array('code' => '20005', 'msg' => 'mobile had used'),
            );
    
    /**
     * 获取广告.
     * 
     * @param unknown_type $adPosId
     * @param unknown_type $num
     */
    public function getUseableAd($adPosId, $num = 4)
    {
        if (empty($adPosId) || !ctype_digit((string)$adPosId) || $adPosId < 1) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_ADPOSID']);
        }
        if (!ctype_digit((string)$num) || $num < 1) {
            return \Helper\Helper::reponseData(self::$reponse['ILLEGAL_NUM']);
        }
        
        $result = \Module\Index::instance()->getUseableAd($adPosId, $num);
        if (!empty($result)) {
            return \Helper\Helper::reponseData(self::$reponse['SUCCESS'], $result);
        }
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
    /**
     * 获取当前正在进行到特卖.
     * 
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getUseableActivity($brandId = '', $num = 10)
    {
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
    /**
     * 获取即将进行到特卖.
     *
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getWillStartActivity($brandId = '', $num = 10)
    {
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
    /**
     * 获取已经结束的特卖.
     *
     * @param unknown_type $brandId
     * @param unknown_type $num
     */
    public function getTheEndActivity($brandId = '', $num = 10)
    {
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
    /**
     * 获取分类.
     * 
     * @param unknown_type $catId
     */
    public function getCategory($catId = 0)
    {
        return \Helper\Helper::reponseData(self::$reponse['SUCCESS']);
    }
    
}