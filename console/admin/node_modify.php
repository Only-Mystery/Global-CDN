<?php
include_once("./head2.php");
include_once('../function/define.php');
?>
<script type="text/javascript">
function FikCdn_ModifyNodeResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
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

function FikCdn_ReLoadNodeList(){	
	parent.window.location.href="node_list.php?page=10000";
}

function FikCdn_ModifyNode(nNodeId){
	var grpSelect    =document.getElementById("grpSelect").value;
	var txtId		 =document.getElementById("txtId").value;
	var txtIP        =document.getElementById("txtIP").value;
	var txtUnicomIP  =document.getElementById("txtUnicomIP").value;
	var txtNodeName  =document.getElementById("txtNodeName").value;
	var txtAllowBW   =document.getElementById("txtAllowBW").value;
	var txtAdminPort =document.getElementById("txtAdminPort").value;
	var txtPasswd    =document.getElementById("txtPasswd").value;
	var txtBackup    =document.getElementById("txtBackup").value;
		
	if (grpSelect.length==0 ){ 
		var boxURL="msg.php?1.3";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		return false;
	}
	
	if (txtNodeName.length==0 ){
		var boxURL="msg.php?1.4";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtNodeName").focus();
	  	return false;
	}
		
	if (txtIP.length==0 && txtUnicomIP==0){
		var boxURL="msg.php?1.5";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtIP").focus();		
	  	return false;
	}
		
	if (txtAdminPort.length==0 ){ 		
		var boxURL="msg.php?1.6";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');		
		document.getElementById("txtAdminPort").focus();
	  	return false;
	}
		
	if (txtPasswd.length==0 ){ 
		var boxURL="msg.php?1.7";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("txtPasswd").focus();
	  	return false;
	}
						 
	var postURL="./ajax_admin.php?mod=fiknode&action=modify";
	var postStr="id="+txtId+"&ip=" + UrlEncode(txtIP) + "&grpid=" + grpSelect + "&unicom_ip="+UrlEncode(txtUnicomIP) + "&name=" + UrlEncode(txtNodeName) + "&allowbw=" + UrlEncode(txtAllowBW) + "&adminport=" + txtAdminPort + "&passwd=" + UrlEncode(txtPasswd) + "&backup=" + UrlEncode(txtBackup);					 				 
	AjaxBasePost("fiknode","modify","POST",postURL,postStr);	
}

var onfocus_count = 0;
function check_onfocus(editID){
	if(onfocus_count == 0)
	{
		var obj = document.getElementById(editID);
		obj.value = "";
		onfocus_count ++;
	}
	return;
}
</script>
<style>
html, body {
	overflow-x: hidden;
	overflow-y: hidden;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
</style>
<table width="450" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="100" height="25" class="objTitle" title="" >服务器组：</td>
    <td width="320">
<select id="grpSelect" name="grpSelect" style="width:200px" disabled="disabled">
<?php
	$nNodeId 		= isset($_GET['id'])?$_GET['id']:'';
	$nGrpId 		= isset($_GET['gid'])?$_GET['gid']:'';
	
 	$db_link = FikCDNDB_Connect();
	if($db_link){
		$nNodeId 	= mysql_real_escape_string($nNodeId); 
		$nGrpId 	= mysql_real_escape_string($nGrpId); 
		
		$sql = "SELECT * FROM fikcdn_node WHERE id=$nNodeId";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0){
			$sNodeName   	= mysql_result($result,0,"name");	
			$ip  		 	= mysql_result($result,0,"ip");
			$unicom_ip	 	= mysql_result($result,0,"unicom_ip");		
			$port   		= mysql_result($result,0,"port");	
			$admin_port   	= mysql_result($result,0,"admin_port");	
			$add_time   	= mysql_result($result,0,"add_time");
			$fik_version   	= mysql_result($result,0,"fik_version");	
			$auth_domain   	= mysql_result($result,0,"auth_domain");
			$backup			= mysql_result($result,0,"note");
			$group_id   	= mysql_result($result,0,"groupid");
			$is_transit   	= mysql_result($result,0,"is_transit");
			$allow_bandwidth= mysql_result($result,0,"allow_bandwidth");
		}		
				
		$sql = "SELECT * FROM fikcdn_group;"; 
		$result = mysql_query($sql,$db_link);
		if($result){
			$row_count=mysql_num_rows($result);
			if($row_count>0){
				for($i=0;$i<$row_count;$i++){
					$this_gid  			= mysql_result($result,$i,"id");	
					$this_grp_name   	= mysql_result($result,$i,"name");	
					
					if($nGrpId == $this_gid){
						echo '<option value="'.$this_gid.' " selected="selected">'.$this_grp_name."</option>";
					}else{
						echo '<option value="'.$this_gid.'">'.$this_grp_name."</option>";
					}
				}
			}
		}
		mysql_close($db_link);
	}			
?>
				</select>
				<input id="txtId" name="txtId" type="hidden"  value="<?php echo $nNodeId; ?>" /> 
		</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>服务器名称：</td>
    <td>
		<input id="txtNodeName" type="text" size="30" maxlength="128"  value="<?php echo $sNodeName; ?>" title="如：广州双线G口、长沙电信、上海联通" onfocus="check_onfocus('txtName');" />&nbsp;<span class="input_tips_txt" id="tipsName">如：广州双线G口</span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>电信 IP：</td>
    <td>
		<input id="txtIP" type="text" size="30" maxlength="64" value="<?php echo $ip; ?>" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。"  /> <span class="input_tips_txt" id="tipsIP"></span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >联通 IP：</td>
    <td><input id="txtUnicomIP" type="text" size="30" maxlength="64" value="<?php echo $unicom_ip; ?>" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。" /> <span class="input_tips_txt4" id="tipsUnicomIP"></span></td>
  </tr>
   <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="Fikker 节点服务器后台管理端口" ><span class="input_red_tips"></span>服务器带宽：</td>
    <td>
		<input id="txtAllowBW" type="text" size="16" maxlength="10" value="<?php echo $allow_bandwidth; ?>" title="机房分配给此服务器的带宽，作为服务器运行参考" />  <span class="input_tips_txt" id="tipsAllowBW" name="tipsAllowBW" > Mbps</span> 
	</td>
  </tr>
  <tr>  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>管理端口：</td>
    <td>
		<input id="txtAdminPort" type="text" size="16" maxlength="32" value="<?php echo $admin_port; ?>" title="Fikker 服务器后台管理端口" />  <span class="input_tips_txt" id="tipsAdminPort" name="tipsNameAdminPort" ></span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>管理员密码：</td>
    <td>
		<input id="txtPasswd" type="password" size="30" maxlength="64" title="Fikker 服务器后台管理员密码"/>  <span class="input_tips_txt" id="tipsPasswd" name="tipsPasswd" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;"><?php echo $backup; ?></textarea>
	</td>
  </tr>
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2"  style="text-align:center;" ><input name="btnAddNode"  type="submit" style="width:95px;height:28px" id="btnAddNode" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyNode(<?php  echo $nNodeId; ?>);" /> </td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
