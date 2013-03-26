<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>signup</title>
</head>

<body>
<div>

<form action="signup/signup_service" method="post">
<input type="text" name="user_email" placeholder="電子郵件..." />
<input type="password" name="user_password" placeholder="密碼..." />
<input type="password" name="user_password_again"  placeholder="再次輸入密碼..." />
<input type="text" name="user_nickname" placeholder="暱稱..." />
<select name="device_type">
<option value="1">ios</option>
<option value="2">android</option>
<option value="3">wp</option>
<option value="4">web</option>
</select>
<input type="submit" value="send"/>
</form>
</div>
</body>
</html>