<?php
set_time_limit(0);
// 升级数据库	
include_once('../db/db.php');
include_once('../function/pub_function.php');
include_once('../function/define.php');
include_once("function_admin.php");

$db_link = FikCDNDB_Connect();
if($db_link)
{		
	$sql ="alter table domain_stat_temp add UserConnections int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
    $sql ="alter table domain_stat_temp add UpstreamConnections int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
    $sql ="alter table domain_stat_temp add RequestCount_increase int(4) DEFAULT '0'";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
		
	$sql ="alter table fikcdn_task add result_str text CHARACTER SET utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
		
	$sql ="alter table fikcdn_upstream add upstream2 varchar(128);";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
			
	$sql ="CREATE TABLE `fikcdn_params` (                                     
                   `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                      
                   `name` varchar(64) NOT NULL,                                                                   
                   `value` varchar(128) NOT NULL,
                   `time` bigint(8) NOT NULL,                                  
                   `note` varchar(128) DEFAULT NULL,                                  
                   `ext` varchar(64) DEFAULT NULL,
                   UNIQUE KEY `id` (`id`),                                            
                   UNIQUE KEY `index_globals_name` (`name`)
                 ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}			
			
	$sql ="CREATE TABLE `domain_stat_product_max_bandwidth` (          
                                      `id` int(4) unsigned NOT NULL AUTO_INCREMENT,          
                                      `group_id` int(4) NOT NULL,                            
                                      `buy_id` int(4) NOT NULL,                              
                                      `time` bigint(8) NOT NULL,                                                     
                                      `bandwidth_down` float DEFAULT '0',                    
                                      `bandwidth_up` float DEFAULT '0',                      
                                      UNIQUE KEY `id` (`id`)                                 
                                    ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="CREATE TABLE `domain_stat_month` (              
                             `id` int(4) unsigned NOT NULL AUTO_INCREMENT,         
                             `Host` varchar(64) NOT NULL,  
                             `domain_id` int(4) NOT NULL,       
                             `buy_id` int(4) NOT NULL,                                 
                             `time` bigint(8) NOT NULL,                            
                             `RequestCount` bigint(8) NOT NULL,                    
                             `UploadCount` float NOT NULL COMMENT 'GB',            
                             `DownloadCount` float NOT NULL COMMENT 'GB',          
                             `IpCount` bigint(8) NOT NULL,                         
                             UNIQUE KEY `id` (`id`),
                             UNIQUE KEY `index_domain_stat_month` (`domain_id`,`time`)                                
                           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table domain_stat_group_day add domain_id int(4) NOT NULL;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql ="alter table domain_stat_host_bandwidth add domain_id int(4) NOT NULL;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql ="alter table domain_stat_host_max_bandwidth add domain_id int(4) NOT NULL;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}		
	
	$sql ="alter table fikcdn_client add domain_need_verify smallint(2) NOT NULL DEFAULT '1';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	// 2013-12-15
	$sql ="alter table domain_stat_host_max_bandwidth add down_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table domain_stat_host_max_bandwidth add up_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table domain_stat_host_max_bandwidth add RequestCount_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	
	$sql ="alter table domain_stat_product_max_bandwidth add down_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table domain_stat_product_max_bandwidth add up_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table domain_stat_product_max_bandwidth add RequestCount_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	// 2013-12-26
	$sql ="alter table fikcdn_domain add upstream_add_all smallint(2) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}			
		
	$sql ="alter table fikcdn_upstream add upstream_add_all smallint(2) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table realtime_list add upstream_bandwidth_down float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
		
	$sql ="alter table realtime_list add upstream_bandwidth_up float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table realtime_list add upstream_down_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql ="alter table realtime_list add upstream_up_increase bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	// ****************** 节点统计 - 每个节点服务器的一小时最大带宽流量统计 *******************
	$sql = "CREATE TABLE `realtime_list_max` (                                       
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                          
                     `group_id` int(4) NOT NULL,                                            
                     `node_id` int(4) NOT NULL,                                             
                     `time` bigint(8) NOT NULL,                                             
                     `bandwidth_down` float DEFAULT '0' COMMENT '下载带宽',             
                     `bandwidth_up` float DEFAULT '0' COMMENT '上传带宽',               
                     `down_increase` bigint(8) DEFAULT '0' COMMENT '下载增量 KB',       
                     `up_increase` bigint(8) DEFAULT '0' COMMENT '上传增量 KB',         
                     `upstream_bandwidth_down` float NOT NULL DEFAULT '0',                  
                     `upstream_bandwidth_up` float NOT NULL DEFAULT '0',                    
                     `upstream_down_increase` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',  
                     `upstream_up_increase` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',    
                     UNIQUE KEY `id` (`id`),                                                
                     KEY `realtime_list_max_time_index` (`node_id`,`time`)                  
                   ) ENGINE=MyISAM DEFAULT CHARSET=utf8";	
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "CREATE realtime_list_max  error:".mysql_error($db_link)."<br />";
	}	
	
	$sql = "CREATE TABLE `realtime_list_all` (                            
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,             
                     `time` bigint(8) NOT NULL,                                  
                     `bandwidth_down` float DEFAULT '0' COMMENT '下载带宽',  
                     `bandwidth_up` float DEFAULT '0' COMMENT '上传带宽',    
                     `user_down` float NOT NULL DEFAULT '0' COMMENT 'MB',        
                     `user_up` float NOT NULL DEFAULT '0' COMMENT 'MB',          
                     `upstream_bandwidth_down` float NOT NULL DEFAULT '0',       
                     `upstream_bandwidth_up` float NOT NULL DEFAULT '0',         
                     `upstream_down` float NOT NULL DEFAULT '0' COMMENT 'MB',    
                     `upstream_up` float NOT NULL DEFAULT '0' COMMENT 'MB',      
                     UNIQUE KEY `id` (`id`)                                      
                   ) ENGINE=MyISAM DEFAULT CHARSET=utf8"; 
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "CREATE realtime_list_all  error:".mysql_error($db_link)."<br />";
	}						      
					   
	$sql = "CREATE TABLE `realtime_list_all_day` (                      
                         `id` int(4) unsigned NOT NULL AUTO_INCREMENT,             
                         `time` bigint(8) NOT NULL,                                
                         `user_down` float NOT NULL DEFAULT '0' COMMENT 'MB',      
                         `user_up` float NOT NULL DEFAULT '0' COMMENT 'MB',        
                         `upstream_down` float NOT NULL DEFAULT '0' COMMENT 'MB',  
                         `upstream_up` float NOT NULL DEFAULT '0' COMMENT 'MB',    
                         UNIQUE KEY `id` (`id`)                                    
                       ) ENGINE=MyISAM DEFAULT CHARSET=utf8";	
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "CREATE realtime_list_all_day  error:".mysql_error($db_link)."<br />";
	}		
	
	$sql = "CREATE TABLE `realtime_list_all_host` (                     
                          `id` int(4) unsigned NOT NULL AUTO_INCREMENT,             
                          `time` bigint(8) NOT NULL,                                
                          `user_down` float NOT NULL DEFAULT '0' COMMENT 'MB',      
                          `user_up` float NOT NULL DEFAULT '0' COMMENT 'MB',        
                          `upstream_down` float NOT NULL DEFAULT '0' COMMENT 'MB',  
                          `upstream_up` float NOT NULL DEFAULT '0' COMMENT 'MB',    
                          UNIQUE KEY `id` (`id`)                                    
                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ";	
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "CREATE realtime_list_all_host  error:".mysql_error($db_link)."<br />";
	}
	
	$sql = "CREATE TABLE `domain_stat_product_bandwidth` (                  
                                 `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                 
                                 `group_id` int(4) NOT NULL,                                   
                                 `buy_id` int(4) NOT NULL,                                     
                                 `time` bigint(8) NOT NULL,                                    
                                 `bandwidth_down` float DEFAULT '0' COMMENT 'Mbps',            
                                 `bandwidth_up` float DEFAULT '0' COMMENT 'Mbps',              
                                 `down_increase` bigint(8) NOT NULL DEFAULT '0' COMMENT 'MB',  
                                 `up_increase` bigint(8) NOT NULL DEFAULT '0' COMMENT 'MB',    
                                 `RequestCount_increase` bigint(8) NOT NULL DEFAULT '0',       
                                 UNIQUE KEY `id` (`id`),                                       
                                 KEY `product_bandwidth_time_id` (`time`,`buy_id`)             
                               ) ENGINE=MyISAM DEFAULT CHARSET=utf8";					   				   				   			
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "CREATE domain_stat_product_bandwidth  error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_domain add down_dataflow_total float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_domain add up_dataflow_total float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}												

	$sql ="alter table fikcdn_domain add request_total bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_buy add down_dataflow_total float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql ="alter table fikcdn_buy add up_dataflow_total float NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_buy add request_total bigint(8) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_task add `url` text CHARACTER SET utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
	
	$sql ="alter table fikcdn_task add `old_url` text CHARACTER SET utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
		
	$sql = "CREATE TABLE `cache_rule_fcache` (                        
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,           
                     `node_id` int(4) NOT NULL,                              
                     `group_id` int(4) NOT NULL,                             
                     `NO` int(4) NOT NULL,                                   
                     `Wid` int(4) NOT NULL,                                  
                     `Url` text NOT NULL,                                    
                     `Icase` smallint(1) NOT NULL,                           
                     `Rules` smallint(1) NOT NULL,                           
                     `Expire` int(4) NOT NULL,                               
                     `Unit` smallint(1) NOT NULL,                            
                     `Icookie` smallint(1) NOT NULL,                         
                     `Olimit` smallint(1) NOT NULL,                          
                     `IsDiskCache` smallint(1) NOT NULL,                     
                     `Note` text,                                            
                     PRIMARY KEY (`id`)                                      
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}					   
		
	$sql = "CREATE TABLE `cache_rule_rcache` (                       
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,          
                     `node_id` int(4) NOT NULL,                             
                     `group_id` int(4) NOT NULL,                            
                     `NO` int(4) NOT NULL,                                  
                     `Wid` int(4) NOT NULL,                                 
                     `Url` text NOT NULL,                                   
                     `Icase` smallint(1) NOT NULL,                          
                     `Rules` smallint(1) NOT NULL,                          
                     `Olimit` smallint(1) NOT NULL,                         
                     `CacheLocation` smallint(1) NOT NULL,                  
                     `Note` text,                                           
                     PRIMARY KEY (`id`)                                     
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}
	
	$sql = "CREATE TABLE `cache_rule_rewrite` (                      
                      `id` int(4) unsigned NOT NULL AUTO_INCREMENT,          
                      `node_id` int(4) NOT NULL,                             
                      `group_id` int(4) NOT NULL,                            
                      `NO` int(4) NOT NULL,                                  
                      `RewriteID` int(4) NOT NULL,                           
                      `SourceUrl` text NOT NULL,                             
                      `DestinationUrl` text NOT NULL,                        
                      `Icase` smallint(1) NOT NULL,                          
                      `Flag` smallint(1) NOT NULL,                           
                      `Note` text,                                           
                      PRIMARY KEY (`id`)                                     
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}		
	
	$sql = "CREATE TABLE `realtime_list_day` (                              
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,      
                     `node_id` int(4) NOT NULL,                                    
                     `group_id` int(4) NOT NULL,                                   
                     `time` bigint(8) NOT NULL,                                    
                     `user_down` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',      
                     `user_up` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',        
                     `upstream_down` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',  
                     `upstream_up` bigint(8) NOT NULL DEFAULT '0' COMMENT 'KB',    
                     UNIQUE KEY `id` (`id`)                                        
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);				   
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}					   
		
	$sql = "ALTER  TABLE  `domain_stat_host_max_bandwidth` ADD  UNIQUE domain_max_time_domain_id(`time`,`domain_id`)";
	$result = mysql_query($sql,$db_link);				   
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
		
	$sql = "ALTER  TABLE  `domain_stat_product_max_bandwidth` ADD  UNIQUE product_max_time_buy_id(`time`,`buy_id`)";
	$result = mysql_query($sql,$db_link);				   
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
		
	$sql = "ALTER  TABLE  `realtime_list_max` ADD  UNIQUE node_max_time_id(`time`,`node_id`)";
	$result = mysql_query($sql,$db_link);				   
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
		
	$sql = "ALTER  TABLE  `realtime_list_all_host` ADD  UNIQUE node_all_max_time(`time`)";
	$result = mysql_query($sql,$db_link);				   
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}		
	
	$sql ="alter table fikcdn_domain add is_hide smallint(2) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
			
	//月套餐流量用完后自动停止域名
	$sql ="alter table fikcdn_buy add auto_stop smallint(2) NOT NULL DEFAULT '0';";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		//echo "update error:".mysql_error($db_link)."<br />";
	}	
					   			   		
	/*
	$sql = "alter table fikcdn_task change hostname hostname varchar (128)  NULL  COLLATE utf8_general_ci";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "update error:".mysql_error($db_link)."<br />";
	}
	*/		
	
	//删除字段
	$sql ="alter table realtime_list drop column AllUsedMemSize";
	$result = mysql_query($sql,$db_link);
	
	$sql ="alter table realtime_list drop column AllUsedMemSize";
	$result = mysql_query($sql,$db_link);	
	
	echo '数据库表升级成功。';	
	
}
else
{
	echo '数据库表升级失败，不能连接到数据库。';
}


?>
