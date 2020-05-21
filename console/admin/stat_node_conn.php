<?php
include_once("./head.php");

	// 节点ID
	$node_id 	= isset($_GET['id'])?$_GET['id']:'';
?>
<script type="text/javascript" src="../highcharts-302/jquery/1.8.2/jquery.min.js"></script>
<script src="../highcharts-302/js/highcharts.js"></script>
<script src="../highcharts-302/js/modules/exporting.js"></script>
<script type="text/javascript">	
function selectGroup(){
	var txtGid		 =document.getElementById("grpSelect").value;
	window.location.href="stat_domain_bandwidth_down.php?buy_id="+txtGid;
}

// 将 GMT 时间转换为本地时间 
// phpLocalTime 时间格式 "2010/12/09 00:00:00"
function  ConvDate(phpLocalTime)
{
	var d=new Date(phpLocalTime); //"2010/12/09 00:00:00");

	day = d.getHours();

	d = d.setHours(8+day);

	d = new Date(d);

	x = d.getTime(); 
	
	return x;
}

function getConnectCountStatData(node_id,timeval)
{
	var xmlhttp;
	
    if (window.XMLHttpRequest)
	{
	  	// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}
	else
	{
	  	// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}

	if(timeval==1)
	{
		sTimeformat = "%H:%M";
	}
	else
	{
		sTimeformat = "%0m-%0d";
	}	
	
	xmlhttp.onreadystatechange=function()
	{
	  	if (xmlhttp.readyState==4 && xmlhttp.status==200)
		{	
			/*
		    var data = [
					{
						label: "United States",
						data: [[1990, 18.9], [1991, 18.7], [1992, 18.4], [1993, 19.3], [1994, 19.5], [1995, 19.3], [1996, 19.4], [1997, 20.2], [1998, 19.8], [1999, 19.9], [2000, 20.4], [2001, 20.1], [2002, 20.0], [2003, 19.8], [2004, 20.4]]
					},
					{
            			label: "Russia", 
			            data: [[1992, 13.4], [1993, 12.2], [1994, 10.6], [1995, 10.2], [1996, 10.1], [1997, 9.7], [1998, 9.5], [1999, 9.7], [2000, 9.9], [2001, 9.9], [2002, 9.9], [2003, 10.3], [2004, 10.5]]
        			}];
			*/	
			var sResponse= xmlhttp.responseText;
			//document.getElementById("textStatDataTable").innerHTML=sResponse;	
			__StatDataSets = eval('('+sResponse+')');
			
			var data = [];
			for(var key in __StatDataSets)
			{
				data.push(__StatDataSets[key]);				
			}
			
			//hdrchart.series = data;
			update_enginConn_chart(__StatDataSets);
		}
	}

	var postUrl = "request_stat_data.php?mod=realtime&action=connect"+"&node_id=" + node_id +"&timeval="+timeval;
	xmlhttp.open("GET",postUrl,true);
	xmlhttp.send(null);
	return false;
}

function OnSelectConnectCountDate()
{
	var txtNodeID = document.getElementById("txtNodeID").value;
	var nConnectCountDateSelect = document.getElementById("ConnectCountDateSelect").value;
	
	getConnectCountStatData(txtNodeID,nConnectCountDateSelect);
}

function OnSelectNode()
{
	var txtSelectNode = document.getElementById("SelectNode").value;
	window.location.href="stat_node_conn.php?id="+txtSelectNode;
}

</script>

<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="stat_node_bandwidth.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">实时带宽</span></a></td>
				<td height="31" width="85"><a href="stat_node_bandwidth_max.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">峰值带宽</span></a></td>
				<td height="31" width="85"><a href="stat_node_day_download.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">日流量统计</span></a></td>				
				<td height="31" width="85"><a href="stat_node_conn.php?id=<?php echo $node_id; ?>"><span class="title_bt">连接数统计</span></a></td>				
				<td height="31" width="85"><a href="stat_node_hostorder.php?id=<?php echo $node_id; ?>"><span class="title_bt_active">最新排名</span></a></td>
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">		
	   	<table width="800" border="0" class="bottom_btn">
			<tr height="30">
			<td bgcolor="#FFFFE6"><span class="input_tips_txt3"></span>
			<div class="div_search_title" style="padding-right:190px">
					<input id="txtNodeID" type="hidden" size="20" maxlength="256" value="<?php echo $node_id; ?>" />

		<select id="SelectNode" style="width:280px" onChange="OnSelectNode()">
<?php  
	$node_id 	= isset($_GET['id'])?$_GET['id']:'';
	
  	$db_link = FikCDNDB_Connect();
	if($db_link)
	{
		$node_id 	= mysql_real_escape_string($node_id); 
		
		$sql = "SELECT * FROM fikcdn_node;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);	echo $row_count;
			for($i=0;$i<$row_count;$i++)
			{
				$this_node_id	= mysql_result($result,$i,"id");	
				$node_name  	= mysql_result($result,$i,"name");	
				$node_ip 		= mysql_result($result,$i,"ip");
				$node_unicom_ip = mysql_result($result,$i,"unicom_ip");
				$group_id 		= mysql_result($result,$i,"groupid");
				
				if(strlen($node_id)<=0) $node_id = $this_node_id;
				
				$sFikIP = $node_ip;
				if(strlen($sFikIP)<=0) $sFikIP=$node_unicom_ip;
				$show_name = $sFikIP.' ('.$node_name.')';
	
				if($this_node_id==$node_id)
				{
					echo '<option value="'.$this_node_id.'" selected="selected" >'.$show_name.'</option>';
					
					$show_this_name = $sFikIP.' - '.$node_name;		
				}
				else
				{
					echo '<option value="'.$this_node_id.'" >'.$show_name.'</option>';
				}
			}
		}
		
		$timenow=time();
		
		// 计算最大带宽
		$timeval = $timenow-24*60*60;
		$sql = "SELECT max(CurrentUserConnections),max(CurrentUpstreamConnections),avg(CurrentUserConnections),avg(CurrentUpstreamConnections) FROM realtime_list WHERE node_id=$node_id AND time>=$timeval";
		$result2 = mysql_query($sql,$db_link);
		if($result2 && mysql_num_rows($result2)>0)
		{
			$max1_user_connections= mysql_result($result2,0,"max(CurrentUserConnections)");
			$max1_upstream_connections = mysql_result($result2,0,"max(CurrentUpstreamConnections)");	
			$avg1_user_connections= mysql_result($result2,0,"avg(CurrentUserConnections)");
			$avg1_upstream_connections = mysql_result($result2,0,"avg(CurrentUpstreamConnections)");

			$avg1_user_connections = round($avg1_user_connections,1);
			$avg1_upstream_connections = round($avg1_upstream_connections,1);														
		}
				
		if(strlen($max1_user_connections)<=0) $max1_user_connections=0;
		if(strlen($max1_upstream_connections)<=0) $max1_upstream_connections=0;			
		if(strlen($avg1_user_connections)<=0) $avg1_user_connections=0;
		if(strlen($avg1_upstream_connections)<=0) $avg1_upstream_connections=0;	
				
		// 计算最大带宽
		$timeval = $timenow-24*60*60*7;
		$sql = "SELECT max(CurrentUserConnections),max(CurrentUpstreamConnections) FROM realtime_list WHERE node_id=$node_id AND time>=$timeval";
		$result2 = mysql_query($sql,$db_link);
		if($result2 && mysql_num_rows($result2)>0)
		{
			$max7_user_connections = mysql_result($result2,0,"max(CurrentUserConnections)");
			$max7_upstream_connections = mysql_result($result2,0,"max(CurrentUpstreamConnections)");								
		}	
		
		if(strlen($max7_user_connections)<=0) $max7_user_connections=0;
		if(strlen($max7_upstream_connections)<=0) $max7_upstream_connections=0;							
	}
?>
				</select>&nbsp;
							
				<select id="ConnectCountDateSelect" style="width:120px" onChange="OnSelectConnectCountDate()">
					<option value="1" >最近24小时</option>				
					<option value="7" >最近七天</option>
				</select>
			</div></td>
			</tr>
		</table>
		<table border="0" style="float:left">
		<tr>
		 <td width="820"><div id="placeholder" style="width:940px;height:380px; float:left;"></div></td>
		 		 <td style="padding-top:0px;">
		</td>	
		 </tr>
		</table> 
				
		<table width="940" border="0" cellspacing="0" cellpadding="0" style="padding-left:45px">
			<tr>
				<td class="title_line" width="20%" height="25" align="right" style="background-color: whitesmoke"><strong>24小时最大用户并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_max_mirror_flow_of_1day"><?php echo $max1_user_connections; ?></span></td>
				<td class="title_line" width="20%" height="25" align="right" style="background-color: whitesmoke"><strong>24小时平均用户并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_max_mirror_flow_of_1day"><?php echo $avg1_user_connections; ?></span></td>								
				<td class="title_line" width="18%" height="25" align="right" style="background-color: whitesmoke"><strong>7天内最大用户并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_max_mirror_flow_of_7day"><?php echo $max7_user_connections; ?></span></td>
			</tr>
			<tr>
				<td class="title_line" width="20%" height="25" align="right" style="background-color: whitesmoke"><strong>24小时最大源站并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_avg_mirror_flow_of_1day"><?php echo $max1_upstream_connections; ?></span></td>
				<td class="title_line" width="20%" height="25" align="right" style="background-color: whitesmoke"><strong>24小时平均源站并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_avg_mirror_flow_of_1day"><?php echo $avg1_upstream_connections; ?></span></td>								
				<td class="title_line" width="18%" height="25" align="right" style="background-color: whitesmoke"><strong>7天内最大源站并发数：</strong></td>
				<td class="title_line" width="10%" height="25" align="right" style="padding-right: 10px;"><span id="tb_avg_mirror_flow_of_7day"><?php echo $max7_upstream_connections; ?></span></td>
			</tr>
		</table>
						
	  </td> 
	  <td background="../images/mail_rightbg.gif">&nbsp;</td>
  </tr>
  
   <tr>
    <td valign="bottom" background="../images/mail_leftbg.gif"><img src="../images/buttom_left2.gif" width="17" height="17" /></td>
    <td background="../images/buttom_bgs.gif"><img src="../images/buttom_bgs.gif" width="17" height="17"></td>
    <td valign="bottom" background="../images/mail_rightbg.gif"><img src="../images/buttom_right2.gif" width="16" height="17" /></td>
  </tr> 
</table>
</div>

<div id="textStatDataTable"></div>

<script type="text/javascript">	


var enginConn_chart;

function update_enginConn_chart(data){
	enginConn_chart.redraw();
	var down_data=[];
	var up_data=[];

	down_data = __StatDataSets[0];
	up_data = __StatDataSets[1];
	
	var down_name = down_data['name'];
	var up_name = up_data['name'];
	
	var down_num = [];
	var up_num = [];
	
	var data_grp = [];
	
	/*
    for(var k = enginConn_chart.series.length - 1; k >= 0; k--){
         enginConn_chart.series[k].remove();
    }	
	
	enginConn_chart.addSeries({
		name: down_name,
		data: down_num,
	});
	
	//[{x: 12,y: 310}, {x: 24,y: 345},{x: 34,y: 225},{x: 67,y: 465},{x: 123,y: 78},{x: 233,y: 35},{x: 363,y: 234}],
	enginConn_chart.addSeries({
		name:  up_name,
		data: up_num,
	});	
		
	var series1 = enginConn_chart.series[0];
	var series2 = enginConn_chart.series[1];
	
  var series = this.series[0];
                        setInterval(function() {
                            var x = (new Date()).getTime(), // current time
                                y = Math.random();
                            series.addPoint([x, y], true, true);
                        }, 1000);
	*/				
	
	var xData = 0;		
	var yData = 0;	
	
	for(var key in down_data['data'])
	{		
		data_grp = down_data['data'][key];
			
		xData = parseInt(data_grp[0])*1000;			
		yData = parseInt(data_grp[1]);
		
		var xDate =	new Date(parseInt(data_grp[0]));
			
		down_num.push({x : xData, y : yData});
		
	}
		
	for(var key in up_data['data'])
	{		
		data_grp = up_data['data'][key];		

		xData = parseInt(data_grp[0])*1000;	
		yData = parseInt(data_grp[1]);
				
		up_num.push({ x : xData, y : yData});
	}
	
	//var jsonText1 = JSON.stringify(down_num); 
	//var jsonText2 = JSON.stringify(up_num);

	//[{x: 12,y: 10}, {x: 24,y: 45},{x: 34,y: 25},{x: 67,y: 265},{x: 123,y: 365},{x: 233,y: 95},{x: 363,y: 87}],
	
    for(var k = enginConn_chart.series.length - 1; k >= 0; k--){
         enginConn_chart.series[k].remove();
    }	
		
	enginConn_chart.addSeries({
		type: 'area',
		color: '#2ebacb',//'#2f7ed8',
		name: down_name,
		data: down_num,
	});
	
	//[{x: 12,y: 310}, {x: 24,y: 345},{x: 34,y: 225},{x: 67,y: 465},{x: 123,y: 78},{x: 233,y: 35},{x: 363,y: 234}],
	enginConn_chart.addSeries({
		type: 'area',
		color: '#f0d52e',//color: '#a8d822',
		name:  up_name,
		data: up_num,
	});
}

Highcharts.setOptions( {
	global : {
		useUTC : false
	}
});

jQuery(document).ready(function(){
		sLabelName='';
		aryData=[];                          

		enginConn_chart = new Highcharts.Chart({
		   chart: {
				renderTo: 'placeholder',
				defaultSeriesType: 'spline',
                marginRight: 0,
                marginBottom: 40,
				backgroundColor: '#F8F9FA'
		   },   

		   title: {
				text: '<span class="input_tips_txt"><strong><?php echo $show_this_name; ?></strong></span>',
				style: {color:'#3E576F',fontSize:'13px'},
				align: 'center',
				x: -40, //center
				y: 15
		   },		
			
		   xAxis: {
				type: 'datetime',
            	lineWidth :2,//自定义x轴宽度  
            	gridLineWidth :0,//默认是0，即在图上没有纵轴间隔线
				dateTimeLabelFormats : {
					second: '%H:%M:%S',
					minute: '%H:%M',
					hour: '%H:%M',
					day: '%m-%d', 
					week: '%m-%d',
					month: '%Y-%m',
					year: '%Y'
				},		
				lineColor : '#3E576F'
		   },

		   yAxis: {
		   		min: 0,
				labels:{
					// 标签位置
					align: 'right',
					// 标签格式化
					formatter: function(){
						return this.value + '';
					}
				},				
				title: {
					text: '服务器并发连接数统计 (单位：个)',
					style: {color:'#aaaaaa',fontSize:'10px'},
				},
		   },
		   
		   tooltip: {
				formatter: function() { //当鼠标悬置数据点时的格式化提示 
					var myDate = new Date(this.x);
					var strTime = numAddZero((myDate.getMonth()+1),2) + '-' + numAddZero(myDate.getDate(),2) + " " + numAddZero(myDate.getHours(),2) + ':' + numAddZero(myDate.getMinutes(),2) + ':' + numAddZero(myDate.getSeconds(),2); 
					
					//var strTime = myDate.toLocaleString();
	       	        return '<b>' + strTime + '</b><br/><b>' + this.series.name + ': ' + this.y + ' 个</b>'; 
				}
		   },
		  
		   exporting:{
				// 是否允许导出
				enabled:false
				
		  },	
				
    	   plotOptions: {
				area: {
					fillOpacity: 0.2,
					lineWidth: 1,
					marker: {
						enabled: false,
						states: {
							hover: {
								enabled: true,
								radius: 5
							}
						}
					},
					shadow: false,
					states: {
						hover: {
							lineWidth: 1
						}
					},
					threshold: null
				}
			},		   
								   
           legend: {
				enabled: true,       
                layout: 'horizontal',
                align: 'right',
                verticalAlign: 'top',
                x: 0,
                y: 0,
                borderWidth: 0
            },   
   			
			credits: {  
                enabled: false     //去掉highcharts网站url  
           	},
	});
});


getConnectCountStatData(<?php echo $node_id; ?>,1);	

</script>
<?php

include_once("./tail.php");
?>
