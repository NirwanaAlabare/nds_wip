<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use App\Models\SewingOutH;
use App\Models\SewingOutDetTemp;
use App\Models\BppbSB;
use App\Models\Tempbpb;
use DB;
use PDF;

class SewingOutController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::connection('mysql_sb')->select("
                SELECT a.id, a.no_bppb, a.tgl_bppb, a.no_po,
                       c.supplier, buyer, a.jenis_pengeluaran, a.jenis_dok,
                       CONCAT(a.created_by,' (',a.created_at,')') created_by, a.status
                FROM sewing_out_h a
                INNER JOIN sewing_out_det b ON b.no_bppb = a.no_bppb
                INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
                LEFT JOIN (
                    SELECT id_jo, kpno, styleno, supplier buyer
                    FROM act_costing ac
                    INNER JOIN so ON ac.id = so.id_cost
                    INNER JOIN jo_det jod ON so.id = jod.id_so
                    INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer
                    GROUP BY id_jo
                ) d ON d.id_jo = b.id_jo
                WHERE a.tgl_bppb BETWEEN '" . $request->tgl_awal . "' AND '" . $request->tgl_akhir . "'
                GROUP BY a.no_bppb
            ");
            return DataTables::of($data)->toJson();
        }

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('Supplier', 'like', '%Production -%')->get();
        $no_po = DB::connection('mysql_sb')->select("select DISTINCT no_po pono from packing_in_h");

        return view('sewing-out.sewing-out', [
            'msupplier'    => $msupplier,
            'no_po'        => $no_po,
            'page'         => 'dashboard-sewing-eff',
            'subPageGroup' => 'sewing-out',
            'subPage'      => 'sewing-out',
        ]);
    }

    public function create()
    {
        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('Supplier', 'like', '%Production -%')->get();
        $no_po     = DB::connection('mysql_sb')->select("select DISTINCT no_po pono from packing_in_h");
        $kode_gr   = DB::connection('mysql_sb')->select("
            SELECT CONCAT('SEW-OUT-', DATE_FORMAT(CURRENT_DATE(), '%Y')) Mattype,
                   IF(MAX(no_bppb) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0)) nomor,
                   CONCAT('SEW/OUT/',DATE_FORMAT(CURRENT_DATE(), '%m'),DATE_FORMAT(CURRENT_DATE(), '%y'),'/',
                   IF(MAX(RIGHT(no_bppb,5)) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0))) no_bppb
            FROM sewing_out_h
            WHERE MONTH(tgl_bppb) = MONTH(CURRENT_DATE()) AND YEAR(tgl_bppb) = YEAR(CURRENT_DATE())
            AND LEFT(no_bppb,3) = 'SEW'
        ");

        DB::connection('mysql_sb')->delete("DELETE FROM sewing_out_det_temp WHERE created_by = ?", [Auth::user()->name]);

        return view('sewing-out.create-sewing-out', [
            'kode_gr'      => $kode_gr,
            'msupplier'    => $msupplier,
            'no_po'        => $no_po,
            'page'         => 'dashboard-sewing-eff',
            'subPageGroup' => 'sewing-out',
            'subPage'      => 'sewing-out',
        ]);
    }

    public function getDetailList(Request $request)
    {
        $user = Auth::user()->name;

        $data = DB::connection('mysql_sb')->select("
            WITH detail_po AS (
                select a.no_po pono, kpno, styleno, jo_no, b.id_jo, b.id_item, mi.itemdesc, b.unit, sum(b.qty) qty, id_po, id_buyer, buyer from packing_in_h a INNER JOIN packing_in_det b on b.no_bpb = a.no_bpb INNER JOIN (select id_jo, jo_no,kpno,styleno, id_buyer, supplier buyer from act_costing ac inner join so on ac.id=so.id_cost inner join jo_det jod on so.id=jod.id_so inner join jo on jo.id=jod.id_jo inner join mastersupplier mb on mb.id_supplier=ac.id_buyer group by id_jo) jo on jo.id_jo = b.id_jo INNER JOIN masteritem mi on mi.id_item = b.id_item where no_po = '" . $request->pono . "' and a.status != 'CANCEL' and b.status = 'Y' GROUP BY b.id_jo, b.id_item
            ),
            detail_input AS (
                SELECT id_po, id_jo, id_item, SUM(qty) qty_input
                FROM sewing_out_det_temp
                WHERE created_by = '" . $user . "'
                GROUP BY id_po, id_jo, id_item
            ),
            detail_out AS (
                SELECT id_po, id_jo, id_item, SUM(qty) qty_out
                FROM sewing_out_det WHERE status = 'Y'
                GROUP BY id_po, id_jo, id_item
            )
            SELECT a.*,
                   COALESCE(c.qty_out, 0) qty_out,
                   COALESCE(b.qty_input, 0) qty_input,
                   (a.qty - COALESCE(b.qty_input, 0) - COALESCE(c.qty_out, 0)) qty_balance
            FROM detail_po a
            LEFT JOIN detail_input b ON b.id_po = a.id_po AND b.id_jo = a.id_jo AND b.id_item = a.id_item
            LEFT JOIN detail_out c ON c.id_po = a.id_po AND c.id_jo = a.id_jo AND c.id_item = a.id_item
            ORDER BY a.kpno ASC
        ");

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => count($data),
            "recordsFiltered" => count($data),
            "data"            => $data,
        ]);
    }

    public function showdetailitem(Request $request)
    {
        $det_item = DB::connection('mysql_sb')->select("
            WITH detail_po AS (
                SELECT a.no_po pono, kpno, styleno, jo_no, b.id_jo, b.id_item, mi.itemdesc,
                       id_po, id_buyer, buyer, b.color, b.size, b.unit, SUM(b.qty) qty
                FROM packing_in_h a
                INNER JOIN packing_in_det b ON b.no_bpb = a.no_bpb
                INNER JOIN (
                    SELECT id_jo, jo_no, kpno, styleno, id_buyer, supplier buyer
                    FROM act_costing ac
                    INNER JOIN so ON ac.id = so.id_cost
                    INNER JOIN jo_det jod ON so.id = jod.id_so
                    INNER JOIN jo ON jo.id = jod.id_jo
                    INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer
                    GROUP BY id_jo
                ) jo ON jo.id_jo = b.id_jo
                INNER JOIN masteritem mi ON mi.id_item = b.id_item
                WHERE a.no_po = '" . $request->no_po . "'
                  AND a.status != 'CANCEL' AND b.status = 'Y'
                  AND b.id_po   = '" . $request->id_po . "'
                  AND b.id_jo   = '" . $request->id_jo . "'
                  AND b.id_item = '" . $request->id_item . "'
                GROUP BY b.id_jo, b.id_item, b.color, b.size
            ),
            detail_out AS (
                SELECT id_po, id_jo, id_item, color, size, SUM(qty) qty_out
                FROM sewing_out_det
                WHERE status = 'Y'
                  AND id_po   = '" . $request->id_po . "'
                  AND id_jo   = '" . $request->id_jo . "'
                  AND id_item = '" . $request->id_item . "'
                GROUP BY id_po, id_jo, id_item, color, size
            )
            SELECT a.*,
                   COALESCE(c.qty_out, 0) qty_out,
                   (a.qty - COALESCE(c.qty_out, 0)) qty_balance
            FROM detail_po a
            LEFT JOIN detail_out c ON c.id_po = a.id_po AND c.id_jo = a.id_jo
                AND c.id_item = a.id_item AND c.color = a.color AND c.size = a.size
            LEFT JOIN master_size_new i ON i.size = a.size
            ORDER BY a.itemdesc, a.color, i.urutan ASC
        ");

        $html  = '<div class="table-responsive">';
        $html .= '<table id="tableshow" class="table table-head-fixed table-bordered table-striped w-100 text-nowrap">';
        $html .= '<thead><tr>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:20%;">Style</th>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:20%;">Color</th>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:10%;">Size</th>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:13%;">Qty Stock</th>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:13%;">Qty Input</th>';
        $html .= '<th class="text-center" style="font-size:0.6rem;width:10%;">Unit</th>';
        $html .= '<th hidden></th><th hidden></th><th hidden></th>';
        $html .= '</tr></thead><tbody>';

        $x = 1;
        foreach ($det_item as $d) {
            $stock = max(0, (float) $d->qty_balance);
            $html .= '<tr>';
            $html .= '<td>' . $d->styleno . '<input type="hidden" id="det_style'   . $x . '" name="det_style['   . $x . ']" value="' . $d->styleno . '"></td>';
            $html .= '<td>' . $d->color   . '<input type="hidden" id="det_color'   . $x . '" name="det_color['   . $x . ']" value="' . $d->color   . '"></td>';
            $html .= '<td>' . $d->size    . '<input type="hidden" id="det_size'    . $x . '" name="det_size['    . $x . ']" value="' . $d->size    . '"></td>';
            $html .= '<td class="text-right">' . number_format($stock, 2) . '<input type="hidden" id="det_qty_stok' . $x . '" name="det_qty_stok[' . $x . ']" value="' . $stock . '"></td>';
            $html .= '<td><input style="width:90px;text-align:right;" class="form-control det-qty-input" type="text" inputmode="decimal" autocomplete="off" id="det_qty' . $x . '" name="det_qty[' . $x . ']" value="" data-stok="' . $stock . '" data-row="' . $x . '" oninput="validate_qty(this)"></td>';
            $html .= '<td>' . $d->unit    . '<input type="hidden" id="det_unit'    . $x . '" name="det_unit['    . $x . ']" value="' . $d->unit    . '"></td>';
            $html .= '<td hidden><input type="hidden" id="det_id_po'   . $x . '" name="det_id_po['   . $x . ']" value="' . $d->id_po   . '"></td>';
            $html .= '<td hidden><input type="hidden" id="det_id_jo'   . $x . '" name="det_id_jo['   . $x . ']" value="' . $d->id_jo   . '"></td>';
            $html .= '<td hidden><input type="hidden" id="det_id_item' . $x . '" name="det_id_item[' . $x . ']" value="' . $d->id_item . '"></td>';
            $html .= '</tr>';
            $x++;
        }

        $html .= '</tbody></table></div>';
        return $html;
    }

    public function SaveOutDetailTemp(Request $request)
    {
        $qtyDet   = $request->input('det_qty', []);
        $totalQty = floatval($request->mdl_qty_h);

        if ($totalQty <= 0) {
            return ["status" => 400, "message" => "Please input data"];
        }

        $timestamp = now();
        $rows = [];

        foreach ($qtyDet as $key => $qty) {
            $qty = floatval($qty ?? 0);
            if ($qty <= 0) continue;

            $rows[] = [
                "id_po"      => $request->det_id_po[$key]   ?? null,
                "id_jo"      => $request->det_id_jo[$key]   ?? null,
                "id_item"    => $request->det_id_item[$key] ?? null,
                "color"      => $request->det_color[$key]   ?? null,
                "size"       => $request->det_size[$key]    ?? null,
                "unit"       => $request->det_unit[$key]    ?? null,
                "qty"        => $qty,
                "status"     => 'Y',
                "created_by" => Auth::user()->name,
                "created_at" => $timestamp,
                "updated_at" => $timestamp,
            ];
        }

        if (empty($rows)) {
            return ["status" => 400, "message" => "Tidak ada qty yang diisi"];
        }

        DB::transaction(function () use ($rows) {
            SewingOutDetTemp::insert($rows);
        });

        return ["status" => 200, "message" => "Add data successfully", "redirect" => ''];
    }

    public function DeleteOutDetailTemp(Request $request)
    {
        SewingOutDetTemp::where('id_po', $request->id_po)
            ->where('id_jo', $request->id_jo)
            ->where('id_item', $request->id_item)
            ->where('created_by', Auth::user()->name)
            ->delete();
    }

    public function store(Request $request)
    {
        $request->validate([
            "txt_no_po" => "required",
            "txt_supp"  => "required",
        ]);

        $tglbppb = $request->txt_tgl_bppb;

        $num = DB::connection('mysql_sb')->select("
            SELECT CONCAT('SEW-OUT-', DATE_FORMAT('" . $tglbppb . "', '%Y')) Mattype,
                   IF(MAX(no_bppb) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0)) nomor,
                   CONCAT('SEW/OUT/',DATE_FORMAT('" . $tglbppb . "', '%m'),DATE_FORMAT('" . $tglbppb . "', '%y'),'/',
                   IF(MAX(RIGHT(no_bppb,5)) IS NULL,'00001',LPAD(MAX(RIGHT(no_bppb,5))+1,5,0))) no_bppb
            FROM sewing_out_h
            WHERE MONTH(tgl_bppb) = MONTH('" . $tglbppb . "') AND YEAR(tgl_bppb) = YEAR('" . $tglbppb . "')
            AND LEFT(no_bppb,3) = 'SEW'
        ");

        $m_type = $num[0]->Mattype;
        $no_type = $num[0]->nomor;
        $bppbno  = $num[0]->no_bppb;

        $cek = DB::connection('mysql_sb')->select("SELECT * FROM tempbpb WHERE Mattype = '" . $m_type . "'");
        if ($cek) {
            Tempbpb::where('Mattype', $m_type)->update(['BPBNo' => $no_type]);
        } else {
            Tempbpb::insert(["Mattype" => $m_type, "BPBNo" => $no_type]);
        }

        SewingOutH::create([
            'no_bppb'           => $bppbno,
            'tgl_bppb'          => $tglbppb,
            'no_po'             => $request->txt_no_po,
            'id_supplier'       => $request->txt_supp,
            'jenis_pengeluaran' => 'PRODUKSI',
            'jenis_dok'         => 'INHOUSE',
            'berat_garment'     => 0,
            'berat_karton'      => 0,
            'keterangan'        => $request->txt_notes,
            'status'            => 'DRAFT',
            'created_by'        => Auth::user()->name,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::connection('mysql_sb')->insert(
            "INSERT INTO sewing_out_det SELECT '', ?, id_po, id_jo, id_item, color, size, unit, qty, status, created_by, created_at, updated_at FROM sewing_out_det_temp WHERE created_by = ?",
            [$bppbno, Auth::user()->name]
        );

        SewingOutDetTemp::where('created_by', Auth::user()->name)->delete();

        return [
            "status"     => 200,
            "message"    => $bppbno . ' Saved Successfully',
            "additional" => [],
            "redirect"   => route('sewing-out'),
        ];
    }

    public function editSewingOut($id)
    {
        $header = DB::connection('mysql_sb')->selectOne("
            SELECT a.id, a.no_bppb, a.tgl_bppb, a.no_po, a.id_supplier,
                   c.supplier, a.keterangan, a.status
            FROM sewing_out_h a
            INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
            WHERE a.id = ?
        ", [$id]);

        if (!$header || $header->status !== 'DRAFT') {
            return redirect()->route('sewing-out')->with('error', 'Data tidak dapat diedit.');
        }

        // clear temp supaya Add Item bersih saat buka edit
        DB::connection('mysql_sb')->delete("DELETE FROM sewing_out_det_temp WHERE created_by = ?", [Auth::user()->name]);

        $msupplier = DB::connection('mysql_sb')->table('mastersupplier')->select('id_supplier', 'Supplier')->where('Supplier', 'like', '%Production -%')->get();
        $no_po     = DB::connection('mysql_sb')->select("SELECT DISTINCT no_po pono FROM packing_in_h");

        $detail = DB::connection('mysql_sb')->select("
            SELECT b.id, b.id_po, b.id_jo, b.id_item, b.color, b.size, b.qty, b.unit,
                   mi.itemdesc, kpno, styleno, jo.jo_no,
                   (COALESCE(pin.qty_in, 0) - COALESCE(other_out.qty_out, 0)) AS max_qty
            FROM sewing_out_det b
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            LEFT JOIN (
                SELECT id_jo, kpno, styleno FROM act_costing ac
                INNER JOIN so ON ac.id = so.id_cost
                INNER JOIN jo_det jod ON so.id = jod.id_so
                GROUP BY id_jo
            ) d ON d.id_jo = b.id_jo
            LEFT JOIN jo ON jo.id = b.id_jo
            LEFT JOIN (
                SELECT id_po, id_jo, id_item, color, size, SUM(qty) qty_in
                FROM packing_in_det WHERE status = 'Y'
                GROUP BY id_po, id_jo, id_item, color, size
            ) pin ON pin.id_po = b.id_po AND pin.id_jo = b.id_jo
                AND pin.id_item = b.id_item AND pin.color = b.color AND pin.size = b.size
            LEFT JOIN (
                SELECT id_po, id_jo, id_item, color, size, SUM(qty) qty_out
                FROM sewing_out_det
                WHERE status = 'Y' AND no_bppb != ?
                GROUP BY id_po, id_jo, id_item, color, size
            ) other_out ON other_out.id_po = b.id_po AND other_out.id_jo = b.id_jo
                AND other_out.id_item = b.id_item AND other_out.color = b.color AND other_out.size = b.size
            WHERE b.no_bppb = ? AND b.status = 'Y'
        ", [$header->no_bppb, $header->no_bppb]);

        return view('sewing-out.edit-sewing-out', [
            'header'       => $header,
            'detail'       => $detail,
            'msupplier'    => $msupplier,
            'no_po'        => $no_po,
            'page'         => 'dashboard-sewing-eff',
            'subPageGroup' => 'sewing-out',
            'subPage'      => 'sewing-out',
        ]);
    }

    public function updateSewingOut(Request $request, $id)
    {
        $request->validate([
            "txt_no_po" => "required",
            "txt_supp"  => "required",
        ]);

        $header = SewingOutH::where('id', $id)->first();

        if (!$header || $header->status !== 'DRAFT') {
            return ["status" => 400, "message" => "Data tidak dapat diedit."];
        }

        SewingOutH::where('id', $id)->update([
            'tgl_bppb'    => $request->txt_tgl_bppb,
            'no_po'       => $request->txt_no_po,
            'id_supplier' => $request->txt_supp,
            'keterangan'  => $request->txt_notes,
            'updated_at'  => now(),
        ]);

        // update qty item yang sudah ada
        if ($request->has('det_id') && is_array($request->det_id)) {
            foreach ($request->det_id as $i => $det_id) {
                $qty = floatval($request->det_qty[$i] ?? 0);
                DB::connection('mysql_sb')->update(
                    "UPDATE sewing_out_det SET qty = ? WHERE id = ?",
                    [$qty, $det_id]
                );
            }
        }

        // insert item baru dari temp
        DB::connection('mysql_sb')->insert(
            "INSERT INTO sewing_out_det SELECT '', ?, id_po, id_jo, id_item, color, size, unit, qty, status, created_by, created_at, updated_at FROM sewing_out_det_temp WHERE created_by = ?",
            [$header->no_bppb, Auth::user()->name]
        );
        SewingOutDetTemp::where('created_by', Auth::user()->name)->delete();

        return [
            "status"     => 200,
            "message"    => "Data berhasil diupdate",
            "additional" => [],
            "redirect"   => route('sewing-out'),
        ];
    }

    public function DeleteSewingOutDet($id)
    {
        DB::connection('mysql_sb')->update("UPDATE sewing_out_det SET status = 'N' WHERE id = ?", [$id]);
        return response()->json(["status" => 200, "message" => "Item dihapus"]);
    }

    public function DetailSewingOut($id)
    {
        $header = DB::connection('mysql_sb')->selectOne("
            SELECT a.id, a.no_bppb, a.tgl_bppb, a.no_po,
                   c.supplier, d.buyer, a.jenis_pengeluaran, a.jenis_dok, a.status
            FROM sewing_out_h a
            INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
            LEFT JOIN (
                SELECT id_jo, supplier buyer
                FROM act_costing ac INNER JOIN so ON ac.id = so.id_cost
                INNER JOIN jo_det jod ON so.id = jod.id_so
                INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer
                GROUP BY id_jo
            ) d ON d.id_jo = (SELECT id_jo FROM sewing_out_det WHERE no_bppb = a.no_bppb LIMIT 1)
            WHERE a.id = ?
        ", [$id]);

        $detail = DB::connection('mysql_sb')->select("
            SELECT b.id, kpno, styleno, mi.itemdesc, b.color, b.size, b.qty, b.unit
            FROM sewing_out_h a
            INNER JOIN sewing_out_det b ON b.no_bppb = a.no_bppb
            LEFT JOIN (
                SELECT id_jo, kpno, styleno
                FROM act_costing ac INNER JOIN so ON ac.id = so.id_cost
                INNER JOIN jo_det jod ON so.id = jod.id_so
                GROUP BY id_jo
            ) d ON d.id_jo = b.id_jo
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            WHERE a.id = ? AND b.status = 'Y'
            GROUP BY b.id
        ", [$id]);

        return response()->json(['header' => $header, 'detail' => $detail]);
    }

    public function PrintPdfSewingOut($id)
    {
        $header = DB::connection('mysql_sb')->selectOne("
            SELECT a.no_bppb, a.tgl_bppb, a.no_po, c.supplier tujuan,
                   kpno, styleno, a.berat_garment, a.berat_karton, a.keterangan, a.created_by
            FROM sewing_out_h a
            INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
            LEFT JOIN (
                SELECT id_jo, kpno, styleno
                FROM act_costing ac INNER JOIN so ON ac.id = so.id_cost
                INNER JOIN jo_det jod ON so.id = jod.id_so
                GROUP BY id_jo
            ) d ON d.id_jo = (SELECT id_jo FROM sewing_out_det WHERE no_bppb = a.no_bppb LIMIT 1)
            WHERE a.id = ?
        ", [$id]);

        $detail = DB::connection('mysql_sb')->select("
            SELECT mi.itemdesc, b.color, b.size, b.qty, b.unit
            FROM sewing_out_det b
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            WHERE b.no_bppb = ? AND b.status = 'Y'
            ORDER BY mi.itemdesc, b.color, b.size
        ", [$header->no_bppb]);

        $total = DB::connection('mysql_sb')->selectOne("
            SELECT round(SUM(qty),2) total_qty FROM sewing_out_det WHERE no_bppb = ? AND status = 'Y'
        ", [$header->no_bppb]);

        PDF::setOption(['dpi' => 150, 'defaultFont' => 'Helvetica-Bold']);
        $pdf = PDF::loadView('sewing-out.pdf.print-sewing-out', [
            'header' => $header,
            'detail' => $detail,
            'total'  => $total,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('SewingOut-' . $header->no_bppb . '.pdf');
    }

    public function ExportExcelSewingOut(Request $request)
    {
        $from = $request->from;
        $to   = $request->to;

        $data = DB::connection('mysql_sb')->select("
            SELECT a.no_bppb, a.tgl_bppb, a.no_po, c.supplier, d.buyer,
                   a.jenis_pengeluaran, a.jenis_dok, kpno, styleno,
                   b.id_jo, b.id_item, mi.itemdesc, b.color, b.size, b.qty, b.unit,
                   a.berat_garment, a.berat_karton, a.status,
                   COALESCE(a.keterangan,'-') keterangan,
                   CONCAT(a.created_by,' (',a.created_at,')') created_by
            FROM sewing_out_h a
            INNER JOIN sewing_out_det b ON b.no_bppb = a.no_bppb
            INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
            LEFT JOIN (
                SELECT id_jo, kpno, styleno, supplier buyer
                FROM act_costing ac INNER JOIN so ON ac.id = so.id_cost
                INNER JOIN jo_det jod ON so.id = jod.id_so
                INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer
                GROUP BY id_jo
            ) d ON d.id_jo = b.id_jo
            INNER JOIN masteritem mi ON mi.id_item = b.id_item
            WHERE a.tgl_bppb BETWEEN '" . $from . "' AND '" . $to . "' AND b.status = 'Y'
            GROUP BY b.id
        ");

        $rows = array_map(fn($r) => (array) $r, $data);

        $excel = FastExcel::create('SewingOut');
        $sheet = $excel->getSheet();

        $sheet->writeRow(['Laporan Pengeluaran Sewing Out'])->applyFontStyleBold();
        $sheet->writeRow(["Periode {$from} s/d {$to}"])->applyFontStyleBold();
        $sheet->writeRow([]);
        $sheet->mergeCells('A1:U1');

        $sheet->writeRow([
            'No BPB', 'Tgl BPB', 'No PO', 'Supplier', 'Buyer',
            'Jenis Pengeluaran', 'Jenis Dok', 'WS', 'Style',
            'ID JO', 'ID Item', 'Item Desc', 'Color', 'Size', 'Qty', 'Unit',
            'Berat Garment', 'Berat Karton', 'Status', 'Keterangan', 'Created User',
        ])->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->mergeCells('A2:U2');

        $maxLen = [];
        foreach ($rows as $r) {
            $rowData = [
                $r['no_bppb'] ?? '', $r['tgl_bppb'] ?? '', $r['no_po'] ?? '',
                $r['supplier'] ?? '', $r['buyer'] ?? '',
                $r['jenis_pengeluaran'] ?? '', $r['jenis_dok'] ?? '',
                $r['kpno'] ?? '', $r['styleno'] ?? '',
                $r['id_jo'] ?? '', $r['id_item'] ?? '', $r['itemdesc'] ?? '',
                $r['color'] ?? '', $r['size'] ?? '',
                round($r['qty'] ?? 0, 2), $r['unit'] ?? '',
                round($r['berat_garment'] ?? 0, 2), round($r['berat_karton'] ?? 0, 2),
                $r['status'] ?? '', $r['keterangan'] ?? '', $r['created_by'] ?? '',
            ];
            foreach ($rowData as $i => $v) {
                $len = strlen((string) $v);
                $maxLen[$i] = max($maxLen[$i] ?? 0, $len);
            }
            $sheet->writeRow($rowData)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        }

        foreach ($maxLen as $i => $len) {
            $sheet->setColWidth($i + 1, $len + 3);
        }

        return $excel->download("Laporan_Sewing_Out_{$from}_sd_{$to}.xlsx");
    }

    public function ReportSewingOut(Request $request)
    {
        if ($request->ajax()) {
            $data = DB::connection('mysql_sb')->select("
                SELECT a.id, a.no_bppb, a.tgl_bppb, a.no_po, c.supplier, d.buyer,
                       a.jenis_pengeluaran, a.jenis_dok, kpno, styleno,
                       b.id_jo, b.id_item, mi.itemdesc, b.color, b.size, b.qty, b.unit,
                       a.berat_garment, a.berat_karton, a.status,
                       COALESCE(a.keterangan,'-') keterangan,
                       CONCAT(a.created_by,' (',a.created_at,')') created_by
                FROM sewing_out_h a
                INNER JOIN sewing_out_det b ON b.no_bppb = a.no_bppb
                INNER JOIN mastersupplier c ON c.id_supplier = a.id_supplier
                LEFT JOIN (
                    SELECT id_jo, kpno, styleno, supplier buyer
                    FROM act_costing ac INNER JOIN so ON ac.id = so.id_cost
                    INNER JOIN jo_det jod ON so.id = jod.id_so
                    INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer
                    GROUP BY id_jo
                ) d ON d.id_jo = b.id_jo
                INNER JOIN masteritem mi ON mi.id_item = b.id_item
                WHERE a.tgl_bppb BETWEEN '" . $request->dateFrom . "' AND '" . $request->dateTo . "'
                AND b.status = 'Y'
                GROUP BY b.id
            ");
            return DataTables::of($data)->toJson();
        }

        return view('sewing-out.report-sewing-out', [
            'page'         => 'dashboard-sewing-eff',
            'subPageGroup' => 'sewing-report',
            'subPage'      => 'report-sewing-out',
        ]);
    }

    public function CancelSewingOut(Request $request)
    {
        $header = SewingOutH::where('no_bppb', $request->no_bppb)->first();

        if (!$header) {
            return response()->json(["status" => 400, "message" => "Data tidak ditemukan"]);
        }

        $header->update([
            'status'     => 'CANCELLED',
            'updated_at' => now(),
        ]);

        DB::connection('mysql_sb')->update(
            "UPDATE sewing_out_det SET status = 'N' WHERE no_bppb = ?",
            [$request->no_bppb]
        );

        return response()->json(["status" => 200, "message" => "Data berhasil dibatalkan"]);
    }
}
