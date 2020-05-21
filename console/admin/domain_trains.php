<?php
include_once("./head.php");
include_once('../function/define.php');
?>
<script type="text/javascript">	

var ___nTrainsId;
function FikCdn_DelUpstreamBox(id){
	___nTrainsId = id;
	
	var boxURL="msg.php?3.6";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');		
}

function FikCdn_DelUpstream(){
	var postURL="./ajax_domain.php?mod=upstream&action=del";
	var postStr="id="+___nTrainsId;
		
	AjaxBasePost("upstream","del","POST",postURL,postStr);		
}

function FikCdn_DelUpstreamResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
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

function FikCdn_SameNodeConfig(id){
	var postURL="./ajax_domain.php?mod=upstream&action=same_config";
	var postStr="id="+id;
		
	AjaxBasePost("upstream","same_config","POST",postURL,postStr);	
}

function FikCdn_SameNodeConfigUpstreamResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=节点服务器的源站修改任务提交成功，请到后台任务列表中查看任务执行结果。";
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

var __nUpstreamID;

function FikCdn_ModifyTrainsBox(id){
	var boxURL="domain_modifytrains.php?id="+id;
	showMSGBOX('',500,350,BT,BL,120,boxURL,'修改:');
}

function selectPage(obj){
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var txtNode		 =document.getElementById("nodeSelect").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtKeyword   =document.getElementById("txtKeyword").value;
	window.location.href="domain_trains.php?page="+pagesSelect+"&action=jump"+"&node="+txtNode+"&type="+UrlEncode(searchSelect)+"&keyword="+UrlEncode(txtKeyword);
}

function FikCdn_UpstreamSearch(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	
	if(txtKeyword.length==0 ){
		
	}	
	selectNode();		
}

function selectNode(){
	var txtNode		 =document.getElementById("nodeSelect").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtKeyword   =document.getElementById("txtKeyword").value;
	window.location.href="domain_trains.php?page=1"+"&action=jump"+"&node="+txtNode+"&type="+UrlEncode(searchSelect)+"&keyword="+UrlEncode(txtKeyword);
}

function FikCdn_AddTrainsBox(){
	var boxURL="domain_addtrains.php";
	showMSGBOX('',500,350,BT,BL,120,boxURL,'添加中转域名:');
}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">中转列表</span></td>
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
			<td><div class="div_search_title">服务器：	
				<select id="nodeSelect" style="width:260px" onChange="selectNode()">
<?php
	// 组ID
	$nNode 		= isset($_GET['node'])?$_GET['node']:'all';
	$sType		= isset($_GET['type'])?$_GET['type']:'';
	$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
	
	if($nNode=="all")
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
		$sql = "SELECT * FROM fikcdn_node;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			if($row_count>0)
			{
				for($i=0;$i<$row_count;$i++)
				{
					$node_id   		= mysql_result($result,$i,"id");	
					$node_name 		= mysql_result($result,$i,"name");	
					$node_ip   		= mysql_result($result,$i,"ip");
					$node_unicom_ip = mysql_result($result,$i,"unicom_ip");	
				
					$sFikIP = $node_ip;
					if(strlen($sFikIP)<=0) $sFikIP = $node_unicom_ip;						
						
					if($nNode==$node_id)
					{
						echo '<option value="'.$node_id.'" selected="selected">'.$sFikIP.' ('.$node_name.')</option>';		
					}
					else
					{
						echo '<option value="'.$node_id.'">'.$sFikIP.' ('.$node_name.')</option>';				
					}	
				}
			}
		}
	}			
 ?>
				</select>&nbsp;&nbsp;&nbsp;
				<input id="txtNode" type="hidden"  value="<?php echo $nNode; ?>" /> 
				<select id="searchSelect" name="searchSelect" style="width:150px">
					<option value="hostname" <?php if($sType=="hostname") echo 'selected="selected"'; ?> >网站域名</option>
					<option value="upstream" <?php if($sType=="upstream") echo 'selected="selected"'; ?> >源站IP一</option>
					<option value="upstream2" <?php if($sType=="upstream2") echo 'selected="selected"'; ?> >源站IP二</option>
				</select>
				<input id="txtKeyword" name="txtKeyword" type="text" size="36" maxlength="256" value="<?php echo "$sKeyword" ?>" />
				<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="FikCdn_UpstreamSearch();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="50">ID</th>
				<th align="center" width="100">服务器IP</th>
				<th align="center" width="150">服务器名称</th> 
				<th align="center" width="140">域名</th>				 
				<th align="center" width="165" align="center">中转服务器 IP</th>
				<th align="center" align="center">备注</th>
				<th align="center" width="160">操作</th>
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
		$total_host 	= 0;
			
		if($nNode=="all" || strlen($nNode)<=0)
		{
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT count(*) FROM fikcdn_upstream;"; 
			}
			else
			{
				if($sType=="hostname")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE hostname='$sKeyword';"; 
				}
				else if($sType=="upstream")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE upstream='$sKeyword';"; 
				}	
				else if($sType=="upstream2")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE upstream2='$sKeyword';"; 
				}
			}
		}
		else
		{
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT count(*) FROM fikcdn_upstream WHERE node_id=$nNode;";
			}
			else
			{
				if($sType=="hostname")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE node_id=$nNode AND hostname='$sKeyword';";
				}
				else if($sType=="upstream")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE node_id=$nNode upstream='$sKeyword';";
				}
				else if($sType=="upstream2")
				{
					$sql = "SELECT count(*) FROM fikcdn_upstream WHERE node_id=$nNode upstream2='$sKeyword';";
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
		
		if($nNode=="all" || strlen($nNode)<=0)
		{
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT * FROM fikcdn_upstream Limit $offset,$PubDefine_PageShowNum;"; 
			}
			else
			{
				if($sType=="hostname")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE hostname='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="upstream")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE upstream='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="upstream2")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE upstream2='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}				
			}
		}
		else
		{
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT * FROM fikcdn_upstream WHERE node_id=$nNode Limit $offset,$PubDefine_PageShowNum;";
			}
			else
			{
				if($sType=="hostname")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE node_id=$nNode AND hostname='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="upstream")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE node_id=$nNode AND upstream='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="upstream2")
				{
					$sql = "SELECT * FROM fikcdn_upstream WHERE node_id=$nNode AND upstream2='$sKeyword' Limit $offset,$PubDefine_PageShowNum;"; 
				}
			}
		}
		$result = mysql_query($sql,$db_link);
		if($result)
		{		
			$row_count=mysql_num_rows($result);		
			for($i=0;$i<$row_count;$i++)
			{
				$id  		= mysql_result($result,$i,"id");	
				$node_id   	= mysql_result($result,$i,"node_id");	
				$group_id	= mysql_result($result,$i,"group_id");
				$hostname	= mysql_result($result,$i,"hostname");		
				$upstream   = mysql_result($result,$i,"upstream");
				$upstream2  = mysql_result($result,$i,"upstream2");		
				$note   	= mysql_result($result,$i,"note");					
				
				$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$node_group_id	= mysql_result($result2,0,"groupid");
					$node_name	   	= mysql_result($result2,0,"name");
					$node_ip	   	= mysql_result($result2,0,"ip");
					$node_unicom_ip	= mysql_result($result2,0,"unicom_ip");
				}	
								
				$admin_url = $ip.":"."$admin_port"."/fikker/";	
				
				$sFikIp = $node_ip;
				if(strlen($sFikIp)<=0)
				{
					$sFikIp = $node_unicom_ip; 
				}
				
				$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$id.'</td>';
				echo '<td><a href="login_fikker.php?mod=logging&action=redirect&id='.$node_id.'" title="登录管理后台" target="_blank" >'.$sFikIp.'</a></td>';
				echo '<td>'.$node_name.'</td>';				
				echo '<td>'.$hostname.'</td>';
				echo '<td><span id="upstream_'.$id.'">'.$upstream.'</span></td>';
				echo '<td><span id="note_'.$id.'">'.$note.'</span></td>';
				echo '<td><a href="#" onclick="javescript:FikCdn_ModifyTrainsBox('.$id.');" title="修改中转服务器信息">修改</a>&nbsp;';
				echo '<a href="#" onclick="javescript:FikCdn_SameNodeConfig('.$id.');" title="同步源站到 Fikker 主机管理中">同步源站</a>&nbsp;';
				echo '<a href="#" onclick="javescript:FikCdn_DelUpstreamBox('.$id.');" title="删除中转服务器">删除</a></td>';
				echo '</tr>';
			}
		}
		mysql_close($db_link);
	}
?>
		 </table></div>
		 <table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6"><div class="div_page_bar"> 记录总数：<?php echo $total_host;?>条&nbsp;&nbsp;&nbsp;当前第<?php echo $nPage; ?>页|共<?php echo $total_pages; ?>页&nbsp;&nbsp;&nbsp;跳转
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
				页&nbsp;&nbsp;&nbsp;
				<a href="domain_trains.php?page=1&action=first&node=<?php echo $nNode.'&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="domain_trains.php?page=<?php echo $pageup; ?>&action=pageup&node=<?php echo $nNode.'&type='.$sType.'&keyword='.$sKeyword; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="domain_trains.php?page=<?php echo $pagedown; ?>&action=pagedown&node=<?php echo $nNode.'&type='.$sType.'&keyword='.$sKeyword; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="domain_trains.php?page=<?php echo $total_pages; ?>&action=tail&node=<?php echo $nNode.'&type='.$sType.'&keyword='.$sKeyword; ?>">尾页 </a></div></td>
			</tr>
		</table>	
		
 		<table width="800" border="0" class="bottom_btn">
			<tr>
			<td height="28">
					<input name="btnAddTrainsBox"  type="submit" style="width:110px;height:28px" id="btnAddTrainsBox" value="添加中转域名" style="cursor:pointer;" onClick="FikCdn_AddTrainsBox();" /> 
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
