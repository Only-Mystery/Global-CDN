<?php
include_once("./head2.php");
?>
<script type="text/javascript">
function FikCdn_ModifyDomain(domain_id){
	var txtOffset    =document.getElementById("txtOffset").value;
	var txtUpOffset  =document.getElementById("txtUpOffset").value;
	var txtDownBegin =document.getElementById("txtDownBegin").value;
	var txtUpBegin	 =document.getElementById("txtUpBegin").value;
	
	var postURL="./ajax_domain.php?mod=domain&action=modifyset";
	var postStr="domain_id="+ domain_id+ "&offset="+UrlEncode(txtOffset) + "&upoffset=" + UrlEncode(txtUpOffset) + "&down_begin="+UrlEncode(txtDownBegin)+ "&up_begin=" + UrlEncode(txtUpBegin);
	AjaxBasePost("domain","modifyset","POST",postURL,postStr);	
}

function FikCdn_ModifySetDomainResult(sResponse){
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
		$offset			= mysql_result($result,$i,"offset");	
		$upoffset		= mysql_result($result,$i,"upoffset");	
		$down_begin_val = mysql_result($result,$i,"down_begin_val");	
		$up_begin_val	= mysql_result($result,$i,"up_begin_val");				
		$use_transit_node 	= mysql_result($result,0,"use_transit_node");		
		$icp 		= mysql_result($result,0,"icp");
		$DNSName 	= mysql_result($result,0,"DNSName");		
		$note	 	= mysql_result($result,0,"note");
		$upstream_add_all	 	= mysql_result($result,0,"upstream_add_all");
	}			
 ?>    
 
<table width="350" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="150" height="25" class="objTitle" title="" >域名：</td>
    <td width="140">
		<label><?php echo $hostname; ?></label><span class="input_tips_txt" id="tipsDomain" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips"></span>下载偏移量系数：</td>
    <td>
		<input id="txtOffset" type="text" size="10" maxlength="64"   value='<?php echo $offset; ?>' title="" /> <span class="input_tips_txt" id="tipsSrcIP" name="tipsSrcIP" ></span>  
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >上传偏移系数：</td>
    <td>
		<input id="txtUpOffset" type="text" size="10" maxlength="64"  value='<?php echo $upoffset; ?>'  title="" /><span class="input_tips_txt4" id="tipsUnicomIP" ></span>
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >下载带宽开始值(Mbps)：</td>
    <td>
		<input id="txtDownBegin" type="text" size="10" maxlength="64" value="<?php echo $down_begin_val; ?>" title="" /> <span class="input_tips_txt" id="tipsICP" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>

  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >上传带宽开始值(Mbps)：</td>
    <td>
		<input id="txtUpBegin" type="text" size="10" maxlength="64" value="<?php echo $up_begin_val; ?>" title="" /> <span class="input_tips_txt" id="tipsICP" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
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
