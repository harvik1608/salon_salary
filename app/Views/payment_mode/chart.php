<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<style>
	h5.text-primary, .card-body p {
		color: #000 !important;
	}
</style>
<?php 
	$session = session();
	$userdata = $session->get('userdata');
?>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<?php
				if($modes) {
					foreach($modes as $mode) {
			?>
						<div class="col-xxl-3 col-lg-3 mt-5">
							<div class="card h-100">
								<div class="card-header d-flex align-items-center justify-content-between">
									<div class="card-title mb-0">
										<h5 class="mb-1"><?php echo $mode['name']; ?></h5>
									</div>
								</div>
								<div class="card-body">
									<div class="row">
										<div class="col-xxl-12">
											<canvas id="modeChart<?php echo $mode['id']; ?>"></canvas>
										</div>
									</div>
								</div>
							</div>
						</div>
			<?php
					} 
				}
			?>
		</div>
	</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
	var page_title = "Payment Modes";
	// var mode_labels = $.parseJSON('<?php echo json_encode($mode_labels); ?>');
	// var mode_values = $.parseJSON('<?php echo json_encode($mode_values); ?>');
	// var xValues = mode_labels;
	// var yValues = mode_values;
	// var barColors = [
	//   "#b91d47",
	//   "#00aba9",
	//   "#2b5797",
	//   "#e8c3b9",
	//   "#1e7145"
	// ];
	// new Chart("modeChart", {
  	// 	type: "pie",
  	// 	data: {
    // 		labels: xValues,
    // 		datasets: [{
    //   			backgroundColor: barColors,
    //   			data: yValues
    // 		}]
  	// 	},
  	// 		options: {
    // 		title: {
    //   			display: true,
    //   			text: "World Wide Wine Production 2018"
    // 		}
  	// 	}
	// });
</script>
<?= $this->endSection(); ?>