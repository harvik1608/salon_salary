<tr id="extra-staff-<?php echo $no; ?>">
	<td>
		<center>
			<select class="form-control select2" name="staff_id[]" style="color: #000000 !important;">
				<option value="" disabled selected>Please select</option>
				<?php
					if($staffs) {
						foreach($staffs as $staff) {
				?>
							<option value="<?php echo $staff['id']; ?>" data-salon="<?php echo $staff['salon_id']; ?>" data-rate="<?php echo $staff['rate']; ?>"><?php echo ucwords(strtolower($staff['fname'].' '.$staff['lname'])); ?></option>
				<?php
						}
					}
				?>
			</select>
			<input type="hidden" name="old_salon_id[]" />
			<input type="hidden" name="rate[]" />
			<input type="hidden" name="old_staff_id[]" value="0" />
		</center>
	</td>
	<td>
		<center>
			<input type="hidden" name="salon_id[]" value="<?php echo $salon_id; ?>" />
			<?php
				if($salons) {
					foreach($salons as $salon) {
						if($salon['id'] == $salon_id) {
							echo "<small>".ucwords(strtolower($salon['name']))."</small>";
						}
					}
				}
			?>
		</center>
	</td>
	<td><center><input type="text" name="in_time[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" /></center></td>
	<td><center><input type="text" name="out_time[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" /></center></td>
	<td><center><input type="text" name="break[]" class="form-control" style="color: #000000 !important;" onblur="calc_extra_staff_hours('<?php echo $no; ?>')" /></center></td>
	<td>
		<center>
			<input type="hidden" name="hours[]" class="form-control" style="font-size: 12px !important;color: #000000 !important;" />
			<span></span>
		</center>
	</td>
	<td><center><input type="text" name="tip[]" class="form-control" style="color: #000000 !important;" /></center></td>
</tr>