<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="description" content="Fikker CDN 后台管理系统" />
<meta name="keywords" content="Fikker CDN 后台管理系统" />
<title>Fikker CDN 后台管理系统</title>
<style>
body {font:12px Arial, Helvetica, sans-serif;color: #000;background-color: #EEF2FB;margin: 0px;}
#container {width: 182px;}

H1 {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 14px;
	margin: 0px;
	width: 182px;
	cursor: pointer;
	height: 30px;
	line-height: 20px;
}

H1 a {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	display: block;
	width: 182px;
	color: #000;
	height: 30px;
	text-decoration: none;
	moz-outline-style: none;
	background-image: url(../images/main_nav/menu_bgS.gif);
	background-repeat: no-repeat;
	line-height: 30px;
	text-align: center;
	margin: 0px;
	padding: 0px;
}
.content{width: 182px;}

.MM .LiActive {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 13px;
	line-height: 26px;
	color:#FFFFFF;
	background-color:#006699;
	display: block;
	margin: 0px 0px 0px 1px;
	padding: 0px;
	height: 26px;
	width: 179px;
}


.MM ul {list-style-type: none;margin: 0px;padding: 0px;display: block;}
.MM li {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 12px;
	line-height: 26px;
	color: #333333;
	list-style-type: none;
	display: block;
	text-decoration: none;
	height: 26px;
	width: 182px;
	padding-left: 0px;
}
.MM {width: 182px;margin: 0px;padding: 0px;left: 0px;top: 0px;right: 0px;bottom: 0px;}
.MM a:link {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 13px;
	line-height: 26px;
	color: #333333;
	background-image: url(../images/main_nav/menu_bg1.gif);
	background-repeat: no-repeat;
	height: 26px;
	width: 182px;
	display: block;
	text-align:center;
	margin: 0px;
	padding: 0px;
	overflow: hidden;
	text-decoration: none;
	text-indent:0px;
}
.MM a:visited {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 13px;
	line-height: 26px;
	color: #333333;
	background-image: url(../images/main_nav/menu_bg1.gif);
	background-repeat: no-repeat;
	display: block;
	text-align: center;
	margin: 0px;
	padding: 0px;
	height: 26px;
	width: 182px;
	text-decoration: none;
	text-indent:0px;
}
.MM a:active {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 13px;
	line-height: 26px;
	color: #333333;
	background-image: url(../images/main_nav/menu_bg1.gif);
	background-repeat: no-repeat;
	height: 26px;
	width: 182px;
	display: block;
	text-align: center;
	margin: 0px;
	padding: 0px;
	overflow: hidden;
	text-decoration: none;
	text-indent:0px;
}
.MM a:hover {
	font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif;
	font-size: 13px;
	line-height: 26px;
	color: #006600;
	background-image: url(../images/main_nav/menu_bg2.gif);
	background-repeat: no-repeat;
	text-align: center;
	display: block;
	margin: 0px;
	padding: 0px;
	height: 26px;
	width: 182px;
	text-decoration: none;
	text-indent:0px;
}
</style>
</head>

<script language="javascript" src="../js/fikcdn_function.js"></script>
<script language="javascript" src="../js/ajax.js"></script>
<script language="javascript" src="../js/cookie.js"></script>
<script type="text/javascript">	
function FikCdn_AdminLogout(){
	var postURL="ajax_login.php?mod=login&action=logout";
	var postStr="";
	AjaxBasePost("login","logout","POST",postURL,postStr);
}

function FikCdn_IsLogin(){
	var xmlhttp;	
    if (window.XMLHttpRequest)
	{
	  	// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	{
	  	// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
		
	xmlhttp.onreadystatechange=function()
	{
	  	if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{	
			var sResponse= xmlhttp.responseText;
			if(sResponse!=1)
			{
				parent.location.href = "./login.php";
			}
		}
	}
	
	var postUrl = "ajax_login.php?mod=login&action=is_login";
	var postStr="";
	xmlhttp.open("POST",postUrl,true);
	xmlhttp.setRequestHeader("Content-Type","application/x-www-form-urlencoded"); 
	xmlhttp.send(null);
	return false;	
}

var __sCurActiveSpanID="span_node_list";

function OnClickNav(sSpanID,sUrl){
	if(__sCurActiveSpanID==sSpanID){
		FikCdn_IsLogin();
		return true;
	}
	var OdjSpan =document.getElementById(sSpanID);
	var OdjCurSpan =document.getElementById(__sCurActiveSpanID);
	OdjSpan.className="LiActive";

	if(typeof(OdjCurSpan) != "undefined"){
		OdjCurSpan.className="LiNormal";
	}
		
	__sCurActiveSpanID= sSpanID;
	FikCdn_IsLogin();
		
	//self.parent.mainFrame.FrameReloadUrl(sUrl);
	
	//window.frames["main"].location = "fikcdn_addhost.php";; 
	
	return true;
}

function OnSelectNav(sSpanID){
	if(__sCurActiveSpanID==sSpanID){
		FikCdn_IsLogin();
		return true;
	}
	var OdjSpan =document.getElementById(sSpanID);
	var OdjCurSpan =document.getElementById(__sCurActiveSpanID);
	OdjSpan.className="LiActive";

	if(typeof(OdjCurSpan) != "undefined"){
		OdjCurSpan.className="LiNormal";
	}
		
	__sCurActiveSpanID= sSpanID;
}

</script>

<body>

<div id="container">
  <h1 class="type"><a href="javascript:void(0)">服务器管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	   <li><a href="node_list.php" target="mainFrame" onclick="OnClickNav('span_node_list');"><span id='span_node_list' class="LiActive">服务器列表</span></a></li>
   	   <li><a href="group_list.php" target="mainFrame" onclick="OnClickNav('span_group_list');"><span id='span_group_list' class="LiNormal">服务器组</span></a></li>
	   <li><a href="node_status.php" target="mainFrame" onclick="OnClickNav('span_node_status');"><span id='span_node_status' class="LiNormal">服务器状态</span></a></li>	  
	   <li><a href="stat_node_bandwidth.php" target="mainFrame" onclick="OnClickNav('span_stat_node_bandwidth');"><span id='span_stat_node_bandwidth' class="LiNormal">服务器流量</span></a></li>	  
       <li><a href="cache_rules_fcache.php" target="mainFrame" onclick="OnClickNav('span_cache_rules_fcache');"><span id='span_cache_rules_fcache' class="LiNormal">缓存规则</span></a></li>	  
	  <!-- <li><a href="fikcdn_listpull.php" target="main">拉取任务</a></li> -->
	</ul>
  </div>  

  <h1 class="type"><a href="javascript:void(0)">站点管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="domain_list.php" target="mainFrame" onclick="OnClickNav('span_domain_list');"><span id='span_domain_list' class="LiNormal">域名列表</span></a></li>
	  <li><a href="domain_verify.php" target="mainFrame" onclick="OnClickNav('span_domain_verify');"><span id='span_domain_verify' class="LiNormal">域名审核</span></a></li>	  
	  <li><a href="domain_trains.php" target="mainFrame" onclick="OnClickNav('span_domain_trains');"><span id='span_domain_trains' class="LiNormal">中转列表</span></a></li>
	  <li><a href="stat_domain_bandwidth.php"target="mainFrame" onclick="OnClickNav('span_domain_bandwidth');" ><span id='span_domain_bandwidth' class="LiNormal">域名流量</span></a></li>	
	  <li><a href="domain_cleancache.php" target="mainFrame" onclick="OnClickNav('span_domain_cleancache');"><span id='span_domain_cleancache' class="LiNormal">更新缓存</span></a></li>
	  <li><a href="task_list.php" target="mainFrame" onclick="OnClickNav('span_task_list');"><span id='span_task_list' class="LiNormal">后台任务</span></a></li>
	</ul>
  </div>   
    
<div id="container">
  <h1 class="type"><a href="javascript:void(0)">套餐管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="product_list.php" target="mainFrame" onclick="OnClickNav('span_product_list');"><span id='span_product_list' class="LiNormal">产品套餐</span></a></li>
	  <li><a href="buy_list.php" target="mainFrame" onclick="OnClickNav('span_buy_list');"><span id='span_buy_list' class="LiNormal">售出套餐</span></a></li>
	  <li><a href="stat_buy_product_bandwidth.php" target="mainFrame" onclick="OnClickNav('span_buy_product_bandwidth');"><span id='span_buy_product_bandwidth' class="LiNormal">套餐流量</span></a></li>	  
	  <li><a href="order_list.php" target="mainFrame" onclick="OnClickNav('span_order_list');"><span id='span_order_list' class="LiNormal">订单列表</span></a></li>
	  <!-- <li><a href="fikcdn_listpull.php" target="mainFrame">拉取任务</a></li> -->
	</ul>
  </div>  
   
  <h1 class="type"><a href="javascript:void(0)">财务管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="recharge_list.php" target="mainFrame" onclick="OnClickNav('span_recharge_list');"><span id='span_recharge_list' class="LiNormal">充值记录</span></a></li>
	  <li><a href="recharge_add.php" target="mainFrame" onclick="OnClickNav('span_recharge_add');"><span id='span_recharge_add' class="LiNormal">用户充值</span></a></li>
	  <li><a href="buy_history.php" target="mainFrame" onclick="OnClickNav('span_buy_history');"><span id='span_buy_history' class="LiNormal">消费记录</span></a></li>
	</ul>
  </div>
  
  <h1 class="type"><a href="javascript:void(0)">用户管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="user_list.php" target="mainFrame" onclick="OnClickNav('span_user_list');"><span id="span_user_list" class="LiNormal">用户列表</span></a></li>
  	  <li><a href="user_add.php" target="mainFrame" onclick="OnClickNav('span_user_add');"><span id="span_user_add" class="LiNormal">注册用户</span></a></li>
	  <li><a href="admin_list.php" target="mainFrame" onclick="OnClickNav('span_admin_list');"><span id="span_admin_list" class="LiNormal">管理员列表</span></a></li>
	</ul>
  </div>
    	   
  <h1 class="type"><a href="javascript:void(0)">系统设置</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="admin_info.php" target="mainFrame" onclick="OnClickNav('span_admin_info');"><span id='span_admin_info' class="LiNormal">个人资料</span></a></li>
	  <li><a href="passwd_modify.php" target="mainFrame" onclick="OnClickNav('span_passwd_modify');"><span id='span_passwd_modify' class="LiNormal">修改密码</span></a></li>
	 <!-- <li><a href="#" onclick="FikCdn_AdminLogout();">安全退出</a></li> -->
	</ul>
  </div>   
  
  <h1 class="type"><a href="javascript:void(0)">日志管理</a></h1>
  <div class="content">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	  <tr>
		<td><img src="../images/main_nav/menu_topline.gif" width="182" height="5" /></td>
	  </tr>
	</table>
	<ul class="MM">
	  <li><a href="log_login.php" target="mainFrame" onclick="OnClickNav('span_log_login');"><span id='span_log_login' class="LiNormal">登录日志</span></a></li>
	  <li><a href="log_clear.php" target="mainFrame" onclick="OnClickNav('span_log_clear');"><span id='span_log_clear' class="LiNormal">清理日志</span></a></li>
	</ul>
  </div> 
      	 
</div>

</body>
</html>