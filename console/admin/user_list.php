<?php
	include_once("./head.php");
	$sType 		= isset($_GET['type'])?$_GET['type']:'';
	$sKeyword 	= isset($_GET['keyword'])?$_GET['keyword']:'';
?>
<script type="text/javascript">	
var __sUserId;
function FikCdn_DelUserBox(userid){
	__sUserId = userid;
	var boxURL="msg.php?4.2";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
}

function FikCdn_DelUser(){
	var postURL="./ajax_admin.php?mod=user&action=del";
	var postStr="id="+__sUserId;
		
	AjaxBasePost("user","del","POST",postURL,postStr);		
}

function FikCdn_DelUserResult(sResponse)
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

function FikCdn_ModifyUserBox(uid)
{
	var __sUserId;
	var boxURL="user_modify.php?id="+uid;
	showMSGBOX('',500,400,BT,BL,120,boxURL,'操作提示:');		
}

function FikCdn_SelectRecharge()
{
	parent.window.leftFrame.window.OnSelectNav("span_recharge_add");
}

function selectPage(obj){
	var pagesSelect    =document.getElementById("pagesSelect").value;
	var txtKeyword    =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;	
	window.location.href="user_list.php?page="+pagesSelect+"&action=ump&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
}

function fiknode_search(){
	var txtKeyword  =document.getElementById("txtKeyword").value;
	var searchSelect  =document.getElementById("searchSelect").value;
	
	if(txtKeyword.length==0 ){
		//return;
	}	
	window.location.href="user_list.php?page=1&action=ump"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
	//var getURL="./ajax_search.php?mod=search&action=user"+"&type="+UrlEncode(searchSelect) +"&keyword="+UrlEncode(txtKeyword);
	
	//AjaxBasePost("search","user","GET",getURL);			
}

</script>

<div style="min-width:1100px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">用户列表</span></td>
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
				<select id="searchSelect" name="searchSelect" style="width:80px">
					<option value="username" <?php if($sType=="username") echo 'selected="selected"'; ?>>用户帐号</option>		
					<option value="realname" <?php if($sType=="realname") echo 'selected="selected"'; ?>>姓名</option>								
					<option value="compname" <?php if($sType=="compname") echo 'selected="selected"'; ?>>公司名称</option>
				</select>
				<input id="txtKeyword" name="txtKeyword" type="text" size="20" maxlength="256"  value="<?php echo $sKeyword; ?>"/>
				<input name="btn_search"  type="submit" style="width:80px;height:28px" id="btn_search" value="查询" style="cursor:pointer;" onClick="fiknode_search();" /> 
				</div></td>
			</tr>
		</table>	
		<div id="div_search_result">	
		  <table width="800" border="0" class="dataintable">
			<tr>
				<th align="center" width="150">用户帐号</th>
				<th align="center" width="80">姓名</th> 
				<th align="center" width="205" align="center">公司名称</th>
				<th align="center" width="90">联系电话</th>
				<th align="center" width="90">QQ号</th>	
				<th align="center" width="100">账户余额(元)</th>
				<th align="center" width="100">域名数量</th>						
				<th align="center" width="45" align="center">状态</th>
				<th align="center">操作</th>
			</tr>		
			
<?php	
	$nPage 		= isset($_GET['page'])?$_GET['page']:'1';
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
		$sKeyword 	= mysql_real_escape_string($sKeyword); 
		do
		{
			$total_host 	= 0;
			
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT count(*) FROM fikcdn_client;"; 
			}
			else
			{
				if($sType=="username")
				{
					$sql = "SELECT count(*) FROM fikcdn_client WHERE username like '%$sKeyword%';"; 
				}
				else if($sType=="realname")
				{
					$sql = "SELECT count(*) FROM fikcdn_client WHERE realname like '%$sKeyword%';"; 
				}
				else if($sType=="compname")
				{
					$sql = "SELECT count(*) FROM fikcdn_client WHERE company_name like '%$sKeyword%';"; 
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
			
			if(strlen($sKeyword)<=0)
			{
				$sql = "SELECT * FROM fikcdn_client Limit $offset,$PubDefine_PageShowNum;"; 
			}
			else
			{
				if($sType=="username")
				{
					$sql = "SELECT * FROM fikcdn_client WHERE username like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="realname")
				{
					$sql = "SELECT * FROM fikcdn_client WHERE realname like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
				}
				else if($sType=="compname")
				{
					$sql = "SELECT * FROM fikcdn_client WHERE company_name like '%$sKeyword%' Limit $offset,$PubDefine_PageShowNum;"; 
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
				$username   	= mysql_result($result,$i,"username");
				$realname   	= mysql_result($result,$i,"realname");		
				$enable_login  	= mysql_result($result,$i,"enable_login");	
				$money  		= mysql_result($result,$i,"money");				
				$register_time	= mysql_result($result,$i,"register_time");	
				$register_ip   	= mysql_result($result,$i,"register_ip");	
				$addr   		= mysql_result($result,$i,"addr");	
				$phone   		= mysql_result($result,$i,"phone");
				$company_name	= mysql_result($result,$i,"company_name");
				$qq	   			= mysql_result($result,$i,"qq");
				$note  			= mysql_result($result,$i,"note");					
				$last_login_time = mysql_result($result,$i,"last_login_time");	
				$last_login_ip	 = mysql_result($result,$i,"last_login_ip");		
				
				$domain_count=0;
				$sql = "SELECT count(*) FROM fikcdn_domain WHERE username='$username'";
				$result2 = mysql_query($sql,$db_link);
				if($result2&&mysql_num_rows($result)>0)
				{
					$domain_count = mysql_result($result2,0,"count(*)");
				}		
				
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" title="'.$note.'">';
				echo '<td>'.$username.'</td>';
				echo '<td>'.$realname.'</td>';
				echo '<td>'.$company_name.'</td>';
				echo '<td>'.$phone.'</td>';
				echo '<td>'.$qq.'</td>';
				echo '<td>'.$money.'</td>';
				echo '<td>'.$domain_count.'</td>';
				echo '<td>'.$PubDefine_ClientStatus[$enable_login].'</td>';
				echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyUserBox('.$id.');" title="修改用户信息">修改资料</a>&nbsp;
						<a href="recharge_add.php?id='.$id.'" onclick="javescript:FikCdn_SelectRecharge();"  title="账户充值">充值</a>&nbsp;
						<a href="javascript:void(0);" onclick="javescript:FikCdn_DelUserBox('.$id.');" title="删除用户帐号信息">删除</a>				
						 </td>';
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
	
	//<a href="#" onclick="javescript:FikCdn_DelUser('.$id.');" title="删除用户信息">删除</a>
?>	 
		 </table>
		</div>	
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
				页&nbsp;&nbsp;&nbsp;&nbsp;<a href="user_list.php?page=1&action=first<?php echo '&type='.$sType.'&keyword='.$sKeyword; ?>">首页</a>&nbsp;&nbsp;
				<a href="user_list.php?page=<?php echo $pageup.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pageup">上一页</a>&nbsp;&nbsp;				
				<a href="user_list.php?page=<?php echo $pagedown.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=pagedown">下一页</a>&nbsp;&nbsp;
				<a href="user_list.php?page=<?php echo $total_pages.'&type='.$sType.'&keyword='.$sKeyword; ?>&action=tail">尾页 </a></div></td>
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
