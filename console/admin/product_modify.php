<?php
include_once("./head2.php");
?>
<script type="text/javascript">	
function FikCdn_ModifyProduct(pid){
	var txtName	 	 =document.getElementById("txtName").value;	
	var txtDataFlow  =document.getElementById("txtDataFlow").value;
	var txtDomainNum =document.getElementById("txtDomainNum").value;
	var txtPrice     =document.getElementById("txtPrice").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var txtCName    =document.getElementById("txtCName").value;
	var statusSelect =document.getElementById("statusSelect").value;
	
	if (txtName.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请输入产品套餐的名称。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtName").focus();
	  	return false;
	}
	
	if (txtDataFlow.length==0 || isNaN(txtDataFlow)){ 
		var boxURL="msg.php?1.9&msg=请输入加速的月度总流量。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtDataFlow").focus();
	  	return false;
	}	
	
	if (txtDomainNum.length==0 || isNaN(txtDomainNum)){ 
		var boxURL="msg.php?1.9&msg=请输入加速的域名个数。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtDomainNum").focus();
	  	return false;
	}
		
	if (txtPrice.length==0 || isNaN(txtPrice)){ 
		var boxURL="msg.php?1.9&msg=请输入产品的价格。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtPrice").focus();
	  	return false;
	}
	
	var postURL="./ajax_product.php?mod=product&action=modify";
	var postStr="id="+pid+"&name=" + UrlEncode(txtName) + "&data_flow=" + UrlEncode(txtDataFlow) + "&is_online="+statusSelect + "&cname=" + UrlEncode(txtCName) +
			     "&domain_num=" + UrlEncode(txtDomainNum) + "&price=" + UrlEncode(txtPrice) + "&backup=" + UrlEncode(txtBackup);				 
					 
	AjaxBasePost("product","modify","POST",postURL,postStr);	
}


function FikCdn_ModifyProductResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		parent.window.location.href = "./product_list.php";
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

function fikcnd_addgroup()
{
	msgboxOBJ=document.getElementById("msgbox"); 
	msgboxOBJ.style.display="block";	
	document.getElementById("txtGrpName").value="";
}

</script>

<?php
	$product_id 	= isset($_GET['id'])?$_GET['id']:'';
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$sql = "SELECT * FROM fikcdn_product WHERE id=$product_id";
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			break;
		}
		
		$id  			= mysql_result($result,0,"id");
		$name  			= mysql_result($result,0,"name");
		$price   		= mysql_result($result,0,"price");	
		$data_flow   	= mysql_result($result,0,"data_flow");
		$domain_num		= mysql_result($result,0,"domain_num");
		$is_online  	= mysql_result($result,0,"is_online");
		$buy_time   	= mysql_result($result,0,"begin_time");
		$buy_ip 		= mysql_result($result,0,"is_checks");
		$group_id		= mysql_result($result,0,"group_id");
		$note   		= mysql_result($result,0,"note");
		$dns_cname 		= mysql_result($result,$i,"dns_cname");	
		
		$sql = "SELECT * FROM fikcdn_group WHERE id='$group_id'";
		$result2 = mysql_query($sql,$db_link);
		if($result2 && mysql_num_rows($result2)>0)
		{
			$grp_name  = mysql_result($result2,0,"name");
		}
	
		mysql_close($db_link);
	}	
 ?>	
<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
  <input id="txtId" name="txtId" type="hidden"  value="<?php echo $uid; ?>" /> 
    <td width="120" height="25" class="objTitle" title="" >套餐名称：</td>
    <td width="220">
		<input id="txtName" type="text" size="36" maxlength="64" value="<?php echo $name; ?>"  />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >月度总流量(MB)：</td>
    <td>
		<input id="txtDataFlow" type="text" size="16" maxlength="16" value="<?php echo $data_flow; ?>"  />
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >加速的域名个数：</td>
    <td>
		<input id="txtDomainNum" type="text" size="16" maxlength="8" value="<?php echo $domain_num; ?>"  />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >所属服务器组：</td>
    <td>
		<label><?php echo $grp_name; ?></label>
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >套餐价格：</td>
    <td>
		<input id="txtPrice" type="text" size="16" maxlength="10" value="<?php echo $price; ?>" />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >CNAME：</td>
    <td>
		<input id="txtCName" type="text" size="36" maxlength="64" value="<?php echo $dns_cname; ?>" />
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >产品状态：</td>
    <td>
		<select id="statusSelect" style="width:80px">
			<option value="1" <?php if($is_online) echo 'selected="selected";' ?>>上架</option>					
			<option value="0" <?php if($is_online==0) echo 'selected="selected";' ?>>下架</option>
		</select>
	</td>
  </tr>
     
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >套餐说明：</td>
    <td>	  
		<textarea id="txtBackup" name="txtBackup" maxlength="128" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php echo $note; ?></textarea>
	</td>
  </tr>
  	 
   <tr>
    <td height="20" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnModifyOrder"  type="submit" style="width:95px;height:28px" id="btnModifyOrder" value="保存" style="cursor:pointer;" onClick="FikCdn_ModifyProduct(<?php echo $product_id; ?>);" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
