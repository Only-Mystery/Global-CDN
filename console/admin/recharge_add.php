<?php
include_once("./head.php");
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
?>
<script language="javascript" src="../js/calendar.js"></script>

<script type="text/javascript">	
function FikCdn_AddRechargeBox()
{
	var boxURL="msg.php?4.3";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_AddRecharge(){
	var txtUsername=document.getElementById("txtUsername").value;
	var txtPasswd=document.getElementById("txtPasswd").value;
	var txtSerialNo=document.getElementById("txtSerialNo").value;
	var txtBankname=document.getElementById("banknameSelect").value;
	var txtTransactor=document.getElementById("txtTransactor").value;
	var txtMoney=document.getElementById("txtMoney").value;
	var txtBackup=document.getElementById("txtBackup").value;

	if(txtUsername.length==0 ){ 
	  	document.getElementById("tipsUsername").innerHTML="请输入用户帐号";
		document.getElementById("txtUsername").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsUsername").innerHTML="";
	}

	if(txtPasswd.length==0 ){ 
	  	document.getElementById("tipsPasswd").innerHTML="请输入管理员登录密码";
		document.getElementById("txtPasswd").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsPasswd").innerHTML="";
	}

	if(txtSerialNo.length==0 ){ 
	  	document.getElementById("tipsSerialNo").innerHTML="请输入银行流水号";
		document.getElementById("txtSerialNo").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsSerialNo").innerHTML="";
	}

	if (txtTransactor.length==0 ){ 
	  	document.getElementById("tipsTransactor").innerHTML="请输入经办人姓名";
		document.getElementById("txtTransactor").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsTransactor").innerHTML="";
	}	

	if (txtMoney.length==0 ){ 
	  	document.getElementById("tipsMoney").innerHTML="请输入充值金额";
		document.getElementById("txtMoney").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsMoney").innerHTML="";
	}
		
	var postURL="./ajax.php?mod=recharge&action=add";

	var postStr="username="+UrlEncode(txtUsername)+ "&passwd="+UrlEncode(hex_md5(txtPasswd)) + "&serialno=" + UrlEncode(txtSerialNo)+	
				"&bankname=" + UrlEncode(txtBankname) + "&transactor=" + UrlEncode(txtTransactor)+ "&money=" + UrlEncode(txtMoney) +
				"&backup=" + UrlEncode(txtBackup);
	AjaxBasePost("recharge","add","POST",postURL,postStr);
}

function fikcdn_AddRechargeResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=充值成功。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');		
		//window.location.href = "./fikcdn_listrecharge.php";
		document.getElementById("txtUsername").value="";
		document.getElementById("txtPasswd").value="";
		document.getElementById("txtSerialNo").value="";
		document.getElementById("banknameSelect").value="";
		document.getElementById("txtTransactor").value="";
		document.getElementById("txtMoney").value="";
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

</script>
			
<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">用户充值</span></td>
				<td width="95%"></td>
			</tr>	
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    

<?php
	$id 		= isset($_GET['id'])?$_GET['id']:'';
	
	if(strlen($id)>0 && is_numeric($id))
	{
		$db_link = FikCDNDB_Connect();
		if($db_link)
		{
			$sql = "SELECT * FROM fikcdn_client WHERE id=$id;";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$username  = mysql_result($result,0,"username");	
			}
		}
	}
?>
  
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th colspan="2" align="left" height="35"></th> 
			</tr>	
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">用户帐号：</span></td>
				<td><input id="txtUsername" type="text" size="26" maxlength="64" /> <span class="input_tips_txt" id="tipsUsername" ></span> </td>
    		</tr>			
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">管理员密码：</span></td>
				<td><input id="txtPasswd" type="password" size="26" maxlength="64" /> <span class="input_tips_txt" id="tipsPasswd" ></span> </td>
    		</tr>		
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">银行流水号：</span></td>
				<td><input id="txtSerialNo" type="text" size="64" maxlength="128" />  <span class="input_tips_txt" id="tipsSerialNo" ></span> </td>
    		</tr>				
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt3">银行名称：</span></td>
				<td>
				<select id="banknameSelect" style="width:200px">						
						<option value="0">工商银行帐号</option>
						<option value="1">建设银行帐号</option>
						<option value="2">农业银行帐号</option>
						<option value="3">支付宝帐号</option>
						<option value="4">公司开户行帐号</option>
				</select>				
				</td>
    		</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">经办人姓名：</span></td>
				<td><input id="txtTransactor" type="text" size="26" maxlength="64" />  <span class="input_tips_txt" id="tipsTransactor" ></span> </td>
    		</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right"><span class="input_tips_txt5">*</span><span class="input_tips_txt3">充值金额：</span></td>
				<td><input id="txtMoney" name="txtPhone" type="text" size="26" maxlength="16"  />  <span class="input_tips_txt" id="tipsMoney"></span> </td>
    		</tr>						
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" align="right"><span class="input_tips_txt3">备注：</span></td>
				<td><textarea id="txtBackup" name="txtBackup" maxlength="128" cols="80" rows="3" ></textarea> </td>
    		</tr>	
			
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="center"> </td>
				<td>
				<input id="btn_RechargeAdd"  type="submit" style="width:80px;height:28px;cursor:pointer;" value="确定" onClick="FikCdn_AddRechargeBox();" /> 
				</td>
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
