<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<div class="col-md-12">
				<div class="nav-align-top">
					<ul class="nav nav-pills flex-column flex-md-row mb-6">
						<li class="nav-item"><a class="nav-link active" href="javascript:void(0);"><i class="bx bx-sm bx-user me-1_5"></i> Account</a></li>
						<?php 
							$disabled = "";
							if(isset($current_user_id) && $current_user_id != $profile["id"]) {
								$disabled = "disabled";
							}
							if($profile['role'] == 3) {
						?>
								<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staff-attendance/'.$profile['id']); ?>"><i class="bx bx-sm bx-list-check me-1_5"></i> Attendance</a></li>
								<!--<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staff-earnings/'.$profile['id']); ?>"><i class="bx bx-sm bx-dollar me-1_5"></i> Earnings</a></li>-->
								<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staff-rate-history/'.$profile['id']); ?>"><i class="bx bx-sm bx-dollar me-1_5"></i> Rate History</a></li>
						<?php
							}
						?>
					</ul>
				</div>
				<div class="card mb-6">
					<div class="card-header" style="padding-bottom: 0px;"><h5><b><?php echo $profile['fname']." ".$profile['lname']; ?></b></h5><hr></div>
					<div class="card-body">
						<form id="main-form" action="<?php echo base_url('submit-profile'); ?>">
							<input type="hidden" name="profile_id" value="<?php echo $profile['id']; ?>" />
							<div class="row g-6">
								<div class="col-md-3">
									<label for="firstName" class="form-label">First Name</label>
									<input class="form-control" type="text" id="fname" name="fname" value="<?php echo $profile['fname']; ?>" autofocus <?php echo $disabled; ?> />
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Last Name</label>
									<input class="form-control" type="text" id="lname" name="lname" value="<?php echo $profile['lname']; ?>" <?php echo $disabled; ?> />
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Mobile No.</label>
									<input class="form-control" type="text" id="phone" name="phone" value="<?php echo $profile['phone']; ?>" <?php echo $disabled; ?> />
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Email</label>
									<input class="form-control" type="text" id="email" name="email" value="<?php echo $profile['email']; ?>" <?php echo $disabled; ?> />
								</div>
							</div>
							<div class="row mt-5">
								<div class="col-md-3">
									<label for="firstName" class="form-label">Joining Date</label>
									<input class="form-control" type="date" id="joining_date" name="joining_date" value="<?php echo $profile['joining_date']; ?>" <?php echo $disabled; ?> />
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Role</label>
									<?php
										$role = "-";
										switch($profile["role"]) {
											case 1:
												$role = "Admin";
												break;

											case 2:
												$role = "Accountant";
												break;

											case 3:
												$role = "Staff";
												break;
										} 
									?>
									<input class="form-control" type="text" id="role" name="role" value="<?php echo $role; ?>" disabled />
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Status</label>
									<select class="form-control select2" name="is_active" id="is_active" <?php echo $disabled; ?>>
										<option value="1" <?php echo $profile['is_active'] == 1 ? 'selected' : ''; ?>>Active</option>
										<option value="0" <?php echo $profile['is_active'] == 0 ? 'selected' : ''; ?>>Inactive</option>
									</select>
								</div>
								<div class="col-md-3">
									<label for="firstName" class="form-label">Password</label>
									<input class="form-control" type="text" id="password" name="password" <?php echo $disabled; ?> />
								</div>
							</div>
							<div class="row mt-5">
								<div class="col-md-12">
									<label for="firstName" class="form-label">Address</label>
									<input class="form-control" type="text" id="address" name="address" value="<?php echo $profile['address']; ?>" <?php echo $disabled; ?> />
								</div>
							</div>
							<?php
								if($profile['role'] == 3) {
							?>
									<div class="row mt-5">
										<div class="col-md-4">
											<label for="firstName" class="form-label">Salon</label>
											<input class="form-control" type="text" id="address" name="address" value="<?php echo $profile['salon']; ?>" disabled />
										</div>
										<div class="col-md-4">
											<label for="firstName" class="form-label">Wage</label>
											<select class="form-control select2" name="wage" id="wage" <?php echo $disabled; ?>>
												<option value="1" <?php echo $profile['wage'] == 1 ? 'selected' : ''; ?>>Monthly</option>
												<option value="2" <?php echo $profile['wage'] == 2 ? 'selected' : ''; ?>>Hourly</option>
											</select>
										</div>
										<div class="col-md-4">
											<label for="firstName" class="form-label">Rate <small style="font-size: 10px;">(<?php echo $profile['currency']; ?>)</small></label>
											<input class="form-control" type="text" id="address" name="address" value="<?php echo $profile['rate']; ?>" disabled />
										</div>
									</div>		
							<?php
								}
								echo '<div class="mt-6">';
								if($disabled == "disabled") {
									echo '<a href="'.base_url('staffs').'" class="btn btn-sm btn-primary me-3">Back</a>';
								} else {
									echo '<button type="submit" class="btn btn-sm btn-primary me-3">Save</button>';
								}
								echo '</div>';
							?>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "<?php echo $page_title; ?>";
	$("#main-form").validate({
		rules:{
			fname:{
				required: true
			},
			lname:{
				required: true
			}
		},
		messages:{
			fname:{
				required: "<b>First name is required.</b>"
			},
			lname:{
				required: "<b>Last name is required.</b>"
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
						window.location.reload();
					}
				},
				error: function(xhr, status, error) {  // Function to handle errors
				    alert(error);
				    $("#main-form button[type=submit]").html('SAVE').attr("disabled",false);
				},
			});
		}
	});
</script>
<?= $this->endSection(); ?>