<?= $this->extend('include/header'); ?>
<?= $this->section('main_content'); ?>
<!-- <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css"> -->
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/responsive.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/datatables.checkboxes.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/buttons.bootstrap5.css'); ?>">
<link rel="stylesheet" href="<?php echo base_url('public/assets/css/datatable/rowgroup.bootstrap5.css'); ?>">
<div class="content-wrapper">
	<div class="container-xxl flex-grow-1 container-p-y">
		<div class="card">
			<h5 class="card-header">
				Entries <a href="<?php echo base_url('entries/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a>
			</h5>
			<div class="card-body">
				<form method="post" action="<?php echo base_url('export-entries'); ?>">
					<div class="row">
						<div class="col-lg-4">
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
						<div class="col-lg-2">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Month & Year</label>
								<input type="month" class="form-control" id="datetime" />
							</div>
						</div>
						<div class="col-lg-2">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Date</label>
								<input type="date" class="form-control" id="date" />
							</div>
						</div>
						<div class="col-lg-2">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Amount</label>
								<input type="number" class="form-control" id="amount" placeholder="Enter amount" />
							</div>
						</div>
						<div class="col-lg-2">
							<div class="mb-4">
								<label class="form-label" for="basic-default-fullname">Tip</label>
								<input type="number" class="form-control" id="tip" placeholder="Enter tip" />
							</div>
						</div>
					</div>
					<div class="row">
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
	          					<th width="25%">Salon</th>
	          					<th width="15%">Date</th>
	          					<th width="15%">Day</th>
	          					<th width="10%">Amount</th>
	          					<th width="10%">Tip</th>
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
<!-- <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> -->
<script src="<?php echo base_url('public/assets/css/datatable/datatables-bootstrap5.js'); ?>"></script>
<script type="text/javascript">
	var page_title = "Entries";
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
	            	date: $("#date").val(),
	            	amount: $("#amount").val(),
	            	tip: $("#tip").val()
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
		        // { "data": 7 },
		        // { "data": 8 },
		        // { "data": 9 }
		    ],
		    "order": [[0, "desc"]]
		});
    }
</script>
<?= $this->endSection(); ?>