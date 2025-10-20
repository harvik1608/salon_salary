<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<style>
	input[name^="in_time"],input[name^="out_time"]  {
		font-size: 12px !important;
	}
</style>
<?php 
	if(empty($entry)) {
		$module_name = "New Entry";
		$action = base_url("entries");

		$salon_id = $default_salon_id;
		$date = $default_date;
		$total_amount = 0;
		$note = "";
		$tip = "";
	} else {
		$module_name = "Edit Entry";
		$action = base_url("entries");
		// $action = base_url("entries/".$entry['id']);

		$salon_id = $entry['salon_id'];
		$total_amount = $entry['amount'];
		$date = $entry['date'];
		$note = $entry['note'];
		$tip = $entry['tip'];
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
							<!-- < ?php
								if($salon_id != 0) {
									echo '<input type="hidden" name="_method" value="PUT" />';
								} 
							?> -->
							<div class="row">
								<div class="col-lg-3">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon<small class='astrock'>*</small></label>
										<select class="form-control select2" id="salon_id" name="global_salon_id">
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
								<div class="col-lg-3">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Date<small class='astrock'>*</small></label>
										<input type="date" class="form-control" id="date" name="date" value="<?php echo $date; ?>" />
									</div>
								</div>
								<div class="col-lg-3 mt-7">
									<a class="btn btn-sm btn-info text-white" id="prevbtn">Previous</a>
									<a class="btn btn-sm btn-info text-white" id="nextbtn">Next</a>
								</div>
							</div>
							<div id="form-element">
							</div><br>
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
	var salonId = "<?php echo $salon_id; ?>";
	$(document).ready(function(){
		if(parseInt(salonId) > 0) {
			load_data();
		}
		$("#main-form").validate({
			rules:{
				global_salon_id:{
					required: true
				},
				date:{
					required: true
				}
			},
			messages:{
				global_salon_id:{
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
							show_toast("Entry & Attendance added successfully.");
							$("#main-form button[type=submit]").html('SUBMIT').attr("disabled",false);
							// window.location.href = $("#back-btn").attr("href");
							$("html, body").animate({ scrollTop: 0 }, "slow");
						}
					},
					error: function(xhr, status, error) {  // Function to handle errors
					    alert(error);
					    $("#main-form button[type=submit]").html('SUBMIT').attr("disabled",false);
					},
				});
			}
		});
		$(document).on("blur",".amount",function(){
			var total_amount = 0;
			$("input.amount").each(function(){
				if($(this).val() != "") {
					if(parseInt($(this).attr("data-operation")) == 0) {
						total_amount = total_amount + parseFloat($(this).val());
					} else {
						total_amount = total_amount - parseFloat($(this).val());
					}
				}
			});
			$("#total_amount").val(total_amount.toFixed(2));
		});
		$(document).on("change","#salon_id",function(){
			load_data();
		});
		$(document).on("change","#date",function(){
			load_data();
		});
		$(document).on("click","#prevbtn",function(){
			var currentDate = new Date($("#date").val());
			currentDate.setDate(currentDate.getDate() - 1);

			// Format the date to a string (optional, depends on your needs)
			var formattedDate = currentDate.toISOString().split('T')[0]; // YYYY-MM-DD

			$("#date").val(formattedDate);
			load_data(); 
		});
		$(document).on("click","#nextbtn",function(){
			console.log($("#date").val());
			var currentDate = new Date($("#date").val());
			currentDate.setDate(currentDate.getDate() + 1);
			console.log(currentDate);

			// Format the date to a string (optional, depends on your needs)
			var formattedDate = currentDate.toISOString().split('T')[0]; // YYYY-MM-DD
			console.log(formattedDate);

			$("#date").val(formattedDate);
			load_data(); 
		});
		$(document).on("change","select[name^=staff_id]",function(){
			$(this).parent().find("input:eq(0)").val($(this).find("option:selected").attr("data-salon"));
			$(this).parent().find("input:eq(1)").val($(this).find("option:selected").attr("data-rate"));
		});
	});
	function load_data()
	{
		$.ajax({
			url: "<?php echo base_url('get-ajax-form-entry'); ?>",
			type: "GET",
			data:{
				salon_id: $("#salon_id").val(),
				date: $("#date").val(),
			},
			success:function(response){
				$("#form-element").html(response);
				check_empty_dd();
				$(".select2").select2();
			}
		});
	}
	function check_empty_dd()
	{
		$("tr[id^=extra-staff-]").each(function(){
			if($.trim($(this).find("td:eq(0) select").val()) == "") {
				$(this).remove();
			}
		});
	}
	function calc_hours(staff_id)
	{
		var in_time = $('#staff-' + staff_id).find("td:eq(2) input").val(); // In Time (HH:MM)
		var out_time = $('#staff-' + staff_id).find("td:eq(3) input").val(); // Out Time (HH:MM)
		var break_time = $('#staff-' + staff_id).find("td:eq(4) input").val(); // Break Time (MM)

		// Parse in_time and out_time into hours and minutes
		var in_time_parts = in_time.split(':');
		var out_time_parts = out_time.split(':');

		var in_hours = parseInt(in_time_parts[0], 10);
		var in_minutes = parseInt(in_time_parts[1], 10);
		var out_hours = parseInt(out_time_parts[0], 10);
		var out_minutes = parseInt(out_time_parts[1], 10);

		// Calculate total work time in minutes
		var total_minutes = (out_hours * 60 + out_minutes) - (in_hours * 60 + in_minutes);

		// Initialize break time in minutes
		var break_minutes = break_time && !isNaN(break_time) ? parseInt(break_time, 10) : 0;

		// Subtract break time from total work time
		var working_minutes = total_minutes - break_minutes;
		console.log(working_minutes);
		// Convert working time to hours and fractional minutes
		var working_hours = Math.floor(working_minutes / 60); // Full hours
		var working_fractional = (working_minutes % 60) / 60; // Remaining minutes as fraction
		// console.log(working_fractional);
		// Final working time in decimal hours
		var working_time_in_hours = working_hours + working_fractional;

		// Display the result in the input field
		if(!isNaN(working_time_in_hours)) {
			$('#staff-' + staff_id).find("td:eq(5) input").val(working_time_in_hours.toFixed(2));
			$('#staff-' + staff_id).find("td:eq(5) span").html(calculateWorkingHours(in_time,out_time,break_time));
		} else {
			$('#staff-' + staff_id).find("td:eq(5) input").val("0.00");
			$('#staff-' + staff_id).find("td:eq(5) span").html("0.00");
		}

	}

	function calculateWorkingHours(start, end, breakMinutes) {
	    // Convert start and end times to Date objects
	    let startTime = new Date(`1970-01-01T${start}`);
	    let endTime = new Date(`1970-01-01T${end}`);

	    // Calculate total time difference in seconds
	    let totalSeconds = (endTime - startTime) / 1000;

	    // Subtract break time (convert minutes to seconds)
	    totalSeconds -= breakMinutes * 60;

	    // Convert back to HH:MM:SS format
	    let hours = Math.floor(totalSeconds / 3600);
	    let minutes = Math.floor((totalSeconds % 3600) / 60);
	    let seconds = totalSeconds % 60;

	    // return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
	    return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
	}

	function calc_extra_staff_hours(no)
	{
		var in_time = $('#extra-staff-' + no).find("td:eq(2) input").val(); // In Time (HH:MM)
		var out_time = $('#extra-staff-' + no).find("td:eq(3) input").val(); // Out Time (HH:MM)
		var break_time = $('#extra-staff-' + no).find("td:eq(4) input").val(); // Break Time (MM)

		// Parse in_time and out_time into hours and minutes
		var in_time_parts = in_time.split(':');
		var out_time_parts = out_time.split(':');

		var in_hours = parseInt(in_time_parts[0], 10);
		var in_minutes = parseInt(in_time_parts[1], 10);
		var out_hours = parseInt(out_time_parts[0], 10);
		var out_minutes = parseInt(out_time_parts[1], 10);

		// Calculate total work time in minutes
		var total_minutes = (out_hours * 60 + out_minutes) - (in_hours * 60 + in_minutes);

		// Initialize break time in minutes
		var break_minutes = break_time && !isNaN(break_time) ? parseInt(break_time, 10) : 0;

		// Subtract break time from total work time
		var working_minutes = total_minutes - break_minutes;
		console.log(working_minutes);
		// Convert working time to hours and fractional minutes
		var working_hours = Math.floor(working_minutes / 60); // Full hours
		var working_fractional = (working_minutes % 60) / 60; // Remaining minutes as fraction
		// console.log(working_fractional);
		// Final working time in decimal hours
		var working_time_in_hours = working_hours + working_fractional;

		// Display the result in the input field
		if(!isNaN(working_time_in_hours)) {
			$('#extra-staff-' + no).find("td:eq(5) input").val(working_time_in_hours.toFixed(2));
			$('#extra-staff-' + no).find("td:eq(5) span").html(calculateWorkingHours(in_time,out_time,break_time));
		} else {
			$('#extra-staff-' + no).find("td:eq(5) input").val("0.00");
			$('#extra-staff-' + no).find("td:eq(5) span").html("0.00");
		}

	}
	function add_more_staff()
	{
		var no = $("tr[id^=extra-staff-]").length+1;
		$.ajax({
			url: "<?php echo base_url('get-extra-salon-staff'); ?>",
			type: "GET",
			data:{
				salon_id: $("#salon_id").val(),
				no: no
			},
			success:function(response){
				$("#staff-checkin tbody").append(response);
				$(".select2").select2();
			}
		});
	}
	function remove_daily_entry(entry_id)
	{
		if(confirm("Are you sure to remove this row?")) {
			$.ajax({
				url: "<?php echo base_url('remove-daily-entry'); ?>",
				type: "GET",
				data:{
					entry_id: entry_id
				},
				dataType: "json",
				success:function(response){
					show_toast(response.message);
					if(response.status == 200) {
						load_data();
					}
				}
			});
		}
	}
	function remove_entry()
	{
		$.ajax({
			url: "<?php echo base_url('remove-entry'); ?>",
			type: "GET",
			data:{
				salon_id: $("#salon_id").val(),
				date: $("#date").val(),
			},
			dataType: "json",
			success:function(response){
				show_toast(response.message);
				load_data();
			}
		});
	}
</script>
<?= $this->endSection(); ?>