<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	$permissions = array();
	if(empty($accoutant)) {
		$module_name = "New Accoutant";
		$action = base_url("accoutants");

		$fname = "";
		$lname = "";
		$joining_date = "";
		$email = "";
		$phone = "";
		$address = "";
		$is_active = "";
	} else {
		$module_name = "Edit Accoutant";
		$action = base_url("accoutants/".$accoutant['id']);

		$fname = $accoutant['fname'];
		$lname = $accoutant['lname'];
		$joining_date = $accoutant['joining_date'];
		$email = $accoutant['email'];
		$phone = $accoutant['phone'];
		$address = $accoutant['address'];
		$is_active = $accoutant['is_active'];
		if(!empty($accoutant["permissions"]) != "") {
			$permissions = explode(",", $accoutant["permissions"]);
		}
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
								if($fname != "") {
									echo '<input type="hidden" name="_method" value="PUT" />';
								} 
							?>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">First Name<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="fname" name="fname" placeholder="Enter first name" value="<?php echo $fname; ?>" autofocus />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Last Name<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="lname" name="lname" placeholder="Enter last name" value="<?php echo $lname; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Joining Date</label>
										<input type="date" class="form-control" id="joining_date" name="joining_date" value="<?php echo $joining_date; ?>" max="<?php echo date('Y-m-d'); ?>" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Mobile No.</label>
										<input type="text" class="form-control" id="phone" name="phone" placeholder="Enter mobile no." value="<?php echo $phone; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Email<small class='astrock'>*</small></label>
										<input type="text" class="form-control" id="email" name="email" placeholder="Enter email" value="<?php echo $email; ?>" />
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Password <?php echo $fname == '' ? '<small class="astrock">*</small>' : ''; ?></label>
										<input type="password" class="form-control" id="password" name="password" placeholder="Enter password" />
									</div>
								</div>
							</div>
							<div class="row">
								<div class="col-lg-8">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Address</label>
										<input type="text" class="form-control" id="address" name="address" placeholder="Enter address" value="<?php echo $address; ?>" />
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
							<div class="row">
								<div class="col-lg-12">
									<label class="form-label" for="basic-default-fullname">Permissions</label>
									<div class="form-check mt-3" hidden>
              							<input class="form-check-input" type="checkbox" value="dashboard" id="dashboard" name="permission[]" checked />
          								<label class="form-check-label" for="dashboard">Dashboard</label>
            						</div>
									<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="salon" id="salon" name="permission[]" <?php echo in_array('salon',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="salon">Salons</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="accountant" id="accountant" name="permission[]" <?php echo in_array('accountant',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="accountant">Accountants</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="staff" id="staff" name="permission[]" <?php echo in_array('staff',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="staff">Staffs</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="payment_mode" id="payment_mode" name="permission[]" <?php echo in_array('payment_mode',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="payment_mode">Payment Modes</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="tip" id="tip" name="permission[]" <?php echo in_array('tip',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="tip">Tips</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="attendance" id="attendance" name="permission[]" <?php echo in_array('attendance',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="attendance">Attendances</label>
            						</div>
            						<div class="form-check mt-3">
              							<input class="form-check-input" type="checkbox" value="report" id="report" name="permission[]" <?php echo in_array('report',$permissions) ? 'checked' : ''; ?> />
          								<label class="form-check-label" for="report">Reports</label>
            						</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-sm">SUBMIT</button>
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('accoutants'); ?>">Back</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Accountants";
	var is_edit_page = "<?php echo $fname == '' ? 0 : 1; ?>";
	$(document).ready(function(){
		$("#main-form").validate({
			rules:{
				fname:{
					required: true
				},
				lname:{
					required: true
				},
				email:{
					required: true
				},
				password:{
					required: true
				}
			},
			messages:{
				fname:{
					required: "<b>First name is required.</b>"
				},
				lname:{
					required: "<b>Last name is required.</b>"
				},
				email:{
					required: "<b>Email is required.</b>"
				},
				password:{
					required: "<b>Password is required.</b>"
				}
			}
		});
		if(parseInt(is_edit_page) === 1) {
			$("#main-form").validate().settings.rules.password.required = false;
			$("#main-form").validate().settings.messages.password = {};
		}

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
						} else {
							show_toast(response.message);
							$("#main-form button[type=submit]").html('SUBMIT').attr("disabled",false);
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