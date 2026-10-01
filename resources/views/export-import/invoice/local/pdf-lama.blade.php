{{-- ===========================================================================
     CADANGAN tampilan LAMA dari local/pdf.blade.php (sebelum gaya CARING).
     Disimpan sebagai pembanding: buka PDF-nya dengan tambahan &gaya=lama di
     alamatnya. Setelah gaya baru disetujui, berkas ini boleh dihapus.
     =========================================================================== --}}
{{-- ===========================================================================
     PDF Invoice Local EXIM. PDF Invoice Export ada di ../export/pdf.blade.php.
     Disalin APA ADANYA dari application/views/arnag/reportinvoice3.php di AR
     (template yang dipakai tombol Print di List Invoice), supaya hasil
     cetaknya sama persis. Mesin PDF-nya juga sama: mPDF.
     Yang berbeda hanya sumber datanya - dari tabel booking EXIM, bukan
     tbl_invoice_detail. Nama variabel & kuncinya sengaja dipertahankan
     supaya template ini tidak perlu diubah.
     =========================================================================== --}}
<!DOCTYPE html>

<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=11">
	<title>Invoice</title>
	<style>
		/* @page {
			margin-top: 1.54cm;
			margin-bottom: 1.54cm;
			margin-left: 3.175cm;
			margin-right: 3.175cm;
		} */

		table {
			margin: auto;
		}

		td,
		th {
			padding: 1px;
			text-align: left
		}

		h1 {
			text-align: center
		}

		th {
			text-align: center;
			padding: 10px;
		}

		.footer {
			width: 100%;
			height: 30px;
			margin-top: 50px;
			text-align: right;
		}

		/*
CSS HEADER
*/
		.header {
			width: 100%;
			height: 20px;
			padding-top: 0;
			margin-bottom: 10px;
		}

		.title {
			font-size: 30px;
			font-weight: bold;
			text-align: center;
			margin-top: -90px;
		}


		.horizontal {
			height: 0;
			width: 100%;
			border: 3px solid #000000;
		}

		.position_top {
			vertical-align: top;
		}

		table {
			border-collapse: collapse;
			width: 100%;
		}

		.td1 {
			border: 1px solid black;
			border-top: none;
			border-bottom: none;
		}

		.header_title {
			width: 100%;
			height: auto;
			text-align: center;
			font-weight: bold;
			font-size: 12px;
		}
	</style>

</head>

<body>
	<div class="header">
		<table width="100%">
			<tr>
				<td>
					<img src="<?= $logo; ?>" width="15%">
				</td>
				<td class="title">
					PT.NIRWANA ALABARE GARMENT
					<div style="font-size:12px;line-height:9">
						Jl. Raya Rancaekek – Majalaya No. 289 Desa Solokan Jeruk Kecamatan Solokan Jeruk, <br />Kabupaten Bandung 40382 <br />Telp. 022-85962081
					</div>
				</td>
			</tr>
		</table>
		&nbsp;
		<div class="horizontal">

		</div>
	</div>

	<div class="header_title">
		<?= $data_invoice['type']; ?> INVOICE <br />
		<?= $data_invoice['no_invoice']; ?>
	</div>
	<br />
	<table style="width:100%;font-size:10px;">
		<tr>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top">

			</td>
			<td class="position_top">

			</td>
			<td colspan="3" class="position_top">

			</td>
		</tr>

		<!-- <tr>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" style="text-align:right">
				Date
			</td>
			<td class="position_top">
				:
			</td>
			<td colspan="3" class="position_top" style="text-align:right">
				<?= $data_invoice['tgl_inv']; ?>
			</td>
		</tr> -->

		<tr>
			<td class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td colspan="3" class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td class="position_top">

			</td>

			<td colspan="3" class="position_top">

			</td>
		</tr>

		<tr>
			<td>
				Date
			</td>
			<td>
				:
			</td>
			<td colspan="3">
				<?= $data_invoice['tgl_inv']; ?>
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
		</tr>

		<tr>
			<td>
				To
			</td>
			<td>
				:
			</td>
			<td colspan="3">
				<?= $data_invoice['customer']; ?>
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
		</tr>

		<tr>
			<td>
				Address
			</td>
			<td class="position_top">
				:
			</td>
			<td class="position_top" colspan="3">
				<?= $data_invoice['alamat']; ?>
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
			<td class="position_top" colspan="8">
				&nbsp;
			</td>
		</tr>

		<tr>
			<td>
				Telp.
			</td>
			<td>
				:
			</td>
			<td colspan="3">
				<?= $data_invoice['phone']; ?>
			</td>
			<td>
			</td>
			<td>
			</td>
			<td colspan="7">
			</td>
		</tr>


		<tr>
			<td>
				BPPB#
			</td>
			<td>
				:
			</td>

			<td>
				<?php foreach ($group_bppb_number as $bppb) : ?>
				<?= $bppb['shipp_number'] ?> ,
				<?php endforeach; ?>
			</td>
		</tr>

		<tr>
			<td>
				SALES ORDER#
			</td>
			<td>
				:
			</td>
			<td>
				<?php foreach ($group_so_number as $so) : ?>
					<?= $so['so_number']; ?> ,
				<?php endforeach; ?>
			</td>
		</tr>
	</table>

	<br />

	<table style="width:100%;font-size:10px;" border="1">
		<tr align="center">
			<th>
				Style
			</th>
			<th>
				Color
			</th>
			<th>
				Product Item
			</th>
			<th colspan="2" style="width: 100px">
				Quantity
			</th>
			<th>
				Unit Price
			</th>
			<th colspan="2" style="width: 100px">
				Total
			</th>
		</tr>

		<?php
		$my_tot_unit = 0;
		foreach ($data_invoice_detail as $inv_det) : ?>
			<?php $my_tot_unit += $inv_det["qty"]; ?>
			<?php $mata_uang = $inv_det['curr']; ?>
			<tr>
				<td><?= $inv_det['styleno']; ?></td>
				<td><?= $inv_det['color']; ?></td>
				<td><?= $inv_det['product_item']; ?> (<?= $inv_det['size']; ?>)</td>
				<td align='right'><?= $inv_det['qty']; ?></td>
				<td align='center'><?= $inv_det['uom']; ?></td>
				<td align='right' style="width: 100px"><?= $inv_det['curr']; ?>  <?= $inv_det['unit_price']; ?></td>
				<td align='center' style="width: 35px"><?= $inv_det['curr']; ?></td>
				<td align='right'><?= $inv_det['total_price']; ?></td>
			</tr>
		<?php endforeach; ?>

		<tr>
			<td colspan='3'></td>
			<td align='right'><?= $my_tot_unit; ?></td>
			<td colspan='4'></td>
		</tr>

		<tr>
			<td colspan='6'>Total</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['total']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Discount</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['discount']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Down Payment</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['dp']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Return</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['retur']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Total Before Value Added Tax</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['twot']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Value Added Tax</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['vat']; ?></td>
		</tr>

		<tr>
			<td colspan='6'>Grand Total</td>
			<td align='center'><?= $mata_uang ?></td>
			<td align='right'><?= $data_invoice_pot['grand_total']; ?></td>
		</tr>
	</table>

	<br />
	<br />

	<!-- <div style="margin-bottom: 2.54cm;"> -->
	<div style="margin-bottom: 0.5cm; page-break-inside: avoid;">
	<table style="page-break-inside: avoid;" cellpadding="0" cellspacing="0" border="0" width="100%">
		<tr>
			<td style="width:65%">&nbsp;</td>
			<td style="font-size:11px;text-align:center">Approved By</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="height:70px">&nbsp;</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="font-size:12px;text-align:center;border-bottom:1px solid #000000">Yus Yulius</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="font-size:12px;text-align:center">Exim Manager</td>
		</tr>
	</table>

	</div>