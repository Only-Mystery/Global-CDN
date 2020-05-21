<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>数据库升级</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="description" content="Fikker CDN 后台管理系统" />
<meta name="keywords" content="Fikker CDN 后台管理系统" />
</head>
<body>
<?php
set_time_limit(0);

// 升级数据库	
include_once('../db/db.php');
include_once('../function/pub_function.php');
include_once('../function/define.php');
include_once("function_admin.php");


//是否只允许本地IP运行 
$client_ip = PubFunc_GetRemortIP();
if($FikConfig_TaskIsLocalRun)
{
	if($client_ip!="127.0.0.1")
	{
		echo "Forbidden";
		exit();
	}
}

echo "ip=".$client_ip."<br />";


$db_link = FikCDNDB_Connect();
if($db_link)
{	
	//删除字段
	$sql ="alter table realtime_list drop column AllUsedMemSize";
	$result = mysql_query($sql,$db_link);
	
	$sql ="alter table realtime_list drop column CacheUsedMemSize";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table realtime_list drop column NumOfCaches";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table realtime_list drop column NumOfMemberCaches";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table realtime_list drop column NumOfCachedSessions";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table realtime_list drop column NumOfPublicCaches";
	$result = mysql_query($sql,$db_link);			
	
	$sql ="alter table realtime_list drop column NumOfVisitorCaches";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table realtime_list drop column PublicCacheUsedMemSize";
	$result = mysql_query($sql,$db_link);			
	
	$sql ="alter table realtime_list drop column MemberCacheUsedMemSize";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table realtime_list drop column VisitorCacheUsedMemSize";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table realtime_list drop column VisitorCacheUsedMemSize";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table fikcdn_domain add offset float NOT NULL DEFAULT '1';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}

	$sql ="alter table fikcdn_domain add upoffset float NOT NULL DEFAULT '1';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql ="alter table fikcdn_recharge add order_id varchar(32);";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table fikcdn_recharge add ali_trade_no varchar(128);";
	$result = mysql_query($sql,$db_link);			
			
	$sql ="alter table fikcdn_recharge add sub_ip varchar(32);";
	$result = mysql_query($sql,$db_link);	
				
	$sql ="alter table fikcdn_recharge add status int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table fikcdn_product add max_bandwidth int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);		
	
	$sql ="alter table fikcdn_order add max_bandwidth int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);		
		
	$sql ="alter table fikcdn_buy add max_bandwidth int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
			
	$sql ="alter table fikcdn_node add allow_bandwidth int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);	
	
	//删除字段
	$sql ="alter table domain_stat_temp drop column Note";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="ALTER TABLE domain_stat_temp ENGINE=MEMORY";
	$result = mysql_query($sql,$db_link);
	
	$sql ="alter table domain_stat_group_day add time_for_max bigint(8) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
		
	$sql ="alter table domain_stat_group_day add bandwidth_down float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
			
	$sql ="alter table domain_stat_group_day add bandwidth_up float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table domain_stat_product_day add time_for_max bigint(8) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
		
	$sql ="alter table domain_stat_product_day add bandwidth_down float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
			
	$sql ="alter table domain_stat_product_day add bandwidth_up float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);		
		
	$sql ="alter table realtime_list_day add time_for_max bigint(8) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
		
	$sql ="alter table realtime_list_day add bandwidth_down float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
			
	$sql ="alter table realtime_list_day add bandwidth_up float DEFAULT '0'";
	$result = mysql_query($sql,$db_link);	
					
	$sql ="alter table fikcdn_product add dns_cname varchar(64);";
	$result = mysql_query($sql,$db_link);	
						
	$sql ="alter table fikcdn_buy add dns_cname varchar(64);";
	$result = mysql_query($sql,$db_link);	
						
	$sql ="alter table fikcdn_domain add down_begin_val int(4) DEFAULT '0';";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table fikcdn_domain add up_begin_val int(4) DEFAULT '0';";
	$result = mysql_query($sql,$db_link);	
	
	$sql ="alter table fikcdn_domain add SSLOpt int(4) DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
		
	$sql ="alter table fikcdn_domain add SSLCrtContent text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
		
	$sql ="alter table fikcdn_domain add SSLKeyContent text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
			
	$sql ="alter table fikcdn_domain add SSLExtraParams text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
							
	$sql ="alter table fikcdn_domain add UpsSSLOpt int(4) DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
										
	$sql ="alter table fikcdn_domain add UpsSSLCrtContent text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
	
	$sql ="alter table fikcdn_domain add UpsSSLKeyContent text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
									
	$sql ="alter table fikcdn_domain add UpsSSLExtraParams text CHARACTER SET utf8 COLLATE utf8_general_ci NULL;";
	$result = mysql_query($sql,$db_link);
															
	echo '数据库表升级成功。';		
}
else
{
	echo '数据库表升级失败，不能连接到数据库。';
}

?>
</body>
</html>