<?php
include_once("./head.php");
?>
<script src="../js/md5.js"></script>
<script type="text/javascript">	
function fiknode_AddUser(){
	var txtUsername	 =document.getElementById("txtUsername").value;
	var statusSelect =document.getElementById("statusSelect").value;
	var txtPasswd    =document.getElementById("txtPasswd").value;
	var txtRealname  =document.getElementById("txtRealname").value;
	var txtCompName  =document.getElementById("txtCompName").value;
	var txtPhone	 =document.getElementById("txtPhone").value;
	var txtQQ   	 =document.getElementById("txtQQ").value;
	var txtAddr 	 =document.getElementById("txtAddr").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var verifySelect =document.getElementById("verifySelect").value;
	
	if (txtUsername.length==0 ){ 
	  	document.getElementById("tipsUsername").innerHTML="请输入登录用户名";
		document.getElementById("txtUsername").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsUsername").innerHTML="";
	}

	if (txtPasswd.length<6 ){ 
	  	document.getElementById("tipsPasswd").innerHTML="密码必须大于等于6位";
		document.getElementById("txtPasswd").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsPasswd").innerHTML="";
	}	
	
	if (txtRealname.length==0 ){ 
	  	document.getElementById("tipsRealname").innerHTML="请输入用户姓名";
		document.getElementById("txtRealname").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsRealname").innerHTML="";
	}	
		
	
	if (txtPhone.length==0 ){ 
	  	document.getElementById("tipsPhone").innerHTML="请输入联系电话或手机";
		document.getElementById("txtPhone").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsPhone").innerHTML="";
	}
	
	if (txtQQ.length==0 ){ 
	  	document.getElementById("tipsQQ").innerHTML="请输入QQ号";
		document.getElementById("txtQQ").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsQQ").innerHTML="";
	}
		
	var postURL="./ajax_admin.php?mod=user&action=add";
	var postStr="username="+UrlEncode(txtUsername)+"&status=" + UrlEncode(statusSelect) + "&password=" + UrlEncode(hex_md5(txtPasswd)) + "&realname=" + UrlEncode(txtRealname)
			         + "&compname=" + UrlEncode(txtCompName) + "&phone=" + UrlEncode(txtPhone) + "&qq=" + UrlEncode(txtQQ) +  "&addr=" + UrlEncode(txtAddr)+ "&need_verify=" + verifySelect 
					 + "&backup=" + UrlEncode(txtBackup);					 
					 
	AjaxBasePost("user","add","POST",postURL,postStr);	
}

function FikCdn_AddUserResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=注册新用户成功。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtUsername").value="";
		document.getElementById("txtPasswd").value="";
		document.getElementById("txtRealname").value="";
		document.getElementById("txtCompName").value="";
		document.getElementById("txtPhone").value="";
		document.getElementById("txtQQ").value="";
		document.getElementById("txtAddr").value="";
		document.getElementById("txtBackup").value="";
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

FikCdn_ContinueAddUser()
{

}


</script>


<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">注册用户</span></td>
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
				<th colspan="2" align="left" height="35"></th> 
			</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"> <span class="input_tips_txt5">*</span><span class="input_tips_txt3">登录用户名：</span></td>
				<td><input id="txtUsername" name="txtUsername" type="text" size="36" maxlength="64"  /> <span class="input_tips_txt" id="tipsUsername" name="tipsUsername" ></span> </td>
    		</tr>		
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"> <span class="input_tips_txt5">*</span><span class="input_tips_txt3">登录密码：</span></td>
				<td><input id="txtPasswd" name="txtPasswd" type="password" size="36" maxlength="36"  /> <span class="input_tips_txt" id="tipsPasswd" name="tipsPasswd" ></span>  </td>
			</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">帐号状态：</span></td>
				<td>
				<select id="statusSelect" name="statusSelect" style="width:80px">
					<option value="1">正常</option>				
					<option value="0">冻结</option>						
				</select>
				
				<span class="input_tips_txt" id="tipsStatus" name="tipsStatus" ></span>  </td>
			</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"> <span class="input_tips_txt5">*</span><span class="input_tips_txt3">姓名：</span></td>
				<td><input id="txtRealname" name="txtRealname" type="text" size="26" maxlength="32"  /> <span class="input_tips_txt" id="tipsRealname" name="tipsRealname" ></span>  </td>
			</tr>		
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">公司名称：</span></td>
				<td><input id="txtCompName" name="txtCompName" type="text" size="64" maxlength="64"  /> <span class="input_tips_txt" id="tipsCompName" name="tipsCompName" ></span>  </td>
    		</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"> <span class="input_tips_txt5">*</span><span class="input_tips_txt3">联系电话：</span></td>
				<td><input id="txtPhone" name="txtPhone" type="text" size="26" maxlength="20" />  <span class="input_tips_txt" id="tipsPhone" name="tipsPhone" ></span> </td>
    		</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">QQ号：</span></td>
				<td><input id="txtQQ" type="text" size="26" maxlength="16" /> <span class="input_tips_txt" id="tipsQQ"></span> </td>
    		</tr>		
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">联系地址：</span></td>
				<td><input id="txtAddr" name="txtAddr" type="text" size="64" maxlength="128" /><span class="input_tips_txt" id="tipsAddr" name="tipsAddr" ></span> </td>
    		</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">添加域名是否需要审核：</span></td>
				<td>
				<select id="verifySelect" name="verifySelect" style="width:80px">
					<option value="1" >是</option>				
					<option value="0" >否</option>						
				</select>
				<span class="input_tips_txt" id="tipsVerifySelect" ></span>  </td>
			</tr>				
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" align="right"><span class="input_tips_txt3">备注：</span></td>
				<td><textarea id="txtBackup" name="txtBackup" maxlength="128" cols="80" rows="3" ></textarea> </td>
    		</tr>	
			
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="center"> </td>
				<td>
				<input name="btn_adddomain"  type="submit" style="width:80px;height:28px" id="btn_adddomain" value="注册" style="cursor:pointer;" onClick="fiknode_AddUser();" /> 
				</td>
    		</tr>
		 </table>
		 <p></p>




		
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
