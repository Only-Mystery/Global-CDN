<?php
include_once("./head.php");
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
?>
<script src="../js/md5.js"></script>
<script language="javascript" src="../js/calendar.js"></script>

<script type="text/javascript">	
function FikCdn_ClearLoginLogBox(){
	var timeSelect=document.getElementById("timeSelect").value;
	var txtPasswd=document.getElementById("txtPasswd").value;
	if (txtPasswd.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请输入管理员密码。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');			
		document.getElementById("txtPasswd").focus();
		return false;
	}
	
	var boxURL="msg.php?4.1";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_ClearLoginLog(){
	var timeSelect=document.getElementById("timeSelect").value;
	var txtPasswd=document.getElementById("txtPasswd").value;
	if (txtPasswd.length==0 ){
		return false;
	}	
	var postURL="./ajax.php?mod=logs&action=clearloginlog";
	var postStr="passwd="+UrlEncode(hex_md5(txtPasswd))+"&timeval="+timeSelect;		 
	AjaxBasePost("logs","clearloginlog","POST",postURL,postStr);
}

function FikCdn_ClearLoginLogResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=删除登录日志信息成功。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
	}
	else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];	
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}else{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}	 
	}		
}

</script>


<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">清理日志</span></td>
				<td width="95%"></td>
			</tr>	
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>   
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th height="35" align="right">清理登录日志：</th>
				<th height="35" align="left" width="500">	
				<select id="timeSelect" style="width:180px" onChange="selectTimeval()">
					<option value="0">所有日志记录</option>
					<option value="30" selected="selected">一个月前的日志记录</option>	
					<option value="90">三个月前的日志记录</option>
					<option value="180">六个月前的日志记录</option>				
				</select> &nbsp;&nbsp;&nbsp;&nbsp;
				管理员密码 :
				<input id="txtPasswd" type="password" size="26" maxlength="64"  /></th>
				<th height="35" align="left">	
				<input name="btn_modify"  id="btn_modify" type="submit" style="width:80px;height:28px"  value="清理" style="cursor:pointer;" onClick="FikCdn_ClearLoginLogBox();" />
				</th>
			</tr>					
		 </table>		
	  </td> 
	  <td background="../images/mail_rightbg.gif">&nbsp;</td>
  </tr>
  
   <tr>
    <td valign="bottom" background="../images/mail_leftbg.gif"><img src="../images/buttom_left2.gif" width="17" height="17" /></td>
    <td background="../images/buttom_bgs.gif"><img src="../images/buttom_bgs.gif" width="17" height="17"></td>
    <td valign="bottom" background="../images/mail_rightbg.gif"><img src="../images/buttom_right2.gif" width="16" height="17" /></td>
  </tr> 
</table>
</div>

<?php

include_once("./tail.php");
?>
