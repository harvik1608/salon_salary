<?php 
	if($staffs) {
		$months = [1,2,3,4,5,6,7,8,9,10,11,12];
		foreach($staffs as $staff) {
?>
			<div class="table-responsive">
				<table class="table table-default table-bordered">
					<thead>
						<tr>
							<th colspan="4"><?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<?php
								if($salons) {
								    $month_wise_salary = $month_wise_staff_salary = 0;
									foreach ($salons as $key => $val) {
							?>
										<td>
											<table class="table table-default">
												<thead>
													<tr>
														<th colspan="4"><b><?php echo $val['name']; ?></b></th>
													</tr>
													<tr>
														<th style="float: left;">Month</th>
														<th>Days</th>
														<th>Hours</th>
														<th>Salary</th>
													</tr>
												</thead>
												<tbody>
													<?php
														$total_days = $total_hours = $total_salary = 0;
														foreach($months as $month) {
															$calc = attendanceReport($val['id'],$staff['id'],$year,$month);
															$days = isset($calc['total_days']) ? $calc['total_days'] : 0;
															$total_days = $total_days + $days;

															$hours = isset($calc['total_month_hours']) ? $calc['total_month_hours'] : 0;
															$total_hours = $total_hours + $hours;

															$rate = isset($calc['rate']) ? $calc['rate'] : 0;
															if($rate > 100) {
															    $calc1 = attendanceReport(0,$staff['id'],$year,$month);
															    if(isset($calc1['total_month_hours'])) {
															        $salary = ($rate*$hours)/$calc1['total_month_hours']; 
															    }
																// $salary = $rate;
															} else {
																$salary = $hours*$rate;
															}
															$total_salary = $total_salary + $salary;

															echo '<tr>';
															echo '<td><b>'.strtoupper(date("F", mktime(0, 0, 0, $month, 1))).'</b></td>';
															echo '<td align="center">'.$days.'</td>';
															echo '<td align="center">'.number_format($hours,2).'</td>';
															echo '<td align="center">'.number_format($salary,2).'</td>';
															echo '</tr>';
														} 
													?>
													<tr>
														<td align="center"><b>TOTAL</b></td>
														<td align="center"><b><?php echo $total_days; ?></b></td>
														<td align="center"><b><?php echo number_format($total_hours,2); ?></b></td>
														<td align="center"><b><?php echo number_format($total_salary,2); ?></b></td>
													</tr>
												</tbody>
											</table>
										</td>
							<?php
									}
						    ?>
						            <td>
    						            <table class="table table-default">
    										<thead>
    											<tr>
    												<th colspan="4"><b>TOTAL</b></th>
    											</tr>
    											<tr>
    												<th style="float: left;">Month</th>
    												<th>Salary</th>
    											</tr>
    										</thead>
    										<tbody>
    										    <?php
    										        
												    foreach($months as $month) {
												        $calc = attendanceReport($val['id'],$staff['id'],$year,$month);
												        $hours = isset($calc['total_month_hours']) ? $calc['total_month_hours'] : 0;
														$rate = isset($calc['rate']) ? $calc['rate'] : 0;
														if($rate > 100) {
														    $calc1 = attendanceReport(0,$staff['id'],$year,$month);
														    if(isset($calc1['total_month_hours'])) {
														        $month_wise_salary = ($rate*$hours)/$calc1['total_month_hours']; 
														    }
														} else {
															$month_wise_salary = 0;
														}
														$month_wise_staff_salary = $month_wise_staff_salary + $month_wise_salary;
														echo '<tr>';
													    echo '<td><b>'.strtoupper(date("F", mktime(0, 0, 0, $month, 1))).'</b></td>';
													    echo '<td align="center"><b>'.number_format($month_wise_salary,2).'</b></td>';
												    }
												?>
												<tr>
													<td align="center"><b>TOTAL</b></td>
													<td align="center"><b><?php echo number_format($month_wise_staff_salary,2); ?></b></td>
												</tr>
    										</tbody>
    								    </table>
    								</td>
						    <?php
								}
							?>
						</tr>
						<tr>
						    <td colspan="<?php echo count($salons); ?>">
						        <label>Note for <?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?></label>
						        <textarea class="form-control" id="staff_note_<?php echo $staff['id']; ?>" placeholder="Write note for <?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?>..."><?php echo isset($staff['yearly_note']) ? $staff['yearly_note'] : ''; ?></textarea>
						        <br><a class="btn btn-sm btn-info" onclick="save_staff_yearly_note(<?php echo $staff['id']; ?>)">Save</a>
						    </td>
						</tr>
					</tbody>
				</table>
			</div>	<br>
<?php
		}
	}
?>