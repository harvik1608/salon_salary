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
				Salons <a href="<?php echo base_url('salons/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a>
			</h5>
			<div class="container">
				<div class="table-responsive">
				<table class="table table-default" id="tbl-list">
					<thead>
						<tr>
          					<th>No</th>
          					<th>Name</th>
          					<th>Opening Time</th>
          					<th>Closing Time</th>
          					<th>Currency</th>
          					<th>Status</th>
          					<th>Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if($salons) {
								$no = 0;
								foreach($salons as $salon) {
									$no++;
						?>
									<tr>
										<td><small><?php echo $no; ?></small></td>
										<td><small><?php echo $salon['name']; ?></small></td>
										<td><small><?php echo $salon['stime']; ?></small></td>
										<td><small><?php echo $salon['etime']; ?></small></td>
										<td><small><?php echo $salon['currency']; ?></small></td>
										<td>
											<?php
												switch($salon['is_active']) {
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
											<!-- <a href="<?php echo base_url('salon-entries/'.$salon['id']); ?>">Entries</a> -->
											<a href="<?php echo base_url('salons/'.$salon['id'].'/edit'); ?>"><i class="icon-base bx bx-edit icon-sm"></i></a>
											<a href="<?php echo base_url('salon-mode-entries/'.$salon['id']); ?>" title="Reports"><i class="icon-base bx bx-bar-chart icon-sm"></i></a>
											<!-- <a href="javascript:;" onclick="remove_row('< ?php echo base_url('salons/'.$salon['id']); ?>')"><i class="icon-base bx bx-trash icon-sm"></i></a> -->
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
	var page_title = "Salons";
	$(document).ready(function() {
        $('#tbl-list').DataTable();
    });
    
</script>
<?= $this->endSection(); ?>