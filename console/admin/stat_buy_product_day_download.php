<?php
include_once("./head.php");

// ID
$buy_id 		= isset($_GET['buy_id'])?$_GET['buy_id']:'';
$date2          = isset($_GET['date2'])?$_GET['date2']:'30';
?>
<script type="text/javascript" src="../highcharts-302/jquery/1.8.2/jquery.min.js"></script>
<script src="../highcharts-302/js/highcharts.js"></script>
<script src="../highcharts-302/js/modules/exporting.js"></script>
<script type="text/javascript">	

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

function getCrashReportStatData(buy_id,timeval)
{
	var xmlhttp;
	
    if (window.XMLHttpRequest){
	  	// code for IE7+, Firefox, Chrome, Opera, Safari
	  	xmlhttp=new XMLHttpRequest();
	}else{
	  	// code for IE6, IE5
	  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
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

	var postUrl = "request_stat_data.php?mod=product&action=DayDownloadCount&buy_id="+buy_id+"&timeval="+timeval;
	xmlhttp.open("GET",postUrl,true);
	xmlhttp.send(null);
	return false;
}

function OnSelectFlawDate()
{
	var buy_id= document.getElementById("buySelect").value;	
	var nFlawDateSelect = document.getElementById("FlawDateSelect").value;
	getCrashReportStatData(buy_id,nFlawDateSelect);
}

function selectProduct()
{
	var buy_id= document.getElementById("buySelect").value;
	var nDateSelect2 = document.getElementById("BandwidthDateSelect2").value;
	window.location.href="stat_buy_product_day_download.php?buy_id="+buy_id+"&date2="+nDateSelect2;
}
function OnSelectBandwidthDate2()
{
	selectProduct();
}
</script>

<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="stat_buy_product_bandwidth.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">实时带宽</span></a></td>
				<td height="31" width="85"><a href="stat_buy_product_bandwidth_max.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">峰值带宽</span></a></td>	
				<td height="31" width="85"><a href="stat_buy_product_day_download.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt">日流量统计</span></a></td>
				<td height="31" width="85"><a href="stat_buy_product_day_request.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">日请求量统计</span></a></td>					
				<td height="31" width="85"><a href="stat_buy_product_month.php?buy_id=<?php  echo $buy_id; ?>"><span class="title_bt_active">月度流量</span></a></td>					
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
			<tr height="30" bgcolor="#FFFFE6">
			<td><span class="input_tips_txt3"></span><div class="div_search_title" style="padding-right:190px">
			
		选择查看的套餐：	
				<select id="buySelect" name="buySelect" style="width:240px" onChange="selectProduct()">
<?php
	
 	$db_link = FikCDNDB_Connect();
	if($db_link)
	{	
		$buy_id = mysql_real_escape_string($buy_id);
		$date2 = mysql_real_escape_string($date2);
			
		$sql = "SELECT * FROM fikcdn_buy;"; 
		$result = mysql_query($sql,$db_link);
		if($result)
		{
			$row_count=mysql_num_rows($result);
			for($i=0;$i<$row_count;$i++)
			{
				$this_id  	 		= mysql_result($result,$i,"id");
				$this_username  	 = mysql_result($result,$i,"username");	
				$this_product_id  	 = mysql_result($result,$i,"product_id");	
				
				if(strlen($buy_id)<=0) $buy_id  = $this_id;
				
				$sql = "SELECT * FROM fikcdn_product WHERE id='$this_product_id'";
				$result2 = mysql_query($sql,$db_link);
				if($result2 && mysql_num_rows($result2)>0)
				{
					$product_name  		= mysql_result($result2,0,"name");
					$product_name = $product_name.'('.$this_username.')';
				}
										
				if($buy_id==$this_id)
				{
					echo '<option value="'.$this_id.'" selected="selected">'.$product_name."</option>";
					$show_this_name = $product_name;
				}
				else
				{
					echo '<option value="'.$this_id.'">'.$product_name."</option>";				
				}
			}
		}
	}			
 ?>
				</select>&nbsp;&nbsp;
				
				<select id="FlawDateSelect" style="width:120px" onChange="OnSelectFlawDate()">
					<option value="30" >最近一个月</option>
					<option value="60" >最近三个月</option>
				</select>				
										
				</div></td>
			</tr>
		</table>
		<table border="0" style="float:left">
		<tr>
		 <td width="820"><div id="placeholder" style="width:940px;height:380px; float:left;"></div></td>
		 </tr>
		</table> 
		
		<br />
		<br />
		<br />		
		
	<table width="800" border="0" class="bottom_btn">
			<tr height="30" bgcolor="#FFFFE6">
			<td>
			<span class="input_tips_txt3">日流量详细统计数据 - <?php echo $show_this_name; ?></span>
			<div class="div_search_title" style="padding-right:190px">选择日期：			
				<select id="BandwidthDateSelect2" style="width:120px" onChange="OnSelectBandwidthDate2()">
					<option value="30" <?php if($date2==30) echo 'selected="selected"'; ?> >最近一个月</option>
					<option value="60" <?php if($date2==60) echo 'selected="selected"'; ?>>最近三个月</option>
				</select>
			</div>
			</td>
			</tr>
		</table>
						 
		<div id="div_search_result2" style="width:930px; padding-left:0px">
			<table width="800" border="0" class="dataintable" id="domain_table">
				<tr id="tr_domain_title">
					<th align="center" width="150">时间</th> 
					<th align="right" width="120">每日用户下载数据</th>
					<th align="right" width="120">每日用户上传数据</th>
					<th align="right" width="100" align="center">每日请求量</th>
				</tr>	
<?php
			if($db_link){
				$timeval1 = mktime(0,0,0,date("m"),date("d"),date("Y"))-($date2*60*60*24);
				$timeval2 = $timeval1 + 60*60*24;
				//echo "the timeval1 is=". date("Y-m-d H:i:s",$timeval1)."<br/>";
				//echo "the timeval2 is=". date("Y-m-d H:i:s",$timeval2)."<br/>";
				
				$sql = "SELECT * FROM domain_stat_product_day where buy_id='$buy_id' AND time>='$timeval1' "; 
				$result = mysql_query($sql,$db_link);
				if($result){
					$row_count=mysql_num_rows($result);
					for($i=0;$i<$row_count;$i++){
						$id  			= mysql_result($result,$i,"id");	
						$this_buy_id	 	= mysql_result($result,$i,"buy_id");	
						$this_time  		= mysql_result($result,$i,"time");	
						$RequestCount   		= mysql_result($result,$i,"RequestCount");	
						$UploadCount	= mysql_result($result,$i,"UploadCount");
						$DownloadCount	   	= mysql_result($result,$i,"DownloadCount");		
						
						echo '<tr bgcolor="#FFFFFF" align="center" onMouseMove="Event_trOnMouseOver(this)" onMouseOut="Event_trOnMouseOut(this)" id="tr_domain_'.$id.'">';
						echo '<td>'.date("Y-m-d",$this_time).'</td>';
						echo '<td align="right">'.PubFunc_GBToString($DownloadCount).'</td>';
						echo '<td align="right">'.PubFunc_GBToString($UploadCount).'</td>';	
						echo '<td align="right">'.$RequestCount.' 次</td>';					
					}		
				}
			}	
?>								
		 	</table>
		 </div>		

<?php		 
/*
		 <div style="width:930px; padding-left:0px">
		 <table width="800" border="0" class="disc">
			<tr>
			<td bgcolor="#FFFFE6" height="25"></td>
			</tr>
		</table>	
		</div>
		*/
?>		 		
	  </td> 
	  <td background="../images/mail_rightbg.gif">&nbsp;</td>
  </tr>
  
   <tr>
    <td valign="bottom" background="../images/mail_leftbg.gif"><img src="../images/buttom_left2.gif" width="17" height="17" /></td>
    <td background="../images/buttom_bgs.gif"><img src="../images/buttom_bgs.gif" width="17" height="17"></td>
    <td valign="bottom" background="../images/mail_rightbg.gif"><img src="../images/buttom_right2.gif" width="16" height="17" /></td>
  </tr> 
</table>
</div><div id="textStatDataTable"> </div>

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
	
	var xData = 0;		
	var yData = 0;	
		
	for(var key in down_data['data'])
	{		
		data_grp = down_data['data'][key];
		
		xData = parseInt(data_grp[0])*1000;	
		yData = parseFloat(data_grp[1]);
				
		down_num.push({ y : yData,x : xData});
	}
	
		
	for(var key in up_data['data'])
	{		
		data_grp = up_data['data'][key];
			
		xData = parseInt(data_grp[0])*1000;	
		yData = parseFloat(data_grp[1]);
				
		up_num.push({ y : yData, x : xData});
	}
		
    for(var k = enginConn_chart.series.length - 1; k >= 0; k--){
         enginConn_chart.series[k].remove();
    }
		
	//var jsonText = JSON.stringify(up_num); 
	//alert(jsonText);
	
	//[{x: 12,y: 10}, {x: 24,y: 45},{x: 34,y: 25},{x: 67,y: 265},{x: 123,y: 365},{x: 233,y: 95},{x: 363,y: 87}],
	
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
				defaultSeriesType: 'column',
                marginRight: 0,
                marginBottom: 40,
				backgroundColor: '#F8F9FA'
		   },                                 

		   title: {
				text: '<span class="input_tips_txt"><strong><?php echo $show_this_name; ?></strong></span>',
				style: {color:'#004499',fontSize:'13px'},
				align: 'center',
				x: -40, //center
				y: 15
		   },
		   /*	
           subtitle: {
               text: '服务器带宽统计',
               x: -20
           },
		   */
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

  		   exporting:{
				// 是否允许导出
				enabled:false,
				// 按钮配置
				buttons:{
					// 导出按钮配置
					exportButton:{
						menuItems: null,
						onclick: function() {
							this.exportChart();
						}
					},
					// 打印按钮配置
					printButton:{
						enabled:false
					}
				},
				// 文件名
				filename: '报表',
				// 导出文件默认类型
				type:'application/pdf'
			},
			
    		plotOptions: {			   
			    column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                },
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
			/*									   
            plotOptions: {
                area: {
                    fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                        stops: [
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]				
                        ]
                    },
                    lineWidth: 1,
                    marker: {
                        enabled: false
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
			*/
		   yAxis: {
		   		min: 0,
				labels:{
					// 标签位置
					align: 'right',
					// 标签格式化
					formatter: function(){
						return this.value + ' GB';
					}
				},
								  
				title: {
					text: '套餐日流量统计',
					style: {color:'#aaaaaa',fontSize:'12px'},
				},
				showFirstLabel: true,  
				plotLines: [{
						 value: 0,
						 width: 1,
						 color: '#87BED3'
				}]
		   },
		   
		   tooltip: {
		   		enabled: true,
				userHTML: true,
				valueSuffix: 'GB',
				formatter: function() { //当鼠标悬置数据点时的格式化提示 
					var myDate = new Date(this.x);
					var strTime = myDate.getFullYear() + '-' + numAddZero((myDate.getMonth()+1),2) + '-' + numAddZero(myDate.getDate(),2); 
					
					//var strTime = myDate.toLocaleString();
	       	        return '<b>' + strTime + '</b><br/><b>' + this.series.name + ': ' + this.y + ' GB</b>'; 
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

getCrashReportStatData(<?php echo $buy_id; ?>,30);
</script>
<?php

include_once("./tail.php");
?>
