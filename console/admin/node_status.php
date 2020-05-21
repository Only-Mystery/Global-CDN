<?php
include_once("./head.php");
include_once('../function/define.php');

// 组ID
$nGid 		= isset($_GET['gid'])?$_GET['gid']:'all';
?>
<script type="text/javascript">
function selectPage(obj){
	var txtGid		 =document.getElementById("grpSelect").value;
	var pagesSelect  =document.getElementById("pagesSelect").value;
	window.location.href="node_status.php?page="+pagesSelect+"&action=jump"+"&gid="+txtGid;
}

function fiknode_search(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtGid		 =document.getElementById("grpSelect").value;
	
	if(txtKeyword.length==0 ){
		return;
	}	
	
	var getURL="./ajax_search.php?mod=search&action=node"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&gid="+UrlEncode(txtGid);
	
	AjaxBasePost("search","node","GET",getURL);			
}

function selectGroup(){
	var txtGid		 =document.getElementById("grpSelect").value;
	window.location.href="node_status.php?page=1"+"&action=jump"+"&gid="+txtGid;
}

function FikCdn_ViewNodeStat(nodeid,grpid){
	parent.window.leftFrame.window.OnSelectNav("span_stat_node_bandwidth");
}

function FikCdn_NodeRealtimeResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){		
		
		var StartTime = json['StartTime'];
		var CurrentTime = json['CurrentTime'];
		var CurrentUserConnections = json['CurrentUserConnections'];
		var CurrentUpstreamConnections = json['CurrentUpstreamConnections'];
		var AllUsedMemSize = json['AllUsedMemSize'];
		var CacheUsedMemSize = json['CacheUsedMemSize'];
		var NumOfCaches = json['NumOfCaches'];
		var TotalSendKB = json['TotalSendKB'];
		var TotalRecvKB = json['TotalRecvKB'];
		var NumOfCachedSessions = json['NumOfCachedSessions'];
		var NumOfPublicCaches = json['NumOfPublicCaches'];
		var NumOfMemberCaches = json['NumOfMemberCaches'];
		var NumOfVisitorCaches = json['NumOfVisitorCaches'];
		var PublicCacheUsedMemSize = json['PublicCacheUsedMemSize'];
		var MemberCacheUsedMemSize = json['MemberCacheUsedMemSize'];
		var VisitorCacheUsedMemSize = json['VisitorCacheUsedMemSize'];
		var TotalSendToResponseKB = json['TotalSendToResponseKB'];
		var TotalRecvFromResponseKB = json['TotalRecvFromResponseKB'];
		var NodeID = json['NodeID'];
		
		var ObjName = "begin_time_"+NodeID;
		document.getElementById(ObjName).innerHTML = StartTime;
		
		ObjName = "status_"+NodeID;
		document.getElementById(ObjName).innerHTML = "正常";
		
		ObjName = "user_connect_"+NodeID;
		document.getElementById(ObjName).innerHTML = CurrentUserConnections;		
		
		ObjName = "upstream_connect_"+NodeID;
		document.getElementById(ObjName).innerHTML = CurrentUpstreamConnections;
		
		ObjName = "memory_pages_"+NodeID;
		document.getElementById(ObjName).innerHTML = NumOfCaches;
				
		ObjName = "memory_"+NodeID;
		document.getElementById(ObjName).innerHTML = KBToString(CacheUsedMemSize);
		
		ObjName = "total_send_"+NodeID;
		//document.getElementById(ObjName).innerHTML = KBToString(TotalSendKB);		
		
		ObjName = "total_recv_"+NodeID;
		//document.getElementById(ObjName).innerHTML = KBToString(TotalRecvKB);	
		
		ObjName = "total_memory_"+NodeID;
		document.getElementById(ObjName).innerHTML = KBToString(AllUsedMemSize);
		
	}else{
		var nErrorNo = json["ErrorNo"];
		var nFikErrorNo = json['FikErrorNo'];
		var NodeID = json['NodeID'];
		
		var ObjName = "begin_time_"+NodeID;
		document.getElementById(ObjName).innerHTML = "";
		
		ObjName = "user_connect_"+NodeID;
		document.getElementById(ObjName).innerHTML = 0;		
		
		ObjName = "upstream_connect_"+NodeID;
		document.getElementById(ObjName).innerHTML = 0;
		
		ObjName = "memory_pages_"+NodeID;
		document.getElementById(ObjName).innerHTML = 0;
				
		ObjName = "memory_"+NodeID;
		document.getElementById(ObjName).innerHTML = 0;
		
		ObjName = "total_send_"+NodeID;
		//document.getElementById(ObjName).innerHTML = 0;		
		
		ObjName = "total_recv_"+NodeID;
		//document.getElementById(ObjName).innerHTML = 0;		
		
		ObjName = "total_memory_"+NodeID;
		document.getElementById(ObjName).innerHTML = 0;
		
		ObjName = "status_"+NodeID;
		
		if(nErrorNo==30000){
			parent.location.href = "./login.php"; 
		}	
		else if(nErrorNo==20010){
			document.getElementById(ObjName).innerHTML ='<span class="input_red_tips2">连接数据库错误</span>';
		}	
		else if(nErrorNo==20011){
			document.getElementById(ObjName).innerHTML = '<span class="input_red_tips2">服务器不存在</span>';
		}
		else if(nErrorNo==40000){
			document.getElementById(ObjName).innerHTML = '<span class="input_red_tips2">连接服务器失败</span>';
		}		
		else if(nErrorNo==40001){
				var str = '<span class="input_red_tips2">Fikker 错误号：'+nFikErrorNo+'</span>';
			document.getElementById(ObjName).innerHTML =str;
		}			
		else if(nErrorNo==40002){
			document.getElementById(ObjName).innerHTML = '<span class="input_red_tips2">服务器忙</span>';
		}	
		else if(nErrorNo==40003){
			document.getElementById(ObjName).innerHTML = '<span class="input_red_tips2">Fikker 密码错误</span>';
		}
		else {
			var str = '<span class="input_red_tips2">错误号：'+nErrorNo+'</span>';
			document.getElementById(ObjName).innerHTML =str;
		}			
	}
}
</script>

<div style="min-width:1120px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="node_status.php?gid=<?php echo $nGid; ?>"><span class="title_bt">运行状态</span></a></td>	
				<td height="31" width="85"><a href="node_auth.php?gid=<?php echo $nGid; ?>"><span class="title_bt_active">授权状态</span></a></td>
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
			<td><div class="div_search_title">服务器组：	
				<select id="grpSelect" name="grpSelect" style="width:180px" onChange="selectGroup()">
<?php
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$nGid 	= mysql_real_escape_string($nGid); 
		
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
					if($nGid=="all")
					{
						$nGid = $gid;
					}
											
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
				</select>&nbsp;&nbsp;&nbsp;				
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="120">服务器IP</th>
				<th align="center" width="100">运行状态</th>
				<th align="center" width="140">启动时间</th>  				
				<th align="center" width="70" align="center">用户连接数</th>
				<th align="center" width="70" align="center">源站连接数</th>
				<th align="center" width="78" align="center">内存缓存数量</th>
				<th align="right" width="78" align="center">内存缓存尺寸</th>
				<th align="right" width="90" align="center">当前带宽</th>
				<th align="right" width="90" align="center">服务器带宽</th>
				<th align="right" width="75" align="center">内存总占用</th>
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
				$sql = "SELECT count(*) FROM fikcdn_node;"; 
			}
			else
			{
				$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$nGid;"; 
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
				$sql = "SELECT * FROM fikcdn_node Limit $offset,$PubDefine_PageShowNum;"; 
			}
			else
			{
				$sql = "SELECT * FROM fikcdn_node WHERE groupid=$nGid Limit $offset,$PubDefine_PageShowNum;";
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
			
			$bandwidth_total=0;
			
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
				$version_ext	= mysql_result($result,$i,"version_ext");	
				$allow_bandwidth= mysql_result($result,$i,"allow_bandwidth");
				
				$bandwidth_down=0;
				$sql = "SELECT * FROM realtime_list where node_id='$id' order by id DESC Limit 0,1;"; 
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$bandwidth_down = mysql_result($result2,0,"bandwidth_down");	
				}
				
				$bandwidth_total += $bandwidth_down;
				
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0)
				{
					$sFikIP = $unicom_ip;
				}						
				
				$admin_url = $ip.":"."$admin_port"."/fikker/";	
				
				$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));	
				$show_version = $version_ext.'/'.$show_version;				
				
				echo '<input id="node_'.$i.'" name="node_'.$i.'" type="hidden"  value="'.$id.'" />';
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td><a href="login_fikker.php?mod=logging&action=redirect&id='.$id.'" title="'.$name.' - '.$show_version.'" target="_blank" >'.$sFikIP.'</a></td>';
				echo '<td><span id="status_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td><span id="begin_time_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';				
				echo '<td><span id="user_connect_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td><span id="upstream_connect_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td><span id="memory_pages_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td align="right"><span id="memory_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td align="right"><span id="total_send_'.$id.'">'.round($bandwidth_down,2).' Mbps</span></td>';
				echo '<td align="right"><span id="total_recv_'.$id.'">'.$allow_bandwidth.' Mbps</span></td>';
				echo '<td align="right"><span id="total_memory_'.$id.'"><img src="../images/loading.gif" alt="正在导入" /></span></td>';
				echo '<td> <a href="####" title="刷新" onclick="javescript:FikCdn_RefreshRealTime('.$id.');" >刷新</a>&nbsp;';
				echo '<a href="stat_node_bandwidth.php?id='.$id.'" onclick="javescript:FikCdn_ViewNodeStat();" title="查看服务器流量统计数据">流量统计</a></td>';
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		 <table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6"><div class="div_page_bar">当前带宽汇总：<?php echo $bandwidth_total.' Mbps'; ?>&nbsp;&nbsp;&nbsp;记录总数：<?php echo $total_host;?>条&nbsp;&nbsp;&nbsp;当前第<?php echo $nPage; ?>页|共<?php echo $total_pages; ?>页&nbsp;&nbsp;&nbsp;跳转
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
				<a href="node_status.php?page=1&action=first&gid=<?php echo $nGid; ?>">首页</a>&nbsp;&nbsp;
				<a href="node_status.php?page=<?php echo $pageup; ?>&action=pageup&gid=<?php echo $nGid; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="node_status.php?page=<?php echo $pagedown; ?>&action=pagedown&gid=<?php echo $nGid; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="node_status.php?page=<?php echo $total_pages; ?>&action=tail&gid=<?php echo $nGid; ?>">尾页 </a></div></td>
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

<script type="text/javascript">

function FikCdn_GetNodeStatus(node_count){
	var i=0;
	var	sNodeIDName;
	var nNodeID;
	var postURL;
	var postStr;
	var sNodeStatus;

	sNodeIDName = "node_"+i;
	if(typeof(document.getElementById(sNodeIDName))=="undefined"){
		clearInterval(nIntervalID);
		return;
	}
	nNodeID = document.getElementById(sNodeIDName).value;
	//alert(nNodeID);
				
	postURL="./ajax_fikker.php?mod=fiknode&action=realtime";
	postStr="nodeid="+nNodeID;
	//Sleep(5);
	RealtimeAjaxBasePost("fiknode","realtime","POST",postURL,postStr);	
	i++;
		
	var nIntervalID = setInterval(ontimeout,250);

	function ontimeout(){
		var sNodeIDName2 = "node_"+i;
		if(typeof(document.getElementById(sNodeIDName2))=="undefined"){
			clearInterval(nIntervalID);
			return;
		}
		nNodeID = document.getElementById(sNodeIDName2).value;
		//alert(nNodeID);
					
		postURL="./ajax_fikker.php?mod=fiknode&action=realtime";
		postStr="nodeid="+nNodeID;
		//Sleep(5);
		RealtimeAjaxBasePost("fiknode","realtime","POST",postURL,postStr);	
		i++;	
		if(i>=node_count)
		{
			clearInterval(nIntervalID);
			return;
		}
	}
}

FikCdn_GetNodeStatus(<?php echo $row_count;?>);

function FikCdn_RefreshRealTime(nNodeID)
{
	var postURL="./ajax_fikker.php?mod=fiknode&action=realtime";
	var postStr="nodeid="+nNodeID;
	
	var img = '<img src="../images/loading.gif" alt="正在导入" />';
	
	var ObjName = "begin_time_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
	
	ObjName = "status_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
	
	ObjName = "user_connect_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;		
	
	ObjName = "upstream_connect_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
	
	ObjName = "memory_pages_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
			
	ObjName = "memory_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
	
	ObjName = "total_send_"+nNodeID;
	//document.getElementById(ObjName).innerHTML = img;		
	
	ObjName = "total_recv_"+nNodeID;
	//document.getElementById(ObjName).innerHTML = img;	
		
	ObjName = "total_memory_"+nNodeID;
	document.getElementById(ObjName).innerHTML = img;
		
	RealtimeAjaxBasePost("fiknode","realtime","POST",postURL,postStr)
}

</script>

<?php

include_once("./tail.php");
?>
