<!DOCTYPE html>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html; charset=windows-1250">
    <title>Your Gastro-Booking</title>
</head>
<body bgcolor="#FFFFFF" lang=CSlink=#000080 vlink=#800080 text="#000000">
<div>
    <TABLE width=800 BORDER=3 CELLPADDING=4 CELLSPACING=0>
        <COL width= 150px>
        <TR><TD>
                <IMG SRC="http://www.gastro-booking.com/assets/images/logomini.png">
            </TD><TD>
                <?php echo ($order->pick_up === 'Y') ? "VYZVEDNUTÍ " : (($order->delivery_address && $order->delivery_phone) ? "DOVOZ " : "REZERVACE "); ?>
                NA&nbsp;&nbsp;&nbsp; <?= $order->cancellation  ?> &nbsp;&nbsp;&nbsp;ČÍSLO: <?= $order->order_number ?><BR>
                <?php echo ($order->delivery_address && $order->delivery_phone) ? $order->delivery_address . ", GPS: latitude - " . $order->delivery_latitude . ", longitude - " . $order->delivery_longitude
                        : $restaurant->name; ?><BR>
            </TD></TR>
        <TR><TD>
                Objednatel
            </TD><TD>
                <?= $user->name ?> (objednávka ze dne <?= $order->created_at->format('d.m.Y H:i') ?>)
                <?php echo ($order->delivery_address && $order->delivery_phone) ? ", tel.: ". $order->delivery_phone :
                           ($user->client->phone ? ", tel.: ". $user->client->phone : "" ); ?>
            </TD></TR>
        <TR><TD>
                Počty a cena
            </TD><TD>
                <?= $order->persons?> osob - <?= $orders_detail_count ?> položek - celkem <?= $orders_detail_total_price ?> <?= $order->currency ?>
            </TD></TR>
        <TR><TD colspan=2>
                Poznámka: <?= $order->comment ?>
            </TD></TR>
    </TABLE><BR>

    <TABLE width=800 BORDER=3 CELLPADDING=2 CELLSPACING=0>
        <COL width= 0px>
        <COL width= 0px>
        <COL width= 60px>
        <COL width= 500px>
        <COL width= 100px>
        <COL width= 100px style = "text-align: right">

        <?php foreach ($order->orders_detail as $orders_detail) {
        if ($orders_detail->status != 3 && !$orders_detail->side_dish) { ?>

        <TR><TD>
                <?= \DateTime::createFromFormat('Y-m-d H:i:s', $orders_detail->serve_at)->format('H:i') ?>
            </TD><TD style="text-align: center">
                <?= $orders_detail->x_number ?>x
            </TD><TD>
                <?= $orders_detail->menu_list->prefix ?>
            </TD><TD>
                <?php echo $orders_detail->is_child ? 'Dětská porce: ' . $orders_detail->menu_list->name : $orders_detail->menu_list->name; ?>
            </TD><TD>
                <?= $orders_detail->client->user->name ?>
            </TD><TD>
                <?= $orders_detail->price ?>
                <?= $orders_detail->menu_list->currency ?>&nbsp;x<?= $orders_detail->x_number ?>
            </TD></TR>
        <?php if ($orders_detail->comment) { ?>
        <TR><TD colspan=6 >
                <?= $orders_detail->comment ?>
            </TD></TR>
        <?php } ?>

        <?php  if (count($orders_detail->sideDish)) {
        foreach ($orders_detail->sideDish as $sideDish) {
        if ($sideDish->status != 3) {
        ?>
        <TR><TD colspan="2" style="text-align: right">
                <?= $sideDish->x_number ?>x
            </TD><TD>
                <?= $sideDish->menu_list->prefix ?>
            </TD><TD>
                <?php echo $sideDish->is_child ? 'Dětská porce: ' . $sideDish->menu_list->name : $sideDish->menu_list->name; ?>
            </TD><TD>
                <?= $sideDish->client->user->name ?>
            </TD><TD>
                <?= $sideDish->price ?>
                <?= $sideDish->menu_list->currency?>&nbsp;x<?= $sideDish->x_number ?>
            </TD></TR>
        <?php } } ?>
        <?php
        }
        } } ?>
        <TR><TD colspan=5 style = "text-align: right">
                Celkem
            </TD><TD>
                <?= $orders_detail_total_price ?> <?= $order->currency ?>
            </TD></TR>
    </TABLE>

    <?php
    if (array_filter( $order->orders_detail->all(), function ($orders_detail) {
        return $orders_detail->status == 3  && (($orders_detail->side_dish && $orders_detail->mainDish->status != 3) || (!$orders_detail->side_dish));
    })) {
    ?>

    <FONT SIZE="+0"><B>Zrušeno:</B><BR></FONT>

    <TABLE style = "text-decoration: line-through" width=800 BORDER=3 CELLPADDING=2 CELLSPACING=0>
        <COL width= 0px>
        <COL width= 0px>
        <COL width= 60px>
        <COL width= 500px>
        <COL width= 100px>
        <COL width= 100px style = "text-align: right">

        <?php foreach ($order->orders_detail as $orders_detail) {
        if ( $orders_detail->status == 3  && (($orders_detail->side_dish && $orders_detail->mainDish->status != 3) || (!$orders_detail->side_dish))){
        ?>
        <TR><TD>
                <?= \DateTime::createFromFormat('Y-m-d H:i:s', $orders_detail->serve_at)->format('H:i') ?>
            </TD><TD style="text-align: center">
                <?=  $orders_detail->x_number ?>x
            </TD><TD>
                <?= $orders_detail->menu_list->prefix ?>
            </TD><TD>
                <?php echo $orders_detail->is_child ? 'Dětská porce: ' . $orders_detail->menu_list->name : $orders_detail->menu_list->name; ?>
            </TD><TD>
                <?= $orders_detail->client->user->name ?>
            </TD><TD>
                <?= $orders_detail->price ?>
                <?= $orders_detail->menu_list->currency ?>&nbsp;x<?= $orders_detail->x_number ?>
            </TD></TR>
        <?php if ($orders_detail->comment) { ?>
        <TR><TD colspan=6 >
                <?= $orders_detail->comment ?>
            </TD></TR>
        <?php } ?>

        <?php  if (count($orders_detail->sideDish)) {
        foreach ($orders_detail->sideDish as $sideDish) {

        ?>
        <TR><TD colspan="2" style="text-align: right">
                <?= $sideDish->x_number ?>x
            </TD><TD>
                <?= $sideDish->menu_list->prefix ?>
            </TD><TD>
                <?php echo $sideDish->is_child ? 'Dětská porce: ' . $sideDish->menu_list->name : $sideDish->menu_list->name; ?>
            </TD><TD>
                <?= $sideDish->client->user->name ?>
            </TD><TD>
                <?= $sideDish->price ?>
                <?= $sideDish->menu_list->currency?>&nbsp;x<?= $sideDish->x_number ?>
            </TD></TR>
        <?php } } ?>
        <?php
        }
        }?>
        <TR>
    </TABLE>
    <?php } ?>
    <BR>
    Přejeme Vám dobrou chuť!<BR>

    <A HREF="http://www.gastro-booking.com/">www.gastro-booking.com</A>
</div>
</body></html>
