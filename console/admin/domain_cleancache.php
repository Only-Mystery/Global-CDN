<?php
include_once("./head.php");
?>
<script type="text/javascript">
function FikCdn_CleanCache(){
	var nodeSelect =document.getElementById("nodeSelect").value; 
	var txtUrl1    =document.getElementById("txtUrl1").value;
	var txtUrl2    =document.getElementById("txtUrl2").value;
	var txtUrl3    =document.getElementById("txtUrl3").value;
	
	if(nodeSelect.length==0)
	{
		document.getElementById("tipsNodeSelect").innerHTML="无服务器组，不能更新缓存文件";	
		return false;
	}
	else
	{
		document.getElementById("tipsNodeSelect").innerHTML="";
	}
		
	if (txtUrl1.length==0 && txtUrl2.length==0 && txtUrl3.length==0 ){ 
		document.getElementById("tipsUrl1").innerHTML="请输入缓存更新的 URL 链接地址";
		document.getElementById("txtUrl1").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsUrl1").innerHTML="";
	}
	
	var postURL="./ajax_domain.php?mod=domain&action=cleancache";
	var postStr="grp_id=" + nodeSelect + "&url1=" + UrlEncode(txtUrl1) + "&url2=" + UrlEncode(txtUrl2)+ "&url3=" + UrlEncode(txtUrl3);
					 
	AjaxBasePost("domain","cleancache","POST",postURL,postStr);	
}

function FikCdn_CleanCacheResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		document.getElementById("txtUrl1").value="";
		document.getElementById("txtUrl2").value="";
		document.getElementById("txtUrl3").value="";
		
		var boxURL="msg.php?3.14";
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

function FikCdn_CleanDirCache(){
	var nodeDirSelect =document.getElementById("nodeDirSelect").value; 
	var txtDirUrl    =document.getElementById("txtDirUrl").value;

	if(nodeDirSelect.length==0)
	{
		document.getElementById("tipsNodeDirSelect").innerHTML="无服务器组，不能更新缓存文件";	
		return false;
	}
	else
	{
		document.getElementById("tipsNodeDirSelect").innerHTML="";
	}
		
	if (txtDirUrl.length==0){ 
		document.getElementById("tipsDirUrl").innerHTML="请输入缓存更新的 URL 链接地址";
		document.getElementById("txtDirUrl").focus();
	  	return false;
	}
	else
	{
		document.getElementById("tipsDirUrl").innerHTML="";
	}
	
	var postURL="./ajax_domain.php?mod=domain&action=cleandircache";
	var postStr="grp_id=" + nodeDirSelect + "&url=" + UrlEncode(txtDirUrl);
					 
	AjaxBasePost("domain","cleandircache","POST",postURL,postStr);	
}


function FikCdn_CleanDirCacheResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		document.getElementById("txtDirUrl").value="";
		var boxURL="msg.php?3.15";
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

function toTaskList(){
	parent.window.leftFrame.window.OnSelectNav("span_task_list");
	window.location.href="task_list.php";
}

</script>


<div style="min-width:1060px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2">
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">更新缓存</span></td>
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
				<th colspan="2" align="left" height="35">单个文件缓存更新：</th>
			</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">选择服务器组：</td>
				<td><select id="nodeSelect" style="width:200px">
 <?php
 	$grpid 		= isset($_GET['gid'])?$_GET['gid']:'';
	
	$admin_username 	= $_SESSION['fikcdn_admin_username'];
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$sql = "SELECT * FROM fikcdn_group;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$group_id	= mysql_result($result,$i,"id");
				$group_name	= mysql_result($result,$i,"name");	
				
				if($grpid==$group_id)
				{
					echo '<option value="'.$group_id.'" selected="selected">'.$group_name."</option>";	
				}
				else
				{
					echo '<option value="'.$group_id.'">'.$group_name."</option>";									
				}
			}
		}
	
		mysql_close($db_link);
	}			
 ?>
				</select><span class="input_tips_txt" id="tipsNodeSelect" ></span></td>
			</tr>				
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">URL 链接地址 一：</td>
				<td><input id="txtUrl1" type="text" size="85" maxlength="1024"  /> <span class="input_tips_txt" id="tipsUrl1" ></span>  </td>
    		</tr>		
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">URL 链接地址 二：</td>
				<td><input id="txtUrl2" type="text" size="85" maxlength="1024"  /> <span class="input_tips_txt" id="tipsUrl2" ></span>  </td>
    		</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">URL 链接地址 三：</td>
				<td><input id="txtUrl3" type="text" size="85" maxlength="1024"  /> <span class="input_tips_txt" id="tipsUrl3" ></span>  </td>
    		</tr>
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="center"> </td>
				<td>
				<input id="Btn_CleanCache"  type="submit" style="width:100px;height:28px" value="提交更新任务" style="cursor:pointer;" onClick="FikCdn_CleanCache();" /> 
				</td>
    		</tr>
			<tr bgcolor="#FFFFE6">
				<td colspan="2" align="left" height="35"><p style="padding-left:70px">
					更新说明：<br />
					1、单个文件缓存更新支持内存缓存 + 硬盘缓存文件同时更新；<br />
					2、更新缓存列表提交成功后，将在一分钟内通过系统后台程序在各个节点服务器上一一执行，请通过“后台任务”查看执行结果；<br />
				</p></td>
			</tr>
			<tr bgcolor="#FFFFFF">
				<td colspan="2" align="left" height="35"><p style="padding-left:70px"></td>
			</tr>
			<tr>
				<th colspan="2" align="left" height="35">目录文件缓存更新：</th>
			</tr>
		<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">选择服务器组：</td>
				<td><select id="nodeDirSelect" style="width:200px">
 <?php
 	$grpid 		= isset($_GET['gid'])?$_GET['gid']:'';
	
	$admin_username 	= $_SESSION['fikcdn_admin_username'];
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$sql = "SELECT * FROM fikcdn_group;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$group_id	= mysql_result($result,$i,"id");
				$group_name	= mysql_result($result,$i,"name");	
				
				if($grpid==$group_id)
				{
					echo '<option value="'.$group_id.'" selected="selected">'.$group_name."</option>";	
				}
				else
				{
					echo '<option value="'.$group_id.'">'.$group_name."</option>";									
				}
			}
		}
	
		mysql_close($db_link);
	}			
 ?>
				</select><span class="input_tips_txt" id="tipsNodeDirSelect" ></span></td>
			</tr>				
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="right">URL 链接地址：</td>
				<td><input id="txtDirUrl" type="text" size="85" maxlength="1024"  /> <span class="input_tips_txt" id="tipsDirUrl" ></span>  </td>
    		</tr>			
			<tr bgcolor="#FFFFFF" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">
				<td width="200" height="35" align="center"> </td>
				<td>
				<input id="Btn_CleanDirCache"  type="submit" style="width:100px;height:28px" value="提交更新任务" style="cursor:pointer;" onClick="FikCdn_CleanDirCache();" /> 
				</td>
    		</tr>
			
			<tr bgcolor="#FFFFE6">
				<td colspan="2" align="left" height="35"><p style="padding-left:70px">
					更新说明：<br />					
					1、目录文件缓存更新可以做全站更新或者更新某个目录下的所有文件，例如：<br />
					&nbsp;&nbsp;&nbsp;&nbsp;a、全站更新所有已缓存的文件 URL 用：www.fikker.com/*<br />
					&nbsp;&nbsp;&nbsp;&nbsp;b、更新目录下所有已缓存的文件 URL 用：www.fikker.com/css/*<br />
					2、更新缓存列表提交成功后，将在一分钟内通过系统后台程序在各个节点服务器上一一执行，请通过“后台任务”查看执行结果；<br />
				</p></td>
			</tr>								
		 </table>
		 
		 <p></p><p></p>
<?php
	/*		 
		 <p></p>
		 
		 <table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6"><p style="padding-left:70px">
					缓存清理说明：<br />
					1. 清理缓存任务会在一分钟之内完成执行；<br />
					2. 清理主页或目录页面，请在URL链接后加'\'，比如清理 www.fikker.com 的主页，需填写的链接地址为：http://www.fikker.com/ ；
				</p></td>
			</tr>
		</table>	
		*/
?>			
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
