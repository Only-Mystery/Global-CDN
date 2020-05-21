<?php
include_once("./head.php");

// 组ID
$buy_id 		= isset($_GET['buy_id'])?$_GET['buy_id']:'';
?>
<script language="javascript" type="text/javascript" src="../flot/excanvas.js"></script>
<script language="javascript" type="text/javascript" src="../flot/jquery.js"></script>
<script language="javascript" type="text/javascript" src="../flot/jquery.flot.js"></script>
<script language="javascript" type="text/javascript" src="../flot/jquery.flot.stack.js"></script>
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

var __StatDataSets;
$(function () {

	function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y + 5,
            left: x + 15,
            border: '1px solid #aaa',
            padding: '2px',
            'background-color': '#efefef',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }
	
 	var previousPoint = null;
 
    $("#placeholder").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));
     
		if (item) {
			if (previousPoint != item.dataIndex) {
				previousPoint = item.dataIndex;
				
				$("#tooltip").remove();
				//var x = item.datapoint[0].toFixed(2),
				var	y = item.datapoint[1];
				
				showTooltip(item.pageX, item.pageY,
							y+" mbps");
			}
		}
		else {
			$("#tooltip").remove();
			previousPoint = null;            
		}
        
    });		
});

function getCrashReportStatData()
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
							
			$(function(){			
				var opt ={		
					points:{show:true, clickable:true, hoverable:true},		
					lines:{show:true, lineWidth:1},	
				    grid: { hoverable: true, clickable: true,borderWidth: 1,borderColor: "#E3E6EB" },
					legend: {show:true,backgroundColor:"#FFFFE9",backgroundOpacity:0,container: $("#showChartLegend")},
					xaxis: {show:false,mode:"time", timeformat: ""}
				}; 
			
				$.plot($("#placeholder"),data,opt);			
			});
		}
	}

	var postUrl = "request_stat_data.php?mod=product&action=DownloadCount";
	xmlhttp.open("GET",postUrl,true);
	xmlhttp.send(null);
	return false;
}

</script>

<div style="min-width:780px;">
<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#F8F9FA">
  <tr>
    <td width="17" valign="top" background="../images/mail_leftbg.gif"><img src="../images/left-top-right.gif" width="17" height="29" /></td>
    <td valign="top" background="../images/content-bg.gif">
	   <table width="100%" height="31" border="0" cellpadding="0" cellspacing="0" class="left_topbg" id="table2" >
			<tr height="31" class="TabToolTitle">
				<td height="31" width="85"><a href="stat_buy_product_download.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt">流量统计</span></a></td>
				<td height="31" width="85"><a href="stat_buy_product_request.php?buy_id=<?php echo $buy_id; ?>"><span class="title_bt_active">请求量统计</span></a></td>					
				<td width="95%"></td>
			</tr>
        </table>
	</td>
    <td width="16" valign="top" background="../images/mail_rightbg.gif"><img src="../images/nav-right-bg.gif" width="16" height="29" /></td>
  </tr>    
  <tr height="500">
	  <td valign="middle" background="../images/mail_leftbg.gif">&nbsp;</td> 
	  <td valign="top">
	   		<table width="800" border="0" class="dataintable">
			<tr height="30">
			<td><span class="input_tips_txt3">套餐月流量统计： </span><div class="div_search_title" style="padding-right:330px">
				</div></td>
			</tr>
		</table>
		<table border="0" style="float:left">
		<tr>
		 <td width="820"><div id="placeholder" style="width:780px;height:280px; float:left;"></div></td>
		 </tr>
		</table> 
		<div id="showChartLegend" style="padding-top:10px;"></div>
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
				defaultSeriesType: 'spline',
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
						return this.value + ' Mbps';
					}
				},
								  
				title: {
					text: '域名实时带宽统计',
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
				valueSuffix: 'Mbps',
				formatter: function() { //当鼠标悬置数据点时的格式化提示 
					var myDate = new Date(this.x);
					var strTime = myDate.getFullYear() + '-' + numAddZero((myDate.getMonth()+1),2) + '-' + numAddZero(myDate.getDate(),2) + " " + numAddZero(myDate.getHours(),2) + ':' + numAddZero(myDate.getMinutes(),2) + ':' + numAddZero(myDate.getSeconds(),2); 
					
					//var strTime = myDate.toLocaleString();
	       	        return '<b>' + this.series.name + '</b><br/><b>' + strTime + ': ' + this.y + ' Mbps</b>'; 
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


getCrashReportStatData();
</script>
<?php

include_once("./tail.php");
?>
