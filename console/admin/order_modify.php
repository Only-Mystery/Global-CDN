<?php
include_once("head2.php");
?>
<script language="javascript" src="../js/calendar.js"></script>
<script type="text/javascript">
var __nOrderId;
function FikCdn_OrderModifyBox(order_id){
	var txtDomainNum = document.getElementById("txtDomainNum").value;
	var txtDataFlow = document.getElementById("txtDataFlow").value;
	var txtPrice = document.getElementById("txtPrice").value;
	var txtMonth = document.getElementById("txtMonth").value;
	var txtBackup = document.getElementById("txtBackup").value;
	__nOrderId = order_id;
	if (!isNumberFormat(txtDomainNum) || txtDomainNum<=0 || txtDomainNum.length==0){
		var boxURL="msg.php?1.9&msg=请输入可加速域名个数。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');			
		document.getElementById("txtDomainNum").focus();
	  	return false;
	}
	
	if (!isNumberFormat(txtDataFlow) || txtDataFlow.length==0 || txtDataFlow<=0){
		var boxURL="msg.php?1.9&msg=请输入月度流量数量。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');			
		document.getElementById("txtDataFlow").focus();
	  	return false;
	}
	
	if (isNaN(txtPrice) || txtPrice<=0 || txtPrice.length==0){
		var boxURL="msg.php?1.9&msg=请输入套餐价格。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtPrice").focus();
	  	return false;
	}

	if (!isNumberFormat(txtMonth) || txtMonth<=0 || txtMonth.length==0){
		var boxURL="msg.php?1.9&msg=请输入购买月份数。";
		showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');				
		document.getElementById("txtMonth").focus();
	  	return false;
	}
	
	var total_money = parseInt(txtPrice)*(parseInt(txtMonth));
	document.getElementById("tipsTotalMoney").innerHTML=total_money;
					
	var boxURL="msg.php?4.6";
	showMSGBOX('',350,100,BT,BL,120,boxURL,'操作提示:');
}

function FikCdn_OrderModify(){
	var txtDomainNum = document.getElementById("txtDomainNum").value;
	var txtDataFlow = document.getElementById("txtDataFlow").value;
	var txtPrice = document.getElementById("txtPrice").value;
	var txtMonth = document.getElementById("txtMonth").value;
	var txtBackup = document.getElementById("txtBackup").value;
	
	if (!isNumberFormat(txtDomainNum) || txtDomainNum<=0 || txtDomainNum.length==0){	
		document.getElementById("txtDomainNum").focus();
	  	return false;
	}
	
	if (!isNumberFormat(txtDataFlow) || txtDataFlow.length==0 || txtDataFlow<=0){	
		document.getElementById("txtDataFlow").focus();
	  	return false;
	}
	
	if (isNaN(txtPrice) || txtPrice<=0 || txtPrice.length==0){		
		document.getElementById("txtPrice").focus();
	  	return false;
	}

	if (!isNumberFormat(txtMonth) || txtMonth<=0 || txtMonth.length==0){		
		document.getElementById("txtMonth").focus();
	  	return false;
	}
	
	var total_money = parseInt(txtPrice)*(parseInt(txtMonth));
	document.getElementById("tipsTotalMoney").innerHTML=total_money;
						
	var postURL="./ajax_buy.php?mod=order&action=modify";
	var postStr="order_id="+UrlEncode(__nOrderId)+"&domain_num="+UrlEncode(txtDomainNum)+"&data_flow="+UrlEncode(txtDataFlow)+
		"&price="+UrlEncode(txtPrice)+"&month="+UrlEncode(txtMonth)+"&note="+UrlEncode(txtBackup);
					
	AjaxBasePost("order","modify","POST",postURL,postStr);
}

function FikCdn_ModifyOrderResult(sResponse)
{
	var json = eval("("+sResponse+")");
	if(json["Return"]=="True"){
		var nOrderID = json["order_id"];
		parent.window.location.href = "./order_list.php?order_id="+nOrderID; 
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

function PriceValueChange(){
	var txtPrice = document.getElementById("txtPrice").value;
	var txtMonth = document.getElementById("txtMonth").value;
	
	if (isNaN(txtPrice)||txtPrice.length==0|| txtPrice<0){
	  	document.getElementById("tipsPrice").innerHTML="请输入套餐价格";
		document.getElementById("txtPrice").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsPrice").innerHTML="";
	}
	
	if (!isNumberFormat(txtMonth) ||txtMonth.length==0 || txtMonth<=0){
	  	document.getElementById("tipsMonth").innerHTML="请输入购买月份数";
		document.getElementById("txtMonth").focus();
	  	return false;
	}
	else{
		document.getElementById("tipsMonth").innerHTML="";
	}
	
	var total_money = parseInt(txtPrice)*(parseInt(txtMonth));
	document.getElementById("tipsTotalMoney").innerHTML=total_money;
}

</script>
<?php
	$order_id = isset($_GET['order_id'])?$_GET['order_id']:'';
 	$admin_username 	=$_SESSION['fikcdn_admin_username'];
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$order_id = mysql_real_escape_string($order_id);
		$admin_username = mysql_real_escape_string($admin_username);
		
		$sql = "SELECT * FROM fikcdn_order WHERE id='$order_id' ;"; 
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			mysql_close($db_link);
			exit();
		}
	
		$username = mysql_result($result,0,"username");
		$product_id = mysql_result($result,0,"product_id");
		$buy_time = mysql_result($result,0,"buy_time");
		$status = mysql_result($result,0,"status");
		$auto_renew = mysql_result($result,0,"auto_renew");
		$price 		= mysql_result($result,0,"price");
		$month = mysql_result($result,0,"month");
		$type = mysql_result($result,0,"type");
		$data_flow	= mysql_result($result,0,"data_flow");	
		$domain_num = mysql_result($result,0,"domain_num");
		$note	 	= mysql_result($result,0,"note");
		$buy_id	 	= mysql_result($result,0,"buy_id");
		$frist_month_money =  mysql_result($result,0,"frist_month_money");
		
		$total_money = $price*$month;
		
		$sql = "SELECT * FROM fikcdn_product WHERE id='$product_id' ;"; 
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			mysql_close($db_link);		
			exit();
		}
		
		$product_name = mysql_result($result,0,"name");
		$product_note= mysql_result($result,0,"note");	
		
		$sql = "SELECT * FROM fikcdn_client WHERE username='$username' ;"; 
		$result = mysql_query($sql,$db_link);
		if(!$result || mysql_num_rows($result)<=0)
		{
			mysql_close($db_link);		
			exit();
		}
		$client_money = mysql_result($result,0,"money");
		$enable_login = mysql_result($result,0,"enable_login");
		
		if($type==$PubDefine_BuyTypeRenew)
		{
			$sql = "SELECT * FROM fikcdn_buy WHERE id='$buy_id' ;"; 
			$result = mysql_query($sql,$db_link);
			if(!$result || mysql_num_rows($result)<=0)
			{
				mysql_close($db_link);		
				exit();
			}
			$buy_begin_time = mysql_result($result,0,"begin_time");
			$buy_end_time = mysql_result($result,0,"end_time");
		}	
		
		mysql_close($db_link);						
	}			
 ?>    
<table width="500" border="0" align="center" cellpadding="0" cellspacing="0" class="normal">
  <tr>
  <input id="txtId" name="txtId" type="hidden"  value="<?php echo $uid; ?>" /> 
    <td width="120" height="25" class="objTitle" title="" >套餐名称：</td>
    <td width="220">
		<label><?php echo $product_name; ?></label>
	</td>
  </tr>
 
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >可加速的域名个数：</td>
    <td>
		<input id="txtDomainNum" type="text" size="16" maxlength="10" value="<?php echo $domain_num; ?>" />
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >月度总流量(MB)：</td>
    <td>
		<input id="txtDataFlow" type="text" size="16" maxlength="16" value="<?php echo $data_flow; ?>" />
	</td>
  </tr>
    
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >套餐说明：</td>
    <td>
		<label><?php echo $product_note; ?></label>
	</td>
  </tr>
  
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >客户账户余额：</td>
    <td>
		<label><?php echo $client_money; ?></label>
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
    <td height="25" class="objTitle" title="" >购买月份数：</td>
    <td>
		<input id="txtMonth" type="text" size="16" maxlength="10" value="<?php echo $month; ?>" />
	</td>
  </tr>
     
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >支付总金额：</td>
    <td>	  
		<span id="tipsTotalMoney"><?php echo $total_money; ?></span>
	</td>
  </tr>
  	 
  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >购买类型：</td>
    <td>	  
		<label><?php echo $PubDefine_BuyTypeStr[$type]; ?></label>
	</td>
  </tr>

  <tr>
    <td height="6" colspan="2"></td>
  </tr>
  <tr>
    <td height="25" class="objTitle" title="" >备注：</td>
    <td>
		<textarea id="txtBackup" name="txtBackup" maxlength="128" style="width:320px;height:46px;font-size:14px;border:1px solid #94C7E7;overflow:auto;" ><?php echo $note; ?></textarea>
	</td>
  </tr>
  <tr>
    <td height="15" colspan="2"></td>
  </tr>
  <tr>
    <td colspan="2">
	    <center><input name="btnModifyOrder"  type="submit" style="width:95px;height:28px" id="btnModifyOrder" value="保存" style="cursor:pointer;" onClick="FikCdn_OrderModifyBox(<?php echo $order_id; ?>);" /></center></td>
  </tr>
</table>

<script type="text/javascript">
//firefox下检测状态改变只能用oninput,且需要用addEventListener来注册事件。
if(/msie/i.test(navigator.userAgent))    //ie浏览器 
{
	document.getElementById('txtPrice').onpropertychange=PriceValueChange;
	document.getElementById('txtMonth').onpropertychange=PriceValueChange;
} 
else 
{
	//非ie浏览器，比如Firefox 
	document.getElementById('txtPrice').addEventListener("input",PriceValueChange,false);
	document.getElementById('txtMonth').addEventListener("input",PriceValueChange,false); 
} 
</script>

<?php

include_once("./tail.php");
?>
