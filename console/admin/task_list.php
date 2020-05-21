<?php
include_once("./head.php");
?>
<script type="text/javascript">	
function doCheckAll(obj){
	var form = obj.form;
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		e.checked = obj.checked;
	}
}

var ___sFrmAction;
var ___sAction;

function doInfoActionBox(sAction){
	var frm = document.form;
	___sAction = sAction;
	if(sAction=='delall' ){
		___sFrmAction  = frm.action + sAction;
		var boxURL="msg.php?3.9";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		return;
	}
	
	if(sAction=='delfalse' ){
		___sFrmAction  = frm.action + sAction;
		var boxURL="msg.php?3.12";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		return;
	}
		
	var boolFind = false ;
	for(i=0;i< frm.length;i++) { 
		e = frm.elements[i]; 
		if ( e.type=='checkbox'){
			if(e.checked){
				boolFind = true;
				break;
			}else{
				boolFind = false ;
			}			
		}		
	}
	
	if(boolFind){		
		if(sAction == "del"){
			___sFrmAction  = frm.action + sAction;
			var boxURL="msg.php?3.10";
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}else{
			___sFrmAction  = frm.action + sAction;
			var boxURL="msg.php?3.11";
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		}
	}else{
		var boxURL="msg.php?1.9&msg="+"请选择至少一条后台任务记录再执行操作！";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		boolFind = false;
	}		
}

function doInfoAction(){
	var frm = document.form;
	if(___sAction=='delall' || ___sAction=='delfalse'){
		frm.action = ___sFrmAction;
		frm.submit();
		return;
	}
	
	var boolFind = false ;
	for(i=0;i< frm.length;i++) { 
		e = frm.elements[i]; 
		if ( e.type=='checkbox'){
			if(e.checked){
				boolFind = true;
				break;
			}else{
				boolFind = false ;
			}			
		}		
	} 	

	if (boolFind == true){
		frm.action = ___sFrmAction;
		frm.submit();
	}	
}

var ___nTaskId;

function FikCdn_DelTask(){
	var postURL="./ajax_domain.php?mod=task&action=del";
	var postStr="task_id="+___nTaskId;
	
	AjaxBasePost("task","del","POST",postURL,postStr);		
}

function FikCdn_DelTaskBox(task_id){
	___nTaskId = task_id;
	var boxURL="msg.php?3.7";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelTaskResult(sResponse)
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

function FikCdn_ReExecuteTask()
{
	var postURL="./ajax_domain.php?mod=task&action=reexecute";
	var postStr="task_id="+___nTaskId;
	
	AjaxBasePost("task","reexecute","POST",postURL,postStr);	
}

function FikCdn_ReExecuteTaskBox(task_id){
	___nTaskId = task_id;
	var boxURL="msg.php?3.8";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_ReExecuteTaskResult(sResponse)
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

function selectPage(obj){
	var pagesSelect    =document.getElementById("pagesSelect").value;
	var nodeSelect		 =document.getElementById("nodeSelect").value;
	var selectorder      =document.getElementById("selectOrder").value
	
	window.location.href="task_list.php?page="+pagesSelect+"&action=jump"+"&node_id="+nodeSelect+"&selectOrder="+selectorder;
}


function selectNode(){
	var nodeSelect		 =document.getElementById("nodeSelect").value;
	var selectorder      =document.getElementById("selectOrder").value
	
	window.location.href="task_list.php?page=1"+"&action=jump"+"&node_id="+nodeSelect+"&selectOrder="+selectorder;
}

function OnClickAutoDelTask()
{
	var nAutoDelChecked =document.getElementById("auto_delete_task").checked;
	var bAutoDel = 0;
	if(nAutoDelChecked)
	{
		bAutoDel = 1;
	}
	
	var postURL="./ajax_domain.php?mod=task&action=autodel";
	var postStr="autodel="+bAutoDel;
	
	AjaxBasePost("task","autodel","POST",postURL,postStr);	
}

function FikCdn_SetAutoDelTaskResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?1.9&msg=设置成功。";
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

</script>

<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">后台任务</span></td>
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
            <?php 
					$selectorder	= isset($_GET['selectOrder'])?$_GET['selectOrder']:'id';
				?>
				排序：
				<select id="selectOrder" name="selectOrder" style="width:240px" onChange="selectNode()">
			<option value="id" <?php if($selectorder=="id") echo 'selected="selected"'; ?>>序号</option>
					<option value="type" <?php if($selectorder=="type") echo 'selected="selected"'; ?>> 任务类型 </option>
					<option value="hostname" <?php if($selectorder=="hostname") echo 'selected="selected"'; ?>>域名</option>
				</select>
			
选择所属服务器：	
				<select id="nodeSelect" name="nodeSelect" style="width:260px" onChange="selectNode()">
<?php
	// 组ID
	$node_id 	= isset($_GET['node_id'])?$_GET['node_id']:'-1';
	
	if($node_id=="-1")
	{
		echo '<option value="-1" selected="selected">所有服务器</option>';		
	}
	else
	{
		echo '<option value="-1">所有服务器</option>';		
	}
	
	$bIsAutoDeleteTask = 1;
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$node_id 	= mysql_real_escape_string($node_id); 
		
		$sql = "SELECT * FROM fikcdn_params where name='auto_del_task';";
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$bIsAutoDeleteTask  = mysql_result($result,0,"value");
		}
				
		$sql = "SELECT * FROM fikcdn_node;";
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);	echo $row_count;
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id  = mysql_result($result,$i,"id");
				$node_name  = mysql_result($result,$i,"name");
				$node_ip = mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				
				$fik_node_ip = $node_ip;
				if(strlen($fik_node_ip)<=0)
				{
					$fik_node_ip = $node_unicom_ip;
				}
				
				$show_name = $fik_node_ip.'('.$node_name.')';
					
				if($node_id==$this_node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected">'.$show_name."</option>";
				}
				else
				{
					echo '<option value="'.$this_node_id.'">'.$show_name."</option>";				
				}
			}
		}
	}		
 ?>
				</select>&nbsp;&nbsp;</div></td>
			</tr>
		</table>	
		<div id="div_search_result">		
			<form name="form" id="form" action="./ajax_excel.php?mod=task&action=" method="post">
		  <table width="800" border="0" class="dataintable" id="domain_table">
			<tr id="tr_domain_title">
				<th width='40' align=center>&nbsp;<input type='checkbox' name='chkselectAll' title="全选" onclick="doCheckAll(this)"></th>
				<th align="center" width="80">任务类型</th>
				<th align="center" width="110">服务器 IP 地址</th>
				<th align="center" width="360">域名/URL</th>
				<th align="center" width="140">提交时间</th>
				<th align="center" width="55" align="center">执行次数</th>					
				<th align="left">执行结果</th>
				<th align="center" width="90">操作</th>
			</tr>			
<?php
		//	<th align="center" width="80" align="center">月累计流量</th>		
		//		<th align="center" width="80" align="center">月总请求数</th>
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
			
			if($node_id==-1)
			{
				$sql = "SELECT count(*) FROM fikcdn_task;"; 
			}
			else
			{
				$sql = "SELECT count(*) FROM fikcdn_task WHERE node_id=$node_id;";
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
			
			if($node_id==-1)
			{
				$sql = "SELECT * FROM fikcdn_task order by $selectorder Limit $offset,$PubDefine_PageShowNum;"; 		
			}
			else
			{
				$sql = "SELECT * FROM fikcdn_task WHERE node_id=$node_id order by $selectorder  Limit $offset,$PubDefine_PageShowNum;";
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
			
			$timeval1 = mktime(0,0,0,date("m"),0,date("Y"));
			$timeval2 = mktime(0,0,0,(date("m")+1),0,date("Y"));
						
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");	
				$username	 	= mysql_result($result,$i,"username");	
				$type  			= mysql_result($result,$i,"type");	
				$time   		= mysql_result($result,$i,"time");	
				$domain_id		= mysql_result($result,$i,"domain_id");
				$task_node_id	= mysql_result($result,$i,"node_id");		
				$product_id		= mysql_result($result,$i,"product_id");
				$buy_id			= mysql_result($result,$i,"buy_id");
				$hostname		= mysql_result($result,$i,"hostname");	
				$group_id		= mysql_result($result,$i,"group_id");	
				$ext			= mysql_result($result,$i,"ext");
				$execute_count	= mysql_result($result,$i,"execute_count");		
				$result_str		= mysql_result($result,$i,"result_str");
				$url			= mysql_result($result,$i,"url");

				$url_show = $hostname;
				
				$sql = "SELECT * FROM fikcdn_client WHERE username='$username'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{		
					$realname		 = mysql_result($result2,0,"realname");
				}
				
				$sql = "SELECT * FROM fikcdn_node WHERE id=$task_node_id;";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$this_node_id  = mysql_result($result2,0,"id");
					$node_name  = mysql_result($result2,0,"name");
					$node_ip = mysql_result($result2,0,"ip");
					$node_unicom_ip = mysql_result($result2,0,"unicom_ip");
					$version_ext	= mysql_result($result2,0,"version_ext");	
					$fik_version   	= mysql_result($result2,0,"fik_version");	
					
					$fik_node_ip = $node_ip;
					if(strlen($fik_node_ip)<=0)
					{
						$fik_node_ip = $node_unicom_ip;
					}				
					
					$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));	
					$show_version = $version_ext.'/'.$show_version;	
				}
				
				if(strlen($result_str)<=0 && $execute_count<=$PubDefine_TaskMaxExecuteCount)
				{
					$result_str = "等待执行";
				}
				
				if($type>=$PubDefine_TaskAdminClearCache && $type<=$PubDefine_TaskDirClearCache)
				{
					$url_show = $ext;
				}			
				else if($type>=$PubDefine_AddFCache && $type<=$PubDefine_SyncRewrite)
				{
					if($type==$PubDefine_SyncFCache  || $type==$PubDefine_SyncRCache || $type==$PubDefine_SyncRewrite)
					{
						$sql = "SELECT * FROM fikcdn_node WHERE id=$ext;";
						$result3 = mysql_query($sql,$db_link);
						if($result3 && mysql_num_rows($result3)>0)
						{
							$this_node_id2  = mysql_result($result3,0,"id");
							$node_name2  = mysql_result($result3,0,"name");
							$node_ip2 = mysql_result($result3,0,"ip");
							$node_unicom_ip2 = mysql_result($result3,0,"unicom_ip");
							$version_ext2	= mysql_result($result3,0,"version_ext");	
							$fik_version2   	= mysql_result($result3,0,"fik_version");
							
							$fik_node_ip2 = $node_ip2;
							if(strlen($fik_node_ip2)<=0)
							{
								$fik_node_ip2 = $node_unicom_ip2;
							}	
							$url_show = $fik_node_ip.'('.$node_name.') 同步到 '.$fik_node_ip2.'('.$node_name2.')';
							
						}	
					}
					else
					{
						$url_show = urldecode($url);
					}
				}
								
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" '.'id="tr_domain_'.$id.'">';
				echo '<td>&nbsp;<input type=checkbox name=select[] value="'.$id.'"></td>';
				echo '<td>'.$PubDefine_TaskName[$type].'</td>';				
				echo '<td><a href="login_fikker.php?mod=logging&action=redirect&id='.$task_node_id.'" title="'.$node_name.' - '.$show_version.'" target="_blank" >'.$fik_node_ip.'</a></td>';
				echo '<td>'.$url_show.'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$time).'</td>';
				echo '<td>'.$execute_count. '</td>';
				echo '<td align="left">'.$result_str.'</td>';
				echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_ReExecuteTaskBox('.$id.');" title="重新执行后台任务">重新执行</a>&nbsp;';
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DelTaskBox('.$id.');" title="删除任务">删除</a> </td>';
				
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table>
		 <table width="800" border="0" class="disc">
			<tr>						
			<td bgcolor="#FFFFE6" colspan='6' height="25">
				<span>
					<form name='actionform' method="post">
					批量操作：<select name="selection">
					<option value='react'>重新执行</option>
					<option value='del'>删除</option>
					<option value='delall'>删除全部</option>
					<option value='delfalse'>删除失败</option>
					</select> 
					<input type="button" class="gray mini" value='执行' onclick=javascript:index=document.getElementsByName('selection')[0].selectedIndex;doInfoActionBox(document.getElementsByName('selection')[0].options[index].value);>
					</form>
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" <?php if($bIsAutoDeleteTask) echo 'checked="checked"';?> class="checkbox" value="1" id="auto_delete_task" onclick="OnClickAutoDelTask()">自动删除10天前执行失败的任务
				</span>
				<div class="div_page_bar"> 记录总数：<?php echo $total_host;?>条&nbsp;&nbsp;&nbsp;当前第<?php echo $nPage; ?>页|共<?php echo $total_pages; ?>页&nbsp;&nbsp;&nbsp;跳转
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
					页&nbsp;&nbsp;&nbsp;&nbsp;<a href="task_list.php?page=1&action=first&node_id=<?php echo $node_id; ?>&selectOrder=<?php echo $selectorder;?>">首页</a>&nbsp;&nbsp;
					<a href="task_list.php?page=<?php echo $pageup; ?>&action=pageup&node_id=<?php echo $node_id; ?>&selectOrder=<?php echo $selectorder;?>">上一页</a>&nbsp;&nbsp;				
					<a href="task_list.php?page=<?php echo $pagedown; ?>&action=pagedown&node_id=<?php echo $node_id; ?>&selectOrder=<?php echo $selectorder;?>">下一页</a>&nbsp;&nbsp;
					<a href="task_list.php?page=<?php echo $total_pages; ?>&action=tail&node_id=<?php echo $node_id; ?>&selectOrder=<?php echo $selectorder;?>">尾页 </a></div></td>
			</tr>
		</table>	
		    </form>
    </div>
	  </td> 
	  <td background="../images/mail_rightbg.gif">&nbsp;</td>
  </tr>
  
   <tr>
    <td valign="bottom" background="../images/mail_leftbg.gif"><img src="../images/buttom_left2.gif" width="17" height="17" /></td>
    <td background="../images/buttom_bgs.gif"><img src="../images/buttom_bgs.gif" width="17" height="17"></td>
    <td valign="bottom" background="../images/mail_rightbg.gif"><img src="../images/buttom_right2.gif" width="16" height="17" /></td>
  </tr> 
</table>

<?php

include_once("./tail.php");
?>
