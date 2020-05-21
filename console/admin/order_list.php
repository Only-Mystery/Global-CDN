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
	var timeSelect	 =document.getElementById("timeSelect").value;
	var pagesSelect  =document.getElementById("pagesSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;	
	window.location.href="order_list.php?page="+pagesSelect+"&action=jump"+"&timeval="+timeSelect+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}

function fikcdn_search(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var timeSelect	 =document.getElementById("timeSelect").value;

	if(txtKeyword.length==0 ){
		return;
	}	
	window.location.href="order_list.php?page="+pagesSelect+"&action=jump"+"&timeval="+timeSelect+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
	
	//var getURL="./ajax_search.php?mod=search&action=order"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&timeval="+UrlEncode(timeSelect);
	
	//AjaxBasePost("search","order","GET",getURL);
}

function selectTimeval(){
	var timeSelect		 =document.getElementById("timeSelect").value;
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;	
	window.location.href="order_list.php?page=1"+"&action=jump"+"&timeval="+timeSelect+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}
var __nOrderId;
function FikCdn_DelOrderBox(order_id){
	__nOrderId = order_id;
	var boxURL="msg.php?4.4";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelOrder(){
	var postURL="./ajax_buy.php?mod=order&action=del";
	var postStr="order_id="+__nOrderId;
	
	AjaxBasePost("order","del","POST",postURL,postStr);		
}

function FikCdn_DelOrderResult(sResponse)
{
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

function FikCdn_ModifyOrderBox(order_id){
	__nOrderId = order_id;
	var boxURL="order_modify.php?order_id="+order_id;
	showMSGBOX('',500,400,BT,BL,120,boxURL,'修改订单:');	
}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">订单列表</span></td>
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
					<input id="txtKeyword" name="txtKeyword" type="text" size="20" maxlength="64" value="<?php echo $sKeyword; ?>"/>
					<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="fikcdn_search();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="120">客户帐号</th>
				<th align="center" width="100">产品名称</th>
				<th align="center" width="100">月度总流量</th>
				<th align="center" width="55">域名个数</th>
				<th align="center" width="75">价格(元/月)</th>
				<th align="center" width="75">购买月份数</th>
				<th align="center" width="70">总金额(元)</th>
				<th align="center" width="75">下单日期</th> 
				<th align="center" width="60">购买类型</th>
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
					$sql = "SELECT count(*) FROM fikcdn_order WHERE buy_time>'$this_timeval';"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_order WHERE buy_time>'$this_timeval' AND username like '%$sKeyword%';";
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_order;"; 
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT count(*) FROM fikcdn_order WHERE username like '%$sKeyword%';"; 
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
					$sql = "SELECT * FROM fikcdn_order WHERE buy_time>'$this_timeval' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT * FROM fikcdn_order WHERE buy_time>'$this_timeval' AND username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
					}
				} 
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_order ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
				}
				else
				{
					if($sType=="username")
					{
						$sql = "SELECT * FROM fikcdn_order WHERE username like '%$sKeyword%' ORDER BY id DESC Limit $offset,$PubDefine_PageShowNum;";
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
				$product_id	  	= mysql_result($result,$i,"product_id");
				$buy_time   	= mysql_result($result,$i,"buy_time");
				$note   		= mysql_result($result,$i,"note");
				$status   		= mysql_result($result,$i,"status");
				$auto_renew		= mysql_result($result,$i,"auto_renew");				
				$price   		= mysql_result($result,$i,"price");	
				$type   		= mysql_result($result,$i,"type");
				$month   		= mysql_result($result,$i,"month");	
				$type   		= mysql_result($result,$i,"type");		
				$domain_num  	= mysql_result($result,$i,"domain_num");
				$data_flow   	= mysql_result($result,$i,"data_flow");
				$frist_month_money	= mysql_result($result,$i,"frist_month_money");
				
				$total_money = $price*$month;
				
				$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$product_name  = mysql_result($result2,0,"name");
				}
								
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$note.'">';
				echo '<td>'.$username.'</td>';
				echo '<td>'.$product_name.'</td>';
				echo '<td>'.PubFunc_MBToString($data_flow).'</td>';
				echo '<td>'.$domain_num.'</td>';
				echo '<td>'.$price.'</td>';
				echo '<td>'.$month.'</td>';
				echo '<td>'.$total_money.'</td>';
				echo '<td>'.date("Y-m-d",$buy_time).'</td>';
				echo '<td>'.$PubDefine_BuyTypeStr[$type].'</td>';
				//echo '<td>'.$note.'</td>';
				echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyOrderBox('.$id.');">修改订单</a>&nbsp;
						<a href="javascript:void(0);" onclick="javescript:FikCdn_DelOrderBox('.$id.');" title="删除任务">删除</a></td>';
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
				<a href="order_list.php?page=1&action=first&timeval=<?php echo $timeval.'&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="order_list.php?page=<?php echo $pageup.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pageup&timeval=<?php echo $timeval; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="order_list.php?page=<?php echo $pagedown.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pagedown&timeval=<?php echo $timeval; ?> ">下一页</a>&nbsp;&nbsp;
				<a href="order_list.php?page=<?php echo $total_pages.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=tail&timeval=<?php echo $timeval; ?>">尾页 </a></div></td>
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
