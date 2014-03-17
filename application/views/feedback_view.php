<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>User</title>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.0.min.js" ></script>
<link rel="stylesheet" type="text/css" href="http://howeather.com/howeather/source/bootstrap/css/bootstrap.css" />
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
	padding:0;
}
li{
	list-style:none;
}
select{
	margin-top:50px;
}
.block {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background-color: rgba(0,0,0,0.5);
	z-index: 9999;
}

table{

	margin:0px auto;
	margin-top:20px;
	width:960px;
	border-left:1px solid #999;
	border-top:1px solid #999;
}

thead{
	border:px solid #999;
}

tr ,th{
	border-right:1px solid #999;
	border-bottom:1px solid #999;
}


</style>

</head>
<script type="text/javascript">
//var base_url = '<?=base_url();?>'
$(document).ready(function(e) {
    
$('#select').change(function(){
var a = $(this).val();
		location.href = 'http://127.0.0.1/HTWServerPHP/index.php/feedback/index/'+a+'?howeatoken=De6d8371d1w3T7IbJh2Yfycavi0I4h2GPP9Au20d4OD3A3aWCMzH4RT3h';  

});
	
	
});





</script>
<body>
<div>

<ul>
	<li>
        <select id="select" >
       		<option value="0">Device select</option>
            <option value="0">All device</option>
            <option value="1">IOS</option>
            <option value="2">Android</option>
            <option value="3">Windows phone</option>
        
        </select>
    </li>
   
	
</ul>



<table>
    <thead>
   		<tr>
            <th width='20%'>姓名</th>
            <th width='40%'>內容</th>
            <th width='10%'>時間</th>
            <th width='10%'>裝置</th>
        </tr>
    </thead>
    <tbody>
		<?php  foreach($feedbackquery->result() as $row){?>
            <tr>       
                    <th><?php echo $row->user_nickname; ?></th>           
                    <th><?php echo $row->feedback_content; ?></th>
                    <th><?php echo $row->feedback_time; ?></th> 
                    <th><?php $device = $row->device_type; 
                    if($device==1){echo 'IOS';}
                    elseif ($device==2){echo 'android';} 
                    else{ echo 'window phone' ;} ?></th>           
            </tr>
         <?php }?>
     </tbody>
</table>

  
  

</div>
</body>
</html>