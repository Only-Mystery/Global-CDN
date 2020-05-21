<?php
include_once("./head.php");
$node_id 		= isset($_GET['id'])?$_GET['id']:'';
$order_type		= isset($_GET['order_type'])?$_GET['order_type']:'';
$order_field 	= isset($_GET['order_field'])?$_GET['order_field']:'';

if(strlen($order_type)<=0)
{
	$order_type="desc";
}

if(strlen($order_field)<=0)
{
	$order_field="UserConnections";
}
?>
<script type="text/javascript">	

function selectPage(obj){
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var order_type		 =document.getElementById("orderTypeSelect").value;
	var order_field		 =document.getElementById("orderFieldSelect").value;
	window.location.href="stat_node_hostorder.php?page="+pagesSelect+"&action=jump"+"&id="+node_id+"&order_type="+order_type+"&order_field="+order_field;
}

function OnSelectOrderType(node_id){
	var order_type		 =document.getElementById("orderTypeSelect").value;
	var order_field		 =document.getElementById("orderFieldSelect").value;
	window.location.href="stat_node_hostorder.php?page="+1+"&action=jump"+"&id="+node_id+"&order_type="+order_type+"&order_field="+order_field;
}

function OnSelectFieldType(node_id){
	var order_type		 =document.getElementById("orderTypeSelect").value;
	var order_field		 =document.getElementById("orderFieldSelect").value;
	window.location.href="stat_node_hostorder.php?page="+1+"&action=jump"+"&id="+node_id+"&order_type="+order_type+"&order_field="+order_field;
}

function OnSelectNode()
{
	var order_type		 =document.getElementById("orderTypeSelect").value;
	var order_field		 =document.getElementById("orderFieldSelect").value;
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="stat_node_hostorder.php?id="+txtSelectNode+"&order_type="+order_type+"&order_field="+order_field;
}

</script>

<div style="min-width:1000px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="stat_node_bandwidth.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">实时带宽</span></a></td>
				<td height="31" width="85"><a href="stat_node_bandwidth_max.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">峰值带宽</span></a></td>
				<td height="31" width="85"><a href="stat_node_day_download.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">日流量统计</span></a></td>				
				<td height="31" width="85"><a href="stat_node_conn.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">连接数统计</span></a></td>
				<td height="31" width="85"><a href="stat_node_hostorder.php?id=<?php echo $node_id; ?>"><span class="title_bt">最新排名</span></a></td>
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">
	  		<table width="1000" border="0" class="dataintable">
			<tr height="30">
			<td><span class="input_tips_txt3">最近10分钟内流量排名:</span>
			<div class="div_search_title">
		<select id="SelectNode" style="width:280px" onChange="OnSelectNode()">
<?php  
	$node_id 	= isset($_GET['id'])?$_GET['id']:'';
	
  	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$node_id 	= mysql_real_escape_string($node_id); 
		
		$sql = "SELECT * FROM fikcdn_node;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);	echo $row_count;
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				
				if(strlen($node_id)<=0) $node_id = $this_node_id;
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';
	
				if($this_node_id==$node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
		}		
	}
?>
				</select>&nbsp;
				排序方式：					
				<select id="orderTypeSelect" name="orderTypeSelect" style="width:65px" onChange="OnSelectOrderType(<?php echo $node_id;?>)">
				<option value="desc" <?php if($order_type=='desc')  echo 'selected="selected"'; ?>>降序</option>
					<option value="asc" <?php if($order_type=='asc')  echo 'selected="selected"'; ?>>升序</option>
				</select>	
				<select id="orderFieldSelect" name="orderFieldSelect" style="width:150px" onChange="OnSelectFieldType(<?php echo $node_id;?>)">
					<option value="UserConnections" <?php if($order_field=='UserConnections')  echo 'selected="selected"'; ?>>用户连接数</option>
					<option value="UpstreamConnections" <?php if($order_field=='UpstreamConnections')  echo 'selected="selected"'; ?>>源站连接数</option>
					<option value="bandwidth_down" <?php if($order_field=='bandwidth_down')  echo 'selected="selected"'; ?>>下行带宽</option>					
					<option value="bandwidth_up" <?php if($order_field=='bandwidth_up')  echo 'selected="selected"'; ?>>上行带宽</option>
					<option value="RequestCount" <?php if($order_field=='RequestCount')  echo 'selected="selected"'; ?>>日总请求量</option>
					<option value="RequestCountIn" <?php if($order_field=='RequestCountIn')  echo 'selected="selected"'; ?>>最近10分钟请求量</option>
				</select>
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="left" width="150">域名</th> 
				<th align="center" width="140">统计时间</th>
				<th align="right" width="100">用户连接数</th>
				<th align="right" width="100">源站连接数</th>				 
				<th align="right" width="100" align="center">用户下载带宽</th>
				<th align="right" width="100" align="center">用户上传带宽</th>
				<th align="right" width="100" align="center">总请求量</th>
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
	//$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{		
			$total_host 	= 0;
			
			$sql = "SELECT count(*) FROM domain_stat_temp WHERE node_id='$node_id';";
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

			if($order_type=="asc")
			{
				if($order_field=='UserConnections')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UserConnections ASC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='UpstreamConnections')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UpstreamConnections ASC Limit $offset,$PubDefine_PageShowNum;";
				}				
				else if($order_field=='bandwidth_down')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by bandwidth_down ASC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='bandwidth_up')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by bandwidth_up ASC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='RequestCount')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by RequestCount ASC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='RequestCountIn')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by RequestCount_increase ASC Limit $offset,$PubDefine_PageShowNum;";
				}				
				else if($order_field=='IpCount')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by IpCount ASC Limit $offset,$PubDefine_PageShowNum;";
				}		
				else
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UserConnections ASC Limit $offset,$PubDefine_PageShowNum;";
				}						
				
			}
			else
			{
				if($order_field=='UserConnections')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UserConnections DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='UpstreamConnections')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UpstreamConnections DESC Limit $offset,$PubDefine_PageShowNum;";
				}				
				else if($order_field=='bandwidth_down')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by bandwidth_down DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='bandwidth_up')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by bandwidth_up DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='RequestCount')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by RequestCount DESC Limit $offset,$PubDefine_PageShowNum;";
				}	
				else if($order_field=='RequestCountIn')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by RequestCount_increase DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else if($order_field=='IpCount')
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by IpCount DESC Limit $offset,$PubDefine_PageShowNum;";
				}		
				else
				{
					$sql = "SELECT * FROM domain_stat_temp WHERE node_id='$node_id' order by UserConnections DESC Limit $offset,$PubDefine_PageShowNum;";
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
				$time   		= mysql_result($result,$i,"time");
				$Host   		= mysql_result($result,$i,"Host");
				$RequestCount  	= mysql_result($result,$i,"RequestCount");
				$UploadCount	= mysql_result($result,$i,"UploadCount");
				$DownloadCount  = mysql_result($result,$i,"DownloadCount");
				$IpCount   		= mysql_result($result,$i,"IpCount");
				$bandwidth_down = mysql_result($result,$i,"bandwidth_down");	
				$bandwidth_up   = mysql_result($result,$i,"bandwidth_up");
				$down_increase	= mysql_result($result,$i,"down_increase");
				$up_increase	= mysql_result($result,$i,"up_increase");
				$UserConnections	= mysql_result($result,$i,"UserConnections");
				$UpstreamConnections	= mysql_result($result,$i,"UpstreamConnections");
				$RequestCount_increase	= mysql_result($result,$i,"RequestCount_increase");
					
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td align="left">'.$Host.'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$time).'</td>';
				echo '<td align="right">'.$UserConnections.' 个</td>';
				echo '<td align="right">'.$UpstreamConnections.' 个</td>';
				echo '<td align="right">'.$bandwidth_down.' Mbps</td>';
				echo '<td align="right">'.$bandwidth_up.' Mbps</td>';
				echo '<td align="right">'.$RequestCount.' 次</td>';
				echo '</tr>';
			}
		}while(0);
		
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
				页&nbsp;&nbsp;&nbsp;&nbsp;
				<a href="stat_node_hostorder.php?page=1&action=first&id=<?php echo $node_id.'&order_type='.$order_type.'&order_field='.$order_field; ?>">首页</a>&nbsp;&nbsp;
				<a href="stat_node_hostorder.php?page=<?php echo $pageup; ?>&action=pageup&id=<?php echo $node_id.'&order_type='.$order_type.'&order_field='.$order_field; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="stat_node_hostorder.php?page=<?php echo $pagedown; ?>&action=pagedown&id=<?php echo $node_id.'&order_type='.$order_type.'&order_field='.$order_field; ?>">下一页</a>&nbsp;&nbsp;
				<a href="stat_node_hostorder.php?page=<?php echo $total_pages; ?>&action=tail&id=<?php echo $node_id.'&order_type='.$order_type.'&order_field='.$order_field; ?>">尾页 </a></div></td>
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

