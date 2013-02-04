<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.min.js" ></script>
<style>
body {
	height: 100%;
	margin: 0px;
	padding: 0px;
	min-width: 1000px;
	overflow: hidden;
}
html {
	height: 100%;
}
h1,h2,h3{
	margin:0;
	padding:0;}
.block {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0,0.5);
	z-index: 9999;
}
.box {
	background-color: #f0f1f2;
	border: 2px solid #FFF;
	box-shadow: 1px 1px 10px black;
	border-radius: 1px;
}
#sign_in_box {
	position: absolute;
	top: 150px;
	left: 50%;
	margin-left: -150px;
	width: 300px;
	z-index: 100;
	opacity: 1;
	padding: 20px;

}
#sign_up_box {
	position: absolute;
	top: 150px;
	left: 50%;
	margin-left: -200px;
	width: 400px;
	z-index: 100;
	opacity: 1;
	padding: 20px;
	display:none;
}
#sign_up_box #user_email
{
	width:95%;
}
.box h3 {
	margin-bottom: 10px;
}
.box label {
	color: #848484;
	margin-top: 5px;
	font-size: 12px;
}
#sign_in_box #sign_up_link {
	margin-left: 10px;
	font-size: 13px;
	color: #3b5998;
	text-decoration: underline;
	cursor:pointer;
}
#sign_in_box input
{
	width:95%;
}
#sign_up_box table
{
	width:100%;
	table-layout:fixed;
}
#sign_up_box table input
{
	width:90%;
}
.user_form_btn {
	color: #FFF;
	background-color: #333333;
	border: 1px solid #111111;
	padding: 2px 5px;
	font-size: 12px;
	cursor: pointer;
}
.user_form_btn:hover
{
	background-color:#222222;
	border-color:#000000;
}
.box input {
	height: 20px;
	border: none;
	background: none;
	border-bottom: 1px solid #DDD;
	padding: 10px;
	font-size: 14px;
	margin-bottom: 5px;
	color: #232323;
}
.box input:focus {
	outline: none;
	background: #E3E3E3;
}

</style>
<script type="text/javascript">
var base_url = '<?=base_url();?>'
$(document).ready(function(e) {
    
	$('#sign_in_form').submit(function(e) {
		$.post(
			'user/sign_in_service',
			$("#sign_in_form").serializeArray(),
			function(data){
				if(data.status==='success')
				{
					var url = base_url;
					$(location).attr('href',url);
				}
				else
				{
					alert(data.msg);
				}
			}
			,'json'
		);
		return false;
	});
	
	$('#sign_up_form').submit(function(e) {
		$.post(
			'user/sign_up_service',
			$("#sign_up_form").serializeArray(),
			function(data){
				if(data.status==='success')
				{
					var url = base_url;
					$(location).attr('href',url);
				}
				else
				{
					alert(data.msg);
				}
			}
			,'json'
		);
		return false;
	});

	$('#sign_up_link').on('click',function(e){
		e.preventDefault();
		$('#sign_in_box').fadeOut('fast');	
		$('#sign_up_box').fadeIn('fast');
		
		
	});
	
	
});


</script>
</head>

<body>
<div class="block">
  <div class="box" id="sign_in_box">
    <form id="sign_in_form">
      <h3>登入HOWeather</h3>
      <label>電子郵件</label>
      <br />
      <input name="user_email" id="user_email" type="text" value=""/>
      <br />
      <label>密碼</label>
      <br />
      <input type="password" id="user_password" name="user_password" />
      <br />
      <button type="submit" class="user_form_btn" id="sign_in_btn">登入</button>
      <a id="sign_up_link"><span>註冊</span></a>
    </form>
  </div>
  <div class="box" id="sign_up_box">
 	 <form id="sign_up_form">
      <h3>註冊HOWeather</h3>
      <label>電子郵件</label>
      <br />
      <input name="user_email" id="user_email" type="text" value=""/>
      <br />
      <table >
      <tr><td>
      <label>密碼</label>
      <br />
      <input type="password" id="user_password" name="user_password" />
      </td>
      <td>
      <label>密碼確認</label>
      <br />
      <input type="password" id="user_password_again" name="user_password_again" />
      </td>
      </tr>
      </table>
      <br />
      <input type="hidden" name="device_type" value="4" />
      <button type="submit" class="user_form_btn" id="sign_up_btn">註冊</button>
     
    </form>
  
  
  
  </div>
</div>
</body>
</html>