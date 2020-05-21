<?php
include_once("./head2.php");
?>
<script type="text/javascript">
function FikCdn_AddRCache(){
	var groupSelect=document.getElementById("groupSelect").value;
	var Url=document.getElementById("Url").value;
	var IcaseOBJ=document.getElementsByName("Icase");
		for(i=0;i<IcaseOBJ.length;i++){
			if(IcaseOBJ[i].checked==true)var Icase=IcaseOBJ[i].value;
		}
	var RulesOBJ=document.getElementsByName("Rules");
		for(i=0;i<RulesOBJ.length;i++){
			if(RulesOBJ[i].checked==true)var Rules=RulesOBJ[i].value;
		}
	var OlimitOBJ=document.getElementsByName("Olimit");
		for(i=0;i<OlimitOBJ.length;i++){
			if(OlimitOBJ[i].checked==true)var Olimit=OlimitOBJ[i].value;
		}
	var CacheLocationObj=document.getElementsByName("CacheLocation");
		for(i=0;i<CacheLocationObj.length;i++){
			if(CacheLocationObj[i].checked==true) var CacheLocation=CacheLocationObj[i].value;
		}
	var Note=document.getElementById("Note").value;
	if (groupSelect.length==0){ 
		var boxURL="msg.php?1.9&msg=目前没有服务器组，不能添加拒绝缓存。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
	  	return false;
	}
	
	if (Url.length==0 || onfocus_count==0){ 
		var boxURL="msg.php?1.9&msg=请输入拒绝缓存地址 URL。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');	
		document.getElementById("Url").focus();
	  	return false;
	}
	
	var postURL="./ajax_cache_rules.php?mod=rcache&action=add";
	var postStr="gid="+UrlEncode(groupSelect)+"&Url=" + UrlEncode(Url) + "&Icase=" + UrlEncode(Icase) + "&Rules="+UrlEncode(Rules)
         +"&Olimit=" + UrlEncode(Olimit) +"&CacheLocation=" + UrlEncode(CacheLocation) +"&Note=" + UrlEncode(Note);
	AjaxBasePost("rcache","add","POST",postURL,postStr);
}

function FikCdn_AddRCacheResult(sResponse){
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var boxURL="msg.php?6.1";
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
var onfocus_count = 0;
function check_onfocus(){
	if(onfocus_count == 0)
	{
		var obj = document.getElementById("Url");
		obj.value = "";
		onfocus_count ++;
	}
	return;
}
</script>
<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="130" height="25" class="objTitle" title="将此页面缓存规则添加到组内所有服务器中" >服务器组：</td>
    <td width="220">
		<select id="groupSelect" style="width:165px" title="将此页面缓存规则添加到此组内所有服务器中">
 <?php	
 	$node_id 	= isset($_GET['node_id'])?$_GET['node_id']:'';
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_node WHERE id=$node_id;"; 
			$result = mysql_query($sql,$db_link);
			if($result && mysql_num_rows($result)>0)
			{
				$group_id 		= mysql_result($result,0,"groupid");
			}
					
			$sql = "SELECT * FROM fikcdn_group;";
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
				$this_group_id  = mysql_result($result,$i,"id");	
				$group_name  	= mysql_result($result,$i,"name");				
				
				if($group_id==$this_group_id)
				{
					echo '<option value="'.$this_group_id.'" selected="selected">'.$group_name."</option>";
				}
				else
				{
					echo '<option value="'.$this_group_id.'">'.$group_name."</option>";
				}
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
    <td width="130" height="25" class="objTitle" title="输入拒绝缓存的页面地址 URL 或匹配表达式，所有匹配的页面将会被拒绝缓存" >拒绝缓存地址URL：</td>
    <td width="320"><input name="Url" type="text" class="inputText" id="Url" style="width:330px;height:16px" onfocus="check_onfocus();" value="输入拒绝缓存的页面地址 URL 或匹配表达式" title="修改拒绝缓存的页面地址 URL 或匹配表达式，所有匹配的页面将会被拒绝缓存" /></td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="匹配拒绝缓存的页面地址 URL 时是否忽略大小写" >是否忽略大小写：</td>
    <td>
	  <input name="Icase" type="radio" class="radio" id="Icase" value="1" checked="checked" />忽略&nbsp;&nbsp;
	  <input name="Icase" type="radio" class="radio" id="Icase" value="0" />不忽略
	</td>
  
 <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="正确选择页面地址 URL 匹配规则，匹配成功的页面将会被拒绝缓存，详情参看【相关使用帮助】" >URL匹配规则：</td>
    <td>
	  <input name="Rules" type="radio" class="radio" id="Rules" value="0" checked="checked" />通配符&nbsp;&nbsp;
      <input name="Rules" type="radio" class="radio" id="Rules" value="1" />正则表达式&nbsp;&nbsp;
      <input name="Rules" type="radio" class="radio" id="Rules" value="2" />精确匹配
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="指定拒绝缓存页面的开放权限，详情参看【相关使用帮助】" >拒绝开放权限：</td>
    <td>
	  <input name="Olimit" type="radio" class="radio" id="Olimit" value="0" checked="checked" />公共缓存&nbsp;&nbsp;
      <input name="Olimit" type="radio" class="radio" id="Olimit" value="1" />会员缓存&nbsp;&nbsp;
      <input name="Olimit" type="radio" class="radio" id="Olimit" value="2" />游客缓存
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>

 <tr>
    <td height="25" class="objTitle" title="指定拒绝缓存的位置，双缓存=内存缓存+硬盘缓存，详情参看【相关使用帮助】" >拒绝缓存位置：</td>
    <td>
	  <input name="CacheLocation" type="radio" class="radio" id="CacheLocation" value="0" checked="checked" title="双缓存=内存缓存+硬盘缓存，详情参看【相关使用帮助】" />拒绝双缓存&nbsp;&nbsp;
      <input name="CacheLocation" type="radio" class="radio" id="CacheLocation" value="1" title="双缓存=内存缓存+硬盘缓存，详情参看【相关使用帮助】" />只拒绝硬盘缓存
	</td>
  </tr>
      
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea name="Note" class="inputText" id="Note" style="width:330px;height:66px;font-size:14px;border:1px solid #94C7E7;overflow:auto;"></textarea>
	</td>
  </tr>
  <tr>
    <td height="20" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddRCache"  type="submit" style="width:105px;height:28px" id="btnAddRCache" value="添加拒绝缓存" style="cursor:pointer;" onclick="FikCdn_AddRCache();" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
