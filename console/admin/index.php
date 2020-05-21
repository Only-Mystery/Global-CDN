<?php
session_start(); 
include_once('../db/db.php');
include_once('../function/define.php');
include_once("function_admin.php");

if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
else
{
	header("Location:main.php");
}
?>

