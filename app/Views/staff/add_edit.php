<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<?php 
	$permissions = array();
	if(empty($staff)) {
		$module_name = "New Staff";
		$action = base_url("staffs");

		$fname = "";
		$lname = "";
		$salon_id = "";
		$wage = "";
		$rate = "";
		$joining_date = "";
		$last_working_date = "";
		$email = "";
		$phone = "";
		$address = "";
		$is_active = "";
	} else {
		$module_name = "Edit Staff";
		$action = base_url("staffs/".$staff['id']);

		$fname = $staff['fname'];
		$lname = $staff['lname'];
		$salon_id = $staff['salon_id'];
		$wage = $staff['wage'];
		$rate = $staff['rate'];
		$joining_date = $staff['joining_date'];
		$last_working_date = $staff['last_working_date'];
		$email = $staff['email'];
		$phone = $staff['phone'];
		$address = $staff['address'];
		$is_active = $staff['is_active'];
		if(!empty($staff["permissions"]) != "") {
			$permissions = explode(",", $staff["permissions"]);
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
							<div class="row mt-5">
								<div class="col-3">
									<label class="form-label" for="basic-default-fullname">First Name<small class='astrock'>*</small></label>
								    <input type="text" class="form-control" id="fname" name="fname" placeholder="Enter first name" value="<?php echo $fname; ?>" autofocus />
								</div>
								<div class="col-3">
									<label class="form-label" for="basic-default-fullname">Last Name<small class='astrock'>*</small></label>
								    <input type="text" class="form-control" id="lname" name="lname" placeholder="Enter last name" value="<?php echo $lname; ?>" />
								</div>
								<div class="col-3">
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
								<div class="col-3">
									<label class="form-label" for="basic-default-fullname">Mobile No.</label>
							        <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter mobile no." value="<?php echo $phone; ?>" />
								</div>
								<div class="col-3 mt-5">
									<label class="form-label" for="basic-default-fullname">Email</label>
								    <input type="text" class="form-control" id="email" name="email" placeholder="Enter email" value="<?php echo $email; ?>" />
								</div>
								<div class="col-3 mt-5">
									<label class="form-label" for="basic-default-fullname">Password</label>
							        <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" />
								</div>
								<div class="col-3 mt-5">
									<label class="form-label" for="basic-default-fullname">Joining Date</label>
								    <input type="date" class="form-control" id="joining_date" name="joining_date" value="<?php echo $joining_date; ?>" max="<?php echo date('Y-m-d'); ?>" />
								</div>
								<div class="col-3 mt-5">
									<label class="form-label" for="basic-default-fullname">Last Working Date</label>
								    <input type="date" class="form-control" id="last_working_date" name="last_working_date" value="<?php echo $last_working_date; ?>" min="<?php echo $joining_date; ?>" />
								</div>
								<div class="col-3 mt-5">
									<label class="form-label" for="basic-default-fullname">Status</label>
									<select class="form-control select2" id="is_active" name="is_active">
										<option value="1">Active</option>
										<option value="0">Inactive</option>
									</select>
								</div>
								<div class="col-9 mt-5">
									<label class="form-label" for="basic-default-fullname">Address</label>
								    <input type="text" class="form-control" id="address" name="address" placeholder="Enter address" value="<?php echo $address; ?>" />
								</div>
								<?php
    								if($fname == "") {
    							?>
    							        <div class="col-3 mt-5">
            								<label class="form-label" for="basic-default-fullname">Wage Type<small class='astrock'>*</small></label>
        									<select class="form-control select2" id="wage" name="wage">
        										<option value="">Please select</option>
        										<option value="1" <?php echo $wage == 1 ? "selected" : ""; ?>>Monthly</option>
        										<option value="2" <?php echo $wage == 2 ? "selected" : ""; ?>>Hourly</option>
        									</select>
        									<label id="wage-error" class="error" for="wage" style="display: none;"></label>
            							</div>
            							<div class="col-3 mt-5">
            								<label class="form-label" for="basic-default-fullname">Rate<small class='astrock'>*</small></label>
            							    <input type="text" class="form-control" id="rate" name="rate" placeholder="Enter rate" value="<?php echo $rate; ?>" />
            							</div>
    							<?php
    								}
    							?>
							</div>
							<div class="row mt-5">
								<div class="col-lg-12">
									<label class="form-label" for="basic-default-fullname">Permissions</label>
									<div id="menu-list">
										
									</div>
								</div>
							</div>
							<button type="submit" class="btn btn-primary btn-sm">SUBMIT</button>
							<a class="btn btn-danger btn-sm text-white" id="back-btn" href="<?php echo base_url('staffs'); ?>">Back</a>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Staffs";
	var is_edit_page = "<?php echo $fname == '' ? 0 : 1; ?>";
	var permissions = '<?php echo json_encode($permissions); ?>';
	$(document).ready(function(){
		var content = "";
		var no = 0;
		var all_modules = $.parseJSON(permissions);
		$("#main-menu li").each(function(){
			no++;
			if(no == 1) {
				content += '<div class="form-check mt-3" hidden>';
					content += '<input class="form-check-input" type="checkbox" value="dashboard" id="dashboard" name="permission[]" checked />';
					content += '<label class="form-check-label" for="dashboard">Dashboard</label>';
				content += '</div>';
			} else {
				if ($.inArray($.trim($(this).attr("data-module")), all_modules) !== -1) {
					content += '<div class="form-check mt-3">';
						content += '<input class="form-check-input" type="checkbox" value="'+$.trim($(this).attr("data-module"))+'" id="'+$.trim($(this).attr("data-module"))+'" name="permission[]" checked />';
						content += '<label class="form-check-label" for="'+$.trim($(this).attr("data-module"))+'">'+$.trim($(this).attr("data-title"))+'</label>';
					content += '</div>';
				} else {
					content += '<div class="form-check mt-3">';
						content += '<input class="form-check-input" type="checkbox" value="'+$.trim($(this).attr("data-module"))+'" id="'+$.trim($(this).attr("data-module"))+'" name="permission[]" />';
						content += '<label class="form-check-label" for="'+$.trim($(this).attr("data-module"))+'">'+$.trim($(this).attr("data-title"))+'</label>';
					content += '</div>';
				}
			}
		});
		$("#menu-list").html(content);
		$("#main-form").validate({
			rules:{
				fname:{
					required: true
				},
				lname:{
					required: true
				},
				salon_id:{
					required: true
				},
				wage:{
					required: true
				},
				rate:{
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
				salon_id:{
					required: "<b>Salon is required.</b>"
				},
				wage:{
					required: "<b>Wage Type is required.</b>"
				},
				rate:{
					required: "<b>Rate is required.</b>"
				}
			}
		});
		// if(parseInt(is_edit_page) === 1) {
		// 	$("#main-form").validate().settings.rules.password.required = false;
		// 	$("#main-form").validate().settings.messages.password = {};
		// }

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