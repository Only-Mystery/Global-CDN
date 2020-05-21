<?php
include_once("./head2.php");
?>
<script type="text/javascript">
function FikCdn_ModifyDomain(domain_id){
	var txtSrcIP     =document.getElementById("txtSrcIP").value;
	var txtUnicomIP   =document.getElementById("txtUnicomIP").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var productSelect=document.getElementById("productSelect").value;
	var txtICP     =document.getElementById("txtICP").value;
	
	//var txtDNSName     =document.getElementById("txtDNSName").value;
	
	var selUpstreatType = 0;
	var objRadio = document.getElementsByName("selUpstreatType");
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
	var postStr="domain_id="+UrlEncode(domain_id) + "&srcip=" + UrlEncode(txtSrcIP) + "&unicom_ip="+UrlEncode(txtUnicomIP)+ "&upstream_add_type=" +selUpstreatType+
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
		$SSLCrtContent	 	= mysql_result($result,0,"SSLCrtContent");
		$SSLOpt	 			= mysql_result($result,0,"SSLOpt");
		$SSLKeyContent	 	= mysql_result($result,0,"SSLKeyContent");
		$SSLExtraParams	 	= mysql_result($result,0,"SSLExtraParams");
		$UpsSSLOpt	 		= mysql_result($result,0,"UpsSSLOpt");
		
		$upstream_add_all	 	= mysql_result($result,0,"upstream_add_all");
	}			
 ?>    
 
<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal" style="padding-left:15px; padding-right:15px;">
  <tr>
    <td width="100" height="25" class="objTitle" title="" >域名：</td>
    <td width="220">
		<label><?php echo $hostname; ?></label><span class="input_tips_txt" id="tipsDomain" ></span>
	</td>
  </tr>
  
  <tr>
    <td height="1" colspan="2" bgcolor="#A7C5E2"></td>
  </tr>
   
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
	<td width="140" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" >域名SSL选项：</td>
	<td width="310">
	  <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Http" value="0" checked="checked" onchange="ChangeSSLOpt(0)" />HTTP &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Https" value="1" onchange="ChangeSSLOpt(1)" />HTTPS &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_HttpAndHttps" value="2" onchange="ChangeSSLOpt(2)" />HTTP+HTTPS
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  
  <tr id="SSLAllParams" style="display:none">
    <td colspan="2">
		<table width="450" border="0" align="0" cellpadding="0" cellspacing="0">
		  <tr>
			<td width="140" height="72" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="top" >*SSL证书文件内容：<br /><span style="font-style:italic;font-size:10px">SSL Certificate File&nbsp;<br />Content&nbsp;</span></td>
			<td width="310" height="72" valign="top">
			<textarea name="SSLCrtContent" class="inputText" id="SSLCrtContent" style="margin-top:5px;width:300px;height:60px;font-size:11px;border:1px solid #94C7E7;color:#00BFAA;;overflow:auto;"></textarea>
			</td>
		  </tr>
		  <tr>
			<td width="140" height="72" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="top" >*SSL私钥文件内容：<br /><span style="font-style:italic;font-size:10px">SSL Private Key File&nbsp;<br />Content&nbsp;</span></td>
			<td width="310" height="72" valign="top">
			<textarea name="SSLKeyContent" class="inputText" id="SSLKeyContent" style="margin-top:5px;width:300px;height:60px;font-size:11px;border:1px solid #94C7E7;color:#00BFAA;overflow:auto;"></textarea>
			</td>
		  </tr>
		  <tr>
			<td width="140" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" valign="middle" >SSL附加配置参数：</td>
			<td width="310" height="25" valign="middle">
			<input name="SSLExtraParams" type="text" class="inputText" id="SSLExtraParams" style="width:300px;height:16px" value="" title="相关示例，可参看【相关使用帮助】" />
			</td>
		  </tr>
		</table>
	</td>
  </tr>
  
  <tr>
	<td width="140" height="25" class="objTitle" title="相关示例，可参看【相关使用帮助】" >源站SSL选项：</td>
	<td width="310">
	  <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Http" value="0" checked="checked" onchange="ChangeSSLOpt(0)" />HTTP &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_Https" value="1" onchange="ChangeSSLOpt(1)" />HTTPS &nbsp;&nbsp;
      <input name="SSLOpt" type="radio" class="radio" id="SSLOpt_HttpAndHttps" value="2" onchange="ChangeSSLOpt(2)" />HTTP+HTTPS
	</td>
  </tr>
    
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddDomain"  type="submit" style="width:95px;height:28px" id="btnAddDomain" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyDomain(<?php echo $domain_id; ?>);" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
