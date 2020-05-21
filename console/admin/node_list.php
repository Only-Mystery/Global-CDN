<?php
include_once("./head.php");
include_once('../function/define.php');
?>
<script type="text/javascript">
var ___nNodeId;
var ___nGrpId;

function FikCdn_NodeDeleteBox(nodeid,grpid){
	___nNodeId = nodeid;
	___nGrpId = grpid;
	var boxURL="msg.php?1.9";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_DelNode(){
	var postURL="./ajax_admin.php?mod=fiknode&action=del";
	var postStr="id="+___nNodeId + "&grpid="+___nGrpId;
	AjaxBasePost("fiknode","del","POST",postURL,postStr);	
}

function FikCdn_DelNodeResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		location.reload();
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

function FikCdn_ModifyNodeBox(nodeid,grpid){
	var boxURL="node_modify.php?id="+nodeid+"&gid="+grpid;
	showMSGBOX('',520,335,BT,BL,120,boxURL,'修改服务器:');
	return true;
}

function FikCdn_HostDeleteBox(nodeid,grpid){
	___nNodeId = nodeid;
	___nGrpId = grpid;
	var boxURL="msg.php?1.13";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	return;
}

function FikCdn_HostDelete(){
	var postURL="./ajax_admin.php?mod=fiknode&action=cleanhost";
	var postStr="id="+___nNodeId + "&grpid="+___nGrpId;
		
	AjaxBasePost("fiknode","cleanhost","POST",postURL,postStr);	
}

function FikCdn_CleanHostResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=清空服务器主机管理中的所有域名成功。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	}	
	else{
		var nErrorNo = json["ErrorNo"];
		var strErr = json["ErrorMsg"];	
	
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}
		else
		{
			var boxURL="msg.php?1.9&msg="+strErr;
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}		
}

function FikCdn_ReConfigHostBox(nodeid,grpid){
	___nNodeId = nodeid;
	___nGrpId = grpid;
	var boxURL="msg.php?1.10";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_ReConfigHost(){
	var postURL="./ajax_admin.php?mod=fiknode&action=reconfighost";
	var postStr="id="+___nNodeId + "&grpid="+___nGrpId;
		
	AjaxBasePost("fiknode","reconfighost","POST",postURL,postStr);	
}


function fikcdn_ReConfigHostResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.10&msg=域名同步任务已经提交到后台任务列表中，您可以在后台任务列表中查看域名同步进度。";
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

function FikCdn_StartNodeBox(nodeid,grpid){
	___nNodeId = nodeid;
	___nGrpId = grpid;
	var boxURL="msg.php?1.11";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_StartNode(){
	var postURL="./ajax_admin.php?mod=fiknode&action=modifystatus";
	var postStr="id="+___nNodeId + "&grpid="+___nGrpId+ "&is_close=0";
		
	AjaxBasePost("fiknode","modifystatus","POST",postURL,postStr);		
}

function FikCdn_StopNodeBox(nodeid,grpid){
	___nNodeId = nodeid;
	___nGrpId = grpid;
	var boxURL="msg.php?1.12";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	return;	
}

function FikCdn_StopNode(){
	var postURL="./ajax_admin.php?mod=fiknode&action=modifystatus";
	var postStr="id="+___nNodeId + "&grpid="+___nGrpId+ "&is_close=1";
		
	AjaxBasePost("fiknode","modifystatus","POST",postURL,postStr);
}

function FikCdn_ModifyStatusNodeResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){		
		var nNodeID = json["id"];
		var nGrpID = json["grpid"];
		var is_close = json["is_close"];
		var ObjName1 = "span_is_close_"+nNodeID;
		var ObjName2 = "span_is_close_href_"+nNodeID;
		if(is_close==1){
			document.getElementById(ObjName1).innerHTML ='<a href="#" onclick="javescript:FikCdn_StartNodeBox('+nNodeID+','+nGrpID+');" title="启用节点">已停用</a>';
			
		}else{
			document.getElementById(ObjName1).innerHTML ='<a href="#" onclick="javescript:FikCdn_StopNodeBox('+nNodeID+','+nGrpID+');" title="停止节点">启用中</a>';
			

		}	
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

function selectPage(obj){
	var txtGid		 =document.getElementById("selGroup").value;
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;	
	window.location.href="node_list.php?page="+pagesSelect+"&action=jump"+"&gid="+txtGid+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect;
}

function FikCdn_SearchNode(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtGid		 =document.getElementById("selGroup").value;
	
	if(txtKeyword.length==0 ){
		//return;
	}	
	
	window.location.href="node_list.php?page=1"+"&action=jump"+"&gid="+txtGid+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect;		
}

function FikCdn_ExportExcel(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtGid		 =document.getElementById("selGroup").value;
	
	var  getURL="./ajax_excel.php?mod=excel&action=node"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&gid="+UrlEncode(txtGid);
	window.location.href=getURL;	
}

function FikCdn_SelectGroup(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtGid		 =document.getElementById("selGroup").value;
	
	window.location.href="node_list.php?page=1"+"&action=jump"+"&gid="+txtGid+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect;
}

function FikCdn_AddNodeBox(){								//添加服务器
	var boxURL="node_add.php";
	showMSGBOX('',520,355,BT,BL,120,boxURL,'添加服务器:');
}

function FikCdn_ViewNodeStat(nodeid,grpid){
	parent.window.leftFrame.window.OnSelectNav("span_stat_node_bandwidth");
}

</script>
<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">服务器列表</span></td>
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">
	  		<table width="1100" border="0" class="dataintable">
			<tr height="30">
			<td><div class="div_search_title">服务器组：	
				<select id="selGroup" name="selGroup" style="width:180px" onChange="FikCdn_SelectGroup()">
<?php
	// 组ID
	$nGid 		= isset($_GET['gid'])?$_GET['gid']:'all';
	$sType 		= isset($_GET['type'])?$_GET['type']:'';
	$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
		
	if($nGid=="all")
	{
		echo '<option value="all" selected="selected">所有服务器</option>';		
	}
	else
	{
		echo '<option value="all">所有服务器</option>';		
	}
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$nGid 	= mysql_real_escape_string($nGid); 
		$sKeyword 	= mysql_real_escape_string($sKeyword); 
		
		$sql = "SELECT * FROM fikcdn_group;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			if($row_count>0)
			{
				for($i=0;$i<$row_count;$i++)
				{
					$gid  	 = mysql_result($result,$i,"id");	
					$grpname = mysql_result($result,$i,"name");	
						
					if($nGid==$gid)
					{
						echo '<option value="'.$gid.'" selected="selected">'.$grpname."</option>";
					}
					else
					{
						echo '<option value="'.$gid.'">'.$grpname."</option>";				
					}	
				}
			}
		}
	}			
 ?>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				<input id="txtGid" name="txtGid" type="hidden"  value="<?php echo $nGid; ?>" /> 
				<select id="searchSelect" name="searchSelect" style="width:150px">
					<option value="ip" <?php if($sType=="ip") echo 'selected="selected"'; ?>>电信IP地址</option>
					<option value="unicom_ip" <?php if($sType=="unicom_ip") echo 'selected="selected"'; ?>>联通 IP 地址</option>
					<option value="hardware" <?php if($sType=="hardware") echo 'selected="selected"'; ?>>32位硬件ID</option>					
					<option value="nodename" <?php if($sType=="nodename") echo 'selected="selected"'; ?>>服务器名称</option>
				</select>
				<input id="txtKeyword" name="txtKeyword" type="text" size="36" maxlength="256" value="<?php echo $sKeyword; ?>" />
				<input name="btnSearchNode"  type="submit" style="width:80px;height:28px" id="btnSearchNode" value="查询" style="cursor:pointer;" onClick="FikCdn_SearchNode();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="50">ID</th> 
				<th align="left" width="130">电信 IP </th>
				<th align="left" width="130">联通 IP</th>
				<th align="center" width="130">服务器组</th> 				
				<th align="right" width="180">服务器名称</th>
				<th align="right" width="140" align="right">Fikker 版本</th>
				<th align="center" width="50" align="center">是否启用</th>
				<th align="center">操作</th>
			</tr>			
<?php
	
	$nPage 		= isset($_GET['page'])?$_GET['page']:'';
	$action 	= isset($_GET['action'])?$_GET['action']:'';
	
	if(!is_numeric($nPage))
	{
		$nPage=1;
	}
	
	if($nPage<=0)
	{
		$nPage = 1;
	}		
	
	if($action!="frist" && $action !="pagedown" && $action !="pageup" && $action !="tail" && $action !="jump")
	{
		$action="frist";
	}
		
	if($db_link)
	{
		do
		{		
			$total_host 	= 0;
			
			if($nGid=="all" || strlen($nGid)<=0)
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_node;"; 
				}
				else
				{
					if($sType=="ip")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE ip like '%$sKeyword%';"; 
					}
					else if($sType=="unicom_ip")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE unicom_ip like '%$sKeyword%'";
					}						
					else if($sType=="hardware")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE auth_domain like '%$sKeyword%'";
					}		
					else if($sType=="nodename")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE name like '%$sKeyword%'"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid;"; 
				}
				else
				{
					if($sType=="ip")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid AND ip like '%$sKeyword%';"; 
					}
					else if($sType=="unicom_ip")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid AND unicom_ip like '%$sKeyword%'";
					}						
					else if($sType=="hardware")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid AND auth_domain like '%$sKeyword%'";
					}		
					else if($sType=="nodename")
					{
						$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid AND name like '%$sKeyword%'"; 
					}
				}
			}
			$result = mysql_query($sql,$db_link);
			if($result&&mysql_num_rows($result)>0)
			{
				$total_host  = mysql_result($result,0,"count(*)");	
			}
			
			$total_pages = floor($total_host/$PubDefine_PageShowNum);
			if(($total_host%$PubDefine_PageShowNum)>0)
			{
				$total_pages+=1;
			}
			
			if($nPage>$total_pages)
			{
				$nPage = $total_pages;
			}
			
			$pagedown = $nPage+1;
			if($pagedown>$total_pages)
			{	
				$pagedown = $total_pages;			
			}
			
			$pageup = $nPage-1;
			if($pageup<=0)
			{
				$pageup = 1;
			}			
			$offset = (($nPage-1)*$PubDefine_PageShowNum);
			if($offset<0) $offset=0;
			
			if($nGid=="all" || strlen($nGid)<=0)
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_node Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else
				{
					if($sType=="ip")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE ip like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}
					else if($sType=="unicom_ip")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE unicom_ip like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}						
					else if($sType=="hardware")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE auth_domain like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}		
					else if($sType=="nodename")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE name like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid Limit $offset,$PubDefine_PageShowNum;";
				}
				else
				{
					if($sType=="ip")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid AND ip like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}
					else if($sType=="unicom_ip")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid AND unicom_ip like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}						
					else if($sType=="hardware")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid AND auth_domain like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}		
					else if($sType=="nodename")
					{
						$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid AND name like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
					}
				}
			}
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				break;
			}
			
			$row_count=mysql_num_rows($result);
			if(!$row_count)
			{
				break;
			}
			
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");
				$name   		= mysql_result($result,$i,"name");
				$ip  		 	= mysql_result($result,$i,"ip");
				$unicom_ip	 	= mysql_result($result,$i,"unicom_ip");
				$port   		= mysql_result($result,$i,"port");
				$admin_port   	= mysql_result($result,$i,"admin_port");
				$add_time   	= mysql_result($result,$i,"add_time");
				$fik_version   	= mysql_result($result,$i,"fik_version");	
				$auth_domain   	= mysql_result($result,$i,"auth_domain");
				$groupid	   	= mysql_result($result,$i,"groupid");
				$status	   		= mysql_result($result,$i,"status");
				$note	   		= mysql_result($result,$i,"note");
				$version_ext	= mysql_result($result,$i,"version_ext");
				$is_close		= mysql_result($result,$i,"is_close");
				$allow_bandwidth= mysql_result($result,$i,"allow_bandwidth");			
				
				$group_name = "";
				
				$sql = "SELECT * FROM fikcdn_group WHERE id=$groupid";
				$result11 = mysql_query($sql,$db_link);
				if($result11 && mysql_num_rows($result11)>0)
				{
					$group_name = mysql_result($result11,0,"name");
				}
								
				
				$admin_url = $ip.":"."$admin_port"."/fikker/";	
				
				$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$note.'">';
				echo '<td>'.$id.'</td>';
				echo '<td title="'.$note.'" align="left"><a href="login_fikker.php?mod=logging&action=redirect&id='.$id.'" title="'.$note.'" target="_blank" >'.$ip.'</a></td>';
				echo '<td title="'.$note.'" align="left"><a href="login_fikker.php?mod=logging&action=redirect&id='.$id.'" title="'.$note.'" target="_blank" >'.$unicom_ip.'</a></td>';								
				echo '<td>'.$group_name.'</td>';
				echo '<td align="right" title="'.$note.'">'.$name.'('.$allow_bandwidth.' Mbps)</td>';
				//echo '<td>'.$auth_domain.'</td>';
				//echo '<td>'.date("Y-m-d H:i:s",$add_time).'</td>';
				echo '<td align="right">'.$version_ext.'/'.$show_version.'</td>';
				
				if($is_close)
				{
					echo '<td><span id="span_is_close_'.$id.'"><a href="#" onclick="javescript:FikCdn_StartNodeBox('.$id.','.$groupid.');" title="启用节点">已停用</a></span></td>';
				}
				else
				{
					echo '<td><span id="span_is_close_'.$id.'"><a href="#" onclick="javescript:FikCdn_StopNodeBox('.$id.','.$groupid.');" title="停止节点">启用中</a></span></td>';
				}
								
			//	echo '<td>'.$note.'</td>';
				echo '<td><a href="stat_node_bandwidth.php?id='.$id.'" onclick="javescript:FikCdn_ViewNodeStat('.$id.','.$groupid.');" title="查看服务器流量统计数据">流量统计</a>&nbsp;';
				echo '<a href="#" onclick="javescript:FikCdn_ModifyNodeBox('.$id.','.$groupid.');" title="修改服务器信息">修改</a>&nbsp;';
				echo '<a href="#" onclick="javescript:FikCdn_ReConfigHostBox('.$id.','.$groupid.');" title="同步所有域名到此服务器的主机管理列表">同步域名</a>&nbsp;';				
				echo '<a href="#" onclick="javescript:FikCdn_NodeDeleteBox('.$id.','.$groupid.');" title="删除服务器">删除</a>&nbsp;';
				echo '<a href="#" onclick="javescript:FikCdn_HostDeleteBox('.$id.','.$groupid.');" title="删除此服务器所有域名">删除域名</a></td>';
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		 <table width="800" border="0" class="disc">
			<tr>
			<td><div class="div_page_bar"> 记录总数：<?php echo $total_host;?>条&nbsp;&nbsp;&nbsp;当前第<?php echo $nPage; ?>页|共<?php echo $total_pages; ?>页&nbsp;&nbsp;&nbsp;跳转
				<select id="pagesSelect" name="pagesSelect" style="width:50px" onChange="selectPage(this)">
				<?php
					for($i=1;$i<=$total_pages;$i++){
						if($nPage==$i)
						{
							echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
						}
						else
						{
							echo '<option value="'.$i.'">'.$i.'</option>';
						}
					}
				?>							
				</select>
				页&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="node_list.php?page=1&action=first&gid=<?php echo $nGid.'&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="node_list.php?page=<?php echo $pageup; ?>&action=pageup&gid=<?php echo $nGid.'&type='.$sType.'&keyword='.$sKeyword; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="node_list.php?page=<?php echo $pagedown; ?>&action=pagedown&gid=<?php echo $nGid.'&type='.$sType.'&keyword='.$sKeyword; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="node_list.php?page=<?php echo $total_pages; ?>&action=tail&gid=<?php echo $nGid.'&type='.$sType.'&keyword='.$sKeyword; ?>">尾页 </a></div></td>
			</tr>
		</table>	
 		<table width="800" border="0" class="bottom_btn">
			<tr>
			<td height="28">
					<input name="btnAddNodeBox"  type="submit" style="width:80px;height:28px" id="btnAddNodeBox" value="添加服务器" style="cursor:pointer;" onClick="FikCdn_AddNodeBox();" /> 
					<input name="btnExportExcel"  type="submit" style="width:80px;height:28px" id="btnExportExcel" value="导出报表" style="cursor:pointer;" onClick="FikCdn_ExportExcel();" /> 
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
