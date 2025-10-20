<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	if(empty($salon)) {
		$module_name = "New Salon";
		$action = base_url("salons");

		$name = "";
		$email = "";
		$phone = "";
		$address = "";
		$currency = "£";
		$note = "";
		$stime = "";
		$etime = "";
		$is_active = "";
	} else {
		$module_name = "Edit Salon";
		$action = base_url("salons/".$salon['id']);

		$name = $salon['name'];
		$email = $salon['email'];
		$phone = $salon['phone'];
		$address = $salon['address'];
		$currency = $salon['currency'];
		$note = $salon['note'];
		$stime = $salon['stime'];
		$etime = $salon['etime'];
		$note = $salon['note'];
		$is_active = $salon['is_active'];
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
								if($name != "") {
									echo '<input type="hidden" name="_method" value="PUT" />';
								} 
							?>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Name<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="name" name="name" placeholder="Enter salon name" value="<?php echo $name; ?>" autofocus />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Email</label>
										<input type="text" class="form-control" id="email" name="email" placeholder="Enter salon email" value="<?php echo $email; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Contact No.</label>
										<input type="text" class="form-control" id="phone" name="phone" placeholder="Enter salon contact no." value="<?php echo $phone; ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-8">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Address</label>
										<input type="text" class="form-control" id="address" name="address" placeholder="Enter salon address" value="<?php echo $address; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Currency</label>
										<input type="text" class="form-control" id="currency" name="currency" placeholder="Enter salon currency" value="<?php echo $currency; ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-8" hidden>
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Note</label>
										<input type="text" class="form-control" id="note" name="note" placeholder="Enter salon note" value="<?php echo $note; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Opening Time</label>
										<select class="form-control" name="stime" id="stime">
											<option value="">Please select</option>
											<?php
												for($i = 0; $i < 24; $i ++) {
													for($j = 0; $j < 60; $j = $j + 30) {
														if($stime == date("H:i:s",strtotime($i.":".$j.":00"))) {
															echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'" selected>'.date("H:i:s",strtotime($i.":".$j.":00")).'</option>';
														} else {
															echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'">'.date("H:i:s",strtotime($i.":".$j.":00")).'</option>';
														}
													}
												} 
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon Closing Time</label>
										<select class="form-control" name="etime" id="etime">
											<option value="">Please select</option>
											<?php
												for($i = 0; $i < 24; $i ++) {
													for($j = 0; $j < 60; $j = $j + 30) {
														if($etime == date("H:i:s",strtotime($i.":".$j.":00"))) {
															echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'" selected>'.date("H:i:s",strtotime($i.":".$j.":00")).'</option>';
														} else {
															echo '<option value="'.date("H:i:s",strtotime($i.":".$j.":00")).'">'.date("H:i:s",strtotime($i.":".$j.":00")).'</option>';
														}
													}
												} 
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Status</label>
										<select class="form-control" id="is_active" name="is_active">
											<option value="1">Active</option>
											<option value="0">Inactive</option>
										</select>
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-sm">SUBMIT</button>
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('salons'); ?>">Back</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Salons";
	$(document).ready(function(){
		$("#main-form").validate({
			rules:{
				name:{
					required: true
				}
			},
			messages:{
				name:{
					required: "<b>Salon name is required.</b>"
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