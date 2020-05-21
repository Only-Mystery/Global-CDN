<?php
include_once("./head.php");
if(!FuncAdmin_IsLogin())
{
	FuncAdmin_LocationLogin();
}
	
$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
$sType 		= isset($_GET['type'])?$_GET['type']:'';
$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
?>
<script type="text/javascript">	
function selectPage(obj){
	var timeval	 =document.getElementById("timeSelect").value;
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;		
	window.location.href="buy_history.php?page="+pagesSelect+"&action=jump&timeval="+timeval +"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}

function fikcdn_search(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var timeSelect	 =document.getElementById("timeSelect").value;
	var timeval	 =document.getElementById("timeSelect").value;

	if(txtKeyword.length==0 ){
	}	
	
	window.location.href="buy_history.php?page=1&action=ump&timeval="+timeval +"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
		
	//var getURL="./ajax_search.php?mod=search&action=buyhistory"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&timeval="+UrlEncode(timeSelect);
	
	//AjaxBasePost("search","buyhistory","GET",getURL);			
}

function selectTimeval(){
	var txtTimeval		 =document.getElementById("timeSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;		
	window.location.href="buy_history.php?page=1"+"&action=jump&timeval="+txtTimeval+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}

</script>

<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">消费记录</span></td>
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
			<td><div class="div_search_title">购买日期查询：	
				<select id="timeSelect" style="width:110px" onChange="selectTimeval()">
					<option value="0" <?php if($timeval==0 || strlen($timeval)<=0 ) echo 'selected="selected"';   ?> >所有记录</option>
					<option value="7" <?php if($timeval==7 ) echo 'selected="selected"';   ?> >最近一星期</option>	
					<option value="30" <?php if($timeval==30 ) echo 'selected="selected"';   ?> >最近一个月</option>	
					<option value="90" <?php if($timeval==90 ) echo 'selected="selected"';   ?> >最近三个月</option>
					<option value="180" <?php if($timeval==180) echo 'selected="selected"';   ?> >最近六个月</option>		
					<option value="365" <?php if($timeval==365 ) echo 'selected="selected"';   ?> >最近一年</option>						
					</select>&nbsp;&nbsp;&nbsp;&nbsp;
					<select id="searchSelect" name="searchSelect" style="width:100px">						
						<option value="username">用户帐号</option>
						<!-- <option value="company">客户名称</option> -->						
					</select>
					<input id="txtKeyword" name="txtKeyword" type="text" size="20" maxlength="64"  value="<?php echo $sKeyword; ?>" />
					<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="fikcdn_search();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="130">用户帐号</th>
				<th align="center" width="100">套餐名称</th>
				<th align="center" width="65">加速域名数</th>
				<th align="center" width="75">月度总流量</th>
				<th align="center" width="40">月份数</th>
				<th align="center" width="75">价格(元/月)</th>
				<th align="center" width="70">总金额(元)</th>
				<th align="center" width="55">余额(元)</th>
				<th align="center" width="80">购买日期</th> 
				<th align="center" width="80">到期日期</th>
				<th align="center" width="55">购买类型</th>
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
		
	$db_link = FikCDNDB_Connect();
	if($db_link)
	{		
		$timeval 	= mysql_real_escape_string($timeval); 
		$sKeyword 	= mysql_real_escape_string($sKeyword); 
		$admin_username 	= mysql_real_escape_string($admin_username); 

		do
		{		
			$total_host 	= 0;
			
			if($timeval>1000) $timeval=1000;
			
			if($timeval>0)
			{
				$this_timeval = (time()-$timeval*60*60*24);
				if(strlen($sKeyword)<=0)
				{				
					$sql = "SELECT count(*) FROM fikcdn_buyhistory WHERE buy_time>'$this_timeval';"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_buyhistory WHERE buy_time>'$this_timeval' AND username like '%$sKeyword%';"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_buyhistory;"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_buyhistory WHERE username like '%$sKeyword%';"; 
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
			
			if($timeval>0)
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_buyhistory WHERE buy_time>'$this_timeval' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT * FROM fikcdn_buyhistory WHERE buy_time>'$this_timeval' AND username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_buyhistory ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT * FROM fikcdn_buyhistory WHERE username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;"; 
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
				$username  		= mysql_result($result,$i,"username");
				$buy_id	  		= mysql_result($result,$i,"buy_id");
				$price   		= mysql_result($result,$i,"price");	
				$month   		= mysql_result($result,$i,"month");
				$auto_renew		= mysql_result($result,$i,"auto_renew");
				$domain_num  	= mysql_result($result,$i,"domain_num");
				$data_flow  	= mysql_result($result,$i,"data_flow");
				$balance  		= mysql_result($result,$i,"balance");
				$buy_time   	= mysql_result($result,$i,"buy_time");
				$end_time   	= mysql_result($result,$i,"end_time");
				$buy_ip 		= mysql_result($result,$i,"ip");
				$type   		= mysql_result($result,$i,"type");
				$note   		= mysql_result($result,$i,"note");
				$frist_month_money  = mysql_result($result,$i,"frist_month_money");
				
				$total_money = $price*$month;
				
				$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$product_id  = mysql_result($result2,0,"product_id");
					
					$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$product_name  = mysql_result($result2,0,"name");
						//$product_name = $product_name.'('.$buy_id.')';
					}
				}
								
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$username.'</td>';
				echo '<td>'.$product_name.'</td>';
				echo '<td>'.$domain_num.'</td>';
				echo '<td>'.PubFunc_MBToString($data_flow).'</td>';
				echo '<td>'.$month.'</td>';
				echo '<td>'.$price.'</td>';
				echo '<td>'.$total_money.'</td>';
				echo '<td>'.$balance.'</td>';
				echo '<td>'.date("Y-m-d",$buy_time).'</td>';
				echo '<td>'.date("Y-m-d",$end_time).'</td>';
				echo '<td>'.$PubDefine_BuyTypeStr[$type].'</td>';//或继费
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
				<a href="buy_history.php?page=1&action=first&timeval=<?php echo $timeval.'&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="buy_history.php?page=<?php echo $pageup.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pageup&timeval=<?php echo $timeval; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="buy_history.php?page=<?php echo $pagedown.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pagedown&timeval=<?php echo $timeval; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="buy_history.php?page=<?php echo $total_pages.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=tail&timeval=<?php echo $timeval; ?>">尾页 </a></div></td>
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
