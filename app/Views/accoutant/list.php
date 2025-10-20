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
				Accoutants <a href="<?php echo base_url('accoutants/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a>
			</h5>
			<div class="container">
				<div class="table-responsive">
				<table class="table table-default" id="tbl-list">
					<thead>
						<tr>
          					<th>No</th>
          					<th>Name</th>
          					<th>Email</th>
          					<th>Mobile No.</th>
          					<th>Joining Date</th>
          					<th>Status</th>
          					<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if($accoutants) {
								$no = 0;
								foreach($accoutants as $accoutant) {
									$no++;
						?>
									<tr>
										<td><small><?php echo $no; ?></small></td>
										<td><small><?php echo $accoutant['fname']." ".$accoutant['lname']; ?></small></td>
										<td><small><?php echo $accoutant['email']; ?></small></td>
										<td><small><?php echo $accoutant['phone']; ?></small></td>
										<td><small><?php echo is_null($accoutant['joining_date']) ? "-" : $accoutant['joining_date']; ?></small></td>
										<td>
											<?php
												switch($accoutant['is_active']) {
													case 1:
														echo '<span class="badge bg-success">Active</span>';
														break;

													default:
														echo '<span class="badge bg-danger">Inactive</span>';
														break;
												} 
											?>
										</td>
										<td>
											<a href="<?php echo base_url('accoutants/'.$accoutant['id'].'/edit'); ?>"><i class="icon-base bx bx-edit icon-sm"></i></a>
											<a href="javascript:;" onclick="remove_row('<?php echo base_url('accoutants/'.$accoutant['id']); ?>')"><i class="icon-base bx bx-trash icon-sm"></i></a>
										</td>
									</tr>
						<?php
								}
							} 
						?>
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
	var page_title = "Accountants";
	$(document).ready(function() {
        $('#tbl-list').DataTable();
    });
</script>
<?= $this->endSection(); ?>