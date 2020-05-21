<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
if(!isset($_SESSION)){  
   session_start();  
}
include_once('../db/db.php');
include_once('../function/define.php');
include_once('../function/pub_function.php');
include_once("function_admin.php");
?>
<head>
<title>欢迎使用 Fikker CDN 系统管理系统</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Fikker CDN 后台管理系统" />
<meta name="keywords" content="Fikker CDN 后台管理系统" />
<style>
html, body {
	overflow-x: hidden;
	overflow-y: hidden;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
.input_red_tips{
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 12px;
	margin-left:8px;
	line-height: 25px;
	font-weight: bold;
	color: #ff0000;
}
</style>
</head>
<body>
<link href="../css/fikker.css" type="text/css" rel="stylesheet" />
<script language="javascript" src="../js/urlencode.js"></script> 
<script language="javascript" src="../js/cookie.js"></script>
<script language="javascript" src="../js/fikcdn_event.js"></script>
<script language="javascript" src="../js/fikcdn_function.js"></script>
<script language="javascript" src="../js/ajax.js"></script>
<script language="javascript" src="../js/md5.js"></script>
<script language="javascript" src="../js/event.js"></script>
<script language="javascript" src="../js/formatNumber.js"></script>
<script language="javascript" src="../js/div.js"></script>
</body>