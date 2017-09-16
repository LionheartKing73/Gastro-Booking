<!DOCTYPE html>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1250">
    <title>Your Gastro-Booking</title>
</head>
<body bgcolor="#FFFFFF" lang=CSlink=#000080 vlink=#800080 text="#000000">
<p>
		ÚPRAVA <?= $order->cancellation  ?>  <?= $user->name ?> <?= $client_number ?> <?= $order->delivery_address; ?><br/>
		<?= $order->persons?> osob - <?= $orders_detail_count ?> položek - celkem <?= $orders_detail_total_price ?> <?= $order->currency ?> <br/>
		
		<?php foreach ($orders_detail_filtered as $orders_detail) {
			if ($orders_detail->side_dish == 0) {
        ?>
		
		<?=  $orders_detail->x_number ?>x <?= $orders_detail->menu_list->prefix ?> <?= $orders_detail->menu_list->name ?>  <?= $orders_detail->price ?> <?= $orders_detail->menu_list->currency ?> x <?= $orders_detail->x_number ?>,<br/>
		
		
		<?php  if (count($orders_detail->sideDish)) {
        foreach ($orders_detail->sideDish as $sideDish) {

        ?>
        &nbsp;&nbsp;<?= $sideDish->x_number ?>x <?= $sideDish->menu_list->prefix ?> <?php echo $sideDish->is_child ? 'Dětská porce: ' . $sideDish->menu_list->name : $sideDish->menu_list->name; ?> <?= $sideDish->price ?> <?= $sideDish->menu_list->currency?> x <?= $sideDish->x_number ?>,<br/>
        <?php } } ?>
		
		
		<?php
			}
        } ?>
		
		gastro-booking.com
	</p>
</body></html>
