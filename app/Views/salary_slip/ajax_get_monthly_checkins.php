<?php 
	if($staffs) {
		$total_rate = $total_hour_per_day = $row_total = $total_tip = $row_grand_total = 0;
		foreach($staffs as $staff) {
			$hour_per_day = 0;
			$total = $staff["rate"];
			$tip = 0;
			$grand_total = 0;
			if($staff['wage'] == 2 && isset($staff['hour_per_day'])) {
				$hour_per_day = $staff['hour_per_day'];
				$total = $staff['rate']*$hour_per_day;
			}
			if(isset($staff["tip"])) {
				$tip = $staff["tip"];
			}
			$grand_total = $total + $tip;
?>
			<tr>
				<td><small><?php echo ucwords(strtolower($staff['fname'].' '.$staff['lname'])); ?></small></td>
				<td><small><?php echo $staff['wage'] == 1 ? "Monthly" : "Weekly"; ?></small></td>
				<td align="right"><small><?php echo $currency." ".$staff['rate']; ?></small></td>
				<td align="right"><small><?php echo $hour_per_day; ?></small></td>
				<td align="right"><small><?php echo $currency." ".($total); ?></small></td>
				<td align="right"><small><?php echo $currency." ".$tip; ?></small></td>
				<td align="right"><small><?php echo $currency." ".$grand_total; ?></small></td>
			</tr>
<?php
			$total_rate = $total_rate + $staff["rate"];
			$total_hour_per_day = $total_hour_per_day + $hour_per_day;
			$row_total = $row_total + $total;
			$total_tip = $total_tip + $tip;
			$row_grand_total = $row_grand_total + $grand_total;
		}
?>
		<tr>
			<td colspan="2" align="right"><b>GRAND TOTAL</b></td>
			<td align="right"><small><b><?php echo $currency." ".$total_rate; ?></b></small></td>
			<td align="right"><small><b><?php echo $currency." ".$total_hour_per_day; ?></b></small></td>
			<td align="right"><small><b><?php echo $currency." ".$row_total; ?></b></small></td>
			<td align="right"><small><b><?php echo $currency." ".$total_tip; ?></b></small></td>
			<td align="right"><small><b><?php echo $currency." ".$row_grand_total; ?></b></small></td>
		</tr>
<?php
	}
?>