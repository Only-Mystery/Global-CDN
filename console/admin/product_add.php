<?php
include_once("head2.php");
?>
<script language="javascript" src="../js/calendar.js"></script>

<script type="text/javascript">	
function FikCdn_AddProduct(){
	var txtName	 	 =document.getElementById("txtName").value;	
	var txtDataFlow  =document.getElementById("txtDataFlow").value;
	var txtDomainNum =document.getElementById("txtDomainNum").value;
	var txtPrice     =document.getElementById("txtPrice").value;
	var grpSelect    =document.getElementById("grpSelect").value;
	var txtBackup    =document.getElementById("txtBackup").value;
	var txtCName     =document.getElementById("txtCName").value;
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
		
	if (grpSelect.length==0 ){ 
		var boxURL="msg.php?1.9&msg=请先增加一个服务器组。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		return false;
	}
		
	if (txtPrice.length==0 || isNaN(txtPrice)){ 
		var boxURL="msg.php?1.9&msg=请输入产品的价格。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtPrice").focus();
	  	return false;
	}
	
	var postURL="./ajax_product.php?mod=product&action=add";
	var postStr="grpid="+UrlEncode(grpSelect)+"&name=" + UrlEncode(txtName) + "&data_flow=" + UrlEncode(txtDataFlow) + "&is_online=" + statusSelect +
			     "&cname=" + UrlEncode(txtCName) + "&domain_num=" + UrlEncode(txtDomainNum) + "&price=" + UrlEncode(txtPrice) + "&backup=" + UrlEncode(txtBackup);				 
					 
	AjaxBasePost("product","add","POST",postURL,postStr);	
}

function FikCdn_AddProductResult(sResponse)
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

</script>

<table width="520" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
    <td width="130" height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>套餐名称：</td>
    <td width="320">
		<input id="txtName" type="text" size="36" maxlength="64"  />
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title=""><span class="input_red_tips">*</span>月度总流量(MB)：</td>
    <td>
		<input id="txtDataFlow" type="text" size="16" maxlength="12"  />
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>允许加速的域名个数：</td>
    <td>
		<input id="txtDomainNum" type="text" size="16" maxlength="6"  />
	</td>
  </tr>
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" ><span class="input_red_tips">*</span>绑定服务器组：</td>
    <td>
	<select id="grpSelect" name="grpSelect" style="width:255px">
 <?php	

 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		do
		{
			$sql = "SELECT * FROM fikcdn_group Limit 100;"; 
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
				$name   		= mysql_result($result,$i,"name");	
					
				echo '<option value="'.$id.'">'.$name."</option>";				

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
    <td height="25" class="objTitle"  title="" ><span class="input_red_tips">*</span>价格(元/月)：</td>
    <td>
		<input id="txtPrice" type="text" size="16" maxlength="10" />
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
    <td height="25" class="objTitle"  title="" >产品状态：</td>
    <td>
		<select id="statusSelect" style="width:80px">
			<option value="1" >上架</option>					
			<option value="0" >下架</option>
		</select>
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >套餐说明：</td>
    <td>
		<textarea id="txtBackup" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;"></textarea>
	</td>
  </tr>
  <tr>
    <td height="20" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnAddProduct"  type="submit" style="width:95px;height:28px" id="btnAddProduct" value="保存" style="cursor:pointer;" onClick="FikCdn_AddProduct();" /></center></td>
  </tr>
</table>

<?php

include_once("./tail.php");
?>
