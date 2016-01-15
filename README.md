# FormHash

php FormHash for CSRF defense

参考discuz各个版本的formhash()函数，并结合crypt函数的原理实现不需要存储介质的formhash。

## 安装
-------

To install with composer:

```sh
composer require shuiguang/form-hash
```

## 原理
```php
<?php
$user_input =  '12+#?345';
$pass = urlencode($user_input));
$pass_crypt = crypt($pass);

if ($pass_crypt == crypt($pass, $pass_crypt)) {
  echo "Success! Valid password";
} else {
  echo "Invalid password";
} 
```
FormHash校验类的主要功能是在用户的form表单的hidden input中生成一个动态的校验码，用户提交表单后在处理表单的程序中自动完成校验并判断是不是由本站的表单提交，从而防御CSRF攻击。

discuz在有效期内生成的formhash是固定不变的，这里通过crypt生成的formhash是动态变化的。

注：CSRF防御仅针对于浏览器提交的表单，对灌水机提取formhash后再次提交表单校验无防御能力。

## 使用
```php
// 在a页面($a对象)设置过期时间单位为小时(默认最短有效时间为1小时, 最长有效期为(1+1)小时)
$a = new Shuiguang\FormHash();
// 建议调用format方法, format参数可选Y(小时), m(月), d(天), H(小时), i(分钟), s(秒)
$a->format('h');
// 生成formhash
$hash = $a->get();

// 在b页面首先获取到a页面提交的hash值, 然后创建$b对象设置超时模式为n小时过期(有效时间为n小时, 最长有效期为(n+1)小时)
$b = new Shuiguang\FormHash();
// 建议调用format方法
$b->format('h');
// 验证formhash
if($b->verify($hash))
{
	echo 'success';
}else{
	echo 'fail';
}
```



