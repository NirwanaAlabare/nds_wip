<!DOCTYPE html>
<html>
<head>
    <title>Barcode Material</title>
    <style>
        @page { margin: 0px; }

        body { margin: 0px; }

        /** {
            font-size: 13px;
        }

        img {
            width: 69px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table td, table th{
            text-align: left;
            vertical-align: middle;
            padding: 1px 1px;
            border: 0px solid;
            width: auto;
        }*/
    </style>
</head>
@foreach ($dataItem as $ditem)
<body>
    <table width="100%" style="border:none; font-size: 8px;font-weight:bold;margin-bottom:5px">
        <tr> <td>Product <td>:<td> <td>{{ $ditem->item_desc }}</td></tr>
        <tr> <td>Kode Barang <td>:<td> <td>{{ $ditem->kode_item }}</td></tr>
        <tr> <td>ID Item <td>:<td> <td>{{ $ditem->id_item }}</td></tr>         
        <tr> <td>Supplier <td>:<td> <td>{{ $ditem->supplier }}</td></tr>
        <tr> <td>No BPB <td>:<td> <td>{{ $ditem->no_dok }}</td></tr>
        <tr> <td>No PO <td>:<td> <td>{{ $ditem->no_po }}</td></tr>
        <tr> <td>No WS <td>:<td> <td>{{ $ditem->no_ws }}</td></tr>
        <tr> <td>Style <td>:<td> <td>{{ $ditem->styleno }}</td></tr>
    </table>         
    <table class="main" repeat_header="1" border="1" cellspacing="0" width="100%" 
                 style="border-collapse: collapse; width:100%; font-size: 9px;font-weight:bold">
           <thead>
              <tr class="head">
                        <td align="center">No Roll</td>
                        <td align="center">No Roll Buyer</td>
                        <td align="center">Lot</td>
                        <td align="center">Qty</td>
                        <td align="center">Nama Rak</td> 
                        <td align="center">Unit</td>
                        <td align="center">Grouping</td>                    
                    </tr>
                    </thead>

                   <tbody> 
                          <tr>
                            <td align="center">{{ $ditem->roll }}</td>
                            <td align="center">{{ $ditem->no_roll_buyer }}</td>
                                <td align="center">{{ $ditem->no_lot }}</td>
                                <td align="center">{{ $ditem->qty }}</td>
                                <td align="center">{{ $ditem->kode_lok }}</td>
                                <td align="center">{{ $ditem->satuan }}</td>
                                <td align="center"></td>
                                </tr>
                    </tbody>
            </table>
            <br>
            <div> 
                <!-- <p style="text-align: center;margin-right: 35%;margin-top: 0px;">1111</p> -->
          
          <table align="center" width="100%" border="" cellspacing="0" 
                 style="border-collapse: collapse; font-size: 9px;font-weight:bold">
            <tbody>
            <tr class="head"  width="35%">
                 <td align="center" style="border-top:0;border-left:0;border-bottom:0"></td>    
                 <td colspan="4" align="center">Relaxation</td>                  
            </tr>
            <tr>
            <td rowspan="3" align="center" style="border-top:0;border-left:0;border-bottom:0;"><div style="text-align: center;width: 100%;margin-left: 15%;margin-top: -8px;margin-bottom: 5px;" >{!!  DNS1D::getBarcodeHTML($ditem->id , 'c39',2,70,'black', false); !!}
                <p style="text-align:center;margin-left: -80px;font-size: 13px;margin-top: -0.1rem; margin-bottom: -0.5rem">{{ $ditem->id }}</p>
            </div></td>
                <td colspan="2" align="center">Start</td>
                <td colspan="2" align="center">Finish</td>
            </tr>
            <tr>
                <td align="center">Date</td>
                <td align="center">Time</td>
                <td align="center">Date</td>
                <td align="center">Time</td>                
            </tr>
            <tr>
                <td height="20px" align="center">&nbsp;</td>
                <td align="center"></td>
                <td align="center"></td>
                <td align="center"></td>                                              
            </tr>           
            </tbody>            
            </table>
 <div>   
</body>
@endforeach
</html>
