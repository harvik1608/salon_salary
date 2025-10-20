<?php 
	if($staffs) {
	    if($from_month != "" && $to_month != "") {
	        $months = [];
	        for($i = $from_month; $i <= $to_month; $i ++) {
	            array_push($months,$i);
	        }
	    } else {
	        $months = [1,2,3,4,5,6,7,8,9,10,11,12];
	    }
		$all_salons = [];
		foreach ($salons as $key => $val) {
		    array_push($all_salons,$val['id']);
		}
		$all_salons = implode(",",$all_salons);
		echo '<input type="hidden" value="'.$all_salons.'" id="all_salons" />';
		foreach($staffs as $staff) {
?>
            <br><br><a class="btn btn-primary btn-sm text-white" href="javascript:;" onclick="download_report(<?php echo $staff['id']; ?>)"><small>Download Report of <?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?></small></a>
			<div class="table-responsive">
				<table class="table table-default table-bordered mt-5" id="table-<?php echo $staff['id']; ?>">
					<tbody>
						<tr>
							<td align="center" style="background-color: #000000;color: #FFFFFF !important;"><b><?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?></b></td>
							<?php
								if($salons) {
									foreach ($salons as $key => $val) {
							            echo '<td align="center"><b>'.$val['name'].'</b></td>';
									}
								}
							?>
							<td align="center"><b>TOTAL</b></td>
						</tr>
						<?php
						    $total_hours = $total_salary = $total_days = 0;
						    foreach($months as $month) {
						        if($month%2 == 0) {
								    $color = "#000000";
								    $background_color = "#efefef";
								} else {
								    $color = "#000000";
								    $background_color = "#e8e8e8";
								}
						        echo '<tr>';
						        echo '<td align="center" style="color: '.$color.' !important;background-color: '.$background_color.' !important;"><b>'.strtoupper(date("M", mktime(0, 0, 0, $month, 1))).', '.$year.'</b></td>';
						        if($salons) {
						            $salon_wise_total_hours = $salon_wise_total_salary = $salon_wise_total_days = 0;
									foreach ($salons as $key => $val) {
									    $calc = attendanceReport($val['id'],$staff['id'],$year,$month);
										$days = isset($calc['total_days']) ? $calc['total_days'] : 0;
										$hours = isset($calc['total_month_hours']) ? $calc['total_month_hours'] : 0;
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
										$salon_wise_total_days = $salon_wise_total_days + $days;
										$salon_wise_total_hours = $salon_wise_total_hours + $hours;
										$salon_wise_total_salary = $salon_wise_total_salary + $salary;
										
										echo '<td align="center" style="color: '.$color.' !important;background-color: '.$background_color.' !important;"><b>DAYS : <span class="day-'.$val['id'].'" data-day='.$days.'>'.$days.'</span><br>------------------------------------<br>HOURS : <span class="hour-'.$val['id'].'" data-hour='.$hours.'>'.number_format($hours,2).'</span><br>------------------------------------<br>SALARY : <span class="salary-'.$val['id'].'" data-salary='.$salary.'>'.number_format($salary,2).'</span></b></td>';
									}
									$total_hours = $total_hours + $salon_wise_total_hours;
									echo '<td align="center" style="color: '.$color.' !important;background-color: '.$background_color.' !important;"><b>DAYS : <span class="total-day-'.$val['id'].'" data-day='.$salon_wise_total_days.'>'.$salon_wise_total_days.'</span><br>------------------------------------<br>HOURS : <span class="total-hour-'.$val['id'].'" data-hour='.$salon_wise_total_hours.'>'.number_format($salon_wise_total_hours,2).'</span><br>------------------------------------<br>SALARY : <span class="total-salary-'.$val['id'].'" data-salary='.$salon_wise_total_salary.'>'.number_format($salon_wise_total_salary,2).'</span></b></td>';
								}
						        echo '</tr>';
						    }
		                ?>
		                <tr>
		                    <td align="center"><b>TOTAL</b></td>
		                    <?php
								if($salons) {
									foreach ($salons as $key => $val) {
							            echo '<td align="center" class="total-'.$val['id'].'"></td>';
									}
									echo '<td align="center" class="grand-total"></td>';
								}
							?>
		                </tr>
		                <tr>
		                    <td align="center"><b>NOTE</b></td>
		                    <td colspan="<?php echo count($salons); ?>">
		                        <textarea class="form-control" id="staff_note_<?php echo $staff['id']; ?>" placeholder="Write note for <?php echo strtoupper(strtolower($staff['fname'].' '.$staff['lname'])); ?>..."><?php echo isset($staff['yearly_note']) ? $staff['yearly_note'] : ''; ?></textarea>
		                    </td>
		                    <td align="center"><a class="btn btn-primary btn-sm text-white" onclick="save_staff_yearly_note(<?php echo $staff['id']; ?>)" id="save-btn-<?php echo $staff['id']; ?>">Save</a></td>
						</tr>
					</tbody>
				</table>
			</div>
<?php
		}
	}
?>