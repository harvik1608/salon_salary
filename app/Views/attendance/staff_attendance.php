<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/responsive.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.checkboxes.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/buttons.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/rowgroup.bootstrap5.css'); ?>">
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
								<li class="nav-item"><a class="nav-link active" href="<?php echo base_url('staff-attendance/'.$profile['id']); ?>"><i class="bx bx-sm bx-list-check me-1_5"></i> Attendance</a></li>
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
			          					<th width="10%">DATE</th>
			          					<th width="10%">DAY</th>
			          					<th width="10%">START TIME</th>
			          					<th width="10%">END TIME</th>
			          					<th width="10%">BREAK</th>
			          					<th width="10%">HOURs</th>
			          					<th width="10%">TIP</th>
			          					<th width="10%">SALON</th>
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
<script src="<?php echo base_url('public/assets/css/datatable/datatables-bootstrap5.js'); ?>"></script>
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
		        { "data": 7 },
		        { "data": 8 }
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