<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	$module_name = "Edit Attendance";
	$action = base_url("attendances/".$atten['id']);

	$salon_id = $atten['salon_id'];
	$staff_id = $atten['staff_id'];
	$in_time = $atten['in_time'];
	$out_time = $atten['out_time'];
	$date = $atten['date'];
	$break = $atten['break'];
	$hours_diff = $atten['hours_diff'];
	$note = $atten['note'];
	$old_salon_id = $atten['old_salon_id'];
	$rate = $atten['rate'];
	$is_from_other_salon = $atten['is_from_other_salon'];
	$tip = $atten['tip'];
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
							<input type="hidden" name="old_salon_id" value="<?php echo $old_salon_id; ?>" />
							<input type="hidden" name="rate" value="<?php echo $rate; ?>" />
							<input type="hidden" name="is_from_other_salon" value="<?php echo $is_from_other_salon; ?>" />
							<?php
								if($salon_id != 0) {
									echo '<input type="hidden" name="_method" value="PUT" />';
								} 
							?>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Staff<small class='astrock'>*</small></label>
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
										<label class="form-label" for="basic-default-fullname">Salon<small class='astrock'>*</small></label>
										<select class="form-control" id="salon_id" name="salon_id">
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
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Date<small class='astrock'>*</small></label>
										<input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">In Time<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="in_time" name="in_time" value="<?php echo $in_time; ?>" onblur="calc_hours()" />
									</div>
								</div>
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Out Time<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="out_time" name="out_time" value="<?php echo $out_time; ?>" onblur="calc_hours()" />
									</div>
								</div>
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Break<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="break" name="break" value="<?php echo $break; ?>" onblur="calc_hours()" />
									</div>
								</div>
								<div class="col-lg-2">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Working Hours<small class='astrock'>*</small></label>
										<input type="number" class="form-control" id="hours_diff" name="hours_diff" value="<?php echo $hours_diff; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Tip</label>
										<input type="number" class="form-control" id="tip" name="tip" value="<?php echo $tip; ?>" />
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
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('attendances'); ?>">Back</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Attendance";
	$(document).ready(function(){
		$("#main-form").validate({
			rules:{
				salon_id:{
					required: true
				},
				staff_id:{
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
				},
				time:{
					required: true
				}
			},
			messages:{
				salon_id:{
					required: "<b>Salon is required.</b>"
				},
				staff_id:{
					required: "<b>Staff is required.</b>"
				},
				payment_mode_id:{
					required: "<b>Payment Mode is required.</b>"
				},
				amount:{
					required: "<b>Amount is required.</b>"
				},
				date:{
					required: "<b>Date is required.</b>"
				},
				time:{
					required: "<b>Time is required.</b>"
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
	function calc_hours()
	{
		var in_time = $("#in_time").val();   // In Time (HH:MM)
		var out_time = $("#out_time").val(); // Out Time (HH:MM)
		var break_time = $("#break").val();  // Break Time (MM)

		// Parse in_time and out_time into Date objects
		var in_time_parts = in_time.split(':');
		var out_time_parts = out_time.split(':');

		// Create Date objects to handle in_time and out_time
		var in_date = new Date();
		in_date.setHours(in_time_parts[0]);
		in_date.setMinutes(in_time_parts[1]);
		in_date.setSeconds(0);  // Set seconds to 0 to avoid time inconsistencies

		var out_date = new Date();
		out_date.setHours(out_time_parts[0]);
		out_date.setMinutes(out_time_parts[1]);
		out_date.setSeconds(0);  // Set seconds to 0 to avoid time inconsistencies

		// Calculate the total time worked in milliseconds
		var total_duration = out_date - in_date;

		// Convert break time from minutes to milliseconds
		var break_minutes = parseInt(break_time);
		var break_time_in_ms = break_minutes * 60 * 1000;

		// Calculate the working time (subtract break time from total duration)
		var working_time_in_ms = total_duration - break_time_in_ms;

		// Convert working time to hours (in decimal format)
		var working_time_in_hours = working_time_in_ms / (1000 * 60 * 60); // Convert milliseconds to hours

		// Display the result as hours with two decimals
		$("#hours_diff").val(working_time_in_hours.toFixed(2));
	}
</script>
<?= $this->endSection(); ?>