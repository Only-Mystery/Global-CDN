<?php
include_once("./head2.php");
?>
<script src="../js/md5.js"></script>
<script type="text/javascript">	
function FikCdn_ModifyAdmin(nAdminId){
	var statusSelect =document.getElementById("statusSelect").value;
	var txtRealname  =document.getElementById("txtRealname").value;
	var txtPhone	 =document.getElementById("txtPhone").value;
	var txtQQ   	 =document.getElementById("txtQQ").value;
	var txtAddr 	 =document.getElementById("txtAddr").value;
	var txtBackup    =document.getElementById("txtBackup").value;

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
	
	var postURL="./ajax_admin.php?mod=admin&action=modify";
	var postStr="id="+nAdminId+"&status=" + UrlEncode(statusSelect) + "&realname=" + UrlEncode(txtRealname)+ "&phone=" + UrlEncode(txtPhone) +
			    "&qq=" + UrlEncode(txtQQ) +  "&addr=" + UrlEncode(txtAddr) + "&backup=" + UrlEncode(txtBackup);												 				 				 
	AjaxBasePost("admin","modify","POST",postURL,postStr);	
}

function FikCdn_ModifyAdminResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){		
		var url =  "./admin_list.php";
		parent.window.location.href = url; 
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

<?php
	$nAdminId 	= isset($_GET['id'])?$_GET['id']:'';
	
	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_admin WHERE id=$nAdminId";
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				break;
			}
			
			$id  			= mysql_result($result,$i,"id");	
			$username  		= mysql_result($result,$i,"username");	
			$power 		 	= mysql_result($result,$i,"power");	
			$enable   		= mysql_result($result,$i,"enable");	
			$realname	   	= mysql_result($result,$i,"nick");	
			$phone		   	= mysql_result($result,$i,"phone");
			$qq		   		= mysql_result($result,$i,"qq");
			$addr		   	= mysql_result($result,$i,"addr");
			$backup		   	= mysql_result($result,$i,"note");
		}while(0);
		
		mysql_close($db_link);
	}	
?>

<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="100" height="25" class="objTitle" title="" >登录用户名：</td>
    <td width="220">
		<label><?php echo $username; ?></label><span class="input_tips_txt" id="tipsUsername" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >帐号状态：</td>
    <td>
		<select id="statusSelect" name="statusSelect" style="width:80px">
			<option value="1" <?php if($enable==1) echo 'selected="selected"'; ?> >正常</option>				
			<option value="0" <?php if($enable==0) echo 'selected="selected"'; ?> >冻结</option>						
		</select>	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >显示名称：</td>
    <td>
		<input id="txtRealname" type="text" size="26" maxlength="32" value="<?php echo $realname; ?>" />
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >联系电话：</td>
    <td>
		<input id="txtPhone" type="text" size="26" maxlength="20"value="<?php echo $phone; ?>"  />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >QQ 号：</td>
    <td>
		<input id="txtQQ" type="text" size="26" maxlength="16"value="<?php echo $qq; ?>"  />
	</td>
  </tr>
  
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >联系地址：</td>
    <td>
		<input id="txtAddr" type="text" maxlength="128" style="width:320px;height:18px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" value="<?php echo $addr; ?>"  />
	</td>
  </tr>
  
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" >备注：</td>
    <td>
		<textarea id="txtBackup" maxlength="128" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php echo $backup; ?></textarea>
	</td>
  </tr>
    
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnModifyAdmin"  type="submit" style="width:95px;height:28px" id="btnModifyAdmin" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyAdmin(<?php echo $nAdminId; ?>);" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
