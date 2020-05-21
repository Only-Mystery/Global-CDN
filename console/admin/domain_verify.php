<?php

include_once("./head.php");
include_once("../function/define.php");

?>

<script type="text/javascript">	
function doCheckAll(obj){
	var form = obj.form;
	for (var i=0;i<form.elements.length;i++){
		var e = form.elements[i];
		e.checked = obj.checked;
	}
}

var ___sAction;

function doInfoActionBox(sAction){
	___sAction = sAction;
    var frm = document.form;
	var boolFind = false ;
	for(i=0;i< frm.length;i++){ 
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
		var boxURL="msg.php?3.5";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
	}else{
		var boxURL="msg.php?1.9&msg="+"请选择至少一条域名审核记录再执行操作！";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		boolFind = false;
	}	
}

function FikCdn_VerifyDomainMore(){
    var frm = document.form;
	var boolFind = false ;
	for(i=0;i< frm.length;i++){ 
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
	}else{
		boolFind = false;
	}
	if (boolFind == true){
		frm.action = frm.action + ___sAction;
		frm.submit();
	}
}

var ___nDomainId;

function FikCdn_ModifyDomainBox(domain_id){
	___nDomainId = domain_id;
	var boxURL="domain_modify.php?id="+domain_id;
	showMSGBOX('',500,450,BT,BL,120,boxURL,'修改域名:');
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

function selectPage(obj){
	var pagesSelect    =document.getElementById("pagesSelect").value;
	var nodeSelect		 =document.getElementById("nodeSelect").value;
	
	window.location.href="domain_verify.php?page="+pagesSelect+"&action=jump"+"&node_id="+nodeSelect;
}

function selectNode(){
	var nodeSelect		 =document.getElementById("nodeSelect").value;	
	window.location.href="domain_verify.php?page=1"+"&action=jump"+"&node_id="+nodeSelect;
}

function FikCdn_SelectDomainStat(){
	parent.window.leftFrame.window.OnSelectNav("span_domain_max");
}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" height="32" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
     <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">域名审核</span></td>
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td>
	  <td valign="top">	
		<div id="div_search_result">		
			<form name="form" id="form" action="./ajax_excel.php?mod=domain&action=" method="post">
		  <table width="800" border="0" class="dataintable" id="domain_table">
			<tr id="tr_domain_title">
				<th width='40' align=center>&nbsp;<input type='checkbox' name='chkselectAll' title="全选" onclick="doCheckAll(this)"></th>
				<th align="center" width="45">ID</th> 
				<th align="center" width="130">网站域名</th> 
				<th align="center" width="100">源站电信 IP</th>
				<th align="center" width="100">源站联通 IP</th>
				<th align="center" width="120">所属用户</th>
				<th align="center" width="50">状态</th>							
				<th align="center" width="140">操作</th>			
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

	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$total_host 	= 0;
		
			$sql = "SELECT count(*) FROM fikcdn_domain where status=".$PubDefine_HostStatusVerify; 
				
			
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
			
			$sql = "SELECT * FROM fikcdn_domain where status=".$PubDefine_HostStatusVerify." Limit $offset,$PubDefine_PageShowNum;";				
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
						//$product_name = $product_name.'('.$buy_id.')';
					}
				}
				
				$sql = "SELECT * FROM fikcdn_client WHERE username='$username'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{		
					$realname		 = mysql_result($result2,0,"realname");
				}
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$note.'"'.'id="tr_domain_'.$id.'">';
				echo '<td>&nbsp;<input type=checkbox name=select[] value="'.$id.'"></td>';
				echo '<td>'.$id.'</td>';
				echo '<td title="'.$note.'">'.$hostname.'</td>';
				echo '<td>'.$upstream.'</td>';
				echo '<td>'.$unicom_ip.'</td>';
				//echo '<td>'.$unicom_ip.'</td>';
				echo '<td>'.$username.'</td>';
				//echo '<td>'.date("Y-m-d",$add_time).'</td>';
				echo '<td>'.$PubDefine_HostStatus[$status]. '</td>';
				//echo '<td>'.$SumRequestCount.'</td>';
				echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyDomainBox('.$id.');" title="修改域名信息">修改</a>&nbsp;';
				if($status==$PubDefine_HostStatusVerify)
				{
					echo '<a href="javascript:void(0);" onclick="javescript:FikCdn_VerifyDomainBox('.$id.');" title="域名通过审核后才能同步到节点服务器">通过审核</a>';
				}
				echo '&nbsp;<a href="javascript:void(0);" onclick="javescript:FikCdn_DelDomainBox('.$id.');" title="删除节点信息">删除</a> </td>';
				echo '</tr>';
				
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
		 </table>
         <table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6" colspan='7'>
			
			<form name='actionform' method="post">
			批量操作：<select name="selection">
			<option value='verify'>通过审核</option>
			</select> 
			<input type="button" class="gray mini" value='执行' onclick=javascript:index=document.getElementsByName('selection')[0].selectedIndex;doInfoActionBox(document.getElementsByName('selection')[0].options[index].value);>
			</form>
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
				页&nbsp;&nbsp;&nbsp;&nbsp;<a href="domain_verify.php?page=1&action=first&node_id=<?php echo $node_id; ?>">首页</a>&nbsp;&nbsp;
				<a href="domain_verify.php?page=<?php echo $pageup; ?>&action=pageup&node_id=<?php echo $node_id; ?>">上一页</a>&nbsp;&nbsp;				
				<a href="domain_verify.php?page=<?php echo $pagedown; ?>&action=pagedown&node_id=<?php echo $node_id; ?>">下一页</a>&nbsp;&nbsp;
				<a href="domain_verify.php?page=<?php echo $total_pages; ?>&action=tail&node_id=<?php echo $node_id; ?>">尾页 </a></div></td>
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
</div>
<?php

include_once("./tail.php");
?>
