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
namespace Shuiguang\FormHash;
/**
 * php实现不使用存储介质实现表单来源校验以防御CSRF攻击, 参见php函数: crypt
 * FormHash校验类的主要功能是在用户的form表单的hidden input中生成一个动态的校验码，用户提交表单后在处理表单的程序中自动完成校验并判断是不是由本站的表单提交，从而防御CSRF攻击。
 * discuz在有效期内生成的formhash是固定不变的，这里通过crypt生成的formhash是动态变化的。
 * 注: CSRF防御仅针对于浏览器提交的表单，对灌水机提取formhash后再次提交表单校验无防御能力。
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
     * 有效期粒度, 参见FormHash::timeout
     * @var string
     */
    private static $format = 'd';
    
    /**
     * 有效期标记位
     * @var string
     */
    private static $flag = NULL;
    
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
     * 设置formhash的有效时间, 防止旧的formhash一直有效, 可选Y, m, d, H, i, s
     * 为了安全考虑实际有效时间为区间值0.5年-1年, 0.5月-1月, 0.5小时-1小时, 0.5分钟-1分钟, 0.5秒-1秒
     * @param string $format 时间格式
     */
    public static function timeout($format = 'd')
    {
        if($format === 'Y')
        {
            self::$flag = date('Y');
        }else if($format === 'm')
        {
            self::$flag = date('Y-m');
        }else if($format === 'd')
        {
            self::$flag = date('Y-m-d');
        }else if($format === 'H')
        {
            self::$flag = date('Y-m-d H');
        }else if($format === 'i')
        {
            self::$flag = date('Y-m-d H:i');
        }else
        {
            self::$flag = date('Y-m-d H:i:s');
        }
        self::$format = $format;
    }
    
    /**
     * 返回动态hash值, 直接使用FormHash::get()获取formhash值
     * @param string $salt 可选的加盐选项, 生成formhash时无需提供, 校验时必须提供formhash作为参数
     * @return string
     */
    public static function get($salt = '')
    {
        // 设置默认过期时间区间
        self::timeout(self::$format);
        if($salt === '')
        {
            // 不使用任何盐值进行加密是为了自动生成formhash
            $formhash = @crypt(self::$secret.self::$flag);
        }else{
            // FormHash::verify()校验时必须提供$salt参与加密, 如果再次加密得到的$formhash与$salt一致说明通过验证
            $formhash = crypt(self::$secret.self::$flag, $salt);
        }
        return $formhash;
    }
    
    /**
     * 校验动态formhash值, 请使用上次FormHash::get()得到的formhash值参与校验
     * @param string $formhash 必选的formhash值, 用户通过表单提交的formhash值
     * @return boolean
     */
    public static function verify($formhash = '')
    {
        // 如果用户提交的formhash值与某个密码经过crypt加密得到的结果仍然为formhash值本身, 则说明该formhash值===crypt(某个密码)
        // 如果密码没有泄露, 则可以认为该用户提交的formhash值是合法的
        
        // 设置默认过期时间区间, 尝试判断当前时间区间内$formhash是否通过验证
        self::timeout(self::$format);
        if(self::get($formhash) === $formhash)
        {
            return true;
        }else{
            // 尝试判断上一个时间区间内$formhash是否通过验证
            if(self::$format === 'Y')
            {
                self::$flag = date('Y', mktime(0, 0, 0, 1, 1, date('Y')-1));
            }else if(self::$format === 'm')
            {
                self::$flag = date('Y-m', mktime(0, 0, 0, date('m')-1, 1, date('Y')));
            }else if(self::$format === 'd')
            {
                self::$flag = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d')-1, date('Y')));
            }else if(self::$format === 'H')
            {
                self::$flag = date('Y-m-d H', mktime(date('H')-1, 0, 0, date('m'), date('d'), date('Y')));
            }else if(self::$format === 'i')
            {
                self::$flag = date('Y-m-d H:i', mktime(date('H'), date('i')-1, 0, date('m'), date('d'), date('Y')));
            }else
            {
                self::$flag = date('Y-m-d H:i:s', mktime(date('H'), date('i'), date('s')-1, date('m'), date('d'), date('Y')));
            }
            if(self::get($formhash) === $formhash)
            {
                return true;
            }else{
                return false;
            }
        }
    }
}
