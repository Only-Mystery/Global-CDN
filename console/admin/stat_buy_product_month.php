<?php
include_once("./head.php");
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
	
$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
$buy_id 		= isset($_GET['buy_id'])?$_GET['buy_id']:'';
?>
<script type="text/javascript">	
function selectProduct()
{
	var buy_id= document.getElementById("buySelect").value;
	
	window.location.href="stat_buy_product_month.php?buy_id="+buy_id;

}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="stat_buy_product_bandwidth.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">实时带宽</span></a></td>
				<td height="31" width="85"><a href="stat_buy_product_bandwidth_max.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">峰值带宽</span></a></td>				
				<td height="31" width="85"><a href="stat_buy_product_day_download.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">日流量统计</span></a></td>
				<td height="31" width="85"><a href="stat_buy_product_day_request.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">日请求量统计</span></a></td>				
				<td height="31" width="85"><a href="stat_buy_product_month.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt">月度流量</span></a></td>															
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td>
	  <td valign="top">
	  		<table width="800" border="0" class="bottom_btn">
			<tr height="30">
			<td><div class="div_search_title">
			
			
		选择查看的套餐：	
				<select id="buySelect" name="buySelect" style="width:240px" onChange="selectProduct()">
<?php
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$buy_id = mysql_real_escape_string($buy_id);
		$timeval = mysql_real_escape_string($timeval);
			
		$sql = "SELECT * FROM fikcdn_buy;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_id  	 		= mysql_result($result,$i,"id");
				$this_username  	 = mysql_result($result,$i,"username");	
				$this_product_id  	 = mysql_result($result,$i,"product_id");	
				
				if(strlen($buy_id)<=0) $buy_id  = $this_id;
				
				$sql = "SELECT * FROM fikcdn_product WHERE id='$this_product_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$product_name  		= mysql_result($result2,0,"name");
					$product_name = $product_name.'('.$this_username.')';
				}
										
				if($buy_id==$this_id)
				{
					echo '<option value="'.$this_id.'" selected="selected">'.$product_name."</option>";
				}
				else
				{
					echo '<option value="'.$this_id.'">'.$product_name."</option>";				
				}
			}
		}
	}			
 ?>
				</select>&nbsp;&nbsp;
						</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="55">序号</th> 
				<th align="center" width="140">统计月份</th>
				<th align="center" width="140">月用户下载流量</th>
				<th align="center" width="140">月用户上传流量</th>				
				<th align="center" width="140">月请求数(次)</th>
			</tr>			
<?php
	
	$nPage 		= isset($_GET['page'])?$_GET['page']:'';
	$action 	= isset($_GET['action'])?$_GET['action']:'';
	
	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{			
			$sql = "SELECT * FROM domain_stat_product_month WHERE buy_id=$buy_id ORDER BY id DESC";
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
			
			$timenow = time();
			$timeval1 = mktime(0,0,0,date("m",$timenow),0,date("Y",$timenow));
			
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");
				$stat_time  	= mysql_result($result,$i,"time");
				$RequestCount	= mysql_result($result,$i,"RequestCount");
				$UploadCount   	= mysql_result($result,$i,"UploadCount");
				$DownloadCount  = mysql_result($result,$i,"DownloadCount");
				$IpCount   		= mysql_result($result,$i,"IpCount");
												
				if(strlen($RequestCount)<=0) $RequestCount=0;
				if(strlen($UploadCount)<=0) $UploadCount=0;
				if(strlen($DownloadCount)<=0) $DownloadCount=0;
				if(strlen($IpCount)<=0) $IpCount=0;
																
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.($i+1).'</td>';
				echo '<td>'.date("Y 年 m 月",$stat_time).'</td>';
				echo '<td>'.PubFunc_GBToString($DownloadCount).'</td>';
				echo '<td>'.PubFunc_GBToString($UploadCount).'</td>';
				echo '<td>'.$RequestCount.'</td>';				
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table></div>
		 <table width="800" border="0" class="disc">
			<tr height="28">
			<td bgcolor="#FFFFE6"><div class="div_page_bar"></div>
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
