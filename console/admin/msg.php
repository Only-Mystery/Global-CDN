<link rel="stylesheet" href="../css/fikker.css" type="text/css" />
<script src="../js/urlencode.js"></script>
<script>
//msg.htm?1.3-DELPROXY-testproxy1-4
var sParam=document.location.href.split("?")[1];
var showMSG = sParam.split("&msg=")[1];
var tmpchar = sParam.split("&msg=")[0];
if(tmpchar.indexOf("-")==-1){
	var MSGID=tmpchar;
}else{
	tmpchar=tmpchar.split("-");
	var MSGID=tmpchar[0];
}

// x.y
var prefix = MSGID.split(".")[0];   // x 部分
var suffix = MSGID.split(".")[1];   // y 部分
if( (typeof(showMSG) == "undefined") || showMSG.length<=0){
	if(prefix==1){   // 1.x 错误
		switch(suffix){
			case "1":
				showMSG="请输入服务器组名称。";
				break;
			case "2":
				showMSG="此服务器组名称已经存在。";
				break;
			case "3":
				showMSG="请先创建一个服务器组。";
				break;			
			case "4":
				showMSG="请输入服务器名称，如：广州双线G口。";
				break;
			case "5":
				showMSG="联通和电信 IP 至少输入一个，双线服务器则可两个都输入，其他线路则任意输入一个即可。";
				break;
			case "6":
				showMSG="请输入 Fikker 服务器管理端口，默认端口是：6780。";
				break;
			case "7":
				showMSG="请输入 Fikker 服务器管理员密码。";
				break;		
			case "8":
				showMSG="添加服务器成功。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="继续添加" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ContinueAdd();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();javascript:parent.closeMSGBOX();" /></center>';

				//showMSG="添加服务器成功。<br><br><br><br>" + "<center><a href=\"####\" onclick=\"javascript:parent.FikCdn_ContinueAdd();parent.closeMSGBOX();\">[继续添加]</a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"####\" onclick=\"javascript:parent.FikCdn_ReLoadNodeList();parent.closeMSGBOX();\">[刷新列表]</a></center>";
				break;	
			case "9":
				showMSG="您确定要删除此服务器吗？<br><br><br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelNode();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "10":
				showMSG="您确认重新同步所有域名和源站到此服务器的主机管理列表中吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ReConfigHost();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "11":
				showMSG="您确认要启用此服务器吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StartNode();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "12":
				showMSG="您确认要暂时停用此服务器吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StopNode();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "13":
				showMSG="您确认要删除此节点服务器主机管理中的所有域名吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_HostDelete();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																			
		}
	}	
	else if(prefix==2){   // 2.x 错误
		switch(suffix){
			case "1":
				showMSG="您确认删除此服务器组吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelGroup();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
		}
	}	
	else if(prefix==3){   // 3.x 错误
		switch(suffix){
			case "1":
				showMSG="您确认要删除此域名吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "2":
				showMSG="您确认要开启此域名加速吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StartDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "3":
				showMSG="您确认要暂停此域名加速吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StopDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;				
			case "4":
				showMSG="域名审核通过后将会添加到所在组的服务器主机列表中，您确认要审核通过此域名吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_VerifyDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "5":
				showMSG="您确认要一次性审核通过选择的所有域名吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_VerifyDomainMore();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "6":
				showMSG="您确认删除此域名的中转源站设置吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelUpstream();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "7":
				showMSG="您确认删除此后台任务吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelTask();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "8":
				showMSG="您确认要将此后台任务重新加入到执行队列中吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ReExecuteTask();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "9":
				showMSG="您确认要删除所有后台任务吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.doInfoAction();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "10":
				showMSG="您确认要删除选择的后台任务吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.doInfoAction();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;					
			case "11":
				showMSG="您确认要重新执行任务吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.doInfoAction();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "12":
				showMSG="您确认要删除所有执行失败的后台任务吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.doInfoAction();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "14":
				showMSG="缓存更新任务已经提交到后台任务队列中，后台任务执行程序会在一分钟内开始逐个更新各个服务器缓存。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.toTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "15":
				showMSG="缓存更新任务已经提交到后台任务队列中，后台任务执行程序会在一分钟内开始逐个更新各个服务器缓存。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.toTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																																														
		}
	}
	else if(prefix==4){   // 4.x 错误
		switch(suffix){
			case "1":
				showMSG="您确认要删除用户登录日志吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ClearLoginLog();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "2":
				showMSG="是否确认删除此用户吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelUser();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "3":
				showMSG="您确认给此用户帐号进行充值吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_AddRecharge();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "4":
				showMSG="您确认要删除此订单吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelOrder();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "5":
				showMSG="您确认要删除此套餐吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelBuyProduct();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "6":
				showMSG="您确认要修改此订单信息吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_OrderModify();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "7":
				showMSG="您确认要删除此产品套餐吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelProduct();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;			
			case "8":
				showMSG="您确认要修改此套餐的信息吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_BuyModify();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "9":
				showMSG="您确认要启用此套餐内的所有域名的加速吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StartBuyDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "10":
				showMSG="暂停域名加速将使用户访问此域名时返回 400 错误，建议您先将DNS解析回源后再暂停，您确定要暂停此套餐内所有域名的加速？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_StopBuyDomain();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																																		
		}
	}
	else if(prefix==5){   // 5.x 错误
		switch(suffix){
			case "1":
				showMSG="添加页面缓存的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;				
			case "2":
				showMSG="修改页面缓存的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;				
			case "3":
				showMSG="您确要删除此页面缓存规则吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelFCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "4":
				showMSG="删除页面缓存的任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "5":
				showMSG="您确要将此页面缓存规则向上移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_UpFCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "6":
				showMSG="您确要将此页面缓存规则向下移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DownFCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "7":
				showMSG="页面缓存的上移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "8":
				showMSG="页面缓存的下移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;			
			case "9":
				showMSG="页面缓存同步任务已经加入到后台任务列表中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																						
		}
	}	
	else if(prefix==6){   // 6.x 错误
		switch(suffix){
			case "1":
				showMSG="添加拒绝缓存的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;				
			case "2":
				showMSG="修改拒绝缓存的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;				
			case "3":
				showMSG="您确要删除此拒绝缓存规则吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelRCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "4":
				showMSG="删除拒绝缓存的任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "5":
				showMSG="您确要将此拒绝缓存规则向上移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_UpRCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "6":
				showMSG="您确要将此拒绝缓存规则向下移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DownRCache();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "7":
				showMSG="拒绝缓存的上移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "8":
				showMSG="拒绝缓存的下移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;		
			case "9":
				showMSG="拒绝缓存规则同步任务已经加入到后台任务列表中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																							
		}
	}
	else if(prefix==7){   // 7.x 错误
		switch(suffix){
			case "1":
				showMSG="添加转向规则的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "2":
				showMSG="修改转向规则的任务已经加入到后台任务列表中，系统会逐个同步到组内所有服务器中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "3":
				showMSG="您确要删除此转向规则吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DelRewrite();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "4":
				showMSG="删除转向规则的任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "5":
				showMSG="您确要将此转向规则向上移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_UpRewrite();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "6":
				showMSG="您确要将此转向规则向下移动吗？<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="确定" style="cursor:pointer;" onClick="javascript:parent.FikCdn_DownRewrite();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="取消" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "7":
				showMSG="转向规则的上移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;
			case "8":
				showMSG="转向规则的下移任务已经加入到后台任务列表中，组内所有服务器将会逐个执行。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;	
			case "9":
				showMSG="转向规则同步任务已经加入到后台任务列表中。<br><br>" ;
				showMSG += '<center><input name="btnOk"  type="submit" style="width:95px;height:26px" id="btnAddNode" value="查看后台任务" style="cursor:pointer;" onClick="javascript:parent.FikCdn_ToTaskList();parent.closeMSGBOX();" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				showMSG += '<input name="btnCancel"  type="submit" style="width:75px;height:26px" id="btnAddNode" value="关闭" style="cursor:pointer;" onClick="javascript:parent.closeMSGBOX();" /></center>';
				break;																
		}					
	}
}else{
	showMSG = UrlDecode(showMSG);
}

document.write("<table width=\"95%\" align=center><tr><td height=80 width=\"100%\" style=\"font-size:12px;\">"+showMSG+"</td></tr></table>");

</script>
