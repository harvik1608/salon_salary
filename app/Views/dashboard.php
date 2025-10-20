<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<style>
	h5.text-primary, .card-body p {
		color: #000 !important;
	}
	#salon_report th {
		text-align: center;
	}
	#monthly_summary_report th,#monthly_summary_report td {
		border: 1px solid #000;
		padding: 5px;
	}
</style>
<?php 
	$session = session();
	$userdata = $session->get('userdata');
?>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<div class="col-xxl-5 col-lg-12">
				<div class="card h-100">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="card-title mb-0">
							<input type="month" id="year_month" value="<?php echo date('Y-m'); ?>" />
						</div>
						<a href="javascript:;" class="btn btn-primary btn-sm text-white" onclick="export_monthly_summary_report()" style="float: right;">Export</a>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-xxl-12">
								<div class="table-responsive">
									<table style="width: 100%;" id="monthly_summary_report">
										<thead>
											<tr>
												<th colspan="4"></th>
											</tr>
											<tr>
												<th width="5%">#</th>												
												<th>Salon</th>
												<th>Income</th>
												<th>Expense</th>
											</tr>
										</thead>
										<tbody>
											
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-xxl-7 mb-6 order-0">
				<div class="card">
					<div class="d-flex align-items-start row">
						<div class="col-sm-7">
							<div class="card-body">
								<h5 class="card-title text-primary mb-3">Welcome <?php echo $userdata['fname']." ".$userdata['lname']; ?>! 🎉</h5>
								<p class="mb-6"><?php echo date('d M, Y'); ?><br><?php echo date('l'); ?></p>
								<!-- <a href="javascript:;" class="btn btn-sm btn-outline-primary">View Badges</a> -->
							</div>
						</div>
						<div class="col-sm-5 text-center text-sm-left">
							<div class="card-body pb-0 px-0 px-md-6">
								<img src="<?php echo base_url('public/assets/img/illustrations/man-with-laptop.png'); ?>" height="175" class="scaleX-n1-rtl" alt="View Badge User">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row mt-5">
			<div class="col-xxl-12 col-lg-12">
				<div class="card h-100">
					<div class="card-header d-flex align-items-center justify-content-between">
						<div class="card-title mb-0">
							<input type="month" id="salon_report_month" value="<?php echo date('Y-m'); ?>" />
						</div>
						<a href="javascript:;" class="btn btn-primary btn-sm text-white" onclick="export_report()" style="float: right;">Export</a>
					</div>
					<div class="card-body">
						<div class="row">
							<div class="col-xxl-12">
								<div class="table-responsive">
									<table class="table table-default table-bordered" id="salon_report">
										<thead>
											<tr>
												<th width="20%">Staff/Salon</th>
												<?php
													if($salons) {
														foreach($salons as $salon) {
															echo '<th width="15%">'.$salon['name'].'</th>';
														}
													} 
												?>
												<th width="10%">TOTAL</th>
											</tr>
										</thead>
										<tbody>
											
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script type="text/javascript">
	var page_title = "Dashboard";
	var mode_labels = $.parseJSON('<?php echo json_encode($mode_labels); ?>');
	var mode_values = $.parseJSON('<?php echo json_encode($mode_values); ?>');
	$(document).ready(function(){
		load_monthly_summary_report();
		load_salon_report();
		$("#year_month").change(function(){
			load_monthly_summary_report();
		});
		$("#salon_report_month").change(function(){
			load_salon_report();
		});
	});
	function load_monthly_summary_report()
	{
		$.ajax({
			url: "<?php echo base_url('load-monthly-summary-report'); ?>",
			type: "GET",
			data:{
				year_month: $("#year_month").val()
			},
			dataType: "json",
			success:function(response){
				$("#monthly_summary_report thead tr:eq(0) th").html("<b>Salon Service Income - "+response.title+"</b>");
				$("#monthly_summary_report tbody").html(response.html);
			}
		});
	}
	function load_salon_report()
	{
		$.ajax({
			url: "<?php echo base_url('load-salon-report'); ?>",
			type: "GET",
			data:{
				year_month: $("#salon_report_month").val()
			},
			dataType: "json",
			success:function(response){
				$("#salon_report tbody").html(response.html);
			}
		});
	}
	function export_monthly_summary_report()
	{
		html2canvas($("#monthly_summary_report")[0]).then(canvas => {
            let imgData = canvas.toDataURL("image/png");

            // Create a download link
            let link = document.createElement("a");
            link.href = imgData;
            link.download = "captured_image.png";
            link.click();
        });
        show_toast("Report downloaded");
	}
	function export_report()
	{
		html2canvas($("#salon_report")[0]).then(canvas => {
            let imgData = canvas.toDataURL("image/png");

            // Create a download link
            let link = document.createElement("a");
            link.href = imgData;
            link.download = "captured_image.png";
            link.click();
        });
        show_toast("Report downloaded");
	}
	function load_income_chart()
	{
		var ctx = $('#myChart');

		// Destroy the previous chart instance before creating a new one
	    if (window.myIncomeChart instanceof Chart) {
	        window.myIncomeChart.destroy();
	    }
		$.ajax({
			url: "<?php echo base_url('load-income-chart'); ?>",
			type: "GET",
			data:{
				year_month: $("#year_month").val()
			},
			dataType: "json",
			success:function(response){
		        window.myIncomeChart = new Chart(ctx, {
	                type: 'bar',
	                data: {
	                    labels: response.labels,
	                    datasets: [
	                    	{
	                        	label: 'Income',
		                        data: response.data,
		                        backgroundColor: '#696cff',
		                        borderColor: '#696cff',
		                        borderWidth: 1,
		                        anchor: 'end',
				                align: 'top',
				                font: {
				                    family: "'Nunito', sans-serif",
				                    weight: 'bold',
				                    size: 14
				                },
				                color: '#000',
	                    	}
	                    ]
	                },
	                options: {
	                    plugins: {
	                        datalabels: {
	                            anchor: 'end',
	                            align: 'top',
	                            font: {
	                            	family: "'Nunito', sans-serif",
	                                weight: 'bold',
	                                size: 14
	                            },
	                            color: '#000', // Text color
	                            formatter: function(value, context) {
				                    // Display total when hovered
				                    if (context.datasetIndex === 0) {
				                        // Calculate total of all data points
				                        const total = context.dataset.data.reduce((sum, currentValue) => sum + currentValue, 0);
				                        return total;
				                    }
				                    return value; // Default behavior: display the individual value
				                }
	                        }
	                    },
	                    scales: {
	                        x: {
	                            ticks: {
	                                font: {
	                                    family: "'Nunito', sans-serif", // Custom font family for x-axis labels
	                                    size: 10
	                                }
	                            }
	                        },
	                        y: {
	                            beginAtZero: true,
	                            ticks: {
	                                font: {
	                                    family: "'Nunito', sans-serif", // Custom font family for y-axis labels
	                                    size: 10,
	                                    weight: 500
	                                }
	                            }
	                        }
	                    }
	                }
	            });
			}
		});
	}
</script>
<?= $this->endSection(); ?>