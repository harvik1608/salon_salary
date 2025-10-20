<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/responsive.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.checkboxes.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/buttons.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/rowgroup.bootstrap5.css'); ?>">
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="card">
			<h5 class="card-header">
				Staff's Attendance
				<!-- <a href="< ?php echo base_url('attendances/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a> -->
			</h5>
			<div class="card-body">
				<form method="post" action="<?php echo base_url('export-entries'); ?>">
					<div class="row">
						<div class="col-lg-3">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Salon</label>
								<select class="form-control" id="salon_id">
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
								<label class="form-label" for="basic-default-fullname">Staff</label>
								<select class="form-control" id="staff_id">
									<option value="">Please select</option>
									<?php
										if($staffs) {
											foreach($staffs as $staff) {
									?>
												<option value="<?php echo $staff['id']; ?>"><?php echo $staff['fname']." ".$staff["lname"]; ?></option>
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
						<div class="col-lg-3">
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
	          					<th width="20%">Staff</th>
	          					<th width="15%">Date</th>
	          					<th width="15%">In Time</th>
	          					<th width="15%">Out Time</th>
	          					<th width="15%">Total Hours</th>
	          					<th width="15%">Action</th>
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
<div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<form action="<?php echo base_url('daily-checkout'); ?>" id="checkoutForm">
				<input type="hidden" name="atten_id" id="atten_id" />
				<div class="modal-header">
					<h5 class="modal-title" id="modalCenterTitle">Modal title</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col mb-6">
							<label for="nameWithTitle" class="form-label">Salon</label>
							<select class="form-control" id="checkout_salon_id" name="checkout_salon_id">
								<option value="">Please select</option>
								<?php
									if($salons) {
										foreach($salons as $salon) {
								?>
											<option value="<?php echo $salon['id']; ?>" <?php echo $salon['id'] == $current_salon_id ? "selected" : ""; ?>>
												<?php echo $salon['name']; ?>
											</option>
								<?php
										}
									}
								?>
							</select>
						</div>
					</div>
					<div class="row g-6">
						<div class="col mb-0">
							<label for="emailWithTitle" class="form-label">In Time</label>
							<input type="text" id="checkin_time" name="checkin_time" class="form-control" readonly />
						</div>
						<div class="col mb-0">
							<label for="emailWithTitle" class="form-label">Out Time</label>
							<input type="time" id="checkout_time" name="checkout_time" class="form-control" />
						</div>
					</div><br>
					<div class="row g-6">
						<div class="col mb-6">
							<label for="emailWithTitle" class="form-label">Note</label>
							<textarea class="form-control" id="checkout_note" name="checkout_note" placeholder="Note"></textarea>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js"></script>
<script src="<?php echo base_url('public/assets/css/datatable/datatables-bootstrap5.js'); ?>"></script>
<script type="text/javascript">
	var page_title = "Staff's Attendance";
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
				checkout_salon_id:{
					required: true
				},
				checkout_time:{
					required: true
				}
			},
			messages:{
				checkout_salon_id:{
					required: "<b>Salon is required.</b>"
				},
				checkout_time:{
					required: "<b>Out time is required.</b>"
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
	            	staff_id: $("#staff_id").val(),
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
		        { "data": 6 },
		        { "data": 7 }
		        // { "data": 8 },
		        // { "data": 9 }
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
</script>
<?= $this->endSection(); ?>