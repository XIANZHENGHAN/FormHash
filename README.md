# FormHash

php FormHash for CSRF defense

参考discuz各个版本的formhash()函数，并结合crypt函数的原理实现不需要存储介质的formhash。

## 原理：
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
