<div class="row">
	<div class="col-lg-12">
		<a class="btn btn-danger btn-sm text-white" href="javascript:;" onclick="remove_entry()" style="float: right;">
			<i class="icon-base bx bx-trash icon-sm"></i>
		</a><br><br>
		<div class="table-responsive">
			<table class="table table-default table-bordered">
				<thead>
					<tr>
						<th width="80%">Payment Mode</th>
						<th width="20%">Amount</th>
					</tr>
				</thead>
				<tbody>
					<?php
						if($modes) {
							foreach($modes as $mode) {
					?>
								<tr>
									<td><input type="hidden" name="mode_id[]" value="<?php echo $mode['id']; ?>" /><?php echo $mode['name']; ?><small style="float: right;font-weight: bold;">(<?php echo $mode['is_deduct'] == 0 ? "+" : "-"; ?>)</small></td>
									<td><input type="number" name="price[]" class="form-control amount" placeholder="Enter amount" value="<?php echo isset($mode['price']) ? $mode['price'] : ''; ?>" data-operation="<?php echo $mode['is_deduct']; ?>" style="text-align: right;" /></td>
								</tr>
					<?php
							}
						} 
					?>
					<tr>
						<td align="right">TOTAL</td>
						<td><input type="number" class="form-control" id="total_amount" name="total_amount" value="<?php echo isset($cover['amount']) ? $cover['amount'] : ''; ?>" readonly style="text-align: right;" style="text-align: right;" /></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
<div class="row mt-5">
	<div class="col-lg-10">
		<div class="mb-4">
			<label class="form-label" for="basic-default-fullname">Note</label>
			<input type="text" class="form-control" id="note" name="note" value="<?php echo isset($cover['note']) ? $cover['note'] : ''; ?>" />
		</div>
	</div>
	<div class="col-lg-2">
		<div class="mb-4">
			<label class="form-label" for="basic-default-fullname">Total Tip on Card</label>
			<input type="number" class="form-control" id="tip" name="entry_tip" value="<?php echo isset($cover['tip']) ? $cover['tip'] : ''; ?>" />
		</div>
	</div>
</div>
<hr>
<div class="row">
	<div class="col-lg-12">
		<h3>Staff Attendance</h3>
		<div class="table-responsive">
			<table class="table table-default table-bordered" id="staff-checkin">
				<thead>
					<tr>
						<th width="15%"><center><b>Staff</b></center></th>
						<th width="20%"><center><b>Salon</b></center></th>
						<th width="10%"><center><b>In Time <br><small style="font-size: 10px;">(HH:MM)</small></b></center></th>
						<th width="10%"><center><b>Out Time <br><small style="font-size: 10px;">(HH:MM)</small></b></center></th>
						<th width="15%"><center><b>Break <br><small style="font-size: 10px;">(in min.)</small></b></center></th>
						<th width="10%"><center><b>Hours</b></center></th>
						<th width="10%"><center><b>Tip</b></center></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if($staffs) {
							foreach($staffs as $staff) {
								$in_time = "00:00";
								$out_time = "00:00";
								$break = 0;
								if(isset($staff['in_time']) && $staff['in_time'] != "00:00:00") {
									$in_time = date('H:i',strtotime($staff['in_time']));
								}
								if(isset($staff['out_time']) && $staff['out_time'] != "00:00:00") {
									$out_time = date('H:i',strtotime($staff['out_time']));
								}
								if(isset($staff['break']) && $staff['break'] != 0) {
									$break = $staff['break'];
								}
					?>
								<tr id="staff-<?php echo $staff['id']; ?>">
									<td style="font-size: 11px !important;">
										<input type="hidden" name="staff_id[]" value="<?php echo $staff['id']; ?>" />
										<input type="hidden" name="old_salon_id[]" value="<?php echo $staff['salon_id']; ?>" />
										<input type="hidden" name="rate[]" value="<?php echo $staff['rate']; ?>" />
										<center><?php echo $staff['fname']." ".$staff['lname']; ?></center>
									</td>
									<td>
										<center>
											<input type="hidden" name="salon_id[]" value="<?php echo $staff['salon_id']; ?>" />
											<?php
												if($salons) {
													foreach($salons as $salon) {
														if($salon['id'] == $staff['salon_id']) {
															echo "<small>".ucwords(strtolower($salon['name']))."</small>";
														}
													}
												}
											?>
										</center>
									</td>
									<td>
										<center><input type="text" name="in_time[]" value="<?php echo isset($staff['in_time']) && $staff['in_time'] != "00:00:00" ? date('H:i',strtotime($staff['in_time'])) : ''; ?>" onblur="calc_hours('<?php echo $staff['id']; ?>')" class="form-control" style="color: #000000 !important;" /></center>
									</td>
									<td>
										<center><input type="text" name="out_time[]" value="<?php echo isset($staff['out_time']) && $staff['out_time'] != "00:00:00" ? date('H:i',strtotime($staff['out_time'])) : ''; ?>" onblur="calc_hours('<?php echo $staff['id']; ?>')" class="form-control" style="color: #000000 !important;" /></center>
									</td>
									<td><center><input type="number" name="break[]" value="<?php echo isset($staff['break']) && $staff['break'] != 0 ? $staff['break'] : ''; ?>" class="form-control" value="0" onblur="calc_hours('<?php echo $staff['id']; ?>')" style="color: #000000 !important;" /></center></td>
									<td>
										<center>
											<input type="hidden" name="hours[]" value="<?php echo isset($staff['staff_hours']) && $staff['staff_hours'] != 0 ? $staff['staff_hours'] : ''; ?>" class="form-control" style="font-size: 12px !important;color: #000000 !important;" />
											<span><?php echo isset($staff['staff_hours']) && $staff['staff_hours'] != 0 ? calculateWorkingHours($in_time,$out_time,$break) : ''; ?></span>
										</center>
									</td>
									<td>
										<center>
											<input type="text" name="tip[]" value="<?php echo isset($staff['staff_tip']) && $staff['staff_tip'] != 0 ? $staff['staff_tip'] : ''; ?>" class="form-control" style="font-size: 12px !important;color: #000000 !important;" />
											<?php 
												if(isset($staff['entry_id'])) {
											?>
													<br><a href="javascript:;" onclick="remove_daily_entry('<?php echo $staff['entry_id']; ?>')"><i class="icon-base bx bx-trash icon-sm"></i></a>
											<?php
												}
											?>
										</center>
									</td>
								</tr>
					<?php
							}
						}
						if($extra_staffs) {
							$no = 0;
							foreach($extra_staffs as $extra_staff) {
								$no++;
								$in_time = "00:00";
								$out_time = "00:00";
								$break = 0;
								if(isset($extra_staff['in_time']) && $extra_staff['in_time'] != "00:00:00") {
									$in_time = date('H:i',strtotime($extra_staff['in_time']));
								}
								if(isset($extra_staff['out_time']) && $extra_staff['out_time'] != "00:00:00") {
									$out_time = date('H:i',strtotime($extra_staff['out_time']));
								}
								if(isset($extra_staff['break']) && $extra_staff['break'] != 0) {
									$break = $extra_staff['break'];
								}
					?>
								<tr id="extra-staff-<?php echo $no; ?>">
									<td>
										<center>
											<select class="form-control select2" name="staff_id[]" style="color: #000000 !important;">
												<option value="" disabled selected>Please select</option>
												<?php
													if($other_staffs) {
														foreach($other_staffs as $other_staff) {
												?>
															<option value="<?php echo $other_staff['id']; ?>" data-salon="<?php echo $other_staff['salon_id']; ?>" data-rate="<?php echo $other_staff['rate']; ?>" <?php echo isset($extra_staff['staff_id']) && $extra_staff['staff_id'] == $other_staff['id'] ? "selected" : ""; ?>><?php echo ucwords(strtolower($other_staff['fname'].' '.$other_staff['lname'])); ?></option>
												<?php
														}
													}
												?>
											</select>
											<input type="hidden" name="old_salon_id[]" value="<?php echo isset($extra_staff['old_salon_id']) ? $extra_staff['old_salon_id'] : ''; ?>" />
											<input type="hidden" name="rate[]" value="<?php echo isset($extra_staff['rate']) ? $extra_staff['rate'] : ''; ?>" />
											<input type="hidden" name="old_staff_id[]" value="<?php echo isset($extra_staff['staff_id']) ? $extra_staff['staff_id'] : ''; ?>" />
										</center>
									</td>
									<td>
										<center>
											<input type="hidden" name="salon_id[]" value="<?php echo $selected_salon_id; ?>" />
											<?php
												if($salons) {
													foreach($salons as $salon) {
														if($salon['id'] == $selected_salon_id) {
															echo "<small>".ucwords(strtolower($salon['name']))."</small>";
														}
													}
												}
											?>
										</center>
									</td>
									<td><center><input type="text" name="in_time[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" value="<?php echo isset($extra_staff['in_time']) ? date('H:i',strtotime($extra_staff['in_time'])) : ''; ?>" /></center></td>
									<td><center><input type="text" name="out_time[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" value="<?php echo isset($extra_staff['out_time']) ? date('H:i',strtotime($extra_staff['out_time'])) : ''; ?>" /></center></td>
									<td><center><input type="text" name="break[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" value="<?php echo isset($extra_staff['break']) && $extra_staff['break'] != 0 ? $extra_staff['break'] : ''; ?>" /></center></td>
									<td>
										<center>
											<input type="hidden" name="hours[]" class="form-control" value="<?php echo isset($extra_staff['hours_diff']) && $extra_staff['hours_diff'] != 0 ? $extra_staff['hours_diff'] : ''; ?>" style="font-size: 12px !important;color: #000000 !important;" />
											<span><?php echo isset($extra_staff['hours_diff']) && $extra_staff['hours_diff'] != 0 ? calculateWorkingHours($in_time,$out_time,$break) : ''; ?></span>
										</center>
									</td>
									<td><center><input type="text" name="tip[]" class="form-control" style="color: #000000 !important;" value="<?php echo isset($extra_staff['tip']) && $extra_staff['tip'] != 0 ? $extra_staff['tip'] : ''; ?>" /></center></td>
								</tr>
					<?php
							}
						}
					?>
				</tbody>
			</table>
		</div>
		<br>
		<center><a class="btn btn-sm btn-primary text-white" onclick="add_more_staff()">Add More</a></center>
	</div>
</div>