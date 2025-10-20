<?php
	if($staffs) {
		$currency = "";
		foreach($staffs as $staff) {
			$staff_total = 0;
?>
			<tr>
				<td><small><?php echo $staff['fname']." ".$staff['lname']; ?></small></td>
				<?php
					// if($staff["salons"]) {
					// 	$total = 0;
					// 	foreach($staff["salons"] as $salon) {
					// 		$total = $total + $salon['paid'];
					// 		echo '<td align="right" data-amount="'.$salon['paid'].'"><small>'.$salon['currency'].' '.$salon['paid'].'</small></td>';
					// 		$currency = $salon['currency'];
					// 		$staff_total = $staff_total + $total;
					// 	}
					// 	$formatted_total = number_format($total,2);
					// 	echo '<td align="right"><small><b>'.$currency.' '.$formatted_total.'</b></small></td>';
					// } 
				?>
			</tr>
<?php
		}
		if($salons) {
			$grand_total = 0;
			echo '<tr>';
			echo '<td align="right"><b>TOTAL</b></td>';
			foreach($salons as $key => $val) {
				$grand_total = $grand_total + $val['total'];
?>	
				<td align="right"><?php echo number_format($val['total'],2); ?></td>
<?php
			}
			echo '<td align="right"><b>'.number_format($grand_total,2).'</b></td>';
			echo '</tr>';
		}
	} 
?>