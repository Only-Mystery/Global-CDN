<?php
include_once("head2.php");
?>
<script type="text/javascript">
function FikCdn_AddTrains(){
	var txtDomain	 =document.getElementById("txtDomain").value;
	var nodeSelect=document.getElementById("nodeSelect").value;
	var txtSrcIP     =document.getElementById("txtSrcIP").value;
	var txtBackup    =document.getElementById("txtBackup").value;
			
	if (nodeSelect.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请先添加 Fikker 节点服务器。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		return;
	}
		
	if (txtDomain.length==0 ){
		var boxURL="msg.php?1.9&msg=请输入要中转的网站域名。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("txtDomain").focus();
	  	return false;
	}

	if (txtSrcIP.length==0){ 
		var boxURL="msg.php?1.9&msg=请输入中转服务器的 IP 地址。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtSrcIP").focus();
	  	return false;
	}
	
	var postURL="./ajax_domain.php?mod=upstream&action=add";
	var postStr="node_id="+UrlEncode(nodeSelect)+"&domain=" + UrlEncode(txtDomain) + "&srcip=" + UrlEncode(txtSrcIP) +"&backup=" + UrlEncode(txtBackup);
	AjaxBasePost("upstream","add","POST",postURL,postStr);
}

function FikCdn_AddUpstreamResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var trains_id = json["id"];
		var node_id = json["node_id"];
		var domain_id = json["domain_id"];
		var domain = json["domain"];		
		var src_ip = json["src_ip"];
		var fik_node_ip = json["fik_ip"];
		var node_name = json["node_name"];
		var note = json["note"];
		
		var sNewItem = '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
		sNewItem  	+= '<td>'+trains_id+'</td>';
		sNewItem  	+= '<td><a href="login_fikker.php?mod=logging&action=redirect&id='+node_id+'" title="登录管理后台" target="_blank" >'+fik_node_ip+'</a></td>';
		sNewItem  	+= '<td>'+node_name+'</td>';				
		sNewItem  	+= '<td>'+domain+'</td>';
		sNewItem  	+= '<td><span id="upstream_'+trains_id+'">'+src_ip+'</span></td>';
		sNewItem  	+= '<td><span id="note_'+trains_id+'">'+note+'</span></td>';
		sNewItem  	+= '<td><a href="#" onclick="javescript:FikCdn_ModifyTrainsBox('+trains_id+');" title="修改中转信息">修改</a>&nbsp;';
		sNewItem  	+= '<a href="#" onclick="javescript:FikCdn_SameNodeConfig('+trains_id+');" title="同步源站到 Fikker 主机管理中">同步源站</a>&nbsp;';
		sNewItem  	+= '<a href="#" onclick="javescript:FikCdn_DelUpstreamBox('+trains_id+');" title="删除中转服务器">删除</a></td>';
		sNewItem  	+= '</tr></table>';
		
		var boxURL="msg.php?1.8&msg=配置域名中转节点服务器成功。";
		showMSGBOX('',300,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtDomain").value="";
		
		var nowHTML=parent.document.getElementById("div_search_result").innerHTML;
		var showHTML = nowHTML.split("</table>")[0];
			
		nowHTML = showHTML + sNewItem;		
		parent.document.getElementById("div_search_result").innerHTML=nowHTML;				
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
    <td width="100" height="25" class="objTitle" ><span class="input_red_tips">*</span>服务器：</td>
    <td width="220">
		<select id="nodeSelect" style="width:250px">
 <?php
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_node Limit 100;"; 
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
				$name   	= mysql_result($result,$i,"name");
				$ip   		= mysql_result($result,$i,"ip");
				$unicom_ip	= mysql_result($result,$i,"unicom_ip");	
				
				$sFikIP = $ip;
				if(strlen($sFikIP)<=0) $sFikIP = $unicom_ip;		
					
				echo '<option value="'.$id.'">'.$name.' ('.$sFikIP.")</option>";				

			}
		}while(0);
		
		mysql_close($db_link);
	}			
 ?>
				</select>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="必须是添加到域名列表中的域名" ><span class="input_red_tips">*</span>网站域名：</td>
    <td>
		<input id="txtDomain" type="text" size="36" maxlength="64"  title="必须是添加到域名列表中的域名" /> 
	</td>
  </tr>  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="如果有多个中转服务器 IP，可用“;”间隔，如：211.155.23.66;234.34.55.123" ><span class="input_red_tips">*</span>中转服务器 IP：</td>
    <td>
		<input id="txtSrcIP" type="text" size="36" maxlength="64"  title="如果有多个中转服务器 IP，可用“;”间隔，如：211.155.23.66;234.34.55.123" />  
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" name="txtBackup" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ></textarea>
	</td>
  </tr>
  <tr>
    <td height="10" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddTrains"  type="submit" style="width:95px;height:28px;cursor:pointer;" id="btnAddTrains" value="保存" onclick="FikCdn_AddTrains();" /></center></td>
  </tr>
</table>
 <table width="480" border="0">
	<tr>
	<td><p style="padding-left:20px;">说明：<br />
	1. 如果有某个节点服务器不能访问到源站或者访问源站速度比较慢，可以<br />&nbsp;&nbsp;&nbsp;&nbsp;在此配置通过中转服务器访问源站；<br />
	2. 如果有多个中转服务器 IP 则可用“;”间隔填入多个IP，如：<br />&nbsp;&nbsp;&nbsp;&nbsp;211.155.23.66;234.34.55.123<br />
	3. 中转服务器添加成功后，系统会通过“<a href="javascript:void(0);" onclick="javescript:FikCdn_ToTaskList();" title="查看后台任务列表">后台任务</a>"方式修改节点服务器的<br />&nbsp;&nbsp;&nbsp;&nbsp;域名对应的源站为中转服务器的 IP；<br />
	</p></td>
	</tr>
</table>

<?php

include_once("./tail.php");
?>
