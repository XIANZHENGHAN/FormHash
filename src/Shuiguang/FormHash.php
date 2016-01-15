<?php
/**
 * This file is part of FormHash.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 * IpWatch for php
 * @author shuiguang
 * @link https://github.com/shuiguang/FormHash
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Shuiguang;
/** 
 * php FormHash for CSRF defense
 * php实现不使用存储介质实现表单来源校验以防御CSRF攻击, 参见php函数: crypt
 * 优点: 与discuz在算法上类似, 同样不需要任何存储介质, 超时时间允许设置有效期单位长度, 超过有效时间后自动过期, 参见FormHash::timeout
 * 优点: 与discuz生成hash结果不同, discuz生成的formhash在有效期内不变, 而FormHash通过crypt生成的formhash是动态变化的
 * 特点: 生成formhash时只需要设置有效时间的单位, 默认为小时; 校验formhash时传递第二个参数$expire给FormHash::verify设置过期时间, $expire值越小性能越好, 默认为1
 * 缺点: 和discuz一样, 生成的formhash会被采集机器人搜集并作为参数提交给php, 对灌水机提取formhash后再次提交表单校验无任何防御能力
 * @author shuiguang 
 */
class FormHash
{
    /**
     * 版本号
     * @var string
     */
    const VERSION = '1.0';
     
    /**
     * 密码值, 可以为任意长度固定字符串, 但是请妥善保管该值避免泄露
     * @var string
     */
    const UC_KEY = '127.0.0.1';
      
    /**
     * 有效期单位, 默认单位为小时, 参见FormHash::timeout
     * @var string
     */
    private static $format = 'H';
    
    /**
     * 有效时间戳标记位
     * @var string
     */
    private static $timestamp = NULL;
    
    /**
     * 加密项, 由self::PASSWORD得到或者调用attach进行更改
     * @var string
     */
    private static $secret = self::UC_KEY;
    
    /**
     * 接受一个自定义算法函数, 实现一组获取和验证的个性化操作, 需要保证该组获取时和验证时self::$secret完全一致
     * 如果不调用FormHash::attach()方法所有组获取和验证将共享formhash值
     * @param string $callback 回调参数仅有一个password参数, 要求$callback返回字符串值作为self::$secret
     * @return string
     */
    
    public static function attach($callback = '')
    {
        self::$secret = call_user_func($callback, self::UC_KEY);
    }
    
    /**
     * 设置formhash的有效时间格式, 不同的时间格式之前的formhash不通用, 可选Y, m, d, H, i, s
     * @param string $format 时间格式
     */
    public static function format($format = 'H')
    {
        self::$format = $format;
        // 重新设置有效时间格式时需要重置self::$timestamp
        self::$timestamp = NULL;
    }
    
    /**
     * 获取当前有效时间格式下的时间字符串, 为了安全考虑实际有效时间为区间值$expire年-($expire+1)年, $expire月-($expire+1)月, $expire小时-($expire+1)小时, $expire分钟-($expire+1)分钟, 1秒-($expire+1)秒
     * @param string $expire 有效期单位长度, 解决时间临界值的bug, 例如2015-12-31 23:59:59-2016-01-01 00:00:00仅差1秒的bug
     */
    private static function timeout($expire = 0)
    {
        if(self::$format === 'Y')
        {
            self::$timestamp = date('Y', $expire > 0 ? mktime(0, 0, 0, 1, 1, date('Y')-$expire) : time());
        }else if(self::$format === 'm')
        {
            self::$timestamp = date('Y-m', $expire > 0 ? mktime(0, 0, 0, date('m')-$expire, 1, date('Y')) : time());
        }else if(self::$format === 'd')
        {
            self::$timestamp = date('Y-m-d', $expire > 0 ? mktime(0, 0, 0, date('m'), date('d')-$expire, date('Y')) : time());
        }else if(self::$format === 'H')
        {
            self::$timestamp = date('Y-m-d H', $expire > 0 ? mktime(date('H')-$expire, 0, 0, date('m'), date('d'), date('Y')) : time());
        }else if(self::$format === 'i')
        {
            self::$timestamp = date('Y-m-d H:i', $expire > 0 ? mktime(date('H'), date('i')-$expire, 0, date('m'), date('d'), date('Y')) : time());
        }else
        {
            self::$timestamp = date('Y-m-d H:i:s', $expire > 0 ? mktime(date('H'), date('i'), date('s')-$expire, date('m'), date('d'), date('Y')) : time());
        }
    }
    
    /**
     * 返回动态hash值, 直接使用FormHash::get()获取formhash值
     * @param string $salt 可选的加盐选项, 生成formhash时无需提供, 校验时必须提供formhash作为参数
     * @return string
     */
    public static function get($salt = '')
    {
        // 仅在$timestamp为NULL时初始化self::$timestamp值
        if(is_null(self::$timestamp))
        {
            self::timeout();
        }
        if($salt === '')
        {
            // 不使用任何盐值进行加密是为了自动生成formhash
            $formhash = @crypt(self::$secret.self::$timestamp);
        }else{
            // FormHash::verify()校验时必须提供$salt参与加密, 如果再次加密得到的$formhash与$salt一致说明通过验证
            $formhash = crypt(self::$secret.self::$timestamp, $salt);
        }
        return $formhash;
    }
    
    /**
     * 校验动态formhash值, 请使用上次FormHash::get()得到的formhash值参与校验
     * 如果用户提交的formhash值与某个密码经过crypt加密得到的结果仍然为formhash值本身, 则说明该formhash值===crypt(某个密码)
     * 如果密码没有泄露, 则可以认为该用户提交的formhash值是合法的
     * @param string $formhash 必选的formhash值, 用户通过表单提交的formhash值
     * @param string $expire 有效期单位长度, 默认一个单位时间过期, 例如2015-12-31 23:59:59-2016-01-01 00:00:00仅差1秒的bug
     * @return boolean
     */
    public static function verify($formhash = '', $expire = 1)
    {
        // 正常情况下直接验证
        if(self::get($formhash) === $formhash)
        {
            // return之前重置self::$timestamp
            self::$timestamp = NULL;
            return true;
        }else{
            // 验证失败后尝试$expire个单位时间的补偿
            $expire = (int)$expire;
            while($expire > 0)
            {
                // 修改过期时间区间, 尝试判断当前时间区间内$formhash是否通过验证
                self::timeout($expire);
                if(self::get($formhash) === $formhash)
                {
                    // return之前重置self::$timestamp
                    self::$timestamp = NULL;
                    return true;
                }
                $expire--;
            }
            // return之前重置self::$timestamp
            self::$timestamp = NULL;
            return false;
        }
    }
}
