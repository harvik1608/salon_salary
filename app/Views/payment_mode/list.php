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
				Payment Modes <a href="<?php echo base_url('payment_modes/new'); ?>" class="btn btn-primary btn-sm text-white" style="float: right;">New</a>
			</h5>
			<div class="container">
				<div class="table-responsive">
				<table class="table table-default" id="tbl-list">
					<thead>
						<tr>
          					<th width="5%">No</th>
          					<th width="55%">Name</th>
          					<th width="15%">Total</th>
          					<th width="10%">Status</th>
          					<th width="15%">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							if($payment_modes) {
								$no = 0;
								foreach($payment_modes as $payment_mode) {
									$no++;
						?>
									<tr>
										<td><small><?php echo $no; ?></small></td>
										<td><small><?php echo $payment_mode['name']; ?></small></td>
										<td><small>£ <?php echo $payment_mode['total']; ?></small></td>
										<td>
											<?php
												switch($payment_mode['is_active']) {
													case 1:
														echo '<span class="badge bg-label-primary">Active</span>';
														break;

													default:
														echo '<span class="badge bg-label-danger">Inactive</span>';
														break;
												} 
											?>
										</td>
										<td>
											<!-- <a href="<?php echo base_url('mode-wise-chart/'.$payment_mode['id']); ?>"><i class="bx bx-show"></i></a> -->
											<a href="<?php echo base_url('payment_modes/'.$payment_mode['id'].'/edit'); ?>"><i class="icon-base bx bx-edit icon-sm"></i></a>
											<a href="javascript:;" onclick="remove_row('<?php echo base_url('payment_modes/'.$payment_mode['id']); ?>')"><i class="icon-base bx bx-trash icon-sm"></i></a>
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
	var page_title = "Payment Modes";
	$(document).ready(function() {
        $('#tbl-list').DataTable();
    });
</script>
<?= $this->endSection(); ?>