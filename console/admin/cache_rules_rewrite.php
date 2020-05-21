<?php
include_once("./head.php");
include_once('../function/fik_api.php');
$node_id 		= isset($_GET['node_id'])?$_GET['node_id']:'';
?>
<script type="text/javascript">
function FikCdn_AddRewriteBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rewrite_add.php?node_id="+txtSelectNode;
	showMSGBOX('',550,280,BT,BL,120,boxURL,'添加转向规则:');
}

function FikCdn_ModifyRewriteBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rewrite_modify.php?node_id="+txtSelectNode+"&rid="+Rid;
	showMSGBOX('',550,280,BT,BL,120,boxURL,'修改转向规则:');
}

var ___nNodeId;
var ___nRid;

function FikCdn_DelRewriteBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?7.3";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelRewrite(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rewrite&action=del";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	AjaxBasePost("rewrite","del","POST",postURL,postStr);	
}

function FikCdn_DelRewriteResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?7.4";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
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

function FikCdn_UpRewriteBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?7.5";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_UpRewrite(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rewrite&action=up";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	
	AjaxBasePost("rewrite","up","POST",postURL,postStr);	
}

function FikCdn_UpRewriteResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?7.7";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
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

function FikCdn_DownRewriteBox(Rid){
	var txtSelectNode = document.getElementById("SelectNode").value;
	___nRid = Rid;
	___nNodeId = txtSelectNode;	
	var boxURL="msg.php?7.6";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DownRewrite(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var postURL="./ajax_cache_rules.php?mod=rewrite&action=down";
	var postStr="node_id="+___nNodeId+"&rid="+___nRid;
	
	AjaxBasePost("rewrite","down","POST",postURL,postStr);	
}

function FikCdn_DownRewriteResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?7.8";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
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

function FikCdn_RefreshRewrite(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_rewrite.php?node_id="+txtSelectNode;
	return;
	/*
	var txtSelectNode = document.getElementById("SelectNode").value;
	
	var postURL="./ajax_cache_rules.php?mod=rewrite&action=list";
	var postStr="node_id="+txtSelectNode;
	
	AjaxBasePost("rewrite","list","POST",postURL,postStr);
	*/
}


function FikCdn_RefreshRewriteResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nNodeID = json["node_id"];
		location.reload();
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

function FikCdn_SyncRewriteBox(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	var boxURL="cache_rules_rewrite_sync.php?node_id="+txtSelectNode;
	showMSGBOX('',360,165,BT,BL,120,boxURL,'同步转向规则:');
}

function OnSelectNode(){
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="cache_rules_rewrite.php?node_id="+txtSelectNode;
}

function FikCdn_ToTaskList(){
	parent.window.leftFrame.window.OnSelectNav("span_task_list");
	window.location.href="task_list.php";
}

</script>

<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="cache_rules_fcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt_active">页面缓存</span></a></td>			
				<td height="31" width="85"><a href="cache_rules_rcache.php?node_id=<?php echo $node_id; ?>"><span class="title_bt_active">拒绝缓存</span></a></td>						
				<td height="31" width="85"><a href="cache_rules_rewrite.php?node_id=<?php echo $node_id; ?>"><span class="title_bt">转向规则</span></a></td>				
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
			<tr height="30">
			<td><div class="div_search_title">
					<select id="SelectNode" style="width:280px" onChange="OnSelectNode()">
<?php 	
  	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$node_id 	= mysql_real_escape_string($node_id);
		
		$sql = "SELECT * FROM fikcdn_node;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				$admin_port		= mysql_result($result,$i,"admin_port");
				$password	 	= mysql_result($result,$i,"password");
				$SessionID	 	= mysql_result($result,$i,"SessionID");				
				
				if(strlen($node_id)<=0) $node_id = $this_node_id;
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';
	
				if($this_node_id==$node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
					
					$show_this_name = $sFikIP.' - '.$node_name;						
					
					$this_sFikIP = $sFikIP;
					$this_admin_port = $admin_port;
					$this_password = $password;
					$this_SessionID = $SessionID;		
					$this_group_id = $group_id;		
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
			
			if(strlen($node_id)>0)
			{
				//获取页面缓存规则	
				$aryFikResult = FikApi_RewriteList($this_sFikIP,$this_admin_port,$this_SessionID);
				if($aryFikResult["Return"]=="False")
				{
					if($aryFikResult["ErrorNo"] == $FikCacheError_SessionHasOverdue)
					{
						$aryRelogin = FikApi_Relogin($this_node_id,$this_sFikIP,$this_admin_port,$this_password,$db_link);
						if($aryRelogin["Return"]=="True")
						{
							$this_SessionID = $aryRelogin["SessionID"];
							$aryFikResult= FikApi_RewriteList($this_sFikIP,$this_admin_port,$this_SessionID);
						}
					}
				}
				
				if($aryFikResult["Return"]=="True")
				{
					//先删除原来的
					$sql ="DELETE FROM cache_rule_rewrite WHERE node_id='$node_id'";
					$result3 = mysql_query($sql,$db_link);
								
					$nNumOfLists = $aryFikResult["NumOfLists"];
					for($k=0;$k<$nNumOfLists;$k++)
					{
						$NO = $aryFikResult["Lists"][$k]["NO"];
						$RewriteID = $aryFikResult["Lists"][$k]["RewriteID"];
						$SourceUrl = $aryFikResult["Lists"][$k]["SourceUrl"];
						$DestinationUrl = $aryFikResult["Lists"][$k]["DestinationUrl"];
						$Icase = $aryFikResult["Lists"][$k]["Icase"];
						$Flag = $aryFikResult["Lists"][$k]["Flag"];
						$Note = $aryFikResult["Lists"][$k]["Note"];				
						
						$sSourceUrl = urlencode($SourceUrl);
						$sDestinationUrl = urlencode($DestinationUrl);	
						$sNote = urlencode($Note);
						
						$sql = "INSERT INTO cache_rule_rewrite(id,node_id,group_id,NO,RewriteID,SourceUrl,DestinationUrl,Icase,Flag,Note) 
									VALUES(NULL,'$node_id','$this_group_id','$NO','$RewriteID','$sSourceUrl','$sDestinationUrl','$Icase','$Flag','$sNote')";
						$result3 = mysql_query($sql,$db_link);
					}
				}
			}	
						
		}
	}
?>	
	</select>&nbsp;

			</div></td>
			</tr>
		</table>
		<div id="div_search_result">
		  <table width="1100" border="0" class="dataintable" id="domain_table">
			<tr id="tr_domain_title">
				<th align="center" width="45">序号</th> 
				<th align="center" width="430">访问地址URL</th>
				<th align="center" width="380">转向地址URL</th>
				<th align="center" width="50">大小写</th>				
				<th align="center" width="60">转向逻辑</th>						
				<th align="center">操作</th>
			</tr>			
<?php
	if($db_link)
	{
		$sql = "SELECT * FROM cache_rule_rewrite WHERE node_id='$node_id' ORDER BY NO ASC;";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$row_count = mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");	
				$NO		  		= mysql_result($result,$i,"NO");	
				$RewriteID	 	= mysql_result($result,$i,"RewriteID");	
				$SourceUrl  	= mysql_result($result,$i,"SourceUrl");	
				$DestinationUrl = mysql_result($result,$i,"DestinationUrl");	
				$Icase			= mysql_result($result,$i,"Icase");
				$Flag			= mysql_result($result,$i,"Flag");
				$Note			= mysql_result($result,$i,"Note");	
				
				$SourceUrl = urldecode($SourceUrl);
				$DestinationUrl = urldecode($DestinationUrl);				
				$Note = urldecode($Note);
								
				$sExpire = 	$Expire.' '.$FikFCache_ExpireUnit[$Unit];
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$Note.'" id="tr_fcache_'.$id.'">';
				echo '<td>'.$NO.'</td>';
				echo '<td align="left">'.$SourceUrl.'</td>';
				echo '<td>'.$DestinationUrl.'</td>';
				echo '<td>'.$FikFCache_Icase[$Icase].'</td>';
				echo '<td>'.$FikRewrite_Flag[$Flag].'</td>';
				echo '<td>  <a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyRewriteBox('.$id.');" title="查看此域名流量统计信息">修改</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_DelRewriteBox('.$id.');" title="修改域名信息">删除</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_UpRewriteBox('.$id.');" title="修改域名信息">上移</a>&nbsp;';
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DownRewriteBox('.$id.');" title="删除节点信息">下移</a> </td>';
				echo '</tr>';
			}
		}
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		<table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6" height="25">
			</td>
			</tr>
		</table>	
		
 		<table width="800" border="0" class="bottom_btn">
			<tr>
			<td height="28">
				<input name="btnAddRewriteBox"  type="submit" style="width:100px;height:28px" id="btnAddRewriteBox" value="添加转向规则" style="cursor:pointer;" onClick="FikCdn_AddRewriteBox();" /> 
				<input name="btnSyncRewriteBox"  type="submit" style="width:100px;height:28px" id="btnSyncRewriteBox" value="同步转向规则" style="cursor:pointer;" onClick="FikCdn_SyncRewriteBox();" /> 				
				<input name="btnRefreshRewrite"  type="submit" style="width:80px;height:28px" id="btnRefreshRewrite" value="刷新" style="cursor:pointer;" onClick="FikCdn_RefreshRewrite();" /> 
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
