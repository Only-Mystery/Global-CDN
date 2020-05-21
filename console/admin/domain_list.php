<?php
include_once("./head.php");
$nPage 		= isset($_GET['page'])?$_GET['page']:'';
$action 	= isset($_GET['action'])?$_GET['action']:'';

$buy_id 	= isset($_GET['buy_id'])?$_GET['buy_id']:'all';
$sType 		= isset($_GET['type'])?$_GET['type']:'';
$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';	
$selectoder	= isset($_GET['selectOrder'])?$_GET['selectOrder']:'down_dataflow_total';	
?>
<script type="text/javascript">	
var ___nDomainId;

function FikCdn_DelDomainBox(domain_id){
	___nDomainId = domain_id;
	var boxURL="msg.php?3.1";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelDomain(){
	var postURL="./ajax_domain.php?mod=domain&action=del";
	var postStr="domain_id="+___nDomainId;
	
	AjaxBasePost("domain","del","POST",postURL,postStr);		
}

function FikCdn_DelDomainResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nOrderID = json["order_id"];
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
	
function FikCdn_StartDomainBox(domain_id){
	___nDomainId = domain_id;
	var boxURL="msg.php?3.2";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');		
}

function FikCdn_StartDomain(){
	var postURL="./ajax_domain.php?mod=domain&action=start";
	var postStr="domain_id="+___nDomainId;
	
	AjaxBasePost("domain","start","POST",postURL,postStr);	
}

function FikCdn_StartDomainResult(sResponse){
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

function FikCdn_StopDomainBox(domain_id){
	___nDomainId = domain_id;
	var boxURL="msg.php?3.3";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_StopDomain(){
	var postURL="./ajax_domain.php?mod=domain&action=stop";
	var postStr="domain_id="+___nDomainId;
	
	AjaxBasePost("domain","stop","POST",postURL,postStr);
}

function FikCdn_StopDomainResult(sResponse){
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

function FikCdn_VerifyDomainBox(domain_id){
	___nDomainId = domain_id;
	var boxURL="msg.php?3.4";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_VerifyDomain(){
	var postURL="./ajax_domain.php?mod=domain&action=verify";
	var postStr="domain_id="+___nDomainId;
	
	AjaxBasePost("domain","verify","POST",postURL,postStr);	
}

function FikCdn_VerifyDomainResult(sResponse)
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

function selectPage(obj){
	var pagesSelect    =document.getElementById("pagesSelect").value;
	
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;
	var txtBuyId	  =document.getElementById("grpSelect").value;
	var selectOrder   =document.getElementById("selectOrder").value;
	window.location.href="domain_list.php?page="+pagesSelect+"&action=jump"+"&buy_id="+txtBuyId+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect+"&selectOrder="+selectOrder;
}

function FikHost_Search(){
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;
	var txtBuyId	  =document.getElementById("grpSelect").value;
	
	if(txtKeyword.length==0 ){
	//	return;
	}	
	
	window.location.href="domain_list.php?page=1"+"&action=jump"+"&buy_id="+txtBuyId+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect;
	
	//var getURL="./ajax_search.php?mod=search&action=domain"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&buy_id="+UrlEncode(txtBuyId);
	
	//AjaxBasePost("search","domain","GET",getURL);			
}

function FikCdn_ExportExcel(){
	var txtKeyword   =document.getElementById("txtKeyword").value;
	var searchSelect =document.getElementById("searchSelect").value;
	var txtBuyId	 =document.getElementById("grpSelect").value;
	
	var  getURL="./ajax_excel.php?mod=excel&action=host"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword)+"&buy_id="+UrlEncode(txtBuyId);
	window.location.href=getURL;	
}

function selectGroup(){
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;
	var txtBuyId		 =document.getElementById("grpSelect").value;
	var selectOrder =document.getElementById("selectOrder").value;
	window.location.href="domain_list.php?page=1"+"&action=jump"+"&buy_id="+txtBuyId+"&keyword="+UrlEncode(txtKeyword)+"&type="+searchSelect+"&selectOrder="+selectOrder;
}

function FikCdn_AddDomainBox(){
	var boxURL="domain_add.php";
	showMSGBOX('',560,560,BT,BL,120,boxURL,'添加域名:');
}

function FikCdn_ModifyDomainBox(domain_id){
	var boxURL="domain_modify.php?id="+domain_id;
	showMSGBOX('',560,560,BT,BL,120,boxURL,'修改域名:');
}

function FikCdn_SelectDomainStat(){
	parent.window.leftFrame.window.OnSelectNav("span_domain_bandwidth");
}

function FikCdn_SSLOptBox(domain_id){
	var boxURL="domain_sslopt.php?id="+domain_id;
	showMSGBOX('',500,470,BT,BL,120,boxURL,'SSL配置');
}

</script>

<div style="min-width:1160px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="domain_list.php?buy_id=<?php echo $buy_id.'&page='.$nPage.'&action='.$action.'&type='.$sType.'&keyword='.$sKeyword.'&selectOrder='.$selectoder; ?>"><span class="title_bt">域名列表</span></a></td>
				<td height="31" width="85"><a href="domain_list_info.php?buy_id=<?php echo $buy_id.'&page='.$nPage.'&action='.$action.'&type='.$sType.'&keyword='.$sKeyword.'&selectOrder='.$selectoder; ?>"><span class="title_bt_active">更多信息</span></a></td>
				
								
<?php				
	/*			<td height="31" width="85"><a href="stat_domain_max_bandwidth_down.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">下行峰值带宽</span></a></td>
				<td height="31" width="85"><a href="stat_domain_max_bandwidth_up.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">上行峰值带宽</span></a></td>						
				<td height="31" width="85"><a href="stat_domain_bandwidth_down.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">下行带宽</span></a></td>
				<td height="31" width="85"><a href="stat_domain_bandwidth_up.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">上行带宽</span></a></td>
				<td height="31" width="85"><a href="stat_domain_download.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">流量统计</span></a></td>
				<td height="31" width="85"><a href="stat_domain_request.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">请求量统计</span></a></td>				
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
			<tr height="30">
			<td><div class="div_search_title">
				<?php 
					$selectoder	= isset($_GET['selectOrder'])?$_GET['selectOrder']:'down_dataflow_total';
				?>
				排序：
				<select id="selectOrder" name="selectOrder" style="width:240px" onChange="selectGroup(this)">
					<option value="down_dataflow_total" <?php if($selectoder=="down_dataflow_total") echo 'selected="selected"'; ?>>累计下载流量</option>
					<option value="username" <?php if($selectoder=="username") echo 'selected="selected"'; ?>>用户名</option>
					<option value="id" <?php if($selectoder=="id") echo 'selected="selected"'; ?>> 序号 </option>
					<option value="upstream" <?php if($selectoder=="upstream") echo 'selected="selected"'; ?>> 源站IP一 </option>
					<option value="unicom_ip" <?php if($selectoder=="unicom_ip") echo 'selected="selected"'; ?>> 源站IP二 </option>
				</select>
已售套餐名称：	
<select id="grpSelect" name="grpSelect" style="width:240px" onChange="selectGroup(this)">
					
<?php
	// 组ID
	$buy_id 	= isset($_GET['buy_id'])?$_GET['buy_id']:'all';
	$sType 		= isset($_GET['type'])?$_GET['type']:'';
	$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
	
	if($buy_id=="all")
	{
		echo '<option value="all" selected="selected">所有已售套餐</option>';		
	}
	else
	{
		echo '<option value="all">所有已售套餐</option>';		
	}
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$buy_id = mysql_real_escape_string($buy_id);
		$sKeyword = mysql_real_escape_string($sKeyword);
		$selectoder  = mysql_real_escape_string($selectoder);
		
		if($selectoder == 'down_dataflow_total')
		{
			$selectoder = $selectoder." DESC ";
		}
		
		$sql = "SELECT * FROM fikcdn_buy;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_buy_id  = mysql_result($result,$i,"id");
				$product_id = mysql_result($result,$i,"product_id");
				$buy_username = mysql_result($result,$i,"username");
				
				$sql = "SELECT * FROM fikcdn_product WHERE id=$product_id;";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{						
					$product_name  	 = mysql_result($result2,0,"name");	
					$product_name = $product_name.' ('.$buy_username.')';
				
					if(strlen($buy_id)<=0)
					{
						$buy_id = $this_id;
					}
					
					if($this_buy_id==$buy_id)
					{
						echo '<option value="'.$this_buy_id.'" selected="selected">'.$product_name."</option>";
					}
					else
					{
						echo '<option value="'.$this_buy_id.'">'.$product_name."</option>";				
					}
				}	
			}
		}
	}		
 ?>
				</select>&nbsp;&nbsp;&nbsp;&nbsp;
							
				<select id="searchSelect" name="searchSelect" style="width:100px">
					<option value="domain" <?php if($sType=="domain") echo 'selected="selected"'; ?>>网站域名</option>
					<option value="srcip" <?php if($sType=="srcip") echo 'selected="selected"'; ?>>源站IP一</option>	
					<option value="srcip2" <?php if($sType=="srcip2") echo 'selected="selected"'; ?>>源站IP二</option>					
					<option value="owner" <?php if($sType=="owner") echo 'selected="selected"'; ?>>所属用户</option>
				</select>
				<input id="txtKeyword" name="txtKeyword" type="text" size="20" maxlength="256" value="<?php echo $sKeyword; ?>" />
				<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="FikHost_Search();" /> 
				</div></td>
			</tr>
		</table>
		<div id="div_search_result">
		  <table width="800" border="0" class="dataintable" id="domain_table">
			<tr id="tr_domain_title">
				<th align="left" width="150">网站域名</th>
				<th align="center" width="80">域名SSL选项</th>
				<th align="left" width="240">源站IP(电信/联通)</th>
				<th align="center" width="80">源站SSL选项</th>
				<th align="left" width="220">所属套餐</th>
				<th align="right" width="80">累计下载流量</th>
				<th align="center" width="60">状态</th>							
				<th align="center">操作</th>
			</tr>			
<?php
		//	<th align="center" width="80" align="center">月累计流量</th>		
		//		<th align="center" width="80" align="center">月总请求数</th>
	
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
			
			if($buy_id=="all" || strlen($buy_id)<=0)
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_domain;"; 
				}
				else
				{
					if($sType=="domain")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE hostname like '%$sKeyword%'"; 
					}
					else if($sType=="srcip")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE upstream like '%$sKeyword%'";
					}						
					else if($sType=="srcip2")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE unicom_ip like '%$sKeyword%'";
					}		
					else if($sType=="owner")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE username like '%$sKeyword%'"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$buy_id';"; 
				}
				else
				{
					if($sType=="domain")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$buy_id' AND hostname like '%$sKeyword%'"; 
					}
					else if($sType=="srcip")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$buy_id' AND upstream like '%$sKeyword%'";
					}	
					else if($sType=="srcip2")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$buy_id' AND unicom_ip like '%$sKeyword%'";
					}				
					else if($sType=="owner")
					{
						$sql = "SELECT count(*) FROM fikcdn_domain WHERE buy_id='$buy_id' AND username like '%$sKeyword%'"; 
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
			
			if($buy_id=="all" || strlen($buy_id)<=0)
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_domain order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else
				{
					if($sType=="domain")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE hostname like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}
					else if($sType=="srcip")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE upstream like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}	
					else if($sType=="srcip2")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE unicom_ip like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}					
					else if($sType=="owner")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE username like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}
				}
			}
			else
			{
				if(strlen($sKeyword)<=0)
				{
					$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else
				{
					if($sType=="domain")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id' AND hostname like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}
					else if($sType=="srcip")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id' AND upstream like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}	
					else if($sType=="srcip2")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id' AND unicom_ip like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
					}				
					else if($sType=="owner")
					{
						$sql = "SELECT * FROM fikcdn_domain WHERE buy_id='$buy_id' AND username like '%$sKeyword%' order by $selectoder Limit $offset,$PubDefine_PageShowNum;"; 
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
			
			$timeval1 = mktime(0,0,0,date("m"),0,date("Y"));
			$timeval2 = mktime(0,0,0,(date("m")+1),0,date("Y"));
						
			for($i=0;$i<$row_count;$i++)
			{
				$id  			= mysql_result($result,$i,"id");	
				$hostname  		= mysql_result($result,$i,"hostname");	
				$username	 	= mysql_result($result,$i,"username");	
				$add_time  		= mysql_result($result,$i,"add_time");	
				$status   		= mysql_result($result,$i,"status");	
				$this_buy_id	= mysql_result($result,$i,"buy_id");
				$group_id	   	= mysql_result($result,$i,"group_id");		
				$upstream		= mysql_result($result,$i,"upstream");
				$unicom_ip		= mysql_result($result,$i,"unicom_ip");
				$begin_time		= mysql_result($result,$i,"begin_time");	
				$end_time		= mysql_result($result,$i,"end_time");	
				$note			= mysql_result($result,$i,"note");		
				$down_dataflow_total = mysql_result($result,$i,"down_dataflow_total");
				$request_total  = mysql_result($result,$i,"request_total");
				$SSLOpt 	    = mysql_result($result,$i,"SSLOpt");	
				$UpsSSLOpt	    = mysql_result($result,$i,"UpsSSLOpt");		
				
				
				$sql = "SELECT * FROM fikcdn_buy WHERE id='$this_buy_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{		
					$product_id		 = mysql_result($result2,0,"product_id");
				
					$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id'";
					$result2 = mysql_query($sql,$db_link);
					if($result2 && mysql_num_rows($result2)>0)
					{
						$product_name  		= mysql_result($result2,0,"name");
						$product_name = $product_name.'('.$username.')';
					}
				}
				
				$sql = "SELECT * FROM fikcdn_client WHERE username='$username'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{		
					$realname		 = mysql_result($result2,0,"realname");
				}
				
				/*
				$sql = "SELECT sum(DownloadCount),sum(RequestCount) FROM domain_stat_group_day WHERE buy_id='$this_buy_id' AND Host='$hostname' AND time>=$timeval1 AND time<$timeval2";
				$result2 = mysql_query($sql,$db_link);	
				if($result2 && mysql_num_rows($result2)>0)				
				{
					$SumDownloadCount = mysql_result($result2,0,"sum(DownloadCount)");
					//$SumRequestCount = mysql_result($result2,0,"sum(RequestCount)");
				}
				*/
				
				if(strlen($upstream)>0 && strlen($unicom_ip)>0)
				{
					$upstream = $upstream.'/'.$unicom_ip;
				}
				else if(strlen($upstream)<=0)
				{
					$upstream = $unicom_ip;
				}
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$note.'"'.'id="tr_domain_'.$id.'">';
				echo '<td title="'.$note.'" align="left">'.$hostname.'</td>';
				echo '<td align="center">'.$PubDefine_SSLOptStr[$SSLOpt].'</td>';
				echo '<td align="left">'.$upstream.'</td>';
				echo '<td align="center">'.$PubDefine_SSLOptStr[$UpsSSLOpt].'</td>';
				//echo '<td>'.$username.'</td>';
				echo '<td align="left">'.$product_name.'</td>';
				//echo '<td>'.date("Y-m-d",$add_time).'</td>';
				echo '<td align="right">'.PubFunc_GBToString($down_dataflow_total).'</td>';
				
				echo '<td align="center">';
				if($status==$PubDefine_HostStatusStop )
				{
					echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_StartDomainBox('.$id.');" title="开始域名加速">已停止</a>';
				}
				else if($status==$PubDefine_HostStatusOk )
				{
					echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_StopDomainBox('.$id.');" title="暂停域名加速">加速中</a>';
				}
				else if($status==$PubDefine_HostStatusVerify)
				{
					echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_VerifyDomainBox('.$id.');" title="域名通过审核后才能同步到节点服务器">待审核</a>';
				}
				echo '</td>';				
				//echo '<td>'.$product_name.'</td>';
				//echo '<td>'.$SumRequestCount.'</td>';
				echo '<td>  <a href="stat_domain_bandwidth.php?domain_id='.$id.'" onclick="javescript:FikCdn_SelectDomainStat();" title="查看此域名流量统计信息">流量统计</a>&nbsp;';
				echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyDomainBox('.$id.');" title="修改域名信息">修改</a>&nbsp;';
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DelDomainBox('.$id.');" title="删除节点信息">删除</a> </td>';
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
				<a href="domain_list.php?page=1&action=first&buy_id=<?php echo $buy_id.'&keyword='.$sKeyword.'&type='.$sType.'&selectOrder='.$selectoder; ?>">首页</a>&nbsp;&nbsp;
				<a href="domain_list.php?page=<?php echo $pageup; ?>&action=pageup&buy_id=<?php echo $buy_id.'&keyword='.$sKeyword.'&type='.$sType.'&selectOrder='.$selectoder; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="domain_list.php?page=<?php echo $pagedown; ?>&action=pagedown&buy_id=<?php echo $buy_id.'&keyword='.$sKeyword.'&type='.$sType.'&selectOrder='.$selectoder; ?>">下一页</a>&nbsp;&nbsp;
				<a href="domain_list.php?page=<?php echo $total_pages; ?>&action=tail&buy_id=<?php echo $buy_id.'&keyword='.$sKeyword.'&type='.$sType.'&selectOrder='.$selectoder; ?>">尾页 </a></div></td>
			</tr>
		</table>	
 		<table width="800" border="0" class="bottom_btn">
			<tr>
			<td height="28">
					<input name="btnAddDomainBox"  type="submit" style="width:80px;height:28px" id="btnAddDomainBox" value="添加域名" style="cursor:pointer;" onClick="FikCdn_AddDomainBox();" /> 
					<input name="btnExportExcel"  type="submit" style="width:80px;height:28px" id="btnExportExcel" value="导出报表" style="cursor:pointer;" onClick="FikCdn_ExportExcel();" /> 
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
