<?php
include_once("./head.php");

$timeval 	= isset($_GET['timeval'])?$_GET['timeval']:'';
$sType 		= isset($_GET['type'])?$_GET['type']:'';
$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
?>
<script type="text/javascript">
function selectPage(obj){
	var timeval		 =document.getElementById("timeSelect").value;
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;		
	window.location.href="recharge_list.php?page="+pagesSelect+"&action=jump&timeval="+timeval +"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);	
}

function fikagent_search(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var timeSelect	 =document.getElementById("timeSelect").value;
	
	if(txtKeyword.length==0 ){
	}	
	window.location.href="recharge_list.php?page=1&action=ump&timeval="+timeSelect +"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);		
}

function selectTimeval(){
	var txtTimeval	  =document.getElementById("timeSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;			
	window.location.href="recharge_list.php?page=1"+"&action=jump"+"&timeval="+txtTimeval+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">充值记录</span></td>
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
			<td><div class="div_search_title">查询：	
				<select id="timeSelect" style="width:120px" onChange="selectTimeval()">
					<option value="0" <?php if($timeval==0 || strlen($timeval)<=0 ) echo 'selected="selected"';   ?> >所有记录</option>
					<option value="7" <?php if($timeval==7 ) echo 'selected="selected"';   ?> >最近一星期</option>	
					<option value="30" <?php if($timeval==30 ) echo 'selected="selected"';   ?> >最近一个月</option>	
					<option value="90" <?php if($timeval==90 ) echo 'selected="selected"';   ?> >最近三个月</option>
					<option value="180" <?php if($timeval==180) echo 'selected="selected"';   ?> >最近六个月</option>		
					<option value="365" <?php if($timeval==365 ) echo 'selected="selected"';   ?> >最近一年</option>						
				</select>&nbsp;&nbsp;&nbsp;&nbsp;		
				<select id="searchSelect" name="searchSelect" style="width:120px">
						<option value="username">用户名</option>				
					</select>
				<input id="txtKeyword" name="txtKeyword" type="text" size="30" maxlength="64"  value="<?php echo $sKeyword; ?>" />
				<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="fikagent_search();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="50">ID</th>
				<th align="center" width="150">用户名</th>
				<th align="center" width="120">订单号</th>
				<th align="center" width="140">充值日期</th> 	
				<th align="right" width="70">充值金额</th>
				<th align="right" width="70">充值前余额</th>	
				<th align="center" width="60">订单状态</th>
				<th align="center" width="240">支付宝交易号</th>					
				<th align="left">备注</th>
			</tr>			
<?php
	
	$nPage 		= isset($_GET['page'])?$_GET['page']:'';
	$action 	= isset($_GET['action'])?$_GET['action']:'';
	
	$admin_username 	= $_SESSION['fikcdn_admin_username'];
	
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
		
		do
		{		
			$total_host 	= 0;
			
			if($timeval>1000) $timeval=1000;
			
			if($timeval>0)
			{
				$this_timeval = time()-$timeval*60*60*24;
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_recharge WHERE time>'$this_timeval';"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_recharge WHERE time>'$this_timeval' AND username like '%$sKeyword%';"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_recharge;"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_recharge WHERE username like '%$sKeyword%';"; 
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
					$sql = "SELECT * FROM fikcdn_recharge WHERE time>'$this_timeval' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum; "; 
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_recharge WHERE time>'$this_timeval' AND username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum; "; 
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_recharge ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else
				{
					$sql = "SELECT * FROM fikcdn_recharge WHERE username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
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
				$order_id   	= mysql_result($result,$i,"order_id");
				$ali_trade_no  	= mysql_result($result,$i,"ali_trade_no");
				$status   		= mysql_result($result,$i,"status");
				$username   	= mysql_result($result,$i,"username");
				$money   		= mysql_result($result,$i,"money");	
				$time  		 	= mysql_result($result,$i,"time");	
				$transactor   	= mysql_result($result,$i,"transactor");	
				$bank_name   	= mysql_result($result,$i,"bank_name");	
				$serial_no   	= mysql_result($result,$i,"serial_no");
				$balance	   	= mysql_result($result,$i,"balance");
				$note   	    = mysql_result($result,$i,"note");
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$id.'</td>';
				echo '<td>'.$username.'</td>';
				echo '<td>'.$order_id.'</td>';
				echo '<td>'.date("Y-m-d H:i:s",$time).'</td>';
				echo '<td align="right">'.$money.' 元</td>';
				echo '<td align="right">'.$balance.' 元</td>';
				if($status==0 || strlen($order_id)<=0)
				{
					echo '<td>'." 支付成功".'</td>';	
				}
				else
				{
					echo '<td>'." 等待支付".'</td>';				
				}
				echo '<td>'.$ali_trade_no.'</td>';				
				echo '<td>'.$note.'</td>';
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
				<a href="recharge_list.php?page=1&action=first&timeval=<?php echo $timeval.'&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="recharge_list.php?page=<?php echo $pageup.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pageup&timeval=<?php echo $timeval; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="recharge_list.php?page=<?php echo $pagedown.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pagedown&timeval=<?php echo $timeval; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="recharge_list.php?page=<?php echo $total_pages.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=tail&timeval=<?php echo $timeval; ?>">尾页 </a></div></td>
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
