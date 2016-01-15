<?php
/**
 * 回调设置样本特征测试, 使得各个样本之间的formhash不能通用
 */
class CallbackTest extends PHPUnit_Framework_TestCase
{
    /**
     * 使用回调函数设置样本特征, 测试相同样本特征之间的formhash校验
     */
    public function testFeatureSame()
    {
        // 实例化一个a页面($a对象)
        $a = new Shuiguang\FormHash();
        // 设置a页面($a对象)的样本特征
        $a->attach(function($password) {
            // 样本ID=1
            $id = 1;
            return $password.$id;
        });
        
        // 实例化一个新页面($same对象), 样本特征与$a对象一致
        $same = new Shuiguang\FormHash();
        // 设置$same对象的样本特征
        $same->attach(function($password) {
            // 样本ID=1
            $id = 1;
            return $password.$id;
        });
        
        // 对$a对象和$same对象之间的formhash进行测试
        $this->assertEquals(true, $a->verify($a->get()));
        $this->assertEquals(true, $same->verify($same->get()));
        $this->assertEquals(true, $a->verify($same->get()));
        $this->assertEquals(true, $same->verify($a->get()));
    }
    
    /**
     * 使用回调函数设置样本特征, 测试不同样本特征之间的formhash校验
     */
    public function testFeatureNotSame()
    {
        // 实例化一个a页面($a对象)
        $a = new Shuiguang\FormHash();
        // 设置a页面($a对象)的样本特征
        $a->attach(function($password) {
            // 样本ID=1
            $id = 1;
            return $password.$id;
        });
        
        // 实例化一个b页面($b对象)对象, 样本特征与$a对象不一致
        $b = new Shuiguang\FormHash();
        // 设置b页面($b对象)对象的样本特征
        $b->attach(function($password) {
            // 样本ID=2
            $id = 2;
            return $password.$id;
        });
        
        // 对$a对象和$b对象之间的formhash进行测试
        $this->assertEquals(true, $a->verify($b->get()));
        $this->assertEquals(true, $b->verify($a->get()));
    }
}

