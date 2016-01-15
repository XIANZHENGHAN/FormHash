<?php
/**
 * 普通调用测试
 */
class FormHashTest extends PHPUnit_Framework_TestCase
{
    /**
     * 对象测试
     */
    public function testObject()
    {
        $a = new Shuiguang\FormHash();
        $hash = $a->get();
        $b = new Shuiguang\FormHash();
        $this->assertEquals(true, $b->verify($hash));
    }
    
    /**
     * 静态测试
     */
    public function testStatic()
    {
        $hash = Shuiguang\FormHash::get();
        $b = new Shuiguang\FormHash();
        $this->assertEquals(true, Shuiguang\FormHash::verify($hash));
    }
    
    /**
     * 混合测试
     */
    public function testMixed()
    {
        $a = new Shuiguang\FormHash();
        $hash = $a->get();
        // 对象————静态测试
        $this->assertEquals(true, Shuiguang\FormHash::verify($hash));
        $b = new Shuiguang\FormHash();
        // 静态————对象测试
        $this->assertEquals(true, $b->verify(Shuiguang\FormHash::get()));
    }
}

