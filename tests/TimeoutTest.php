<?php
/**
 * 超时测试
 */
class TimeoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * 超时限定时间内测试
     */
    public function testTimeIn()
    {
		// 在a页面($a对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
        $a = new Shuiguang\FormHash();
		$a->format('s');
        $hash = $a->get();
		
		// 延时0.1秒
        usleep(100000);
		
		// 在b页面($b对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
		$b = new Shuiguang\FormHash();
		$b->format('s');
        
		// 仍未超时, 断言返回值为true
        $this->assertEquals(true, $b->verify($hash));
		// 再延时0.1秒
        usleep(100000);
        // 仍未超时, 断言返回值为true
        $this->assertEquals(true, $b->verify($hash));
    }
    
    /**
     * 超时限定时间外测试
     */
    public function testTimeout()
    {
        // 在a页面($a对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
        $a = new Shuiguang\FormHash();
		$a->format('s');
        $hash = $a->get();
		
		// 延时2秒
        sleep(2);
		
		// 在b页面($b对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
		$b = new Shuiguang\FormHash();
		$b->format('s');
        
        // 必然超时, 断言返回值为false
        $this->assertEquals(false, $b->verify($hash));
    }
    
    /**
     * 混合测试
     */
    public function testTimemixed()
    {
		// 在a页面($a对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
        $a = new Shuiguang\FormHash();
		$a->format('s');
        $hash = $a->get();
		
		// 延时0.999999秒
        usleep(999999);
		
		// 在b页面($b对象)设置过期时间单位为秒: 最短有效时间为1s, 最长有效期为(1+1)s
		$b = new Shuiguang\FormHash();
		$b->format('s');
		
		// 未超时, 断言返回值为true
        $this->assertEquals(true, $b->verify($hash));
		
		// 再延时1.000002秒, 共2.000001s
        usleep(1000002);
        // 必然超时, 断言返回值为false
        $this->assertEquals(false, $b->verify($hash));
    }
}

