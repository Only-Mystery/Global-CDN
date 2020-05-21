<?php
include_once("./head2.php");
?>
<script type="text/javascript">
function FikCdn_SyncFCache(){
	var nodeSelect=document.getElementById("nodeSelect").value;
	var nodeSelect2=document.getElementById("nodeSelect2").value;
	if (nodeSelect.length==0 || nodeSelect2.length==0){ 
		var boxURL="msg.php?1.9&msg=目前没有服务器，不能同步页面缓存规则。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
	  	return false;
	}
	
	var postURL="./ajax_cache_rules.php?mod=fcache&action=sync";
	var postStr="node_id="+UrlEncode(nodeSelect)+"&node_id2=" + UrlEncode(nodeSelect2);
	AjaxBasePost("fcache","sync","POST",postURL,postStr);
}

function FikCdn_SyncFCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?5.9";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
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

function FikCdn_ToTaskList(){
	parent.parent.window.leftFrame.window.OnSelectNav("span_task_list");
	parent.window.location.href="task_list.php";
}

</script>
<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">

  <tr>
  	<td width="30" height="25" class="objTitle" title="" ></td>
  	<td width="220" class="objTitle" style="text-align:left;">将此服务器的所有页面缓存规则：</td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ></td>
    <td width="220">
		<select id="nodeSelect" style="width:255px" title="">
 <?php	
	$node_id 	= isset($_GET['node_id'])?$_GET['node_id']:'';
	 
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$node_id = mysql_real_escape_string($node_id); 
		
		$sql = "SELECT * FROM fikcdn_node;";
		$result = mysql_query($sql,$db_link);
		if($result)
		{		
			$row_count=mysql_num_rows($result);		
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';							
				
				if($this_node_id==$node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
					
					$show_this_name = $sFikIP.' - '.$node_name;	
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
		}
	}			
 ?>
				</select> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>

  <tr>
  	<td width="10" height="25" class="objTitle" title="" ></td>
  	<td width="220" class="objTitle" style="text-align:left;">同步到：</td>
  </tr>
 <tr>
    <td height="25" class="objTitle" title="" ></td>
    <td width="220">
		<select id="nodeSelect2" style="width:255px" title="">
 <?php
	if($db_link)
	{
		if($result)
		{		
			$bSelect = false;
			
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';							
				
				if($this_node_id!=$node_id && $bSelect == false )
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
					
					$show_this_name = $sFikIP.' - '.$node_name;	
					$bSelect = true;
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
		}
		
		mysql_close($db_link);
	}			
 ?>
				</select> 
	</td>
  </tr>
 
  <tr>
    <td height="20" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2" style="padding-left:120px;">
	    <input name="btnSyncFCache"  type="submit" style="width:105px;height:28px" id="btnSyncFCache" value="确定" style="cursor:pointer;" onclick="FikCdn_SyncFCache();" />
	</td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
