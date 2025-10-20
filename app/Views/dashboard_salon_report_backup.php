<?php
	$currency = "";
	if($staffs) {
		foreach($staffs as $staff) {
			$staff_total = 0;
?>
			<tr>
				<td><small><?php echo $staff['fname']." ".$staff['lname']; ?></small></td>
				<?php
					if($staff["salons"]) {
						foreach($staff["salons"] as $salon) {
							$currency = $salon["currency"];
							$total_hours = number_format($salon['total_hours'],2);
							$staff_total = $staff_total + $salon['total_hours'];
				?>
							<td align="right"><?php echo $salon["currency"]." ".$total_hours; ?></td>
				<?php
						}
					} 
				?>
				<td align="right"><b><?php echo $salon["currency"]." ".number_format($staff_total,2); ?></b></td>
			</tr>
<?php
		}
	}
	if($salons) {
		$total_hours = 0;
		echo "<tr>";
		echo '<td align="right"><b>TOTAL</b></td>';
		foreach($salons as $parlour) {
			$total_hours = $total_hours + $parlour['total_hours'];
?>
			<td align="right"><b><?php echo $parlour["currency"]." ".number_format($parlour["total_hours"],2); ?></b></td>	
<?php
		}
		echo '<td align="right"><b>'.$currency.' '.number_format($total_hours,2).'</b></td>';
		echo "</tr>";
	} 
?>