<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<div class="col-xl">
				<div class="card mb-12">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="mb-0">Attendance</h5> <small class="text-body float-end">(<small class='astrock'>*</small>) indicates required field.</small>
					</div>
					<div class="card-body">
						<form id="main-form" action="<?php echo base_url('attendances'); ?>" method="post">
							<div class="row">
								<div class="col-lg-12">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Date<small class='astrock'>*</small></label>
										<input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-12">
									<div class="table-responsive">
										<table class="table table-default table-bordered">
											<thead>
												<tr>
													<th width="10%"><center><b>Staff</b></center></th>
													<th width="15%"><center><b>Salon</b></center></th>
													<th width="15%"><center><b>In Time <br><small style="font-size: 10px;">(HH:MM)</small></b></center></th>
													<th width="15%"><center><b>Out Time <br><small style="font-size: 10px;">(HH:MM)</small></b></center></th>
													<th width="15%"><center><b>Break <br><small style="font-size: 10px;">(in min.)</small></b></center></th>
													<th width="15%"><center><b>Hours</b></center></th>
													<th width="15%"><center><b>Tip</b></center></th>
												</tr>
											</thead>
											<tbody>
												<?php
													if($staffs) {
														foreach($staffs as $staff) {
															$stime = 0;
															$etime = 24;
												?>
															<tr id="staff-<?php echo $staff['id']; ?>">
																<td style="font-size: 11px !important;">
																	<input type="hidden" name="staff_id[]" value="<?php echo $staff['id']; ?>" />
																	<input type="hidden" name="old_salon_id[]" value="<?php echo $staff['salon_id']; ?>" />
																	<input type="hidden" name="rate[]" value="<?php echo $staff['rate']; ?>" />
																	<center><?php echo $staff['fname']." ".$staff['lname']; ?></center>
																</td>
																<td>
																	<center>
																	<select class="form-control select2" name="salon_id[]">	
																		<?php
																			if($salons) {
																				foreach($salons as $salon) {
																					if($salon['id'] ==  $staff['salon_id'] && $salon['stime'] != "00:00:00" && $salon['etime'] != "00:00:00") {
																						$stime = date('H',strtotime($salon['stime']));
																						$etime = date('H',strtotime($salon['etime']));
																					}
																		?>
																					<option value="<?php echo $salon['id']; ?>" <?php echo $salon['id'] ==  $staff['salon_id'] ? "selected" : ""; ?>><?php echo $salon['name']; ?></option>
																		<?php
																				}
																			}
																		?>
																	</select>
																</center>
																</td>
																<td>
																	<center><input type="text" name="in_time[]" onblur="calc_hours('<?php echo $staff['id']; ?>')" class="form-control" /></center>
																	<!-- <select class="form-control select2" name="in_time[]" onchange="calc_hours('<?php echo $staff['id']; ?>')">
																		<option value="">Intime</option>
																		< ?php
																			for($i = $stime; $i < $etime; $i ++) {
																				for($j = 0; $j < 60; $j = $j + 5) {
																					echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'">'.date("H:i",strtotime($i.":".$j.":00")).'</option>';
																				}
																			} 
																		?>
																	</select> -->
																</td>
																<td>
																	<center><input type="text" name="out_time[]" onblur="calc_hours('<?php echo $staff['id']; ?>')" class="form-control" /></center>
																	<!-- <select class="form-control select2" name="out_time[]" onchange="calc_hours('<?php echo $staff['id']; ?>')">
																		<option value="">Outime</option>
																		< ?php
																			for($i = $stime; $i < $etime; $i ++) {
																				for($j = 0; $j < 60; $j = $j + 5) {
																					echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'">'.date("H:i",strtotime($i.":".$j.":00")).'</option>';
																				}
																			} 
																		?>
																	</select> -->
																</td>
																<td><center><input type="number" name="break[]" class="form-control" value="0" onblur="calc_hours('<?php echo $staff['id']; ?>')" /></center></td>
																<td><center><input type="number" name="hours[]" class="form-control" /></center></td>
																<td><center><input type="number" name="tip[]" class="form-control" /></center></td>
															</tr>
												<?php
														}
													} 
												?>
											</tbody>
										</table>
									</div>
								</div>
							</div><br>
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
				date:{
					required: true
				}
			},
			messages:{
				salon_id:{
					required: "<b>Salon is required.</b>"
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
	function calc_hours(staff_id)
	{
		var in_time = $('#staff-'+staff_id).find("td:eq(2) input").val();   // In Time (HH:MM)
		var out_time = $('#staff-'+staff_id).find("td:eq(3) input").val(); // Out Time (HH:MM)
		var break_time = $('#staff-'+staff_id).find("td:eq(4) input").val();  // Break Time (MM)

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
		$('#staff-'+staff_id).find("td:eq(5) input").val(working_time_in_hours.toFixed(2));
	}
</script>
<?= $this->endSection(); ?>