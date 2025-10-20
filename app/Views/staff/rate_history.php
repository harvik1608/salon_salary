<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/responsive.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.checkboxes.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/buttons.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/rowgroup.bootstrap5.css'); ?>">
<style>
    #addIncrementBtn {
        float: right;
    }
</style>
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="row">
			<div class="col-md-12">
				<div class="nav-align-top">
					<ul class="nav nav-pills flex-column flex-md-row mb-6">
						<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staffs/'.$profile['id']); ?>"><i class="bx bx-sm bx-user me-1_5"></i> Account</a></li>
						<?php 
							if($profile['role'] == 3) {
						?>
								<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staff-attendance/'.$profile['id']); ?>"><i class="bx bx-sm bx-list-check me-1_5"></i> Attendance</a></li>
								<!--<li class="nav-item"><a class="nav-link" href="<?php echo base_url('staff-earnings/'.$profile['id']); ?>"><i class="bx bx-sm bx-dollar me-1_5"></i> Earnings</a></li>-->
								<li class="nav-item"><a class="nav-link active" href="<?php echo base_url('staff-rate-history/'.$profile['id']); ?>"><i class="bx bx-sm bx-dollar me-1_5"></i> Rate History</a></li>
						<?php
							}
						?>
					</ul>
				</div>
				<div class="card mb-6">
					<div class="card-header" style="padding-bottom: 0px;">
					    <h5>
					        <b><?php echo $profile['fname']." ".$profile['lname']; ?></b>
					        <button type="button" class="btn btn-primary btn-sm" id="addIncrementBtn" onclick="add_increment(<?php echo $profile['id']; ?>,<?php echo $profile['salon_id']; ?>)">Add Increment</button>
					    </h5><hr>
			        </div>
					<div class="card-body">
						<form method="post" action="<?php echo base_url('export-entries'); ?>">
							<div class="row">
								<div class="col-lg-3">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Salon</label>
										<select class="form-control select2" id="salon_id">
											<option value="">Please select</option>
											<?php
												if($salons) {
													foreach($salons as $salon) {
											?>
														<option value="<?php echo $salon['id']; ?>"><?php echo $salon['name']; ?></option>
											<?php
													}
												}
											?>
										</select>
									</div>
								</div>
								<div class="col-lg-3">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Month & Year</label>
										<input type="month" class="form-control" id="datetime" />
									</div>
								</div>
								<div class="col-lg-3">
									<div class="mb-4">
										<label class="form-label" for="basic-default-fullname">Date</label>
										<input type="date" class="form-control" id="date" />
									</div>
								</div>
								<div class="col-lg-3 mt-6" style="padding: 5px !important;">
									<button type="button" class="btn btn-primary btn-sm" id="filterBtn">Filter</button>
									<a class="btn btn-danger btn-sm text-white" id="clearBtn">Clear</a>
									<button type="submit" class="btn btn-success btn-sm" id="filterBtn">Export</button>
								</div>
							</div>
						</form>
						<div class="table-responsive">
							<table class="table table-default" id="tbl-list">
								<thead>
									<tr>
			          					<th width="5%">No</th>
			          					<th width="20%">Salon</th>
			          					<th width="15%">Wage</th>
			          					<th width="15%">Rate</th>
			          					<th width="15%">Started From</th>
			          					<th width="10%">Added On</th>
			          					<th width="10%">Action</th>
									</tr>
								</thead>
								<tbody>
									
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<form method="post" action="<?php echo base_url('submit-staff-increment'); ?>" id="checkoutForm">
			    <input type="hidden" name="staff_id" id="staff_id" />
			    <input type="hidden" name="salon_id" id="salon_id" />
				<div class="modal-header">
					<h5 class="modal-title" id="modalCenterTitle">Add Increment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
					    <div class="col mb-6">
							<label for="emailWithTitle" class="form-label">Rate</label>
							<input type="number" id="new_rate" name="new_rate" class="form-control" placeholder="Enter rate" min="1" />
						</div>
						<div class="col mb-6">
							<label for="emailWithTitle" class="form-label">Wage</label>
							<select id="new_wage" name="new_wage" class="form-control">
							    <option value="">Choose wage</option>
							    <option value="2">Hourly</option>
							    <option value="1">Monthly</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col mb-6">
							<label for="emailWithTitle" class="form-label">Apply From</label>
							<input type="date" id="new_date" name="new_date" class="form-control" />
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">SUBMIT</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script src="<?php echo base_url('public/assets/css/datatable/datatables-bootstrap5.js'); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script type="text/javascript">
	var page_title = "Staffs";
	$(document).ready(function() {
        load_data();
        $(document).on("click","#filterBtn",function(){
			load_data();
		});
		$(document).on("click","#clearBtn",function(){
			$("select").val("");
			$("input").val("");
			load_data();
		});

		$("#checkoutForm").validate({
			rules:{
				new_rate:{
					required: true
				},
				new_wage:{
					required: true
				},
				new_date:{
					required: true
				}
			},
			messages:{
				new_rate:{
					required: "<b>Rate is required.</b>"
				},
				new_wage:{
					required: "<b>Wage is required.</b>"
				},
				new_date:{
					required: "<b>Apply From is required.</b>"
				}
			}
		});
		$("#checkoutForm").submit(function(e){
			e.preventDefault();

			if($("#checkoutForm").valid()) {
				$.ajax({
					url: $("#checkoutForm").attr("action"),
					type: "post",
					data: new FormData(this),
					contentType: false,
					processData: false,
					cache: false,
					beforeSend:function(){
						$("#checkoutForm button[type=submit]").html('<div class="spinner-border spinner-border-sm text-primary" role="status"><span class="visually-hidden">Loading...</span></div>').attr("disabled",true);
					},
					success:function(response){
						if(response.status == "success") {
							window.location.reload();
						}
					},
					error: function(xhr, status, error) {  // Function to handle errors
					    alert(error);
					    $("#checkoutForm button[type=submit]").html('SUBMIT').attr("disabled",false);
					},
				});
			}
		});
    });
    function load_data()
    {
    	$('#tbl-list').DataTable().destroy();
    	$('#tbl-list').DataTable({
			"serverSide": true, // Enable server-side processing
	    	"processing": true,
			"ajax":{
	            url: "<?php echo $load_ajax_url; ?>",
	            type: "post",
	            data:{
	            	salon_id: $("#salon_id").val(),
	            	datetime: $("#datetime").val(),
	            	date: $("#date").val()
	            }
	        },
	        "searching": false,
	        "columns": [
		        { "data": 0 },
		        { "data": 1 },
		        { "data": 2 },
		        { "data": 3 },
		        { "data": 4 },
		        { "data": 5 },
		        { "data": 6 }
		    ],
		    "order": [[0, "desc"]]
		});
    }
    function checkout(aid,intime,date)
    {
    	$("#modalCenterTitle").text(date);
    	$("#atten_id").val(aid);
    	$("#checkin_time").val(intime);
    	$("#modalCenter").modal("show");
    }
    function add_increment(staff_id,salon_id)
    {
        $("#checkoutForm #staff_id").val(staff_id);
        $("#checkoutForm #salon_id").val(salon_id);
        $("#modalCenter").modal("show");
    }
</script>
<?= $this->endSection(); ?>