<?php
include_once("./head.php");
?>
<script src="../js/md5.js"></script>
<script type="text/javascript">	
function FikCdn_ModifyPasswd()
{
	var txtOldPasswd      =document.getElementById("txtOldPasswd").value;
	var txtNewPasswd      =document.getElementById("txtNewPasswd").value;
	var txtAffirmPasswd   =document.getElementById("txtAffirmPasswd").value;

	if (txtOldPasswd.length==0 ){ 
	  	document.getElementById("tipsOld").innerHTML="请输入现密码";
		document.getElementById("txtOldPasswd").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsOld").innerHTML="";
	}

	if (txtNewPasswd.length==0 ){ 
	  	document.getElementById("tipsNew").innerHTML="请输入新密码";
		document.getElementById("txtNewPasswd").focus();
	  	return false;
	}
	else if(txtNewPasswd.length <6){
		document.getElementById("tipsNew").innerHTML="密码最少6位";
		document.getElementById("txtNewPasswd").focus();
	  	return false;
	}	
	else{
		document.getElementById("tipsNew").innerHTML="";
	}

	if (txtAffirmPasswd.length==0 ){ 
	  	document.getElementById("tipsAffirm").innerHTML="请输入确认密码";
		document.getElementById("txtAffirmPasswd").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsAffirm").innerHTML="";
	}
	
	if(txtAffirmPasswd!=txtNewPasswd){
		document.getElementById("tipsNew").innerHTML="新密码和确认密码不一致，请重新输入";
		document.getElementById("txtNewPasswd").focus();
		document.getElementById("tipsAffirm").innerHTML="";
	  	return false;
	}

	var postURL="./ajax.php?mod=setting&action=modifypasswd";
	var postStr="oldpasswd="+ hex_md5(txtOldPasswd) + "&newpasswd=" + hex_md5(txtNewPasswd);		 
					 
	AjaxBasePost("setting","modifypasswd","POST",postURL,postStr);
}

function FikCdn_ModifyPasswdResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=修改登录密码成功。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		FikCdn_Reset();
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

function FikCdn_Reset()
{
	document.getElementById("txtOldPasswd").value = "";
	document.getElementById("txtNewPasswd").value = "";
	document.getElementById("txtAffirmPasswd").value = "";	
	document.getElementById("txtOldPasswd").focus();
}
</script>


<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31">
				<td height="31" width="85"><span class="title_bt">修改密码</span></td>
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
				<td width="200" height="35" align="right"><span class="input_tips_txt3">现密码：</span></td>
				<td><input id="txtOldPasswd" type="password" size="30" maxlength="32"  /> <span class="input_tips_txt" id="tipsOld" name="tipsOld" ></span> </td>
    		</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">新密码：</span></td>
				<td><input id="txtNewPasswd" type="password" size="30" maxlength="32"  /> <span class="input_tips_txt" id="tipsNew" name="tipsNew" ></span>  </td>
    		</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">确认新密码：</span></td>
				<td><input id="txtAffirmPasswd" type="password" size="30" maxlength="32"/>  <span class="input_tips_txt" id="tipsAffirm" name="tipsAffirm" ></span> </td>
    		</tr>	
			
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="center"> </td>
				<td>
				<input name="btnModify" id="btnModify" type="submit" style="width:80px;height:28px" value="修改" style="cursor:pointer;" onClick="FikCdn_ModifyPasswd();" /> 
				<input name="btnReset"  id="btnReset" type="submit" style="width:90px;height:28px" value="重新输入" style="cursor:pointer;" onClick="FikCdn_Reset();" /> 
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
