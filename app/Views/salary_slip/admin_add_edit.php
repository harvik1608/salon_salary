<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	if(empty($salary_slip)) {
		$module_name = "Generate Salary Slip";
		$action = base_url("salary_slips");

		$salon_id = 0;
		$date = date('Y-m-d');
		$total_amount = 0;
		$note = "";
		$tip = "";
	} else {
		$module_name = "Edit Entry";
		$action = base_url("entries/".$entry['id']);

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
											<option value="" data-currency="">Please select</option>
											<?php
												if($salons) {
													foreach($salons as $salon) {
											?>
														<option value="<?php echo $salon['id']; ?>" data-currency="<?php echo $salon['currency']; ?>"><?php echo $salon['name']; ?></option>
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
										<label class="form-label" for="basic-default-fullname">Month & Year<small class='astrock'>*</small></label>
										<input type="month" class="form-control" id="month_year" name="month_year" />
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
								<div class="col-lg-12">
									<div class="table-responsive">
										<table class="table table-default table-bordered" id="salary-tbl">
											<thead>
												<tr>
													<th>STAFF</th>
													<th>WAGE</th>
													<th>RATE</th>
													<th>H/D</th>
													<th>TOTAL</th>
													<th>TIP</th>
													<th>TOTAL</th>
												</tr>
											</thead>
											<tbody>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<br>
							<button type="submit" class="btn btn-primary btn-sm">SUBMIT</button>
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('salary_slips'); ?>">Back</a>
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
				month_year:{
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
				month_year:{
					required: "<b>Month & Year is required.</b>"
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
		$("#salon_id").change(function(){
			get_monthly_checkins();
		});
		$("#month_year").change(function(){
			get_monthly_checkins();
		});
	});
	function get_monthly_checkins()
	{
		$.ajax({
			url: "<?php echo base_url('get-monthly-checkins'); ?>",
			type: "GET",
			data:{
				salon_id: $("#salon_id").val(),
				currency: $("#salon_id option:selected").attr("data-currency"),
				month_year: $("#month_year").val()
			},
			success:function(response){
				$("#salary-tbl tbody").html(response);
			},
			error: function(xhr, status, error) {  // Function to handle errors
			    alert(error);
			},
		});
	}
</script>
<?= $this->endSection(); ?>