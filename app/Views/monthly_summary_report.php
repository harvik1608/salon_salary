<?php 
	$total_income = $total_expense = 0;
	if($salons) {
		foreach($salons as $key => $val) {
			$total_income = $total_income + $val['income'];
			$total_expense = $total_expense + $val['expense'];
?>
			<tr>
				<td><?php echo $key+1; ?></td>
				<td><small><?php echo $val['name']; ?></small></td>
				<td align="right"><small><?php echo $val['currency'].' '.number_format($val['income'],2); ?></small></td>
				<td align="right"><small><?php echo $val['currency'].' '.number_format($val['expense'],2); ?></small></td>
			</tr>
<?php
		}
		$saving = $total_income - $total_expense;
?>
		<tr>
			<td colspan="2" align="right"><b>TOTAL</b></td>
			<td align="right"><b><?php echo number_format($total_income,2); ?></b></td>
			<td align="right"><b><?php echo number_format($total_expense,2); ?></b></td>
		</tr>
		<tr>
			<td colspan="5" align="center"><b><?php echo number_format($saving,2); ?></b></td>
		</tr>
<?php
	}
?>