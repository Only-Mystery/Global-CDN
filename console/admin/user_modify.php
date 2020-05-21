<?php
include_once("./head2.php");
?>
<script src="../js/md5.js"></script>
<script type="text/javascript">	
function FikCdn_ModifyUser(uid){
	var txtId		 =document.getElementById("txtId").value;
	var statusSelect =document.getElementById("statusSelect").value;
	var verifySelect =document.getElementById("verifySelect").value;
	var txtRealname  =document.getElementById("txtRealname").value;
	var txtCompName  =document.getElementById("txtCompName").value;
	var txtPhone	 =document.getElementById("txtPhone").value;
	var txtQQ   	 =document.getElementById("txtQQ").value;
	var txtAddr 	 =document.getElementById("txtAddr").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var txtPassword=document.getElementById("txtPassword").value;
	if (txtRealname.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请输入用户姓名。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');			
		document.getElementById("txtRealname").focus();
	  	return false;
	}	
	
	if (txtPhone.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请输入联系电话或手机。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');					
		document.getElementById("txtPhone").focus();
	  	return false;
	}
	
	if (txtQQ.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请输入QQ号。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtQQ").focus();
	  	return false;
	}
	
	var md5Passwd;
	if(txtPassword.length>0){
		md5Passwd = hex_md5(txtPassword);
	}
	else{
		md5Passwd="";
	}
	
	var postURL="./ajax_admin.php?mod=user&action=modify";
	var postStr="id="+txtId+"&status=" + UrlEncode(statusSelect) + "&realname=" + UrlEncode(txtRealname) + "&passwd="+UrlEncode(md5Passwd) +  
			   "&compname=" + UrlEncode(txtCompName) + "&phone=" + UrlEncode(txtPhone) + "&qq=" + UrlEncode(txtQQ) + "&need_verify=" + verifySelect +
			   "&addr=" + UrlEncode(txtAddr) +"&backup=" + UrlEncode(txtBackup);
						 					 				 
	AjaxBasePost("user","modify","POST",postURL,postStr);	
}

function fikcdn_ModifyUserResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var url =  "./user_list.php";
		parent.window.location.href = url;
	}else{
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

<?php
	$uid 	= isset($_GET['id'])?$_GET['id']:'';
		
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$uid 	= mysql_real_escape_string($uid); 

		do
		{
			$sql = "SELECT * FROM fikcdn_client WHERE id=$uid";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				break;
			}
			
			$i=0;
			
			$id  			= mysql_result($result,$i,"id");	
			$username   	= mysql_result($result,$i,"username");
			$realname   	= mysql_result($result,$i,"realname");		
			$enable_login  	= mysql_result($result,$i,"enable_login");	
			$addr   		= mysql_result($result,$i,"addr");	
			$phone   		= mysql_result($result,$i,"phone");
			$register_time  = mysql_result($result,$i,"register_time");
			$last_login_time= mysql_result($result,$i,"last_login_time");
			$last_login_ip  = mysql_result($result,$i,"last_login_ip");
			$company_name	= mysql_result($result,$i,"company_name");
			$qq	   			= mysql_result($result,$i,"qq");	
			$backup	   		= mysql_result($result,$i,"note");		
			$domain_need_verify	= mysql_result($result,$i,"domain_need_verify");			
		}while(0);
		
		mysql_close($db_link);
	}			
?>

<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
  <input id="txtId" name="txtId" type="hidden"  value="<?php echo $uid; ?>" /> 
    <td width="120" height="25" class="objTitle" title="" >登录用户名：</td>
    <td width="220">
		<label><?php echo $username; ?></label> <span class="input_tips_txt" id="tipsUsername" name="tipsUsername" ></span> 
	</td>
  </tr>
 
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="当密码为空时，不修改登录密码" >登录密码：</td>
    <td>
		<input id="txtPassword" type="password" size="26" maxlength="64"  title="当密码为空时，不修改登录密码" value="" />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >帐号状态：</td>
    <td>
		<select id="statusSelect" name="statusSelect" style="width:80px">
			<option value="1" <?php if($enable_login==1) echo 'selected="selected"'; ?> >正常</option>				
			<option value="0" <?php if($enable_login==0) echo 'selected="selected"'; ?> >冻结</option>						
		</select>
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >姓名：</td>
    <td>
		<input id="txtRealname" name="txtRealname" type="text" size="26" maxlength="32" value="<?php echo $realname; ?>" />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >公司名称：</td>
    <td>
		<input id="txtCompName" name="txtCompName" type="text" size="48" maxlength="36"  value="<?php echo $company_name; ?>"  />
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >联系电话：</td>
    <td>
		<input id="txtPhone" type="text" size="26" maxlength="16" value="<?php echo $phone; ?>" />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >QQ 号码：</td>
    <td>
		<input id="txtQQ" type="text" size="26" maxlength="16" value="<?php echo $qq; ?>" />
	</td>
  </tr>
     
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >联系地址：</td>
    <td>	  
		<input id="txtAddr" name="txtAddr" type="text" size="48" maxlength="256" value="<?php echo $addr; ?>"  />
	</td>
  </tr>
  	 
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >域名是否需要审核：</td>
    <td>	  
		<select id="verifySelect" name="verifySelect" style="width:80px">
			<option value="1" <?php if($domain_need_verify==1) echo 'selected="selected"'; ?> >是</option>				
			<option value="0" <?php if($domain_need_verify==0) echo 'selected="selected"'; ?> >否</option>						
		</select>
	</td>
  </tr>

  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" name="txtBackup" maxlength="128" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php echo $backup; ?></textarea>
	</td>
  </tr>
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnModifyUser"  type="submit" style="width:95px;height:28px" id="btnModifyUser" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyUser(<?php echo $uid; ?>);" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
