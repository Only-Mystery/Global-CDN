<?php
include_once("./head.php");
?>
<script type="text/javascript">

var __nAdminId;
function FikCdn_ModifyAdminBox(id)
{
	__nAdminId = id;
	var boxURL="admin_modify.php?id="+__nAdminId;
	showMSGBOX('',500,300,BT,BL,120,boxURL,'修改资料:');
}

</script>

<div style="min-width:1080px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><span class="title_bt">管理员列表</span></td>
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
				<th align="center" width="130">用户名</th>
				<th align="center" width="100">真实姓名</th> 
				<th align="center" width="80">权限</th>
				<th align="center" width="120" align="center">联系电话</th>
				<th align="center" width="120" align="center">QQ号</th>
				<th align="center" width="300" align="center">联系地址</th>
				<th align="center" width="55" align="center">状态</th>
				<th align="center">操作</th>
			</tr>			
<?php

	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_admin Limit 100;"; 
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
				$id  		= mysql_result($result,$i,"id");	
				$username	= mysql_result($result,$i,"username");	
				$power 		= mysql_result($result,$i,"power");	
				$enable 	= mysql_result($result,$i,"enable");	
				$nick	  	= mysql_result($result,$i,"nick");	
				$phone	 	= mysql_result($result,$i,"phone");
				$qq		   	= mysql_result($result,$i,"qq");
				$addr		= mysql_result($result,$i,"addr");
				$note		= mysql_result($result,$i,"note");		
								
				echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" title="'.$note.'" onMouseOut="Event_trOnMouseOut(this)">';
				echo '<td>'.$username.'</td>';
				echo '<td>'.$nick.'</td>';
				echo '<td>'.$power.'</td>';
				echo '<td>'.$phone.'</td>';  //date("Y-m-d H:i:s",$add_time).'</td>';
				echo '<td>'.$qq.'</td>'; 
				echo '<td>'.$addr.'</td>';
				echo '<td>'.$PubDefine_ClientStatus[$enable].'</td>';
				echo '<td><a href="javascript:void(0);" onclick="javescript:FikCdn_ModifyAdminBox('.$id.');" title="修改管理员资料">修改资料</a></td>';
				echo '</tr>';
			}
		}while(0);
		
		mysql_close($db_link);
	}
?>
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
