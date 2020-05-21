<?php

include_once("./head.php");
include_once('../function/define.php');
?>

<script type="text/javascript">	

function fiknode_search(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var txtNode		 =document.getElementById("nodeSelect").value;
	var txtTimeval	 =document.getElementById("timevalSelect").value;
	if(txtKeyword.length==0 ){
		return;
	}	
	window.location.href="debug_list_domain_stat.php?node_id="+txtNode+"&timeval="+txtTimeval+"&hostname="+UrlEncode(txtKeyword);	
}
function selectNode(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var txtNode		 =document.getElementById("nodeSelect").value;
	var txtNode		 =document.getElementById("nodeSelect").value;
	if(txtKeyword.length==0 ){
		return;
	}	
		
	window.location.href="debug_list_domain_stat.php?node_id="+txtNode+"&timeval="+txtTimeval+"&hostname="+UrlEncode(txtKeyword);
}

function selectTimeval(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var txtNode		 =document.getElementById("nodeSelect").value;
	var txtTimeval	 =document.getElementById("timevalSelect").value;
	if(txtKeyword.length==0 ){
		return;
	}	
		
	window.location.href="debug_list_domain_stat.php?node_id="+txtNode+"&timeval="+txtTimeval+"&hostname="+UrlEncode(txtKeyword);
}

</script>

<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="fikcdn_list.php"><span class="title_bt">域名统计数据</span></a></td>
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
			<td><div class="div_search_title">服务器列表：	
				<select id="nodeSelect" name="nodeSelect" style="width:180px" onChange="selectNode()">
<?php
	// 组ID
	$node_id 		= isset($_GET['node_id'])?$_GET['node_id']:'';
	$nTimeval 		= isset($_GET['timeval'])?$_GET['timeval']:'';
	$sHostname 		= isset($_GET['hostname'])?$_GET['hostname']:'';
	if(strlen($nTimeval)<=0)
	{
		$nTimeval = time()-60*60*24*2;
	}
	
	$node_id =15;
	
	if(strlen($sHostname)<=0)
	{
		//exit('1');
	}
	
	if(!is_numeric($nTimeval))
	{
		exit('2');
	}
	
	if(strlen($node_id)>0 && !is_numeric($node_id))
	{
		exit('3');
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
					$this_node_id  	 = mysql_result($result,$i,"id");	
					$node_name = mysql_result($result,$i,"name");	
					
					if(strlen($node_id)<=0)
					{
						$node_id=$this_node_id;
					}
						
					if($node_id==$this_node_id)
					{
						echo '<option value="'.$this_node_id.'" selected="selected">'.$node_name."</option>";
					}
					else
					{
						echo '<option value="'.$this_node_id.'">'.$node_name."</option>";				
					}	
				}
			}
		}
	}
			
 ?>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
				<input id="txtGid" name="txtGid" type="hidden"  value="<?php echo $nGid; ?>" /> 
				<select id="timevalSelect" name="timevalSelect" style="width:100px" onChange="selectTimeval()">
					<?php
						for($i=0;$i<30;$i++)
						{
							$time = time()-($i*60*60*24);
							echo '<option value="'.$time.'">'.date("Y-m-d",$time)."</option>";
						}				
					?>
				</select>			
				<input id="txtKeyword" name="txtKeyword" type="text" size="36" maxlength="256" value="<?php echo $sHostname; ?>"/>
				<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="fiknode_search();" /> 

				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="50">序号</th> 
				<th align="center" width="130">服务器名称</th>
				<th align="center" width="150">服务器组</th>
				<th align="center" width="140" align="center">域名</th>
				<th align="center" width="140" align="center">统计开始时间</th>
				<th align="center" width="140" align="center">统计结束时间</th>
				<th align="center" width="65" align="center">总请求量</th>
				<th align="center" width="65" align="center">独立IP数</th>
				<th align="center" width="65" align="center">上传量</th>
				<th align="center" width="65" align="center">下载量</th>
				<th align="center" width="65" align="center">上传带宽</th>
				<th align="center" width="65" align="center">下载带宽</th>								
			</tr>			
<?php

	$nTimeval1 = mktime(0,0,0,date("m",$nTimeval),date("d",$nTimeval)-3,date("Y",$nTimeval));
	$nTimeval2 = $nTimeval1+(60*60*24);
	
	if($db_link)
	{
		$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id;"; 
		$result = mysql_query($sql,$db_link);
		if($result && mysql_num_rows($result)>0)
		{
			$node_name  	= mysql_result($result,0,"name");	
			$node_groupid  	= mysql_result($result,0,"groupid");
			
			$sql = "SELECT * FROM fikcdn_group WHERE id=$node_groupid;"; 
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$group_name  	= mysql_result($result,0,"name");	
			}			
		}
		
		do
		{		
			$sql = "SELECT * FROM domain_stat WHERE node_id=$node_id AND Host='$sHostname' AND StartTime>=$nTimeval1 AND EndTime>=$nTimeval2 ";
			echo $sql;
			$result = mysql_query($sql,$db_link);
			if(!$result)
			{
				break;
			}
			
			$row_count=mysql_num_rows($result);			
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");	
				$Host   		= mysql_result($result,$i,"Host");	
				$StartTime  	= mysql_result($result,$i,"StartTime");	
				$EndTime   		= mysql_result($result,$i,"EndTime");	
				$RequestCount   	= mysql_result($result,$i,"RequestCount");	
				$UploadCount   	= mysql_result($result,$i,"UploadCount");
				$DownloadCount   	= mysql_result($result,$i,"DownloadCount");	
				$IpCount   	= mysql_result($result,$i,"IpCount");
				$bandwidth_down	   	= mysql_result($result,$i,"bandwidth_down");
				$bandwidth_up	   		= mysql_result($result,$i,"bandwidth_up");					
				
				$admin_url = $ip.":"."$admin_port"."/fikker/";	
				
				$show_version = substr($fik_version,strlen("Fikker/Webcache/"),strlen($fik_version)-strlen("Fikker/Webcache/"));
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$id.'</td>';
				echo '<td>'.$node_name.'</td>';
				echo '<td>'.$group_name.'</td>';
				echo '<td>'.$Host.'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$StartTime).'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$EndTime).'</td>';
				echo '<td>'.$RequestCount.'</td>';
				echo '<td>'.$IpCount.'</td>';
				echo '<td>'.PubFunc_ByteToString($UploadCount).'</td>';
				echo '<td>'.PubFunc_ByteToString($DownloadCount).'</td>';
				echo '<td>'.$bandwidth_up.'</td>';
				echo '<td>'.$bandwidth_down.'</td>';
			
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		 <table width="800" border="0" class="disc">
			<tr>
		
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
