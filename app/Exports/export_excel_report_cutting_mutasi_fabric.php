<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class export_excel_report_cutting_mutasi_fabric implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;
    protected $start_date, $end_date, $rowCount;

    public function __construct($start_date, $end_date)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
    }

    public function view(): View
    {

        $start_date = $this->start_date;
        $end_date = $this->end_date;

        $rawData = DB::select("WITH gk_out_sa as (
SELECT
id_roll,
id_item,
id_jo,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ) AS qty_out,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
ifnull(no_ws_aktual,no_ws) ws
from signalbit_erp.whs_bppb_h a
inner join signalbit_erp.whs_bppb_det b on a.no_bppb = b.no_bppb
where tgl_bppb >= '2026-01-01' and tgl_bppb < '$start_date' and tujuan = 'Production - Cutting' and b.status = 'Y'
group by id_roll, ws
),
gr_set_sa as (
select
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN b.qty_pakai * 0.9144
            ELSE b.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
a.act_costing_ws as ws
from form_cut_reject a
inner join form_cut_reject_barcode b on a.id = b.form_id
left join scanned_item s on b.barcode = s.id_roll
where b.created_at >= '2026-01-01 00:00:00' and b.created_at < '$start_date 00:00:00'
group by barcode, ws
),
gr_set_alokasi_sa as (
SELECT
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN a.qty_pakai * 0.9144
            ELSE a.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
ws,
min(sisa_kain) sisa_kain
from form_cut_alokasi_gr_panel_barcode a
left join scanned_item s on a.barcode = s.id_roll
where a.tgl_trans >= '2026-01-01' and a.tgl_trans < '$start_date'
group by barcode, ws
),
gk_retur_sa as (
SELECT
no_barcode,
id_item,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ) AS qty_retur,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
a.no_ws
from signalbit_erp.whs_lokasi_inmaterial a
inner join signalbit_erp.whs_inmaterial_fabric b on a.no_dok = b.no_dok
where b.tgl_dok >= '2026-01-01' and b.tgl_dok < '$start_date' and supplier = 'Production - Cutting' and a.status = 'Y'
group by no_barcode, a.no_ws
),
cutt_sa as (
SELECT
	id_roll,
	id_item,
	roll_status,
	ROUND(SUM(qty_in) - SUM(CASE WHEN roll_status != 'latest' THEN sisa_kain ELSE 0 END), 2) qty_in,
	ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
	MIN(sisa_kain) sisa_kain,
	SUM(short_roll) as short_roll,
	unit_roll,
	act_costing_ws
FROM (
	select
		COALESCE(b.qty) qty_in,
		a.waktu_mulai,
		a.waktu_selesai,
		b.id,
		DATE_FORMAT(b.created_at, '%M') bulan,
		DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
		b.no_form_cut_input,
		UPPER(meja.name) nama_meja,
		mrk.act_costing_ws,
		master_sb_ws.buyer,
		mrk.style,
		mrk.color,
		COALESCE(b.color_act, '-') color_act,
		mrk.panel,
		master_sb_ws.qty,
		cons_ws,
		cons_marker,
		a.cons_ampar,
		a.cons_act,
		(CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
		panjang_marker,
		unit_panjang_marker,
		comma_marker,
		unit_comma_marker,
		lebar_marker,
		unit_lebar_marker,
		a.p_act panjang_actual,
		a.unit_p_act unit_panjang_actual,
		a.comma_p_act comma_actual,
		a.unit_comma_p_act unit_comma_actual,
		a.l_act lebar_actual,
		a.unit_l_act unit_lebar_actual,
		COALESCE(b.id_roll, '-') id_roll,
		b.id_item,
		b.detail_item,
		COALESCE(b.roll_buyer, b.roll) roll,
		COALESCE(b.lot, '-') lot,
		COALESCE(b.group_roll, '-') group_roll,
		(
				CASE WHEN
						b.status != 'extension' AND b.status != 'extension complete'
				THEN
						(CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
				ELSE
						'Sambungan'
				END
		) status_roll,
		COALESCE(c.qty, b.qty) qty_awal,
		b.qty qty_roll,
		b.unit unit_roll,
		COALESCE(b.berat_amparan, '-') berat_amparan,
		b.est_amparan,
		b.lembar_gelaran,
		mrk.total_ratio,
		(mrk.total_ratio * b.lembar_gelaran) qty_cut,
		b.average_time,
		b.sisa_gelaran,
		b.sambungan,
		b.sambungan_roll,
		b.kepala_kain,
		b.sisa_tidak_bisa,
		b.reject,
		b.piping,
		ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
		ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
		ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
		b.status,
		a.operator,
		a.tipe_form_cut,
		b.created_at,
		b.updated_at,
		(CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_input a
		left join form_cut_input_detail b on a.id = b.form_cut_id
		left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
		LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at
		and d.created_at >= '2026-01-01 00:00:00' and d.created_at < '$start_date 00:00:00'
		LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '2026-01-01 00:00:00' and e.created_at < '$start_date 00:00:00'
		left join users meja on meja.id = a.no_meja
		left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
		left join scanned_item on scanned_item.id_roll = b.id_roll
	where
		(a.cancel = 'N'  OR a.cancel IS NULL)
		AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
		AND a.status = 'SELESAI PENGERJAAN'
		and b.status != 'not complete'
		and b.id_item is not null
		and b.created_at >= '2026-01-01 00:00:00' and b.created_at < '$start_date 00:00:00'
	group by
		b.id
	UNION ALL
	select
		COALESCE(form_cut_piping.qty) qty_in,
		form_cut_piping.created_at waktu_mulai,
		form_cut_piping.updated_at waktu_selesai,
		form_cut_piping.id,
		DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
		DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
		'PIPING' no_form_cut_input,
		'-' nama_meja,
		form_cut_piping.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piping.style,
		form_cut_piping.color,
		form_cut_piping.color color_act,
		form_cut_piping.panel,
		master_sb_ws.qty,
		'0' cons_ws,
		0 cons_marker,
		'0' cons_ampar,
		0 cons_act,
		form_cut_piping.cons_piping cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piping.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
		scanned_item.lot,
		'-' group_roll,
		'Piping' status_roll,
		COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
		form_cut_piping.qty qty_roll,
		form_cut_piping.unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		form_cut_piping.piping piping,
		form_cut_piping.qty_sisa sisa_kain,
		form_cut_piping.piping pemakaian_lembar,
		form_cut_piping.piping total_pemakaian_roll,
		ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
		ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
		null `status`,
		form_cut_piping.operator,
		'PIPING' tipe_form_cut,
		form_cut_piping.created_at,
		form_cut_piping.updated_at,
		(CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_piping
		LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '2026-01-01 00:00:00'
		and b.created_at < '$start_date 00:00:00'
		LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '2026-01-01 00:00:00'
        and c.created_at < '$start_date 00:00:00'
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
		left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
	where
		scanned_item.id_item is not null
		and form_cut_piping.created_at >= '2026-01-01 00:00:00' and form_cut_piping.created_at < '$start_date 00:00:00'
	group by
		form_cut_piping.id
	UNION ALL
	SELECT
		form_cut_piece_detail.qty qty_in,
		form_cut_piece.created_at waktu_mulai,
		form_cut_piece.updated_at waktu_selesai,
		form_cut_piece.id,
		DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
		DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
		form_cut_piece.no_form no_form_cut_input,
		'-' nama_meja,
		form_cut_piece.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piece.style,
		form_cut_piece.color,
		form_cut_piece.color color_act,
		form_cut_piece.panel,
		master_sb_ws.qty,
		form_cut_piece.cons_ws cons_ws,
		form_cut_piece.cons_ws cons_marker,
		'0' cons_ampar,
		0 cons_act,
		0 cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piece_detail.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
		scanned_item.lot,
		'-' group_roll,
		( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
		COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
		form_cut_piece_detail.qty qty_roll,
		form_cut_piece_detail.qty_unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		0 piping,
		form_cut_piece_detail.qty_sisa sisa_kain,
		form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
		form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
		ROUND(
		form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
		ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
		form_cut_piece_detail.STATUS `status`,
		form_cut_piece.employee_name,
		'PCS' tipe_form_cut,
		form_cut_piece.created_at,
		form_cut_piece.updated_at,
		(CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	FROM
		form_cut_piece
		LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
		LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
		LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
		LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
	WHERE
		scanned_item.id_item IS NOT NULL
		AND form_cut_piece_detail.STATUS = 'complete'
		and form_cut_piece_detail.created_at >= '2026-01-01 00:00:00' and form_cut_piece_detail.created_at < '$start_date 00:00:00'
	GROUP BY
		form_cut_piece_detail.id
) cutting
where
	cutting.id_roll is not null and cutting.id_roll != '-'
group by
	id_roll,
	act_costing_ws
order by
	created_at
),
-- Periode Transaksi
gk_out as (SELECT
id_roll,
id_item,
id_jo,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_out * 0.9144
            ELSE qty_out
        END
    ) AS qty_out,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
ifnull(no_ws_aktual,no_ws) ws
from signalbit_erp.whs_bppb_h a
inner join signalbit_erp.whs_bppb_det b on a.no_bppb = b.no_bppb
where tgl_bppb >= '$start_date' and tgl_bppb <= '$end_date' and tujuan = 'Production - Cutting' and b.status = 'Y'
group by id_roll, ws
),
gr_set as (
select
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN b.qty_pakai * 0.9144
            ELSE b.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
a.act_costing_ws as ws
from form_cut_reject a
inner join form_cut_reject_barcode b on a.id = b.form_id
left join scanned_item s on b.barcode = s.id_roll
where b.created_at >= '$start_date 00:00:00' and b.created_at <= '$end_date 23:59:59'
group by barcode, ws
),
gr_set_alokasi as (
SELECT
barcode,
id_item,
    SUM(
        CASE
            WHEN s.unit = 'YRD' THEN a.qty_pakai * 0.9144
            ELSE a.qty_pakai
        END
    ) AS qty_pakai,
CASE
		WHEN s.unit = 'YRD' THEN 'METER'
		WHEN s.unit = 'KGM' THEN 'KGM'
		ELSE s.unit
		END as satuan,
ws,
min(sisa_kain) sisa_kain
from form_cut_alokasi_gr_panel_barcode a
left join scanned_item s on a.barcode = s.id_roll
where a.tgl_trans >= '$start_date' and a.tgl_trans <= '$end_date'
group by barcode, ws
),
gk_retur as (
SELECT
no_barcode,
id_item,
    SUM(
        CASE
            WHEN satuan = 'YRD' THEN qty_aktual * 0.9144
            ELSE qty_aktual
        END
    ) AS qty_retur,
CASE
		WHEN satuan = 'YRD' THEN 'METER'
		WHEN satuan = 'KGM' THEN 'KGM'
		ELSE satuan
		END as satuan,
a.no_ws
from signalbit_erp.whs_lokasi_inmaterial a
inner join signalbit_erp.whs_inmaterial_fabric b on a.no_dok = b.no_dok
where b.tgl_dok >= '$start_date' and b.tgl_dok <= '$end_date' and supplier = 'Production - Cutting' and a.status = 'Y'
group by no_barcode, a.no_ws
),
cutt as  (
SELECT
	id_roll,
	id_item,
	roll_status,
	ROUND(SUM(qty_in) - SUM(CASE WHEN roll_status != 'latest' THEN sisa_kain ELSE 0 END), 2) qty_in,
	ROUND(SUM(total_pemakaian_roll), 2) total_pemakaian_roll,
	MIN(sisa_kain) sisa_kain,
	SUM(short_roll) as short_roll,
	unit_roll,
	act_costing_ws
FROM (
	select
		COALESCE(b.qty) qty_in,
		a.waktu_mulai,
		a.waktu_selesai,
		b.id,
		DATE_FORMAT(b.created_at, '%M') bulan,
		DATE_FORMAT(b.created_at, '%d-%m-%Y') tgl_input,
		b.no_form_cut_input,
		UPPER(meja.name) nama_meja,
		mrk.act_costing_ws,
		master_sb_ws.buyer,
		mrk.style,
		mrk.color,
		COALESCE(b.color_act, '-') color_act,
		mrk.panel,
		master_sb_ws.qty,
		cons_ws,
		cons_marker,
		a.cons_ampar,
		a.cons_act,
		(CASE WHEN a.cons_pipping > 0 THEN a.cons_pipping ELSE mrk.cons_piping END) cons_piping,
		panjang_marker,
		unit_panjang_marker,
		comma_marker,
		unit_comma_marker,
		lebar_marker,
		unit_lebar_marker,
		a.p_act panjang_actual,
		a.unit_p_act unit_panjang_actual,
		a.comma_p_act comma_actual,
		a.unit_comma_p_act unit_comma_actual,
		a.l_act lebar_actual,
		a.unit_l_act unit_lebar_actual,
		COALESCE(b.id_roll, '-') id_roll,
		b.id_item,
		b.detail_item,
		COALESCE(b.roll_buyer, b.roll) roll,
		COALESCE(b.lot, '-') lot,
		COALESCE(b.group_roll, '-') group_roll,
		(
				CASE WHEN
						b.status != 'extension' AND b.status != 'extension complete'
				THEN
						(CASE WHEN COALESCE(scanned_item.qty_in, b.qty) > b.qty AND c.id IS NULL THEN 'Sisa Kain' ELSE 'Roll Utuh' END)
				ELSE
						'Sambungan'
				END
		) status_roll,
		COALESCE(c.qty, b.qty) qty_awal,
		b.qty qty_roll,
		b.unit unit_roll,
		COALESCE(b.berat_amparan, '-') berat_amparan,
		b.est_amparan,
		b.lembar_gelaran,
		mrk.total_ratio,
		(mrk.total_ratio * b.lembar_gelaran) qty_cut,
		b.average_time,
		b.sisa_gelaran,
		b.sambungan,
		b.sambungan_roll,
		b.kepala_kain,
		b.sisa_tidak_bisa,
		b.reject,
		b.piping,
		ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2) sisa_kain,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran ) + (b.sambungan_roll ) , 2) pemakaian_lembar,
		ROUND((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping), 2) total_pemakaian_roll,
		ROUND(((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping))+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2))-b.qty, 2) short_roll,
		ROUND((((CASE WHEN b.status != 'extension complete' THEN ((CASE WHEN b.unit = 'KGM' THEN b.berat_amparan ELSE a.p_act + (a.comma_p_act/100) END) * b.lembar_gelaran) ELSE b.sambungan END) + (b.sisa_gelaran) + (b.sambungan_roll) + (b.kepala_kain) + (b.sisa_tidak_bisa) + (b.reject) + (b.piping)+(ROUND(MIN(CASE WHEN b.status != 'extension' AND b.status != 'extension complete' THEN (b.sisa_kain) ELSE (b.qty - b.total_pemakaian_roll) END), 2)))-b.qty)/b.qty*100, 2) short_roll_percentage,
		b.status,
		a.operator,
		a.tipe_form_cut,
		b.created_at,
		b.updated_at,
		(CASE WHEN d.id is null and e.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_input a
		left join form_cut_input_detail b on a.id = b.form_cut_id
		left join form_cut_input_detail c ON c.form_cut_id = b.form_cut_id and c.id_roll = b.id_roll and (c.status = 'extension' OR c.status = 'extension complete')
		LEFT JOIN form_cut_input_detail d on d.id_roll = b.id_roll AND b.id != d.id AND d.created_at > b.created_at and d.created_at >= '$start_date 00:00:00' and d.created_at <= '$end_date 23:59:59'
		LEFT JOIN form_cut_piping e on e.id_roll = b.id_roll AND e.created_at > b.created_at and e.created_at >= '$start_date 00:00:00' and e.created_at <= '$end_date 23:59:59'
		left join users meja on meja.id = a.no_meja
		left join (SELECT marker_input.*, SUM(marker_input_detail.ratio) total_ratio FROM marker_input LEFT JOIN marker_input_detail ON marker_input_detail.marker_id = marker_input.id GROUP BY marker_input.id) mrk on a.id_marker = mrk.kode
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = mrk.act_costing_id
		left join scanned_item on scanned_item.id_roll = b.id_roll
	where
		(a.cancel = 'N'  OR a.cancel IS NULL)
		AND (mrk.cancel = 'N'  OR mrk.cancel IS NULL)
		AND a.status = 'SELESAI PENGERJAAN'
		and b.status != 'not complete'
		and b.id_item is not null
		and b.created_at >= '$start_date 00:00:00' and b.created_at <= '$end_date 23:59:59'
	group by
		b.id
	UNION ALL
	select
		COALESCE(form_cut_piping.qty) qty_in,
		form_cut_piping.created_at waktu_mulai,
		form_cut_piping.updated_at waktu_selesai,
		form_cut_piping.id,
		DATE_FORMAT(form_cut_piping.created_at, '%M') bulan,
		DATE_FORMAT(form_cut_piping.created_at, '%d-%m-%Y') tgl_input,
		'PIPING' no_form_cut_input,
		'-' nama_meja,
		form_cut_piping.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piping.style,
		form_cut_piping.color,
		form_cut_piping.color color_act,
		form_cut_piping.panel,
		master_sb_ws.qty,
		'0' cons_ws,
		0 cons_marker,
		'0' cons_ampar,
		0 cons_act,
		form_cut_piping.cons_piping cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piping.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE(scanned_item.roll_buyer, scanned_item.roll) roll,
		scanned_item.lot,
		'-' group_roll,
		'Piping' status_roll,
		COALESCE(scanned_item.qty_in, form_cut_piping.qty) qty_awal,
		form_cut_piping.qty qty_roll,
		form_cut_piping.unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		form_cut_piping.piping piping,
		form_cut_piping.qty_sisa sisa_kain,
		form_cut_piping.piping pemakaian_lembar,
		form_cut_piping.piping total_pemakaian_roll,
		ROUND((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty, 2) short_roll,
		ROUND(((form_cut_piping.piping + form_cut_piping.qty_sisa) - form_cut_piping.qty)/coalesce(scanned_item.qty_in, form_cut_piping.qty) * 100, 2) short_roll_percentage,
		null `status`,
		form_cut_piping.operator,
		'PIPING' tipe_form_cut,
		form_cut_piping.created_at,
		form_cut_piping.updated_at,
		(CASE WHEN c.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	from
		form_cut_piping
		LEFT JOIN form_cut_input_detail b on b.id_roll = form_cut_piping.id_roll AND b.created_at > form_cut_piping.created_at and b.created_at >= '$start_date 00:00:00' and b.created_at < '$end_date 23:59:59'
		LEFT JOIN form_cut_piping c on c.id_roll = form_cut_piping.id_roll AND c.id != form_cut_piping.id and c.created_at > form_cut_piping.created_at and c.created_at >= '$start_date 00:00:00' and c.created_at < '$end_date 23:59:59'
		left join (SELECT * FROM master_sb_ws GROUP BY id_act_cost) master_sb_ws on master_sb_ws.id_act_cost = form_cut_piping.act_costing_id
		left join scanned_item on scanned_item.id_roll = form_cut_piping.id_roll
	where
		scanned_item.id_item is not null
		and form_cut_piping.created_at >= '$start_date 00:00:00' and form_cut_piping.created_at <= '$end_date 23:59:59'
	group by
		form_cut_piping.id
	UNION ALL
	SELECT
		form_cut_piece_detail.qty qty_in,
		form_cut_piece.created_at waktu_mulai,
		form_cut_piece.updated_at waktu_selesai,
		form_cut_piece.id,
		DATE_FORMAT( form_cut_piece.created_at, '%M' ) bulan,
		DATE_FORMAT( form_cut_piece.created_at, '%d-%m-%Y' ) tgl_input,
		form_cut_piece.no_form no_form_cut_input,
		'-' nama_meja,
		form_cut_piece.act_costing_ws,
		master_sb_ws.buyer,
		form_cut_piece.style,
		form_cut_piece.color,
		form_cut_piece.color color_act,
		form_cut_piece.panel,
		master_sb_ws.qty,
		form_cut_piece.cons_ws cons_ws,
		form_cut_piece.cons_ws cons_marker,
		'0' cons_ampar,
		0 cons_act,
		0 cons_piping,
		0 panjang_marker,
		'-' unit_panjang_marker,
		0 comma_marker,
		'-' unit_comma_marker,
		0 lebar_marker,
		'-' unit_lebar_marker,
		0 panjang_actual,
		'-' unit_panjang_actual,
		0 comma_actual,
		'-' unit_comma_actual,
		0 lebar_actual,
		'-' unit_lebar_actual,
		form_cut_piece_detail.id_roll,
		scanned_item.id_item,
		scanned_item.detail_item,
		COALESCE ( scanned_item.roll_buyer, scanned_item.roll ) roll,
		scanned_item.lot,
		'-' group_roll,
		( CASE WHEN form_cut_piece_detail.qty >= COALESCE ( scanned_item.qty_in, 0 ) THEN 'Roll Utuh' ELSE 'Sisa Kain' END ) status_roll,
		COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) qty_awal,
		form_cut_piece_detail.qty qty_roll,
		form_cut_piece_detail.qty_unit unit_roll,
		0 berat_amparan,
		0 est_amparan,
		0 lembar_gelaran,
		0 total_ratio,
		0 qty_cut,
		'00:00' average_time,
		'0' sisa_gelaran,
		0 sambungan,
		0 sambungan_roll,
		0 kepala_kain,
		0 sisa_tidak_bisa,
		0 reject,
		0 piping,
		form_cut_piece_detail.qty_sisa sisa_kain,
		form_cut_piece_detail.qty_pemakaian pemakaian_lembar,
		form_cut_piece_detail.qty_pemakaian total_pemakaian_roll,
		ROUND(
		form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa )) short_roll,
		ROUND((form_cut_piece_detail.qty - ( form_cut_piece_detail.qty_pemakaian + form_cut_piece_detail.qty_sisa ))/ COALESCE ( scanned_item.qty_in, form_cut_piece_detail.qty ) * 100, 2 ) short_roll_percentage,
		form_cut_piece_detail.STATUS `status`,
		form_cut_piece.employee_name,
		'PCS' tipe_form_cut,
		form_cut_piece.created_at,
		form_cut_piece.updated_at,
		(CASE WHEN b.id is null THEN 'latest' ELSE 'not latest' END) roll_status
	FROM
		form_cut_piece
		LEFT JOIN form_cut_piece_detail ON form_cut_piece_detail.form_id = form_cut_piece.id
		LEFT JOIN form_cut_piece_detail b on b.id_roll = form_cut_piece_detail.id_roll AND b.created_at > form_cut_piece_detail.created_at
		LEFT JOIN ( SELECT * FROM master_sb_ws GROUP BY id_act_cost ) master_sb_ws ON master_sb_ws.id_act_cost = form_cut_piece.act_costing_id
		LEFT JOIN scanned_item ON scanned_item.id_roll = form_cut_piece_detail.id_roll
	WHERE
		scanned_item.id_item IS NOT NULL
		AND form_cut_piece_detail.STATUS = 'complete'
		and form_cut_piece_detail.created_at >= '$start_date 00:00:00' and form_cut_piece_detail.created_at <= '$end_date 23:59:59'
	GROUP BY
		form_cut_piece_detail.id
) cutting
where
	cutting.id_roll is not null and cutting.id_roll != '-'
group by
	id_roll,
	act_costing_ws
order by
	created_at
),
saldo_awal as (
SELECT
barcode,
a.id_item,
MIN(NULLIF(sisa_kain, 0)) AS min_sisa_kain,
SUM(qty_pakai) + SUM(qty_reject_set) + SUM(qty_reject_panel) + COALESCE(MIN(NULLIF(sisa_kain, 0)), 0) + SUM(qty_retur) - SUM(qty_out) as short_roll,
ws,
satuan
FROM
		(
		SELECT id_roll as barcode,id_item, qty_out, 0 as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel, 0 as sisa_kain, satuan, ws FROM gk_out_sa
		UNION ALL
		SELECT id_roll as barcode,id_item, 0 qty_out, total_pemakaian_roll as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,sisa_kain, unit_roll as satuan, act_costing_ws as ws  FROM cutt_sa
		UNION ALL
		SELECT no_barcode as barcode,id_item, 0 qty_out, 0 as qty_pakai, qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, no_ws as ws  FROM gk_retur_sa
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, 0 qty_reject_set, qty_pakai as qty_reject_panel, sisa_kain, satuan, ws  FROM gr_set_alokasi_sa
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, qty_pakai as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, ws  FROM gr_set_sa
		) a
group by barcode, ws, satuan
),
mut as (
SELECT
barcode,
a.id_item,
MIN(NULLIF(sisa_kain, 0)) AS min_sisa_kain,
SUM(qty_pakai) + SUM(qty_reject_set) + SUM(qty_reject_panel) + COALESCE(MIN(NULLIF(sisa_kain, 0)), 0) + SUM(qty_retur) - SUM(qty_out) as short_roll,
SUM(qty_out) as qty_out,
SUM(qty_pakai) as qty_pakai,
SUM(qty_retur) qty_retur,
SUM(qty_reject_set) qty_reject_set,
SUM(qty_reject_panel) qty_reject_panel,
SUM(qty_out) - SUM(qty_pakai)  - SUM(qty_retur) - SUM(qty_reject_set) - SUM(qty_reject_panel) as qty_sakhir,
SUM(sisa_kain) as sisa_kain,
ws,
satuan
FROM
		(
		SELECT id_roll as barcode,id_item, qty_out, 0 as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel, 0 as sisa_kain, satuan, ws FROM gk_out
		UNION ALL
		SELECT id_roll as barcode,id_item, 0 qty_out, total_pemakaian_roll as qty_pakai, 0 as qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,sisa_kain, unit_roll as satuan, act_costing_ws as ws  FROM cutt
		UNION ALL
		SELECT no_barcode as barcode,id_item, 0 qty_out, 0 as qty_pakai, qty_retur, 0  as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, no_ws as ws  FROM gk_retur
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, 0 qty_reject_set, qty_pakai as qty_reject_panel, sisa_kain, satuan, ws  FROM gr_set_alokasi
		UNION ALL
		SELECT  barcode,id_item, 0 qty_out, 0 as qty_pakai, 0 as qty_retur, qty_pakai as qty_reject_set, 0 as qty_reject_panel,0 as sisa_kain, satuan, ws  FROM gr_set
		) a
group by barcode, ws, satuan
)


SELECT
mi.id_item,
mi.itemdesc,
buyer,
styleno,
mi.color,
ROUND(SUM(qty_sawal),2) as qty_sawal,
ROUND(SUM(qty_out),2) as qty_out,
ROUND(SUM(qty_pakai),2) as qty_pakai,
ROUND(SUM(qty_retur),2) as qty_retur,
ROUND(SUM(qty_reject_set),2) as qty_reject_set,
ROUND(SUM(qty_reject_panel),2) as qty_reject_panel,
ROUND(SUM(short_roll_sawal) + SUM(short_roll),2) as short_roll,
ROUND(SUM(qty_sawal) + SUM(sisa_kain),2) as saldo_akhir,
ws,
satuan
FROM
(
select id_item, SUM(short_roll) as short_roll_sawal , SUM(min_sisa_kain) as qty_sawal, 0 as qty_out, 0 as qty_pakai, 0 as qty_retur, 0 as qty_reject_set, 0 as qty_reject_panel, 0 as short_roll, 0 as sisa_kain, ws, satuan
from saldo_awal
GROUP BY id_item, ws, satuan
UNION ALL
select id_item, 0 as short_roll_sawal , 0 as qty_sawal, SUM(qty_out) as qty_out, SUM(qty_pakai) as qty_pakai, SUM(qty_retur) as qty_retur, SUM(qty_reject_set) as qty_reject_set, SUM(qty_reject_panel) as qty_reject_panel, SUM(short_roll) as short_roll, SUM(sisa_kain) as sisa_kain, ws, satuan
from mut
GROUP BY id_item, ws, satuan
) a
inner join signalbit_erp.masteritem mi on a.id_item = mi.id_item
LEFT JOIN (SELECT
						jd.id_jo,
						ac.kpno,
            supplier as buyer,
            styleno
				FROM signalbit_erp.jo_det jd
				INNER JOIN signalbit_erp.so ON jd.id_so = so.id
				INNER JOIN signalbit_erp.act_costing ac ON so.id_cost = ac.id
                INNER JOIN signalbit_erp.mastersupplier ms ON ac.id_buyer = ms.id_supplier
				WHERE jd.cancel = 'N'
				GROUP BY jd.id_jo) k on a.ws = k.kpno
GROUP BY id_item, ws, satuan


    ");


        $this->rowCount = count($rawData) + 1; // 1 for header

        return view('cutting.report.export.export_excel_report_mutasi_fabric', [

            'rawData' => $rawData,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn(); // e.g. 'Z'
                $columnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

                // ===== 1. Format header rows (row 2 and 3) =====
                for ($i = 1; $i <= $columnIndex; $i++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);

                    foreach ([2] as $row) {
                        $cell = $colLetter . $row;

                        $sheet->getStyle($cell)->applyFromArray([
                            'alignment' => [
                                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                            ],
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['argb' => 'FFD9EDF7'], // Light blue
                            ],
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FF000000'], // Black text
                            ],
                        ]);
                    }
                }
                // ===== 3. Apply border to whole table =====
                $range = 'A1:' . $highestColumn . $highestRow;
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            }
        ];
    }
}
