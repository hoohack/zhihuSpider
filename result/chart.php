<?php
	require_once '../spider/pdo_mysql.php';
	require_once '../spider/user.php';

	//total_count
	$total_count = User::totalCount();

	$male_condition = array(
		'where' => array(
			'gender' => 'MALE'
		)
	);
	$male_count = User::totalCount($male_condition);

	$female_condition = array(
		'where' => array(
			'gender' => 'FEMALE'
		)
	);
	$female_count = User::totalCount($female_condition);

	$address_count_list = User::addressCountList();

	$major_count_list = User::majorCountList();

	$business_count_list = User::businessCountList();
?>
<!DOCTYPE html>
<html>
<head>
	<title>知乎用户分析</title>
	<style type="text/css">
		#gender_container {
			float: left;
		}

		#address_container {
			float: left;
		}

		#business_container {
			float: left;
		}

		#major_container {
			float: right;
		}
	</style>
</head>
<body>
	<div id="gender_container" style="min-width:800px;height:400px"></div>

	<div id="address_container" style="min-width:800px;height:400px"></div>

	<div id="major_container" style="min-width:800px;height:400px"></div>

	<div id="business_container" style="min-width:800px;height:400px"></div>
</body>


<script src="./highcharts/js/jquery.min.js"></script>
<script src="./highcharts/js/highcharts.js"></script>
<script>
	$(function () {
	    $('#gender_container').highcharts({
	        chart: {
	            plotBackgroundColor: null,
	            plotBorderWidth: null,
	            plotShadow: false
	        },
	        title: {
	            text: '用户男女比例'
	        },
	        tooltip: {
	    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
	        },
	        plotOptions: {
	            pie: {
	                allowPointSelect: true,
	                cursor: 'pointer',
	                dataLabels: {
	                    enabled: true,
	                    color: '#000000',
	                    connectorColor: '#000000',
	                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	                }
	            }
	        },
	        series: [{
	            type: 'pie',
	            name: 'Browser share',
	            data: [
	                ['男', <?php echo $male_count;?>],
	                ['女', <?php echo ($female_count);?>]
	            ]
	        }]
	    });
	});

	$(function () {
	    $('#address_container').highcharts({
	        chart: {
	            plotBackgroundColor: null,
	            plotBorderWidth: null,
	            plotShadow: false
	        },
	        title: {
	            text: '用户人群地区分布'
	        },
	        tooltip: {
	    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
	        },
	        plotOptions: {
	            pie: {
	                allowPointSelect: true,
	                cursor: 'pointer',
	                dataLabels: {
	                    enabled: true,
	                    color: '#000000',
	                    connectorColor: '#000000',
	                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	                }
	            }
	        },
	        series: [{
	            type: 'pie',
	            name: 'Browser share',
	            data: [
	            	<?php foreach ($address_count_list as $address_count) { ?>
	            		<?php if ($address_count['address'] != '') {?>
	            		['<?php echo $address_count["address"];?>', <?php echo $address_count['address_count'];?>],
	            		<?php }?>
	            	<?php } ?>
	            ]
	        }]
	    });
	});

	$(function () {
	    $('#major_container').highcharts({
	        chart: {
	            plotBackgroundColor: null,
	            plotBorderWidth: null,
	            plotShadow: false
	        },
	        title: {
	            text: '用户专业分布'
	        },
	        tooltip: {
	    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
	        },
	        plotOptions: {
	            pie: {
	                allowPointSelect: true,
	                cursor: 'pointer',
	                dataLabels: {
	                    enabled: true,
	                    color: '#000000',
	                    connectorColor: '#000000',
	                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	                }
	            }
	        },
	        series: [{
	            type: 'pie',
	            name: 'Browser share',
	            data: [
	            	<?php foreach ($major_count_list as $major_count) { ?>
	            		<?php if ($major_count['major'] != '') {?>
	            		['<?php echo $major_count["major"];?>', <?php echo $major_count['major_count'];?>],
	            		<?php }?>
	            	<?php } ?>
	            ]
	        }]
	    });
	});

	$(function () {
	    $('#business_container').highcharts({
	        chart: {
	            plotBackgroundColor: null,
	            plotBorderWidth: null,
	            plotShadow: false
	        },
	        title: {
	            text: '用户职业分布'
	        },
	        tooltip: {
	    	    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
	        },
	        plotOptions: {
	            pie: {
	                allowPointSelect: true,
	                cursor: 'pointer',
	                dataLabels: {
	                    enabled: true,
	                    color: '#000000',
	                    connectorColor: '#000000',
	                    format: '<b>{point.name}</b>: {point.percentage:.1f} %'
	                }
	            }
	        },
	        series: [{
	            type: 'pie',
	            name: 'Browser share',
	            data: [
	            	<?php foreach ($business_count_list as $business_count) { ?>
	            		<?php if ($business_count['business'] != '') {?>
	            		['<?php echo $business_count["business"];?>', <?php echo $business_count['business_count'];?>],
	            		<?php }?>
	            	<?php } ?>
	            ]
	        }]
	    });
	});
</script>
</html>