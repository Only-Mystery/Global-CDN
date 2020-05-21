<?php
// 安装数据库	
include_once('../db/db.php');
include_once('../function/pub_function.php');
include_once('../function/define.php');
include_once("function_admin.php");

$db_link = FikCDNDB_Connect();
if($db_link)
{		
	$sql = "create database if not exists `fikcdn`";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE database error:".mysql_error($db_link)."<br />";
	}
		
	// ****************** 管理员表 ********************
	// username - 登录用户名
	// username - 登录密码
	// power - 权限，10 为最大管理员权限，9. guest 权限
	// enable - 是否允许登录
	// login_count - 登录次数
	$sql ="CREATE TABLE `fikcdn_admin` (                          
                `id` int(4) unsigned NOT NULL AUTO_INCREMENT,        
                `username` varchar(32) NOT NULL,                     
                `password` varchar(64) NOT NULL,                     
                `power` int(4) NOT NULL DEFAULT '0',                 
                `last_login_ip` varchar(32) DEFAULT NULL,            
                `last_login_time` bigint(8) NOT NULL DEFAULT '0',    
                `enable` int(4) DEFAULT '1',                         
                `nick` varchar(64) DEFAULT NULL,                     
                `phone` varchar(32) DEFAULT NULL,                    
                `qq` varchar(16) DEFAULT NULL,                       
                `addr` varchar(256) DEFAULT NULL,                    
                `note` varchar(512) DEFAULT NULL,                    
                `login_count` int(4) DEFAULT '0',                    
                UNIQUE KEY `id` (`id`)                               
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_admin error:".mysql_error($db_link)."<br />";
	}
	
    $sql ="insert into `fikcdn_admin`(`id`,`username`,`password`,`power`,`last_login_ip`,`last_login_time`,`enable`,`nick`,`phone`,`qq`,`addr`,`note`,`login_count`) values (1,'admin','46f94c8de14fb36680850768ff1b7f2a',10,NULL,0,1,'admin','','',NULL,NULL,0),(2,'guest','46f94c8de14fb36680850768ff1b7f2a',9,NULL,0,1,'guest','','',NULL,NULL,0);";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "insert error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 客户帐号表 ********************
	// username - 登录用户名
	// money - 充值帐号余额
	// enable_login - 是否允许登录
	// register_time - 注册时间
	// domain_need_verify - 添加的域名是否需要管理员审核
    $sql ="CREATE TABLE `fikcdn_client` (                            
                 `id` int(4) unsigned NOT NULL AUTO_INCREMENT,           
                 `username` varchar(64) NOT NULL,                        
                 `realname` varchar(32) DEFAULT NULL,                    
                 `password` varchar(64) NOT NULL,                        
                 `money` float DEFAULT '0',                              
                 `enable_login` smallint(2) NOT NULL DEFAULT '1',        
                 `register_time` bigint(8) NOT NULL,                     
                 `register_ip` varchar(64) NOT NULL,                     
                 `addr` varchar(256) DEFAULT NULL,                       
                 `phone` varchar(32) DEFAULT NULL,                       
                 `company_name` varchar(256) DEFAULT NULL,               
                 `qq` varchar(32) DEFAULT NULL,                          
                 `last_login_time` bigint(8) DEFAULT NULL,               
                 `last_login_ip` varchar(64) DEFAULT NULL,               
                 `note` text,                                            
                 `login_count` int(4) DEFAULT '0',                       
                 `domain_need_verify` smallint(2) NOT NULL DEFAULT '1',  
                 UNIQUE KEY `id` (`id`),                                 
                 UNIQUE KEY `username` (`username`)                      
               ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_client error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 已售出套餐 ********************
	// username - 对应 fikcdn_client 的用户名
	// product_id - 对应 fikcdn_product 里的 id
	// begin_time - 开始时间
	// end_time - 到期时间
	// note - 备注
	// status - 0. 停止加速 1. 正在加速
	// auto_renew -  是否自动续费, 暂未启用
	// price - 套餐价格 元/月
	// has_data_flow - 本月已跑流量
	// domain_num - 允许添加的域名个数
	// data_flow - 月流量
	// is_focus - 是否关注，未使用
	$sql ="CREATE TABLE `fikcdn_buy` (                                   
              `id` int(4) unsigned NOT NULL AUTO_INCREMENT,               
              `username` varchar(64) NOT NULL,                            
              `product_id` int(4) NOT NULL,                               
              `begin_time` bigint(8) NOT NULL,                            
              `end_time` bigint(8) NOT NULL,                              
              `note` varchar(128) DEFAULT NULL,                           
              `status` smallint(2) DEFAULT '1',                           
              `auto_renew` smallint(2) DEFAULT '1',                       
              `price` float DEFAULT NULL,                                 
              `has_data_flow` bigint(8) DEFAULT '0',                      
              `domain_num` smallint(2) DEFAULT NULL,                      
              `data_flow` bigint(8) DEFAULT '0',                          
              `is_focus` smallint(1) DEFAULT '1' COMMENT '是否关注',  
              UNIQUE KEY `id` (`id`)                                      
            ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_buy error:".mysql_error($db_link)."<br />";
	}
		
	// ****************** 购买历史记录 ********************
	// username - 对应 fikcdn_client 的用户名
	// buy_id -  对应 fikcdn_buy 里的 id
	// price - 购买价格
	// month - 购买的月份数
	// buy_time - 购买时间
	// end_time - 结束时间
	// auto_renew -  是否自动续费, 暂未启用
	// price - 套餐价格 元/月
	// has_data_flow - 本月已跑流量
	// domain_num - 允许添加的域名个数
	// data_flow - 月流量
	// balance - 购买后账户余额
	// type - 0. 新购买 1. 续费
	// ip - 购买IP地址
	// note - 备注
	// frist_month_money -首月支付金额，字段已经废弃
	$sql ="CREATE TABLE `fikcdn_buyhistory` (                               
                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                  
                     `username` varchar(64) NOT NULL,                               
                     `buy_id` int(4) NOT NULL,                                      
                     `price` float NOT NULL,                                        
                     `month` smallint(2) NOT NULL,                                  
                     `buy_time` bigint(8) NOT NULL,                                 
                     `end_time` bigint(8) NOT NULL,                                 
                     `auto_renew` smallint(2) DEFAULT NULL,                         
                     `domain_num` smallint(2) NOT NULL,                             
                     `data_flow` bigint(8) NOT NULL,                                
                     `balance` float DEFAULT NULL COMMENT '账户余额',           
                     `type` smallint(1) NOT NULL DEFAULT '1',                       
                     `ip` varchar(16) NOT NULL,                                     
                     `note` varchar(128) DEFAULT NULL,                              
                     `frist_month_money` float DEFAULT '0' COMMENT '首月金额',  
                     UNIQUE KEY `id` (`id`)                                         
                   ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_buyhistory error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名列表 ********************
	// hostname - 域名
	// username - 域名所属用户帐号
	// add_time - 域名添加时间
	// buy_id -   域名所在的套餐，对应 fikcdn_buy 里的 id
	// group_id - 域名所在的服务器组, 对应 fikcdn_group 的 id
	// status - 0. 停止加速 1. 正在加速 2. 正在审核
	// upstream - 源站 IP 一
	// unicom_ip - 源站 IP 二
	// begin_time - 开始加速时间
	// end_time - 结束加速时间
	// note - 备注
	// transit_group_id - 中转组，字段已经废弃
	// is_stat - 是否统计，暂未启用
	// use_transit_node  - 是否启用中转，字段已经废弃
	$sql ="CREATE TABLE `fikcdn_domain` (                                                   
                 `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                                  
                 `hostname` varchar(64) CHARACTER SET utf8 NOT NULL,                            
                 `username` varchar(64) CHARACTER SET utf8 NOT NULL,                            
                 `add_time` bigint(8) DEFAULT NULL,                                             
                 `buy_id` int(4) NOT NULL,                                                      
                 `group_id` int(4) DEFAULT NULL,                                                
                 `status` smallint(2) DEFAULT '2',                                              
                 `upstream` varchar(64) CHARACTER SET utf8 NOT NULL,                            
                 `unicom_ip` varchar(64) CHARACTER SET utf8 DEFAULT NULL COMMENT '联通IP',    
                 `begin_time` bigint(8) DEFAULT NULL,                                           
                 `end_time` bigint(8) DEFAULT NULL,                                             
                 `note` text CHARACTER SET utf8,                                                
                 `is_focus` smallint(1) DEFAULT '1' COMMENT '是否显示在统计图表',      
                 `icp` varchar(32) CHARACTER SET utf8 DEFAULT NULL COMMENT 'ICP备案号',      
                 `DNSName` varchar(64) CHARACTER SET utf8 DEFAULT NULL COMMENT '域名别名',  
                 `transit_group_id` int(4) DEFAULT '-1',                                        
                 `is_stat` smallint(1) DEFAULT '1' COMMENT '是否统计',                      
                 `use_transit_node` smallint(1) DEFAULT '0',
				 `upstream_add_all` smallint(2) NOT NULL DEFAULT '0',                                    
                 UNIQUE KEY `id` (`id`)                                                         
               ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_domain error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 服务器组 ********************
	// name - 组名称
	// create_time - 组创建时间
	// status - 组状态
	// is_transit - 是否中转服务器组，字段未启用
	$sql ="CREATE TABLE `fikcdn_group` (                                         
                `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                       
                `name` varchar(128) CHARACTER SET utf8 NOT NULL,                    
                `create_time` bigint(8) DEFAULT NULL,                               
                `status` smallint(2) DEFAULT '0',                                   
                `creator` varchar(64) CHARACTER SET utf8 DEFAULT NULL,              
                `is_transit` smallint(1) DEFAULT '0' COMMENT '是否中转节点',  
                UNIQUE KEY `id` (`id`)                                              
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_group error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 登录日志字段 ********************
	// username - 用户名
	// login_ip - 登录 IP 地址
	// login_time - 登录时间
	// status - 状态
	$sql ="CREATE TABLE `fikcdn_login_log` (                        
                    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,          
                    `username` varchar(64) NOT NULL,                       
                    `login_ip` varchar(64) NOT NULL,                       
                    `login_time` bigint(8) NOT NULL DEFAULT '0',           
                    `status` smallint(2) NOT NULL,                         
                    `type` smallint(2) DEFAULT NULL,                       
                    UNIQUE KEY `id` (`id`)                                 
                  ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_login_log error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** Fikker 服务器节点列表 ********************
	// name - 服务器名称
	// ip - 服务器 IP 地址一
	// unicom_ip - 服务器 IP 地址二
	// port - 端口，默认 80
	// admin_port - Fikker 节点管理端口
	// username - Fikker 管理员登录的用户名
	// password - Fikker 管理员密码
	// type - 未启用
	// groupid - 组 ID, 对应 fikcdn_group 的 id
	// add_time - 增加时间
	// auth_domain - Fikker 的授权域名
	// fik_version - 版本号
	// SessionID - 登录 FIkker 管理后台的会话 ID
	// status - 未启用
	// is_close - 是否关闭
	// version_ext - 版本扩展，windows or linux
	// is_transit - 是否中转
	$sql ="CREATE TABLE `fikcdn_node` (                                                             
               `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                                          
               `name` varchar(64) DEFAULT NULL,                                                       
               `ip` varchar(64) DEFAULT NULL,                                                         
               `unicom_ip` varchar(64) DEFAULT NULL COMMENT '联通IP',                               
               `port` int(4) DEFAULT NULL,                                                            
               `admin_port` int(4) DEFAULT '6780',                                                    
               `username` varchar(64) DEFAULT NULL,                                                   
               `password` varchar(64) DEFAULT NULL,                                                   
               `type` int(4) DEFAULT NULL,                                                            
               `groupid` int(4) DEFAULT NULL,                                                         
               `bandwidth` int(4) DEFAULT '0',                                                        
               `add_time` bigint(8) DEFAULT '0',                                                      
               `note` text,                                                                           
               `auth_domain` varchar(64) DEFAULT '0',                                                 
               `fik_version` varchar(64) DEFAULT NULL,                                                
               `SessionID` varchar(64) DEFAULT NULL,                                                  
               `fik_LastLoginTime` varchar(32) DEFAULT NULL,                                          
               `status` smallint(2) DEFAULT '1',                                                      
               `is_close` char(1) DEFAULT '0',                                                        
               `version_ext` varchar(32) DEFAULT NULL,                                                
               `is_focus` smallint(1) DEFAULT '1' COMMENT '是否关注（显示在统计图表）',  
               `is_transit` smallint(1) DEFAULT '0',                                                  
               UNIQUE KEY `id` (`id`)                                                                 
             ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_node error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 订单列表 ********************
	// username - 用户名
	// product_id - 产品 ID, 对应 fikcdn_product 的 id
	// buy_time - 下单时间
	// auto_renew -  是否自动续费, 暂未启用
	// price - 套餐价格 元/月	
	// month - 购买月数
	// domain_num - 允许添加的域名个数
	// frist_month_money - 已经废弃
	$sql ="CREATE TABLE `fikcdn_order` (                                          
                `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                        
                `username` varchar(64) NOT NULL,                                     
                `product_id` int(4) NOT NULL,                                        
                `buy_time` bigint(8) NOT NULL,                                       
                `note` varchar(128) DEFAULT NULL,                                    
                `status` smallint(2) DEFAULT '1',                                    
                `auto_renew` smallint(2) DEFAULT '1',                                
                `price` float NOT NULL,                                              
                `month` smallint(2) NOT NULL,                                        
                `type` smallint(2) DEFAULT NULL,                                     
                `domain_num` smallint(2) NOT NULL,                                   
                `data_flow` bigint(8) NOT NULL,                                      
                `buy_id` int(4) DEFAULT NULL,                                        
                `frist_month_money` float DEFAULT '0' COMMENT '开通首月金额',  
                UNIQUE KEY `id` (`id`)                                               
              ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_order error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 产品套餐列表 ********************
	// name - 产品名称
	// price - 价格(元/月)
	// data_flow - 月度数据流量
	// domain_num - 套餐允许添加的域名个数
	// is_online - 是否上线
	// group_id - 组 ID，对应 fikcdn_group 的 id，添加到这个域名的套餐都加到这个服务器组里面
	$sql ="CREATE TABLE `fikcdn_product` (                       
                  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,       
                  `name` varchar(64) CHARACTER SET utf8 NOT NULL,     
                  `price` float NOT NULL,                             
                  `data_flow` bigint(8) NOT NULL DEFAULT '0',         
                  `domain_num` int(4) NOT NULL,                       
                  `is_online` smallint(2) NOT NULL DEFAULT '0',       
                  `begin_time` bigint(8) NOT NULL,                    
                  `note` text CHARACTER SET utf8,                                 
                  `group_id` int(4) NOT NULL,                         
                  UNIQUE KEY `id` (`id`)                              
                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_product error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 充值记录表 ********************
	// username - 用户帐号
	// money - 充值金额
	// balance - 充值后帐号余额
	// time - 充值时间
	// transactor - 经办人
	// bank_name - 银行名称
	// serial_no - 银行流水号
	// opt_username - 操作帐号
	// account - 银行卡号
	// note - 备注
	$sql ="CREATE TABLE `fikcdn_recharge` (                          
                   `id` int(4) unsigned NOT NULL AUTO_INCREMENT,           
                   `username` varchar(64) NOT NULL,                        
                   `money` float NOT NULL,                                 
                   `balance` float NOT NULL COMMENT '账户余额',        
                   `time` bigint(8) NOT NULL,                              
                   `transactor` varchar(64) NOT NULL COMMENT '经办人',  
                   `bank_name` varchar(64) NOT NULL,                       
                   `serial_no` varchar(128) NOT NULL,                      
                   `opt_username` varchar(64) DEFAULT NULL,                
                   `account` varchar(32) DEFAULT NULL COMMENT '帐号',    
                   `note` varchar(128) DEFAULT NULL,                       
                   UNIQUE KEY `id` (`id`)                                  
                 ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_recharge error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 系统参数表 ********************						
	// name - 参数名
	// value - 参数值
	// time - 参数修改时间
	// note - 备注
	// ext - 扩展字段
	$sql ="CREATE TABLE `fikcdn_params` (                         
                 `id` int(4) unsigned NOT NULL AUTO_INCREMENT,        
                 `name` varchar(64) NOT NULL,                         
                 `value` varchar(128) NOT NULL,                       
                 `time` bigint(8) NOT NULL,                           
                 `note` varchar(128) DEFAULT NULL,                    
                 `ext` varchar(64) DEFAULT NULL,                      
                 UNIQUE KEY `id` (`id`),                              
                 UNIQUE KEY `index_globals_name` (`name`)             
               ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_params error:".mysql_error($db_link)."<br />";
	}			
	
	// ****************** 后台任务队列 ********************					
	// username - 用户账户
	// type - 任务类型, 参看 define.php 
	// time - 任务添加时间
	// domain_id - 域名 ID,对应 fikcdn_domain 的 id
	// node_id - 节点服务器 ID,对应 fikcdn_node 的 id
	// product_id - 产品 ID,对应 fikcdn_product 的 id
	// buy_id - 购买套餐 ID，对应 fikcdn_buy 的 id
	// hostname - 域名
	// group_id - 服务器组 ID, 对应 fikcdn_group 的 id
	// ext - 扩展，清理缓存任务会保存 清理 url 地址
	// execute_count - 执行次数
	// result_str - 执行结果
	$sql ="CREATE TABLE `fikcdn_task` (                               
               `id` int(4) unsigned NOT NULL AUTO_INCREMENT,            
               `username` varchar(64) CHARACTER SET utf8 NOT NULL,      
               `type` int(4) NOT NULL,                                  
               `time` bigint(8) NOT NULL,                               
               `domain_id` int(4) NOT NULL,                             
               `node_id` int(4) DEFAULT NULL,                           
               `product_id` int(4) DEFAULT NULL,                        
               `buy_id` int(4) DEFAULT NULL,                            
               `hostname` varchar(64) CHARACTER SET utf8 DEFAULT NULL,  
               `group_id` int(4) DEFAULT NULL,                          
               `ext` text CHARACTER SET utf8,                           
               `execute_count` int(4) DEFAULT '0',                      
               `result_str` text CHARACTER SET utf8,                    
               UNIQUE KEY `id` (`id`)                                   
             ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE fikcdn_task error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 源站列表 ********************			
	// node_id - 节点服务器 ID,对应 fikcdn_node 的 id
	// group_id - 服务器组 ID, 对应 fikcdn_group 的 id
	// hostname - 域名
	// upstream - 源站 IP 地址一
	// upstream2 - 源站 IP 地址二
	$sql ="CREATE TABLE `fikcdn_upstream` (                                     
                   `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                      
                   `node_id` int(4) NOT NULL,                                         
                   `group_id` int(4) NOT NULL,                                        
                   `hostname` varchar(64) NOT NULL,                                   
                   `upstream` varchar(128) NOT NULL,                                  
                   `note` varchar(128) DEFAULT NULL,                                  
                   `upstream2` varchar(128) DEFAULT NULL,              
				   `upstream_add_all` smallint(2) NOT NULL DEFAULT '0',               
                   UNIQUE KEY `id` (`id`),                                            
                   UNIQUE KEY `index_upstream_domain_node_id` (`node_id`,`hostname`)  
                 ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "update error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 节点服务器的实时统计列表 ********************			
	// node_id - 节点服务器 ID,对应 fikcdn_node 的 id
	// group_id - 服务器组 ID, 对应 fikcdn_group 的 id	
	// bandwidth_down - 节点服务器上行带宽
	// bandwidth_up - 节点服务器下行带宽
	// down_increase - 统计时间段内上行流量增量
	// up_increase - 统计时间段内下行流量增量
	// 说明： 其他字段参考 FIkker 二次开发包
	$sql ="CREATE TABLE `realtime_list` (                                   
                 `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                  
                 `group_id` int(4) NOT NULL,                                    
                 `node_id` int(4) NOT NULL,                                     
                 `time` bigint(8) NOT NULL,                                     
                 `StartTime` bigint(8) NOT NULL,                                
                 `EndTime` bigint(8) NOT NULL,                                  
                 `CurrentUserConnections` int(4) NOT NULL,                      
                 `CurrentUpstreamConnections` int(4) NOT NULL,                  
                 `AllUsedMemSize` int(4) NOT NULL,                              
                 `CacheUsedMemSize` int(4) NOT NULL,                            
                 `NumOfCaches` int(4) NOT NULL,                                 
                 `TotalSendKB` bigint(8) NOT NULL,                              
                 `TotalRecvKB` bigint(8) NOT NULL,                              
                 `NumOfCachedSessions` int(4) NOT NULL,                         
                 `NumOfPublicCaches` int(4) NOT NULL,                           
                 `NumOfMemberCaches` int(4) NOT NULL,                           
                 `NumOfVisitorCaches` int(4) NOT NULL,                          
                 `PublicCacheUsedMemSize` int(4) NOT NULL,                      
                 `MemberCacheUsedMemSize` int(4) NOT NULL,                      
                 `VisitorCacheUsedMemSize` int(4) NOT NULL,                     
                 `TotalSendToResponseKB` bigint(8) NOT NULL,                    
                 `TotalRecvFromResponseKB` bigint(8) NOT NULL,                  
                 `bandwidth_down` float DEFAULT '0' COMMENT '下载带宽',     
                 `bandwidth_up` float DEFAULT '0' COMMENT '上传带宽',       
                 `down_increase` bigint(8) DEFAULT '0' COMMENT '下载增量',  
                 `up_increase` bigint(8) DEFAULT '0' COMMENT '上传增量',
                 `upstream_bandwidth_down` float NOT NULL DEFAULT '0',          
                 `upstream_bandwidth_up` float NOT NULL DEFAULT '0',            
                 `upstream_down_increase` bigint(8) NOT NULL DEFAULT '0',       
                 `upstream_up_increase` bigint(8) NOT NULL DEFAULT '0', 				     
                 UNIQUE KEY `id` (`id`)                                         
               ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE realtime_list error:".mysql_error($db_link)."<br />";
	}		
	
	// ****************** 节点服务器的流量统计 ********************			
	// 说明： 其他字段参考 FIkker 二次开发包
	$sql ="CREATE TABLE `realtime_totalstat` (                     
                      `id` int(4) unsigned NOT NULL AUTO_INCREMENT,         
                      `group_id` int(4) DEFAULT '0',                        
                      `node_id` int(4) DEFAULT '0',                         
                      `time` bigint(8) DEFAULT NULL,                        
                      `StartTime` bigint(8) DEFAULT NULL,                   
                      `EndTime` bigint(8) DEFAULT NULL,                     
                      `HitCachesRate` float DEFAULT '0',                    
                      `IP` bigint(8) DEFAULT '0',                           
                      `PV` bigint(8) DEFAULT '0',                           
                      `TR` bigint(8) DEFAULT '0',                           
                      `PR` float DEFAULT NULL,                              
                      `RealTimeReport` text,                                
                      UNIQUE KEY `id` (`id`)                                
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "update error:".mysql_error($db_link)."<br />";
	}		
	
	// ****************** 域名统计 - 服务器组内域名日流量统计汇总 ********************
	$sql ="CREATE TABLE `domain_stat_group_day` (                      
                         `id` int(4) unsigned NOT NULL AUTO_INCREMENT,             
                         `group_id` int(4) NOT NULL,                               
                         `buy_id` int(4) NOT NULL,                                 
                         `time` bigint(8) NOT NULL,                                
                         `Host` varchar(64) NOT NULL,                              
                         `RequestCount` bigint(8) NOT NULL DEFAULT '0',            
                         `UploadCount` float NOT NULL DEFAULT '0' COMMENT 'MB',    
                         `DownloadCount` float NOT NULL DEFAULT '0' COMMENT 'MB',  
                         `IpCount` bigint(8) NOT NULL DEFAULT '0',                 
                         `domain_id` int(4) NOT NULL,                              
                         UNIQUE KEY `id` (`id`),                                   
                         UNIQUE KEY `domain_id` (`domain_id`,`time`,`Host`)          
                       ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_group_day error:".mysql_error($db_link)."<br />";
	}
		
	// ****************** 域名统计 - 汇总服务器组内域名带宽 ********************	
	$sql ="CREATE TABLE `domain_stat_host_bandwidth` (                                              
                              `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                                          
                              `group_id` int(4) NOT NULL,                                                            
                              `buy_id` int(4) NOT NULL,                                                              
                              `time` bigint(8) NOT NULL,                                                             
                              `Host` varchar(64) NOT NULL,                                                           
                              `down_increase` bigint(8) DEFAULT '0' COMMENT '相比上次请求的下载增加数',  
                              `up_increase` bigint(8) DEFAULT '0' COMMENT '相比上次请求的上行增加数',    
                              `bandwidth_down` float DEFAULT '0',                                                    
                              `bandwidth_up` float DEFAULT '0',                                                      
                              `RequestCount_increase` bigint(8) DEFAULT '0',                                         
                              `IpCount_increase` bigint(8) DEFAULT '0',                                              
                              `domain_id` int(4) NOT NULL,                                                           
                              UNIQUE KEY `id` (`id`)                                                                 
                            ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_host_bandwidth error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名统计 - 汇总服务器组内域名峰值带宽（一个小时内的峰值） ********************		
	$sql ="CREATE TABLE `domain_stat_host_max_bandwidth` (          
                                  `id` int(4) unsigned NOT NULL AUTO_INCREMENT,          
                                  `group_id` int(4) NOT NULL,                            
                                  `buy_id` int(4) NOT NULL,                              
                                  `time` bigint(8) NOT NULL,                             
                                  `Host` varchar(64) NOT NULL,                           
                                  `bandwidth_down` float DEFAULT '0',                    
                                  `bandwidth_up` float DEFAULT '0',                      
                                  `domain_id` int(4) NOT NULL,    
                                  `down_increase` bigint(8) NOT NULL DEFAULT '0',                
                                  `up_increase` bigint(8) NOT NULL DEFAULT '0',                  
                                  `RequestCount_increase` bigint(8) NOT NULL DEFAULT '0',  								                         
                                  UNIQUE KEY `id` (`id`)                                 
                                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_host_max_bandwidth error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名统计 - 汇总服务器组内域名月流量统计 ********************			
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
                   ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_month error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名统计 - 汇总服务器组内套餐内所有域名的日流量统计 ********************	
	$sql ="CREATE TABLE `domain_stat_product_day` (                
                           `id` int(4) unsigned NOT NULL AUTO_INCREMENT,         
                           `buy_id` int(4) NOT NULL,                             
                           `time` bigint(8) NOT NULL,                            
                           `RequestCount` bigint(8) NOT NULL,                    
                           `UploadCount` float NOT NULL COMMENT 'GB',            
                           `DownloadCount` float NOT NULL COMMENT 'GB',          
                           `IpCount` bigint(8) NOT NULL,                         
                           UNIQUE KEY `id` (`id`)                                
                         ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_product_day error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名统计 - 汇总服务器组内套餐内所有域名的峰值带宽（一个小时内的峰值） ********************		
	$sql ="CREATE TABLE `domain_stat_product_max_bandwidth` (      
                                     `id` int(4) unsigned NOT NULL AUTO_INCREMENT,         
                                     `group_id` int(4) NOT NULL,                           
                                     `buy_id` int(4) NOT NULL,                             
                                     `time` bigint(8) NOT NULL,                            
                                     `bandwidth_down` float DEFAULT '0',                   
                                     `bandwidth_up` float DEFAULT '0',       
									 `down_increase` bigint(8) NOT NULL DEFAULT '0',                
									 `up_increase` bigint(8) NOT NULL DEFAULT '0',                  
									 `RequestCount_increase` bigint(8) NOT NULL DEFAULT '0',									               
                                     UNIQUE KEY `id` (`id`)                                
                                   ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_product_max_bandwidth error:".mysql_error($db_link)."<br />";
	}
	
	// ****************** 域名统计 - 汇总服务器组内套餐内所有月流量统计 ********************	
	$sql ="CREATE TABLE `domain_stat_product_month` (              
                             `id` int(4) unsigned NOT NULL AUTO_INCREMENT,         
                             `buy_id` int(4) NOT NULL,                             
                             `time` bigint(8) NOT NULL,                            
                             `RequestCount` bigint(8) NOT NULL,                    
                             `UploadCount` float NOT NULL COMMENT 'GB',            
                             `DownloadCount` float NOT NULL COMMENT 'GB',          
                             `IpCount` bigint(8) NOT NULL,                         
                             UNIQUE KEY `id` (`id`)                                
                           ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_product_month error:".mysql_error($db_link)."<br />";
	}	
	
	// ****************** 域名统计 - 每个节点服务器的每个域名的实时流量统计 ********************	
	$sql ="CREATE TABLE `domain_stat_temp` (                                                        
                    `id` int(4) unsigned NOT NULL AUTO_INCREMENT,                                          
                    `group_id` int(4) NOT NULL,                                                            
                    `node_id` int(4) NOT NULL,                                                             
                    `time` bigint(8) NOT NULL,                                                             
                    `NO` int(4) NOT NULL,                                                                  
                    `ProxyID` int(4) NOT NULL,                                                             
                    `Host` varchar(64) NOT NULL,                                                           
                    `Balance` int(4) NOT NULL,                                                             
                    `Enable` smallint(2) NOT NULL,                                                         
                    `StartTime` bigint(8) NOT NULL,                                                        
                    `EndTime` bigint(8) NOT NULL,                                                          
                    `RequestCount` bigint(8) NOT NULL,                                                     
                    `UploadCount` bigint(8) NOT NULL,                                                      
                    `DownloadCount` bigint(8) NOT NULL,                                                    
                    `IpCount` int(4) NOT NULL,                                                             
                    `Note` text,                                                                           
                    `bandwidth_down` float DEFAULT '0',                                                    
                    `bandwidth_up` float DEFAULT '0',                                                      
                    `down_increase` bigint(8) DEFAULT '0' COMMENT '相比上次请求的下载增加数',  
                    `up_increase` bigint(8) DEFAULT '0' COMMENT '相比上次请求的上行增加数',    
                    `UserConnections` int(4) DEFAULT '0',                                                  
                    `UpstreamConnections` int(4) DEFAULT '0',                                              
                    `RequestCount_increase` int(4) DEFAULT '0',                                            
                    UNIQUE KEY `id` (`id`),                                                                
                    UNIQUE KEY `domain_stat_temp_index_host` (`node_id`,`Host`)                            
                  ) ENGINE=MyISAM AUTO_INCREMENT=5421 DEFAULT CHARSET=utf8";
	$result = mysql_query($sql,$db_link);
	if(!$result)
	{
		echo "CREATE domain_stat_temp  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_max  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all_day  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all_host  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE domain_stat_product_bandwidth  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE cache_rule_fcache  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE cache_rule_rcache  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE cache_rule_rewrite  error:".mysql_error($db_link)."<br />";
	}	
	
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
		echo "CREATE realtime_list_max  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all_day  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all_host  error:".mysql_error($db_link)."<br />";
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
		echo "CREATE realtime_list_all_host  error:".mysql_error($db_link)."<br />";
	}									
}



?>
