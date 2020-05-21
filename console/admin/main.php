<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<?php
include_once("function_admin.php");
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
?>
<HEAD>
<TITLE>欢迎使用 Fikker CDN 后台管理系统</TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
<!--
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	background-color: #EEF2FB;
}
-->
</style>
<link href="../css/main.css" type="text/css" rel="stylesheet" />
</HEAD>

<frameset rows="84,*" frameborder="NO" border="0" framespacing="0">
	<frame src="main_top.php" noresize="noresize" frameborder="NO" name="topFrame" scrolling="no" marginwidth="0" marginheight="0" target="main" />
    <frameset cols="200,*" id="frame">
		<frame src="main_left.php" name="leftFrame" noresize="noresize" marginwidth="0" marginheight="0" frameborder="0" target="main" />
		<frame src="node_list.php" name="mainFrame"  marginwidth="0" marginheight="0" frameborder="0" scrolling="auto" target="_self" />
	</frameset>
</frameset>

<noframes><body></body></noframes></html>