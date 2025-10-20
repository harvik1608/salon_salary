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
			<div class="col-xxl-8 mb-6 order-0">
				<div class="card">
					<div class="d-flex align-items-start row">
						<div class="col-sm-7">
							<div class="card-body">
								<h5 class="card-title text-primary mb-3">Welcome <?php echo $userdata['fname']." ".$userdata['lname']; ?>! 🎉</h5>
								<p class="mb-6"><?php echo date('d M, Y'); ?><br><?php echo date('l'); ?></p>
								<?php
									if($is_checkedIn == 1) {
										echo '<a href="'.base_url('today-checkin').'" class="btn btn-sm btn-outline-primary">Check In</a>';
									} else if($is_checkedIn == 2){
										echo '<a href="'.base_url('today-checkout').'" class="btn btn-sm btn-outline-primary">Check Out</a>';
									} else {
										echo '<a class="btn btn-sm btn-outline-primary">'.$my_hours.' hours</a>';
									}
								?>
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
	</div>
</div>
<script src="<?php echo base_url('public/assets/vendor/libs/apex-charts/apexcharts.js'); ?>"></script>
<script type="text/javascript">
	var page_title = "Dashboard";
</script>
<?= $this->endSection(); ?>