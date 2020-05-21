<?php
include_once("./head2.php");
include_once('../function/define.php');
?>
<script type="text/javascript">
function FikCdn_AddNodeResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nGrpid = json["grpid"];
		var nNodeId = json["node_id"];
		var sName = json["name"];
		var sIp = json["ip"];
		var sUnicomIp = json["unicom_ip"];
		var sGrpName = json["grp_name"];
		var sShowVersion = json["show_version"];
		var sStatus = json["status"];
		
		var sNewItem  = '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)">';
		sNewItem	  +=   	'<td>'+nNodeId+'</td>';
		sNewItem 	  += 	'<td align="left"><a href="login_fikker.php?mod=logging&action=redirect&id='+nNodeId+'" title="进入 Fikker 管理后台" target="_blank" >'+sIp+'</a></td>';
		sNewItem      +=   	'<td align="left"><a href="login_fikker.php?mod=logging&action=redirect&id='+nNodeId+'" title="进入 Fikker 管理后台" target="_blank" >'+sUnicomIp+'</a></td>';								
		sNewItem	  +=   	'<td>'+sGrpName+'</td>';
		sNewItem	  +=   	'<td>'+sName+'</td>';				
		sNewItem	  +=   	'<td>'+sShowVersion+'</td>';
		sNewItem	  += 	'<td><span id="span_is_close_'+nNodeId+'">启用中</span></td>';		
		sNewItem	  +=   	'<td><a href="stat_node_bandwidth.php?id='+nNodeId+'" onclick="javescript:FikCdn_ViewNodeStat('+nNodeId+','+nGrpid+');" title="查看服务器流量统计数据">流量统计</a>&nbsp;';
		sNewItem	  +=    '<a href="####" onclick="javescript:FikCdn_ModifyNodeBox('+nNodeId+','+nGrpid+');" title="修改服务器信息">修改</a>&nbsp;';
		sNewItem 	  +=	'<a href="####" onclick="javescript:FikCdn_ReConfigHostBox('+nNodeId+','+nGrpid+');" title="同步所有域名到此服务器的主机管理列表">同步域名</a>&nbsp;&nbsp;<span id="span_is_close_href_'+nNodeId+'">';			
		sNewItem 	  += 	'<a href="####" onclick="javescript:FikCdn_StopNodeBox('+nNodeId+','+nGrpid+');" title="停止节点">停止</a>';
		sNewItem 	  +=    '</span>&nbsp;&nbsp;<a href="####" onclick="javescript:FikCdn_NodeDeleteBox('+nNodeId+','+nGrpid+');" title="删除服务器">删除</a>&nbsp;';
		sNewItem      +=    '<a href="####" onclick="javescript:FikCdn_HostDelete('+nNodeId+','+nGrpid+');" title="删除此服务器所有域名">删除域名</a></td>';
		sNewItem      +=    '</tr></table>';
						
		var boxURL="msg.php?1.8";
		showMSGBOX('',300,100,BT,BL,120,boxURL,'操作提示:');
		
		var nowHTML=parent.document.getElementById("div_search_result").innerHTML;
		var showHTML = nowHTML.split("</table>")[0];
			
		nowHTML = showHTML + sNewItem;		
		parent.document.getElementById("div_search_result").innerHTML=nowHTML;		
		return;
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

function FikCdn_ContinueAdd(){
	document.getElementById("txtIP").value="";
	document.getElementById("txtUnicomIP").value="";
	document.getElementById("txtNodeName").value="";
	document.getElementById("txtBackup").value="";
	document.getElementById("txtNodeName").focus();
}	

function FikCdn_ReLoadNodeList(){	
	parent.window.location.href="node_list.php?page=10000";
}

function FikCdn_AddNode(){
	var grpSelect    =document.getElementById("grpSelect").value;
	var txtIP        =document.getElementById("txtIP").value;
	var txtUnicomIP  =document.getElementById("txtUnicomIP").value;
	var txtNodeName  =document.getElementById("txtNodeName").value;
	var txtAllowBW   =document.getElementById("txtAllowBW").value;
	var txtAdminPort =document.getElementById("txtAdminPort").value;
	var txtPasswd    =document.getElementById("txtPasswd").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var nodeSelect2  =document.getElementById("nodeSelect2").value;
		
	if (grpSelect.length==0 ){ 
		var boxURL="msg.php?1.3";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		return false;
	}
	
	if (txtNodeName.length==0 ){
		var boxURL="msg.php?1.4";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtNodeName").focus();
	  	return false;
	}
		
	if (txtIP.length==0 && txtUnicomIP==0){
		var boxURL="msg.php?1.5";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
		document.getElementById("txtIP").focus();		
	  	return false;
	}
		
	if (txtAdminPort.length==0 ){ 		
		var boxURL="msg.php?1.6";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');		
		document.getElementById("txtAdminPort").focus();
	  	return false;
	}
		
	if (txtPasswd.length==0 ){ 
		var boxURL="msg.php?1.7";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("txtPasswd").focus();
	  	return false;
	}
	
	var postURL="./ajax_admin.php?mod=fiknode&action=add";
	var postStr="grpid="+UrlEncode(grpSelect)+ "&sync_node_id=" + nodeSelect2+"&ip=" + UrlEncode(txtIP) + "&unicom_ip="+UrlEncode(txtUnicomIP) + "&name=" + UrlEncode(txtNodeName) + "&allowbw=" + UrlEncode(txtAllowBW) + "&adminport=" + UrlEncode(txtAdminPort) + "&passwd=" + UrlEncode(txtPasswd) + "&backup=" + UrlEncode(txtBackup);					 
					 
	AjaxBasePost("fiknode","add","POST",postURL,postStr);	
}

function FikCdn_AddGroupBox(){
	msgboxOBJ=document.getElementById("msgbox"); 
	msgboxOBJ.style.display="block";	
	document.getElementById("txtGrpName").value="";
	document.getElementById("txtGrpName").focus();
}

var onfocus_count = 0;
function check_onfocus(editID){
	if(onfocus_count == 0)
	{
		var obj = document.getElementById(editID);
		obj.value = "";
		onfocus_count ++;
	}
	return;
}
</script>
<style>
html, body {
	overflow-x: hidden;
	overflow-y: hidden;
}
body {
	margin-left: 0px;
	margin-top: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
}
</style>

<table width="450" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="100" height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>服务器组：</td>
    <td width="320">
<select id="grpSelect" name="grpSelect" style="width:216px">
<?php
 	$db_link = FikCDNDB_Connect();
	if($db_link){
		$sql = "SELECT * FROM fikcdn_group;"; 
		$result = mysql_query($sql,$db_link);
		if($result){
			$row_count=mysql_num_rows($result);
			if($row_count>0){
				for($i=0;$i<$row_count;$i++){
					$this_gid  			= mysql_result($result,$i,"id");	
					$this_grp_name   	= mysql_result($result,$i,"name");	
						
					echo '<option value="'.$this_gid.'">'.$this_grp_name."</option>";
				}
			}
		}
	}			
?>
				</select>&nbsp;&nbsp;<a href="#" onclick="javescript:FikCdn_AddGroupBox();" title="创建一个新的服务器组">创建服务器组</a> 
		</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>  
 <tr>
    <td height="25" class="objTitle" title="同步所选择的服务器中所有页面缓存规则，拒绝缓存规则，转向缓存规则到新添加的服务器" >缓存规则同步：</td>
    <td width="220">
		<select id="nodeSelect2" style="width:216px" title="同步所选择的服务器中所有页面缓存规则，拒绝缓存规则，转向缓存规则到新添加的服务器" >
		<option value="" selected="selected">不同步缓存规则</option>;
 <?php
	if($db_link)
	{
		$sql = "SELECT * FROM fikcdn_node;";
		$result = mysql_query($sql,$db_link);	
		if($result)
		{			
			$row_count = mysql_num_rows($result);
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
				
				echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';				
			}
		}
		
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
    <td height="25" class="objTitle" title="如：广州双线G口、长沙电信、上海联通"><span class="input_red_tips">*</span>服务器名称：</td>
    <td>
		<input id="txtNodeName" type="text" size="30" maxlength="128" title="如：广州双线G口、长沙电信、上海联通" onfocus="check_onfocus('txtName');" />&nbsp;<span class="input_tips_txt" id="tipsName">如：广州双线G口</span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。" ><span class="input_red_tips">*</span>电信 IP：</td>
    <td>
		<input id="txtIP" type="text" size="30" maxlength="64" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。"  /> <span class="input_tips_txt" id="tipsIP"></span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。" >联通 IP：</td>
    <td><input id="txtUnicomIP" type="text" size="30" maxlength="64" title="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。" /> <span class="input_tips_txt4" id="tipsUnicomIP"></span></td>
  </tr>
   <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="Fikker 节点服务器后台管理端口" ><span class="input_red_tips"></span>服务器带宽：</td>
    <td>
		<input id="txtAllowBW" type="text" size="16" maxlength="10" value="100" title="机房分配给此服务器的带宽，作为服务器运行参考" />  <span class="input_tips_txt" id="tipsAllowBW" name="tipsAllowBW" > Mbps</span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle"  title="Fikker 节点服务器后台管理端口" ><span class="input_red_tips">*</span>管理端口：</td>
    <td>
		<input id="txtAdminPort" type="text" size="16" maxlength="32" value="6780" title="Fikker 节点服务器后台管理端口" />  <span class="input_tips_txt" id="tipsAdminPort" name="tipsAdminPort" ></span> 
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="Fikker 节点服务器后台管理员密码" ><span class="input_red_tips">*</span>管理员密码：</td>
    <td>
		<input id="txtPasswd" type="password" size="30" maxlength="64" title="Fikker 节点服务器后台管理员密码"/>  <span class="input_tips_txt" id="tipsPasswd" name="tipsPasswd" ></span>
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;"></textarea>
	</td>
  </tr>
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddNode"  type="submit" style="width:95px;height:28px" id="btnAddNode" value="保存" style="cursor:pointer;" onClick="FikCdn_AddNode();" /></center></td>
  </tr>
</table>



<style type="text/css">
#msgbox{Z-INDEX: 1; clear:both;display:none;WIDTH:309px;POSITION: absolute; TOP: 30px;left: 130px;}
#msgbox a:link,a:hover,a:active,a:visited{color: #1875C6; text-decoration: none;}
#msgtitle{font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif; font-size:12px; color: #666666; height:28px; margin-left:5px; float:left; width:282px;}
#group_item{font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif; font-size:12px; font-weight:bold;color: #666666; margin-left:25px; width:60px; float:left; padding-top:6px;}
#group_item2{font-family: verdana, Tahoma, Arial, "微软雅黑", "宋体", sans-serif; font-size:12px; font-weight:bold;color: #666666; margin-left:0px; margin-right:10px;width:160px; float:left; padding-top:6px;}

.b1,.b2,.b3,.b4,.b1b,.b2b,.b3b,.b4b,.b{display:block;overflow:hidden;}
.b1,.b2,.b3,.b1b,.b2b,.b3b{height:1px;}
.b2,.b3,.b4,.b2b,.b3b,.b4b,.b{border-left:1px solid #83B6E7;border-right:1px solid #83B6E7;}
.b1,.b1b{margin:0 5px;background:#83B6E7;}
.b2,.b2b{margin:0 3px;border-width:2px;}
.b3,.b3b{margin:0 2px;}
.b4,.b4b{height:2px;margin:0 1px;}
.d1{background:#FFFFFF;}
.k {height:120px;}
</style>

<script type="text/javascript">	
//关闭层
function closeMyMSGBOX(){
	msgboxOBJ=document.getElementById("msgbox"); 
	
	if(typeof(msgboxOBJ)!="undefined"){
		msgboxOBJ.style.display="none";
	}
	
	if(typeof(document.getElementById(txtGrpName))!="undefined"){
		//document.getElementById("txtGrpName").focus();
	}
}

function FikCdn_GroupAdd(){
	var txtGrpName    =document.getElementById("txtGrpName").value;
		
	if (txtGrpName.length==0 ){
		var boxURL="msg.php?1.1";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("txtGrpName").focus();	
	  	return false;
	}

	var objSelect = document.getElementById("grpSelect"); 
	for(var i=0;i<objSelect.options.length;i++){
		if(objSelect.options[i].text == txtGrpName){
			objSelect.options[i].selected=true;
			var boxURL="msg.php?1.2";
			showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
			document.getElementById("txtGrpName").focus();
			return false;
		}
	} 
		
	var postURL="./ajax_admin.php?mod=fikgroup&action=add";
	var postStr="grpname="+ UrlEncode(txtGrpName); 
					 
	AjaxBasePost("fikgroup","add","POST",postURL,postStr);		
}

function FikCdn_AddGroupResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var msgboxOBJ=document.getElementById("msgbox"); 
		msgboxOBJ.style.display="none";
		
		var objSelect = document.getElementById("grpSelect"); 
		
		var id = json["id"];
		var name = json["name"];
		
		var varItem = new Option(name, id);      
		objSelect.options.add(varItem);     	
		for(var i=0;i<objSelect.options.length;i++){
			if(objSelect.options[i].text == name){
				objSelect.options[i].selected=true;		
				return;
			}
		} 
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

</script>

<div id="msgbox">  
	<b class="b1"></b><b class="b2 d1"></b><b class="b3 d1"></b><b class="b4 d1"></b>
	<div class="b d1 k">
		<div>
		<div id="msgtitle">创建服务器组：</div>
		<div style="margin-right:3px; height:28px;"><a href="javascript:closeMyMSGBOX()"><span style="font-family:宋体;font-size:14px;">×</span></a></div>
		</div>
		<div style="width:100%; height:36px;"><div id="group_item">组名称：</div><div style="margin-top:0px; margin-right:10px;height:30px; padding-top:0px;"><input id="txtGrpName" name="txtGrpName" type="text" size="20" maxlength="64"  /></div></div>
		<!--<div style="width:100%; height:36px;"><div id="group_item"></div><div id="group_item2" ><input id="group_transit" type="checkbox" class="checkbox" />&nbsp;中转服务器组</div></div>-->
		<div style="padding-top:10px;padding-left:120px; margin:0px;">
		<input name="btnAddGroup"  type="submit" style="width:85px;height:28px;cursor:pointer;" id="btnAddGroup" value="添加" onClick="FikCdn_GroupAdd();" /> 
		</div>
	</div>
	<b class="b4b d1"></b><b class="b3b d1"></b><b class="b2b d1"></b><b class="b1b"></b>
</div>


<?php

include_once("./tail.php");
?>
