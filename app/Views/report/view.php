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
				Reports <a class="btn btn-sm btn-primary" href="<?php echo base_url('reports'); ?>" style="float: right;">Back</a> 
			</h5>
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-default table-bordered">
						<tbody>
							<tr>
								<td align="center" colspan="7">
									<?php echo date('d/m/Y',strtotime($cover['date'])); ?>
									<?php echo date('l',strtotime($cover['date'])); ?>
								</td>
							</tr>
							<tr>
								<td>
									<table class="table table-default">
										<tbody>
											<tr>
												<td><b><?php echo $cover['salon']; ?></b></td>
												<td align="right">Amount</td>
											</tr>
										</tbody>
									</table>
								</td>
								<td>
									<table class="table table-default">
										<tbody>
											<tr>
												<td width="40%">Staff Name</td>
												<td width="15%" align="center">In Time</td>
												<td width="15%" align="center">Out Time</td>
												<td width="15%" align="center">Break Time</td>
												<td width="15%" align="center">Tip on Card</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table class="table table-default">
										<tbody>
											<?php
												$total = 0;
												if($entries) {
													foreach($entries as $entry) {
														$total = $total + $entry['amount'];
											?>
														<tr>
															<td><?php echo $entry['mode']; ?></td>
															<td align="right"><?php echo $cover['currency']." ".$entry['amount']; ?></td>
														</tr>
											<?php
													}
												}
											?>
										</tbody>
									</table>
								</td>
								<td>
									<table class="table table-default">
										<tbody>
											<?php
												$total_tip = 0;
												if($checkins) {
													foreach($checkins as $checkin) {
														if($checkin['in_time'] != "00:00:00") {
															$total_tip = $total_tip + $checkin["tip"];
											?>
															<tr>
																<td width="40%"><?php echo ucwords(strtolower($checkin['staff'])); ?></td>
																<td width="15%" align="center"><?php echo date('H:i',strtotime($checkin['in_time'])); ?></td>
																<td width="15%" align="center"><?php echo date('H:i',strtotime($checkin['out_time'])); ?></td>
																<td width="15%" align="center"><?php echo $checkin['break']; ?> Min.</td>
																<td width="15%" align="center"><?php echo $cover['currency']." ".$checkin["tip"]; ?></td>
															</tr>
											<?php
														}
													}
												}
											?>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td>
									<table class="table table-default">
										<tbody>
											<tr>
												<td><b>TOTAL</b></td>
												<td align="right"><b><?php echo $cover['currency']." ".number_format($total,2); ?></b></td>
											</tr>
										</tbody>
									</table>
								</td>
								<td>
									<table class="table table-default">
										<tbody>
											<tr>
												<td colspan="4" align="right"><b>Total Tip on Card</b></td>
												<td align="right"><b><?php echo $cover['currency']." ".number_format($total_tip,2); ?></b></td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="2">Note : <small class="astrock"><?php echo $cover['note']; ?></small></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	var page_title = "Reports";
</script>
<?= $this->endSection(); ?>