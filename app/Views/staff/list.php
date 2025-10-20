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
				Staffs <a href="<?php echo base_url('staffs/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a>
			</h5>
			<div class="container">
				<div class="table-responsive">
				<table class="table table-default" id="tbl-list">
					<thead>
						<tr>
          					<th>No</th>
          					<th>Name</th>
          					<th>Salon</th>
          					<th>Added On</th>
          					<th>Status</th>
          					<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if($staffs) {
								$no = 0;
								foreach($staffs as $staff) {
									$no++;
						?>
									<tr>
										<td><small><?php echo $no; ?></small></td>
										<td><small><?php echo ucwords(strtolower($staff['fname']." ".$staff['lname'])); ?></small></td>										
										<td><small><?php echo $staff['salon']; ?></small></td>
										<td><small><?php echo format_date($staff['created_at']); ?></small></td>
										<td>
											<?php
												switch($staff['is_active']) {
													case 1:
														echo '<span class="badge bg-success"><small>Active</small></span>';
														break;

													default:
														echo '<span class="badge bg-danger"><small>Inactive</small></span>';
														break;
												} 
											?>
										</td>
										
										<td>
											<a href="<?php echo base_url('staffs/'.$staff['id']); ?>"><i class="icon-base bx bx-user icon-sm"></i></a>
											<a href="<?php echo base_url('staffs/'.$staff['id'].'/edit'); ?>"><i class="icon-base bx bx-edit icon-sm"></i></a>
											<a href="javascript:;" onclick="remove_row('<?php echo base_url('staffs/'.$staff['id']); ?>')"><i class="icon-base bx bx-trash icon-sm"></i></a>
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
	var page_title = "Staffs";
	$(document).ready(function() {
        $('#tbl-list').DataTable();
    });
</script>
<?= $this->endSection(); ?>