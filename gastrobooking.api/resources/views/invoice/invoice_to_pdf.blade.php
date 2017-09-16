<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Gastro_table</title>
    <style>
        *{
            font-family: DejaVu Sans; font-size: 15px;
        }
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }
        td, th {
            width: 50%;
            border: 1px solid #dddddd;
            text-align: left;
            padding:2px 2px 2px 5px;
        }
        th:nth-of-type(1){
            border-right: none;
        }
        th:nth-of-type(2){
            border-left: none;
            font-size: 30px;
            font-weight: normal;
        }
        th img{
            max-width: 188px;
        }
        .small_table > tr, .small_table > td{
            border: none;
        }
    </style>
</head>
<body>
<form action="">
    <table>
        <tr>
            <th><img src="http://gastro-booking.com/assets/images/logo.png" alt="logo"></th>
            <th>{{$invoiceSetting->heading}}</th>
            {{--http://localhost/gastrobooking/gastrobooking.api/public/uploads/images/logo.png--}}
        </tr>
        <tr>
            <td>
                <p style="font-size: 18px; margin-top: 0; font-weight: bold;">{{$invoiceSetting->supplier_label}}</p>
                <p style="line-height: 1.3em;">{!! $invoiceSetting->supplier_name !!}</p>
            </td>
            <td>
                <div style="margin-bottom:8px; overflow: hidden">
                    <label for="Taxable" style="font-style: italic;">{{$invoiceSetting->invoice_number}}</label>
                    {{--<p style="width:53%;font-style: italic;display: inline-block;margin:11px 0;">{{$invoiceSetting->invoice_number}}</p>--}}
                    <span style="margin-right:20px;float:right">{{$invoice->invoice_number}}</span>
                </div>
                <div style="margin-bottom:8px; overflow: hidden">
                    <label for="Taxable" style="font-style: italic;">{{$invoiceSetting->taxable_date}}</label>
                    <span style="margin-right:20px;float:right"> <?php echo date('d.m.Y', strtotime($invoice->invoice_taxable)) ?> </span>
                    {{--<input type="password" style="float:right;border-radius: 4px; border: 1px solid #ccc; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="Taxable" placeholder="invoice_taxable">--}}
                </div>
                <div style="margin-bottom:8px; overflow: hidden">
                    <label for="Data" style="font-style: italic;">{{$invoiceSetting->due_date}}</label>
                    <span style="margin-right:20px;float:right"><?php echo date('d.m.Y', strtotime($invoice->invoice_due)) ?> </span>
                    {{--<input type="password" style="float:right;border-radius: 4px; border: 1px solid #ccc; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143; " id="Data" placeholder="invoice_due">--}}
                </div>
                <div style="margin-bottom:8px; overflow: hidden">
                    <label for="Data" style="font-style: italic;">{{$invoiceSetting->issue_date}}</label>
                    <span style="margin-right:20px;float:right"><?php echo date('d.m.Y', strtotime($invoice->invoice_date)) ?></span>
                </div>
                <div style="margin-bottom:10px; overflow: hidden">
                    <label for="payment" style="font-style: italic;">{{$invoiceSetting->payment_form_label}}</label>
                    <span style="margin-right:20px;float:right"><?php
                        if($invoice->payment_form == '2'){
                            echo $invoiceSetting->payment_form_2;
                        }elseif($invoice->payment_form == '3'){
                            echo $invoiceSetting->payment_form_3;
                        }else{
                            echo $invoiceSetting->payment_form_1;
                        }
                        ?></span>
                    {{--<input type="password" style="float:right;border-radius: 4px; border: 1px solid #ccc; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="payment" placeholder="Bankaccounttransfer">--}}
                </div>

            </td>
        </tr>
        <tr>
            <td>
                <table>
                    <tr>
                        <td style="border: none;"><span style="font-size: 18px; font-weight: bold;margin-top: 0;">{{$invoiceSetting->bank_label}}</span></td>
                    </tr>
                    <tr>
                        <td style="border: none;"><span>{{$invoiceSetting->bank_name}}</span></td>
                    </tr>
                    <tr>
                        <td style="border: none;"><span style="font-size: 18px; font-weight: bold;">{{$invoiceSetting->ac_number_label}}</span></td>
                    </tr>
                    <tr>
                        <td style="border: none;"><span style="font-size: 18px;">{{$invoiceSetting->ac_number}}</span></td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td colspan="4" style="border: none;"><span style="font-size: 18px; font-weight: bold;margin-top: 0;">{{$invoiceSetting->recipient_label}}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border: none;"><span style="display:block;height: 24px">{{$invoice->restaurant->company_name}}</span></td>
                    </tr>
                    <tr>
                        <td colspan="4" style="border: none;"><span style="display:block;height: 24px">{{$invoice->restaurant->company_address}}</span></td>
                    </tr>
                    <tr>
                        <td colspan="2" align="right" style="border: none;">{{$invoiceSetting->CN_label}}&nbsp;{{$invoice->restaurant->company_number}}</td>
                        <td colspan="2" align="right" style="border: none;">{{$invoiceSetting->VATNo_label}}&nbsp;{{$invoice->restaurant->company_tax_number}}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <label style="display: block;font-size: 18px;margin:10px 0 10px 0; font-weight: bold;" >{{$invoiceSetting->subject_text_label}}</label>
                <span>{{$invoiceSetting->subject_text}}</span>
                {{--<p>services of the web site <a href="www.gastro-booking.com">www.gastro-booking.com</a></p>--}}
                <div style="margin:10px 0 10px 0;">
                    <label for="Price_" style="display:inline-block; width:40%">{{$invoiceSetting->price}}</label>
                    <span>{{number_format($invoice->invoice_value * 100 / (100 + $invoice->VAT), 2, '.', '')}}</span>
                    {{--<input type="password" style="border-radius: 4px; border: 1px solid #ccc;max-width: 360px; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="Price_" placeholder="">--}}
                    <span > {{$invoice->restaurant->currency}}</span>
                </div>
                <div style="margin-bottom:5px;">
                    <label for="Delivery_" style="display:inline-block; width:40%">{{$invoiceSetting->vat}}</label>
                    <span>{{$invoice->VAT}}</span>
                    {{--<input type="password" style="border-radius: 4px; border: 1px solid #ccc;max-width: 360px; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="Delivery_" placeholder="">--}}
                    <span >  %</span>
                </div>
                <div style="margin-bottom:5px;">
                    <label for="total_" style="display:inline-block; width:40%;font-weight: bold">{{$invoiceSetting->total}}</label>
                     <span style="font-weight: bold">{{$invoice->invoice_value}}</span>
                    {{--<input type="password" style="border-radius: 4px; border: 1px solid #ccc;max-width: 360px; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="total_" placeholder="invoic_value">--}}
                    <span style="font-weight: bold"> {{$invoice->restaurant->currency}}</span>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="margin:10px 0 15px 0;">
                    <label for="payment_" style="display:inline-block; width:40%; font-weight: bold;">{{$invoiceSetting->for_payment}}</label>
                    <span>{{$invoice->invoice_to_pay}}</span>
                    {{--<input type="password" style="border-radius: 4px; border: 1px solid #ccc;max-width: 360px; height: 18px; padding: 6px 12px; font-size: 14px;line-height: 1.42857143;" id="payment_" placeholder="">--}}
                    <span > {{$invoice->restaurant->currency}}</span>
                </div>
                <div style="margin:10px 0 10px 0;">
                    <label for="note" style="display:inline-block; width:40%; font-weight: bold;">{{$invoiceSetting->note}}</label>
                    <p>{{$invoice->note}}</p>
                </div>
                @if($invoice->signature_label == '1')
                    <div style="margin:20px 0 15px 0;">
                        <label style="font-weight: bold;" > {{$invoiceSetting->signature_label}}</label>
                    </div>
                @endif
                <div style="margin:10px 0 5px 0;">{{$invoiceSetting->issued_by}} <span style="margin:-5px 0 0 0;"> {{$logged_user->name}}</span></div>
            </td>
        </tr>
    </table>
    <p style="text-align: center ;font-size: 22px; font-weight: bold; font-style: italic;"> {{$invoiceSetting->thank}} </p>
    <div>
        <table>
            <tr>
                <td style="border: none; text-align:center">Tel: {{$logged_user->phone}}</td>
                <td style="border: none; text-align:center">Email: {{$logged_user->email}}</td>
            </tr>
        </table>
    </div>
</form>
</body>
</html>