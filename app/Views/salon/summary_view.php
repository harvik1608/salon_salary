<div class="table-responsive">
    <a class="show_more_tbl_1" href="javascript:;" style="float: right;"><small><b>Show More</b></small></a>
	<table class="table table-default table-bordered" id="tbl_1">
		<thead>
			<tr>
				<th>Date</th>
				<?php
					if($modes) {
						foreach($modes as $mode) {
							echo '<th align="right">'.ucwords(strtolower($mode['name'])).'</th>';
						}
					} 
				?>
				<th>Total</th>
				<th>Tip</th>
			</tr>
		</thead>
		<tbody style="display: none;">
			<?php
				$loyalty_discount = 0;
				$grand_total = 0;
				$grand_tip = 0;
				$mode_wise_total = 0;
				if($entries) {
					foreach($entries as $entry) {
						$row_wise_date_total = 0;
			?>
						<tr style="cursor: pointer;" onclick="window.open('<?php echo base_url('entries/new'); ?>?date=<?php echo $entry['date']; ?>&salon_id=<?php echo $salon_id; ?>')">
							<td align="center">
								<b><?php echo date('d M, Y',strtotime($entry['date'])); ?><br><?php echo date('l',strtotime($entry['date'])); ?></b>
							</td>
							<?php
								$model = db_connect();
								if($modes) {
					                foreach($modes as $mod_key => $mod_val) {  
					                    // $amt = $model->selectSum("amount")->where("cover_id",$entry["id"])->where("payment_mode_id",$mod_val['id'])->where("deleted_at IS NULL")->get()->getRowArray();
					                    $query = $model->table("salon_cover_entries ce");
										$query = $query->join("salon_covers c","c.id=ce.cover_id");
										$query = $query->selectSum("ce.amount");
										$query = $query->where("ce.cover_id",$entry["id"]);
										$query = $query->where("ce.payment_mode_id",$mod_val['id']);
										$query = $query->where("ce.deleted_at IS NULL");
										$amt = $query->get()->getRowArray();
					                    if($amt && $amt['amount'] != "") {
					                        $mode_wise_total = $mode_wise_total + $amt['amount'];
					                        $row_wise_date_total = $row_wise_date_total + $amt['amount'];
					                        echo '<td align="right"><b>'.$amt['amount'].'</b></td>';
					                    } else {
					                    	echo '<td align="center"><b>-</b></td>';
					                    }
					                }
					            }
					            echo '<td align="right"><b>'.number_format($row_wise_date_total,2).'</b></td>';
					            echo '<td align="right"><b>'.$entry['tip'].'</b></td>';
							?>
						</tr>
			<?php
						$grand_total = $grand_total + $row_wise_date_total;
						$grand_tip = $grand_tip + $entry['tip'];
					}
				} 
			?>
		</tbody>
		<tfoot>
		    <?php 
		        echo "<tr>";
				echo "<td style='background-color: #696cff;color: #fff !important;'><b>GRAND TOTAL</b></td>";
				if($modes) {
					$model = db_connect();
					foreach($modes as $mod_key => $mod_val) {
						$query = $model->table("salon_cover_entries ce");
						$query = $query->join("salon_covers c","c.id=ce.cover_id");
						$query = $query->selectSum("ce.amount");
						$query = $query->where("c.date >=",$sdate);
						$query = $query->where("c.date <=",$edate);
						$query = $query->where("ce.payment_mode_id",$mod_val['id']);
						$query = $query->where("c.salon_id",$salon_id);
						$amt = $query->get()->getRowArray();
	                    if($amt && $amt['amount'] != "") {
	                        $mode_wise_total = $mode_wise_total + $amt['amount'];
	                        echo '<td align="right" style="background-color: #696cff;color: #fff !important;"><b>'.$salon['currency'].' '.$amt['amount'].'</b></td>';
	                    } else {
	                    	echo '<td align="right" style="background-color: #696cff;color: #fff !important;"><b>'.$salon['currency'].' 0.00</b></td>';
	                    }
					}
				}
				echo "<td align='right' style='background-color: #696cff;color: #fff !important;'><b>".$salon['currency'].' '.number_format($grand_total,2)."</b></td>";
				echo "<td align='right' style='background-color: #696cff;color: #fff !important;'><b>".$salon['currency'].' '.number_format($grand_tip,2)."</b></td>";
				echo "</tr>";
			?>
		</tfoot>
	</table>
	<br><br>
	<center><a class="btn btn-sm btn-success text-white" href="javascript:copy_summary_content()"><b>Download Report</b></a></center><br>
	<table class="table table-default table-bordered" id="staff_summary_tbl">
		<thead>
			<tr>
				<th>STAFF</th>
				<th>WAGE</th>
				<th>RATE</th>
				<th>H/D</th>
				<th>WORKING DAYS</th>
				<th>TOTAL</th>
				<th>TIP</th>
				<th>TOTAL</th>
			</tr>
		</thead>
		<tbody>
			<?php 
				if($staffs) {
					$total_rate = $total_hour_per_day = $row_total = $total_tip = $row_grand_total = $total_hd = 0;
					foreach($staffs as $staff) {
						$hour_per_day = 0;
						$working_days = 0;
						$total = $staff["rate"];
						$tip = 0;
						$grand_total = 0;
						if(isset($staff['hour_per_day'])) {
							$hour_per_day = $staff['hour_per_day'];
						}
						if($staff['wage'] == 2) {
							$total = $staff['rate']*$hour_per_day;
						}
						if(isset($staff["working_days"])) {
							$working_days = $staff["working_days"];
						}
						if(isset($staff["tip"])) {
							$tip = $staff["tip"];
						}
						$grand_total = $total + $tip;
			?>
						<tr>
							<td><small><b><?php echo ucwords(strtolower($staff['fname'].' '.$staff['lname'])); ?></b></small></td>
							<td><small><b><?php echo $staff['wage'] == 1 ? "Monthly" : "Hourly"; ?></b></small></td>
							<td align="right"><small><b><?php echo $staff['rate']; ?></b></small></td>
							<td align="right"><small><b><?php echo number_format($hour_per_day,2); ?></b></small></td>
							<td align="center"><small><b><?php echo $working_days; ?></b></small></td>
							<td align="right"><small><b><?php echo number_format($total,2); ?></b></small></td>
							<td align="right"><small><b><?php echo number_format($tip,2); ?></b></small></td>
							<td align="right"><small><b><?php echo number_format($grand_total,2); ?></b></small></td>
						</tr>
			<?php
						$total_rate = $total_rate + $staff["rate"];
						$total_hour_per_day = $total_hour_per_day + $hour_per_day;
						$row_total = $row_total + $total;
						$total_tip = $total_tip + $tip;
						$row_grand_total = $row_grand_total + $grand_total;
						$total_hd = $total_hd + $hour_per_day;
					}
			?>
					<tr>
						<td colspan="2" align="right" style="background-color: #696cff;color: #fff !important;"><b style="font-size: 15px !important;">GRAND TOTAL</b></td>
						<td align="right" style="background-color: #696cff;color: #fff !important;">-</td>
						<td align="right" style="background-color: #696cff;color: #fff !important;"><small><b style="font-size: 15px !important;"><?php echo $total_hd; ?></b></small></td>
						<td align="right" style="background-color: #696cff;color: #fff !important;">-</td>
						<td align="right" style="background-color: #696cff;color: #fff !important;"><small><b style="font-size: 15px !important;"><?php echo $salon['currency']." ".number_format($row_total,2); ?></b></small></td>
						<td align="right" style="background-color: #696cff;color: #fff !important;"><small><b style="font-size: 15px !important;"><?php echo $salon['currency']." ".number_format($total_tip,2); ?></b></small></td>
						<td align="right" style="background-color: #696cff;color: #fff !important;"><small><b style="font-size: 15px !important;"><?php echo $salon['currency']." ".number_format($row_grand_total,2); ?></b></small></td>
					</tr>
			<?php
				}
			?>
		</tbody>
	</table>
	<br><br>
	<?php
		if($staffs) {
			foreach($staffs as $staff) {
				if(isset($staff["checkins"]) && !empty($staff["checkins"])) {
	?>
					<a href="javascript:;" style="float: right;" id="staff_more_less_view_<?php echo $staff['id']; ?>" onclick="show_full_table(<?php echo $staff['id']; ?>)"><small><b>Show More</b></small></a>
					<table class="table table-default table-bordered" id="staff-checkin-<?php echo $staff['id']; ?>">
						<thead>
							<tr>
								<th colspan="8" style="font-size: 25px;font-weight: bold !important;background-color: #efefef;">
									<b>
										<?php echo $staff['fname'].' '.$staff['lname']; ?> 
										<br>(Rate: <?php echo $staff['rate']; ?>)
									</b>
								</th>
							</tr>
							<tr>
									<th width="10%">DATE</th>									
									<th width="10%">DAY</th>									
									<th width="10%">START TIME</th>
									<th width="10%">END TIME</th>
									<th width="10%">BREAK</th>
									<th width="10%">HOURS</th>
									<th width="10%">TIP</th>
									<th width="15%">SALON</th>
							</tr>
						</thead>
						<tbody style="display: none;">
							<?php
								$row_wise_tip = 0;
								$row_wise_hours = 0;
								if($staff["checkins"]) {
									foreach($staff["checkins"] as $checkin) {
										$row_wise_tip = $row_wise_tip + $checkin['tip'];
										if(is_numeric($checkin['hours_diff'])) {
											$row_wise_hours = $row_wise_hours + $checkin['hours_diff'];
										}
										if($checkin['in_time'] != "00:00:00") {
							?>
											<tr id="checkin-<?php echo $checkin['id']; ?>" <?php echo $checkin["is_from_other_salon"] == 1 ? "style='background-color: #FFFF00;'" : ""; ?>>
												<td align="center">
													<b><?php echo date('d M, Y',strtotime($checkin['date'])); ?></b><br>
													<small>
														<a href="javascript:update_atten('<?php echo $checkin['id']; ?>');">
															<i class="icon-base bx bx-edit icon-sm"></i>
														</a>
													</small>
													<small>
														<a href="javascript:remove_atten('<?php echo $checkin['id']; ?>');">
															<i class="icon-base bx bx-trash icon-sm"></i>
														</a>
													</small>
												</td>
												<td align="center"><b><?php echo date('l',strtotime($checkin['date'])); ?></b></td>
												<td align="center"><b><?php echo $checkin['in_time'] == "00:00:00" ? "-" : date('H:i:s',strtotime($checkin['in_time'])); ?></b></td>
												<td align="center"><b><?php echo $checkin['out_time'] == "00:00:00" ? "-" : date('H:i:s',strtotime($checkin['out_time'])); ?></b></td>
												<td align="center"><b><?php echo $checkin['break'] == 0 ? "-" : $checkin['break']; ?></b></td>
												<td align="center">
													<b>
														<?php echo calculateWorkingHours($checkin['in_time'],$checkin['out_time'],$checkin['break']); ?>
													</b>
													<span hidden><?php echo $checkin['hours_diff']; ?></span>
												</td>
												<td align="center"><b><?php echo $salon['currency']." <span>".$checkin['tip']."</span>"; ?></b></td>
												<td align="center">
													<?php
														if($checkin["is_from_other_salon"] == 1) {
															echo "<b>".strtoupper($checkin['salon'])."</b>";
														} else {
															echo "<b>".strtoupper($checkin['salon'])."</b>";
														}
													?>
													<select style="display: none;">
														<?php
															if($all_salons) {
																foreach($all_salons as $all_salon) {
														?>
																	<option value="<?php echo $all_salon['id']; ?>" <?php echo $all_salon['id'] == $checkin['salon_id'] ? "selected" : ""; ?>>
																		<?php echo $all_salon['name']; ?>
																	</option>
														<?php
																}
															} 
														?>
													</select>
												</td>
											</tr>
							<?php
										}
									}
									$total_salary = $staff["rate"];
									if($staff["wage"] == 2) {
										$total_salary = ($row_wise_hours)*$staff['rate'];
									}
									$total_salary = $total_salary+$row_wise_tip;

									// $total_salary = $checkin['rate'];
									// if($checkin['rate'] < 100) {
									// 	$total_salary = ($row_wise_hours)*$checkin['rate'];
									// }
									// $total_salary = $total_salary+$row_wise_tip;
								} 
							?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" align="right"><b>TOTAL</b></td>
								<td align="center"><b><?php echo $row_wise_hours; ?></b></td>
								<td align="center"><b><?php echo $salon['currency'].' '.number_format($row_wise_tip,2); ?></b></td>
								<td align="center" style="background-color: #696cff;color: #fff !important;font-size: 20px !important;"><b><?php echo $salon['currency'].' '.number_format($total_salary,2); ?></b></td>
							</tr>
							<tr>
								<?php
									if(isset($staff["salons"])) {
										foreach($staff["salons"] as $sl) {
								?>
											<td colspan="2" align="center">
												<b style="font-size: 15px !important;">
													<?php echo $sl->salon; ?>
													<br>
													<?php
														if($checkin['rate'] < 100) {
															echo number_format($sl->hours,2)."*".$checkin['rate']." = ".($sl->hours*$checkin['rate']);
														} else {
														    $total_hours = $row_wise_hours;
														    $staff_rates = $staff['rate'];
														    $salon_hours = $sl->hours;
														    $salon_wise_hours = ($salon_hours*$staff_rates)/$total_hours; 
															echo number_format($salon_wise_hours,2)."<br><small>(staff'rate*salon's hours)/total hours</small>";
														}
													?>
												</b>
											</td>
								<?php
										}
									} 
								?>
							</tr>
						</tfoot>
					</table>
					<br>
					<center><a class="btn btn-sm btn-success" href="javascript:copy_content('<?php echo $staff['id']; ?>')"><b>Download report of <?php echo $staff['fname'].' '.$staff['lname']; ?></b></a></center>
					<br><br>
	<?php 
				}
			}
		}
	?>
</div>