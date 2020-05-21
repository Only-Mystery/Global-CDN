<?php
include_once("head2.php");
?>
<script type="text/javascript">
function FikCdn_ModifyTrains(trains_id){
	var txtUpstream = document.getElementById("txtSrcIP").value;
	var txtBackup = document.getElementById("txtBackup").value;
	
	if(txtUpstream.length<=0){
		var boxURL="msg.php?1.9&msg=请输入中转服务器的 IP 地址。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtSrcIP").focus();
	  	return false;
	}
	
	var postURL="./ajax_domain.php?mod=upstream&action=modify";
	var postStr="id="+trains_id+"&upstream="+UrlEncode(txtUpstream)+"&backup="+UrlEncode(txtBackup);
		
	AjaxBasePost("upstream","modify","POST",postURL,postStr);
}

function FikCdn_ModifyUpstreamResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var upstream_id = json["id"];
		var upstream = json["upstream"];
		var node = json["node"];
		
		var ObjName = "upstream_"+upstream_id;
		parent.document.getElementById(ObjName).innerHTML=upstream;
		
		ObjName = "note_"+upstream_id;
		parent.document.getElementById(ObjName).innerHTML=node;
				
		var boxURL="msg.php?1.9&msg=修改中转服务器信息成功。";
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
function FikCdn_ToTaskList(){
	parent.parent.window.leftFrame.window.OnSelectNav("span_task_list");
	parent.window.location.href="task_list.php";
}
</script>

<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="100" height="25" class="objTitle" ><span class="input_red_tips">*</span>服务器：</td>
    <td width="220">
		<select id="nodeSelect" style="width:250px" disabled="disabled">
 <?php
	$trains_id		= isset($_GET['id'])?$_GET['id']:'';
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$trains_id 	= mysql_real_escape_string($trains_id); 
		$sql = "SELECT * FROM fikcdn_upstream WHERE id='$trains_id'";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$node_id   	= mysql_result($result,0,"node_id");
			$group_id	= mysql_result($result,0,"group_id");
			$hostname	= mysql_result($result,0,"hostname");		
			$upstream   = mysql_result($result,0,"upstream");
			$upstream2  = mysql_result($result,0,"upstream2");		
			$note   	= mysql_result($result,0,"note");
			
			$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$node_group_id	= mysql_result($result,0,"groupid");
				$node_name	   	= mysql_result($result,0,"name");
				$node_ip	   	= mysql_result($result,0,"ip");
				$node_unicom_ip	= mysql_result($result,0,"unicom_ip");
			}
		}
		
		$sql = "SELECT * FROM fikcdn_node Limit 100;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{		
			$row_count=mysql_num_rows($result);			
			for($i=0;$i<$row_count;$i++)
			{
				$id  		= mysql_result($result,$i,"id");	
				$name   	= mysql_result($result,$i,"name");
				$ip   		= mysql_result($result,$i,"ip");
				$unicom_ip	= mysql_result($result,$i,"unicom_ip");	
				
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0) $sFikIP = $unicom_ip;		
				
				if($node_id == $id)
				{
					echo '<option value="'.$id.'" selected="selected">'.$name.' ('.$sFikIP.")</option>";
					
					$node_group_id	= mysql_result($result,0,"groupid");
					$node_name	   	= $name;
					$node_ip	   	= $ip;
					$node_unicom_ip	= $unicom_ip;
				}
				else
				{	
					echo '<option value="'.$id.'">'.$name.' ('.$sFikIP.")</option>";
				}
			}
		}
		
		mysql_close($db_link);
	}			
 ?>
				</select>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="必须是添加到域名列表中的域名" ><span class="input_red_tips">*</span>网站域名：</td>
    <td>
		<input id="txtDomain" type="text" size="36" maxlength="64" value="<?php  echo $hostname;?>" readonly="readonly" title="必须是添加到域名列表中的域名" />
	</td>
  </tr>  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="如果有多个中转服务器 IP，可用“;”间隔，如：211.155.23.66;234.34.55.123" ><span class="input_red_tips">*</span>中转服务器 IP：</td>
    <td>
		<input id="txtSrcIP" type="text" size="36" maxlength="64"  value="<?php  echo $upstream;?>" title="如果有多个中转服务器 IP，可用“;”间隔，如：211.155.23.66;234.34.55.123" />  
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" name="txtBackup" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php  echo $note;?></textarea>
	</td>
  </tr>
  <tr>
    <td height="10" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddTrains"  type="submit" style="width:95px;height:28px;cursor:pointer;" id="btnAddTrains" value="保存" onclick="FikCdn_ModifyTrains('<?php echo $trains_id; ?>');" /></center></td>
  </tr>
</table>
 <table width="480" border="0">
	<tr>
	<td><p style="padding-left:20px;">说明：<br />
	1. 如果有某个节点服务器不能访问到源站或者访问源站速度比较慢，可以<br />&nbsp;&nbsp;&nbsp;&nbsp;在此配置通过中转服务器访问源站；<br />
	2. 如果有多个中转服务器 IP 则可用“;”间隔填入多个IP，如：<br />&nbsp;&nbsp;&nbsp;&nbsp;211.155.23.66;234.34.55.123<br />
	3. 中转服务器修改成功后，系统会通过“<a href="javascript:void(0);" onclick="javescript:FikCdn_ToTaskList();" title="查看后台任务列表">后台任务</a>"方式修改节点服务器的<br />&nbsp;&nbsp;&nbsp;&nbsp;域名对应的源站为中转服务器的 IP；<br />
	</p></td>
	</tr>
</table>

<?php

include_once("./tail.php");
?>
