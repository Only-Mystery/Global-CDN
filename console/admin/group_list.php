<?php
include_once("./head.php");
?>
<script type="text/javascript">
function FikCdn_SelectNodeList(){
	parent.window.leftFrame.window.OnSelectNav("span_node_list");
}

function FikCdn_SelectCleanCache(){
	parent.window.leftFrame.window.OnSelectNav("span_domain_cleancache");
}

var ___nGrpId = 0;

function FikCdn_DelGroupBox(grpid){
	___nGrpId = grpid;
	var boxURL="msg.php?2.1";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_DelGroup(grpid){
	var postURL="./ajax_admin.php?mod=fikgroup&action=del";
	var postStr="grpid="+___nGrpId;
		
	AjaxBasePost("fikgroup","del","POST",postURL,postStr);	
}

function FikCdn_DelGroupResult(sResponse)
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
</script>

<div style="min-width:1080px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">服务器组</span></td>
<?php				
	/*			<td height="31" width="85"><a href="stat_node_bandwidth_down.php"><span class="title_bt_active">下行带宽统计</span></a></td>
				<td height="31" width="85"><a href="stat_node_bandwidth_up.php"><span class="title_bt_active">上行带宽统计</span></a></td>
				<td height="31" width="85"><a href="stat_node_user_connect.php"><span class="title_bt_active">用户连接数</span></a></td>
				<td height="31" width="85"><a href="stat_node_upstream_connect.php"><span class="title_bt_active">源站连接数</span></a></td>
			*/	
?>									
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
			<tr>
				<th align="center" width="50">ID</th> 
				<th align="center" width="240">服务器组名称</th>
				<th align="center" width="150">创建时间</th>  
				<th align="center" width="100">服务器数量</th>
				<th align="center" width="100">域名数量</th>  
				<th align="center">操作</th>
			</tr>			
<?php	

	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_group;"; 
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
				$create_time   	= mysql_result($result,$i,"create_time");	
				$status			= mysql_result($result,$i,"status");
				$is_transit		= mysql_result($result,$i,"is_transit");
				$node_count		= 0;
				
				$sql = "SELECT count(*) FROM fikcdn_node WHERE groupid=$id;"; 
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$node_count = mysql_result($result2,0,"count(*)");	
				}
				
				$sql = "SELECT count(*) FROM fikcdn_domain WHERE group_id=$id;"; 
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$domain_count = mysql_result($result2,0,"count(*)");	
				}
								
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$id.'</td>';
				echo '<td>'.$name.'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$create_time).'</td>';
				echo '<td>'.$node_count.'</td>'; 
				echo '<td>'.$domain_count.'</td>'; 
				echo '<td> <a href="node_list.php?gid='.$id.'"  onclick="javescript:FikCdn_SelectNodeList()" title="查看组内所有服务器">服务器列表</a>&nbsp;
							<a href="####" onclick="javescript:FikCnd_ModifyGroupBox('.$id.',\''.$name.'\');">修改组名称</a>&nbsp';
				echo '<span id="span_href_status_'.$id.'">';	
				
				/*		
				if($status)
				{
					echo '<a href="#0" onclick="javescript:groupDown('.$id.');" title="套餐下线">套餐下线</a>';
				}
				else
				{
					echo '<a href="#0" onclick="javescript:groupUp('.$id.');" title="套餐上线">套餐上线</a>';
				}	
				*/		
				echo '</span>';			
				echo '&nbsp<a href="domain_cleancache.php?gid='.$id.'" onclick="javescript:FikCdn_SelectCleanCache();" title="清理缓存文件">清理缓存</a>&nbsp;
					  <a href="####" onclick="javescript:FikCdn_DelGroupBox('.$id.');" title="删除组">删除</a></td></tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>


		 </table>
		 
		 <table width="800" border="0">
			<tr>
			<td><p style="margin-left:15px"><span class="input_tips_txt3">说明:</span><br />					
			<span class="input_tips_txt6">
			添加服务器时可以创建新的组；<br />
			</span>
			</p></td>
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

<style type="text/css">
#msgbox{Z-INDEX: 1; clear:both;display:none;WIDTH:309px; POSITION: absolute; TOP: 110px;left: 360px;}
#msgbox a:link,a:hover,a:active,a:visited{color: #1875C6; text-decoration: none;}
#msgtitle{font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif; font-size:12px; color: #666666; height:28px; margin-left:5px; float:left; width:282px;}
#group_item{font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif; font-size:12px; font-weight:bold;color: #666666; margin-left:25px; width:60px; float:left; padding-top:6px;}

.b1,.b2,.b3,.b4,.b1b,.b2b,.b3b,.b4b,.b{display:block;overflow:hidden;}
.b1,.b2,.b3,.b1b,.b2b,.b3b{height:1px;}
.b2,.b3,.b4,.b2b,.b3b,.b4b,.b{border-left:1px solid #83B6E7;border-right:1px solid #83B6E7;}
.b1,.b1b{margin:0 5px;background:#83B6E7;}
.b2,.b2b{margin:0 3px;border-width:2px;}
.b3,.b3b{margin:0 2px;}
.b4,.b4b{height:2px;margin:0 1px;}
.d1{background:#FFFFFF;}
.k {height:120px;}
</style>

<script type="text/javascript">	

var __GrpId;
var __GrpName;
var __Price;
function FikCnd_ModifyGroupBox(grpid,grpname)
{
	msgboxOBJ=document.getElementById("msgbox"); 
	msgboxOBJ.style.display="block";	
	document.getElementById("txtGrpName").value=grpname;
	__GrpId = grpid;
	__GrpName = grpname;
}

//关闭层
function closeMyMSGBOX(){
	msgboxOBJ=document.getElementById("msgbox"); 
	msgboxOBJ.style.display="none";
	//document.getElementById("txtGrpName").focus();
	//alert($id);
}

function FikCnd_ModifyGroup(){
	var txtGrpName    =document.getElementById("txtGrpName").value;
	
	if (txtGrpName.length==0 ){ 
		var boxURL="msg.php?2.1&msg=请输入新的组名称";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtGrpName").focus();
	  	return false;
	}
	
	/*
	if (txtGrpPrice.length==0 ){ 
		alert("请输入套餐价格。");
		document.getElementById("txtGrpPrice").focus();
	  	return false;
	}
	*/
				
	var postURL="./ajax_admin.php?mod=fikgroup&action=modify";
	var postStr="grpid="+__GrpId+"&newname="+UrlEncode(txtGrpName);
		
	AjaxBasePost("fikgroup","modify","POST",postURL,postStr);		
}

function FikCdn_ModifyGroupResult(sResponse)
{
	var json = eval("("+sResponse+")");

	if(json["Return"]=="True"){
		var msgboxOBJ=document.getElementById("msgbox"); 
		msgboxOBJ.style.display="none";
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


function groupUp(grpid){
	var returnVal = window.confirm("是否确认将此套餐上线？", "套餐上线");
	if(!returnVal){
		return;
	}
	
	var postURL="./ajax_admin.php?mod=fikgroup&action=modifystatus";
	var postStr="grpid="+grpid+"&status=1";
		
	AjaxBasePost("fikgroup","modifystatus","POST",postURL,postStr);		
}

function groupDown(grpid){
	var returnVal = window.confirm("是否确认将此套餐下线？", "套餐下线");
	if(!returnVal){
		return;
	}
	
	var postURL="./ajax_admin.php?mod=fikgroup&action=modifystatus";
	var postStr="grpid="+grpid+"&status=0";
		
	AjaxBasePost("fikgroup","modifystatus","POST",postURL,postStr);		
}

</script>

<div id="msgbox">  
	<b class="b1"></b><b class="b2 d1"></b><b class="b3 d1"></b><b class="b4 d1"></b>
	<div class="b d1 k">
		<div>
		<div id="msgtitle">修改服务器组名称：</div>
		<div style="margin-right:3px; height:28px;"><a href="javascript:closeMyMSGBOX()"><span style="font-family:宋体;font-size:14px;">×</span></a></div>
		</div>
		<div style="width:100%; height:36px;"><div id="group_item">组名称：</div><div style="margin-top:3px; margin-right:10px;height:30px; padding-top:0px;"><input id="txtGrpName" name="txtGrpName" type="text" size="18" maxlength="64"  /></div></div>
		<!-- <div style="width:100%; height:36px;"><div id="group_item">套餐价格：</div><div style="margin-top:0px; margin-right:10px;height:30px; padding-top:0px;"><input id="txtGrpPrice" name="txtGrpPrice" type="text" size="28" maxlength="64"  /></div></div> -->
		<div style="padding-top:10px;padding-left:120px; margin:0px;">
		<input name="btn_save"  type="submit" style="width:75px;height:28px;cursor:pointer;" id="btn_save" value="修改" onClick="FikCnd_ModifyGroup();" /> 
		</div>
	</div>
	<b class="b4b d1"></b><b class="b3b d1"></b><b class="b2b d1"></b><b class="b1b"></b>
</div>

<?php

include_once("./tail.php");
?>
