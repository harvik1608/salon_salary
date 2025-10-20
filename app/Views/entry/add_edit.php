<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	if(empty($entry)) {
		$module_name = "New Entry";
		$action = base_url("entries");

		$salon_id = 0;
		$staff_id = $current_staff_id;
		$payment_mode_id = 0;
		$amount = "";
		$tip_amount = "";
		$date = date('Y-m-d');
		$time = date('H:i:s');
		$note = "";
	} else {
		$module_name = "Edit Entry";
		$action = base_url("entries/".$entry['id']);

		$salon_id = $entry['salon_id'];
		$staff_id = $entry['staff_id'];
		$payment_mode_id = $entry['payment_mode_id'];
		$amount = $entry['amount'];
		$tip_amount = $entry['tip_amount'];
		$date = $entry['date'];
		$time = $entry['time'];
		$note = $entry['note'];
	}
?>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<div class="col-xl">
				<div class="card mb-12">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0"><?php echo $module_name; ?></h5> <small class="text-body float-end">(<small class='astrock'>*</small>) indicates required field.</small>
					</div>
					<div class="card-body">
						<form id="main-form" action="<?php echo $action; ?>" method="post">
							<?php
								if($salon_id != 0) {
									echo '<input type="hidden" name="_method" value="PUT" />';
								} 
							?>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon<small class='astrock'>*</small></label>
										<select class="form-control select2" id="salon_id" name="salon_id">
											<option value="">Please select</option>
											<?php
												if($salons) {
													foreach($salons as $salon) {
											?>
														<option value="<?php echo $salon['id']; ?>" <?php echo $salon['id'] == $salon_id ? "selected" : ""; ?>><?php echo $salon['name']; ?></option>
											<?php
													}
												}
											?>
										</select>
										<label id="salon_id-error" class="error" for="salon_id" style="display: none;"></label>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Staff</label>
										<select class="form-control" id="staff_id" name="staff_id">
											<option value="">Please select</option>
											<?php
												if($staffs) {
													foreach($staffs as $staff) {
											?>
														<option value="<?php echo $staff['id']; ?>" <?php echo $staff['id'] == $staff_id ? "selected" : ""; ?>>
															<?php echo $staff['fname']." ".$staff['lname']; ?>
														</option>
											<?php
													}
												}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Payment Mode<small class='astrock'>*</small></label>
										<select class="form-control select2" id="payment_mode_id" name="payment_mode_id">
											<option value="">Please select</option>
											<?php
												if($modes) {
													foreach($modes as $mode) {
											?>
														<option value="<?php echo $mode['id']; ?>" <?php echo $mode['id'] == $payment_mode_id ? "selected" : ""; ?>>
															<?php echo $mode['name']; ?>
														</option>
											<?php
													}
												}
											?>
										</select>
										<label id="payment_mode_id-error" class="error" for="payment_mode_id" style="display: none;"></label>
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Amount<small class='astrock'>*</small></label>
										<input type="number" class="form-control" id="amount" name="amount" placeholder="Enter amount" value="<?php echo $amount; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Tip</label>
										<input type="number" class="form-control" id="tip_amount" name="tip_amount" placeholder="Enter tip" value="<?php echo $tip_amount; ?>" />
									</div>
								</div>
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Date<small class='astrock'>*</small></label>
										<input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" />
									</div>
								</div>
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Time</label>
										<input type="time" class="form-control" id="time" name="time" value="<?php echo $time; ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Note</label>
										<input type="text" class="form-control" id="note" name="note" placeholder="Enter note" value="<?php echo $note; ?>" />
									</div>
								</div>						
							</div>
							<button type="submit" class="btn btn-primary btn-sm">SUBMIT</button>
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('entries'); ?>">Back</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Entries";
	$(document).ready(function(){
		$("#main-form").validate({
			rules:{
				salon_id:{
					required: true
				},
				payment_mode_id:{
					required: true
				},
				amount:{
					required: true
				},
				date:{
					required: true
				}
			},
			messages:{
				salon_id:{
					required: "<b>Salon is required.</b>"
				},
				payment_mode_id:{
					required: "<b>Payment Mode is required.</b>"
				},
				amount:{
					required: "<b>Amount is required.</b>"
				},
				date:{
					required: "<b>Date is required.</b>"
				}
			}
		});
		$("#main-form").submit(function(e){
			e.preventDefault();

			if($("#main-form").valid()) {
				$.ajax({
					url: $("#main-form").attr("action"),
					type: "post",
					data: new FormData(this),
					contentType: false,
					processData: false,
					cache: false,
					beforeSend:function(){
						$("#main-form button[type=submit]").html('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>').attr("disabled",true);
					},
					success:function(response){
						if(response.status == "success") {
							window.location.href = $("#back-btn").attr("href");
						}
					},
					error: function(xhr, status, error) {  // Function to handle errors
					    alert(error);
					    $("#main-form button[type=submit]").html('SUBMIT').attr("disabled",false);
					},
				});
			}
		});
	});
</script>
<?= $this->endSection(); ?>