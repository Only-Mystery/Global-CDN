<?php
include_once("./head2.php");
?>
<script type="text/javascript">
function FikCdn_ModifyDomain(domain_id){
	var SSLCrtContent	=document.getElementById("SSLCrtContent").value;
	var SSLKeyContent	=document.getElementById("SSLKeyContent").value;
	var SSLExtraParams	=document.getElementById("SSLExtraParams").value;
	var txtSrcIP     	=document.getElementById("txtSrcIP").value;
	var txtUnicomIP   	=document.getElementById("txtUnicomIP").value;
	var txtBackup    	=document.getElementById("txtBackup").value;
	var productSelect	=document.getElementById("productSelect").value;
	var txtICP     		=document.getElementById("txtICP").value;
	
	//var txtDNSName     =document.getElementById("txtDNSName").value;
	
	var SSLOpt = 0;
	var objRadio = document.getElementsByName("SSLOpt");
	for(var i=0;i<objRadio.length;i++){
		if(objRadio[i].checked){  
			SSLOpt = objRadio[i].value;
		}   
	}
	
	var UpsSSLOpt = 0;
 	objRadio = document.getElementsByName("UpsSSLOpt");
	for(var i=0;i<objRadio.length;i++){
		if(objRadio[i].checked){  
			UpsSSLOpt = objRadio[i].value;
		}   
	}	
	
	if(UpsSSLOpt == 2 && (txtSrcIP.indexOf(":") > 0 || txtUnicomIP.indexOf(":") > 0)){
		var boxURL="msg.php?1.5.1&msg=如果 “源站IP” 指定了具体端口，在 “源站SSL选项” 中，不能选择 HTTP+HTTPS(协议跟随模式) 选项，只能选择 HTTP 或者 HTTPS 选项。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		return false;
	}
	
	var selUpstreatType = 0;
	objRadio = document.getElementsByName("selUpstreatType");
	for(var i=0;i<objRadio.length;i++){
		if(objRadio[i].checked){  
			selUpstreatType = objRadio[i].value;
		}   
	}
	
	var txtDNSName = "";
		
	if (txtSrcIP.length==0 && txtUnicomIP.length==0){ 
		var boxURL="msg.php?1.9&msg=电信源站 IP 和联通源站 IP 至少填一个。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("txtSrcIP").focus();
	  	return false;
	}
	
	var postURL="./ajax_domain.php?mod=domain&action=modify";
	var postStr="domain_id="+UrlEncode(domain_id) + "&SSLOpt=" + SSLOpt + "&SSLCrtContent=" + UrlEncode(SSLCrtContent) + "&SSLKeyContent=" + UrlEncode(SSLKeyContent) + "&SSLExtraParams=" + UrlEncode(SSLExtraParams) + 
			"&srcip=" + UrlEncode(txtSrcIP) + "&unicom_ip="+UrlEncode(txtUnicomIP)+ "&UpsSSLOpt=" + UpsSSLOpt + "&upstream_add_type=" +selUpstreatType+
			"&icp=" + UrlEncode(txtICP) +"&dns_name=" + UrlEncode(txtDNSName) + "&buy_id=" + productSelect+"&backup=" + UrlEncode(txtBackup);
					 
	AjaxBasePost("domain","modify","POST",postURL,postStr);	
}

function FikCdn_ModifyDomainResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nDomainID = json["id"];
		parent.window.location.reload();
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

function FikCdn_ToTaskList(){
	parent.parent.window.leftFrame.window.OnSelectNav("span_task_list");
	parent.window.location.href="task_list.php";
}

function ChangeSSLOpt(opt){
	switch(opt)
	{
		case 0:
		{
			var SSLAllParams = document.getElementById("SSLAllParams");
			SSLAllParams.style.display="none";
			break;
		}
		default:
		{
			if(document.getElementById("SSLExtraParams").value.length == 0){
				document.getElementById("SSLExtraParams").value = "SessionSize=5000&Password=";
			}
			
			var SSLAllParams = document.getElementById("SSLAllParams");
			SSLAllParams.style.display="table-row";
			break;
		}
	}
}

</script>
<?php
	$domain_id = isset($_GET['id'])?$_GET['id']:'';
 	$admin_username 	=$_SESSION['fikcdn_admin_username'];

 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$domain_id 	= mysql_real_escape_string($domain_id); 
		$admin_username 	= mysql_real_escape_string($admin_username); 		
		
		$sql = "SELECT * FROM fikcdn_domain WHERE id='$domain_id' ;"; 
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			exit();
		}
	
		$hostname 	= mysql_result($result,0,"hostname");
		$username 	= mysql_result($result,0,"username");
		$buy_id		= mysql_result($result,0,"buy_id");
		$group_id   = mysql_result($result,0,"group_id");
		$add_time 	= mysql_result($result,0,"add_time");
		$status 	= mysql_result($result,0,"status");
		$upstream 	= mysql_result($result,0,"upstream");
		$unicom_ip 	= mysql_result($result,0,"unicom_ip");
		$use_transit_node 	= mysql_result($result,0,"use_transit_node");		
		$icp 		= mysql_result($result,0,"icp");
		$DNSName 	= mysql_result($result,0,"DNSName");		
		$note	 	= mysql_result($result,0,"note");
		$upstream_add_all = mysql_result($result,0,"upstream_add_all");
		$SSLOpt 	    = mysql_result($result,0,"SSLOpt");	
		$SSLCrtContent	= mysql_result($result,0,"SSLCrtContent");			
		$SSLKeyContent	= mysql_result($result,0,"SSLKeyContent");
		$SSLExtraParams	= mysql_result($result,0,"SSLExtraParams");								
		$UpsSSLOpt	    = mysql_result($result,0,"UpsSSLOpt");			
	}			
 ?>    
 
<table width="550" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="140" height="25" class="objTitle" title="" >域名：</td>
    <td width="320">
		<label><?php echo $hostname; ?></label><span class="input_tips_txt" id="tipsDomain" ></span>
	</td>
  </tr>
  <tr>
    <td height="3" colspan="2"></td>
  </tr>
    
  <tr>
	<td width="140" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" >域名SSL选项：</td>
	<td width="320">
	  <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Http" value="0" <?php if($SSLOpt==0) echo 'checked="checked"'; ?> onchange="ChangeSSLOpt(0)" />HTTP &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Https" value="1" <?php if($SSLOpt==1) echo 'checked="checked"'; ?> onchange="ChangeSSLOpt(1)" />HTTPS &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_HttpAndHttps" value="2" <?php if($SSLOpt==2) echo 'checked="checked"'; ?> onchange="ChangeSSLOpt(2)" />HTTP+HTTPS
	</td>
  </tr>  
 <tr id="SSLAllParams" <?php if($SSLOpt==0) echo 'style="display:none"' ?>>
    <td colspan="2">
		<table width="550" border="0" align="0" cellpadding="0" cellspacing="0">
		  <tr>
			<td width="142" height="72" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="top" >*SSL证书文件内容：<br /><span style="font-style:italic;font-size:10px">SSL Certificate File&nbsp;<br />Content&nbsp;</span></td>
			<td width="325" height="72" valign="top">
			<textarea name="SSLCrtContent" class="inputText" id="SSLCrtContent" style="margin-top:5px;width:318px;height:60px;font-size:11px;border:1px solid #94C7E7;color:#00BFAA;;overflow:auto;"><?php echo $SSLCrtContent; ?></textarea>
			</td>
		  </tr>
		  <tr>
			<td width="142" height="72" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="top" >*SSL私钥文件内容：<br /><span style="font-style:italic;font-size:10px">SSL Private Key File&nbsp;<br />Content&nbsp;</span></td>
			<td width="325" height="72" valign="top">
			<textarea name="SSLKeyContent" class="inputText" id="SSLKeyContent" style="margin-top:5px;width:318px;height:60px;font-size:11px;border:1px solid #94C7E7;color:#00BFAA;overflow:auto;"><?php echo $SSLKeyContent; ?></textarea>
			</td>
		  </tr>
		  <tr>
			<td width="142" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="middle" >SSL附加配置参数：</td>
			<td width="325" height="25" valign="middle">
			<input name="SSLExtraParams" type="text" class="inputText" id="SSLExtraParams" style="width:322px;height:18px" value="<?php echo $SSLExtraParams; ?>" title="相关示例，可参看【相关使用帮助】" />
			</td>
		  </tr>
		</table>
	</td>
  </tr>

   
  <tr>
    <td height="25" class="objTitle" title="如果有多个电信源站，可用“;”间隔，如：211.155.23.66;234.34.55.123" ><span class="input_red_tips">*</span>源站电信 IP：</td>
    <td>
		<input id="txtSrcIP" type="text" size="36" class="inputText" style="width:320px;height:16px" maxlength="64"   value='<?php echo $upstream; ?>' title="如果有多个电信源站，可用“;”间隔，如：211.155.23.66;234.34.55.123" /> <span class="input_tips_txt" id="tipsSrcIP" name="tipsSrcIP" ></span>  
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="如果有多个联通源站，可用“;”间隔，如：211.155.23.66;234.34.55.123" >源站联通 IP：</td>
    <td>
		<input id="txtUnicomIP" type="text" size="36" class="inputText" style="width:320px;height:16px" maxlength="64"  value='<?php echo $unicom_ip; ?>'  title="如果有多个联通源站，可用“;”间隔，如：211.155.23.66;234.34.55.123" /><span class="input_tips_txt4" id="tipsUnicomIP" ></span>
	</td>
  </tr>
 <tr>
    <td height="3" colspan="2"></td>
  </tr>  
  <tr>
	<td width="140" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" >源站SSL选项：</td>
	<td width="320">
	  <input name="UpsSSLOpt" type="radio" class="radio" id="SSLOpt_Http" value="0" <?php if($UpsSSLOpt==0) echo 'checked="checked"'; ?> />HTTP &nbsp;&nbsp;
      <input name="UpsSSLOpt" type="radio" class="radio" id="SSLOpt_Https" value="1" <?php if($UpsSSLOpt==1) echo 'checked="checked"'; ?> />HTTPS &nbsp;&nbsp;
      <input name="UpsSSLOpt" type="radio" class="radio" id="SSLOpt_HttpAndHttps" value="2" <?php if($UpsSSLOpt==2) echo 'checked="checked"'; ?> />
      HTTP+HTTPS(协议跟随模式)
	</td>
  </tr>
    
 <tr>
    <td height="3" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >源站配置方式：</td>
    <td>
	  <input name="selUpstreatType" type="radio" class="radio" id="selUpstreatType" value="0" <?php if($upstream_add_all==0) echo 'checked="checked"'; ?> title="根据节点服务器线路类型匹配相同线路的源站添加到源站列表中。"/><label title="根据节点服务器线路类型匹配相同线路的源站添加到源站列表中。">自动匹配线路</label>&nbsp;&nbsp;
      <input name="selUpstreatType" type="radio" class="radio" id="selUpstreatType" value="1" <?php if($upstream_add_all==1) echo 'checked="checked"'; ?> title="电信和联通源站IP全部添加到节点服务器的源站列表中。"/><label title="电信和联通源站IP全部添加到节点服务器的源站列表中。">双源站配置</label>&nbsp;&nbsp;
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="仅作为记录域名的 ICP 备案号，方便管理。" >域名 IPC 备案号：</td>
    <td>
		<input id="txtICP" type="text" size="36" class="inputText" style="width:320px;height:16px" maxlength="64" value="<?php echo $icp; ?>" title="仅作为记录域名的 ICP 备案号，方便管理。" /> <span class="input_tips_txt" id="tipsICP" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>

  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >已出售产品套餐：</td>
    <td>
		<select id="productSelect" style="width:324px;height:24px;border:1px solid #94c6e1;" class="inputText">
 <?php
	if($db_link)
	{
		//查询产品所属服务器组
		$sql = "SELECT * FROM fikcdn_buy";
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count = mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_buy_id	= mysql_result($result,$i,"id");
				$product_id	= mysql_result($result,$i,"product_id");
				$this_username	= mysql_result($result,$i,"username");
				
				$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$product_name  		= mysql_result($result2,0,"name");
					$product_group_id	= mysql_result($result2,0,"group_id");	

					$sql = "SELECT * FROM fikcdn_client WHERE username='$this_username'";
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$company_name  		= mysql_result($result2,0,"company_name");
						if($buy_id==$this_buy_id)
						{
							echo '<option value="'.$this_buy_id.'" selected="selected">'.$product_name.'('.$this_username.")</option>";
						}
						else
						{
							echo '<option value="'.$this_buy_id.'">'.$product_name.'('.$this_username.")</option>";				
						}
					}				
				}
			}
		}
		
		mysql_close($db_link);
	}			
 ?>
				</select> <span class="input_tips_txt" id="tipsProductSelect" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" name="txtBackup" maxlength="128" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php echo $note; ?></textarea>
	</td>
  </tr>
  <tr>
    <td height="10" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddDomain"  type="submit" style="width:95px;height:28px" id="btnAddDomain" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyDomain(<?php echo $domain_id; ?>);" /></center></td>
  </tr>
</table>

<table width="550" border="0">
	<tr>
	<td><p style="padding-left:25px;">同步说明：<br />
	1. 修改域名信息成功后，系统通过后台任务方式同步到所有节点服务器；<br />
	</p></td>
	</tr>
</table>

<?php

include_once("./tail.php");
?>
