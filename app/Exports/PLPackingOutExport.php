<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use DB;

class PLPackingOutExport implements FromView
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function title(): string
    {
        $row = DB::connection('mysql_sb')->selectOne(
            "SELECT no_bppb FROM packing_out_h WHERE id = ?",
            [$this->id]
        );

        return substr(
            str_replace(['\\','/',':','*','?','[',']'], '-', $row->no_bppb ?? 'PL Packing Out'),
            0,
            31
        );
    }

    public function view(): View
    {
        // ======================================
        // 1. Ambil list size unik
        // ======================================
        $sizes = DB::connection('mysql_sb')->select("
            SELECT DISTINCT ms.size
            FROM packing_out_det b
            JOIN packing_out_h a ON a.no_bppb = b.no_bppb
            JOIN master_size_new ms ON ms.size = b.size
            WHERE a.id = ? 
              AND b.status = 'Y'
            ORDER BY ms.urutan
        ", [$this->id]);

        $sizeCols = array_map(fn($r) => $r->size, $sizes);

        // ======================================
        // 2. Pivot dinamis
        // ======================================
        $cases = [];
        foreach ($sizeCols as $s) {
            $safe = str_replace("'", "''", $s);
            $cases[] = "SUM(CASE WHEN b.size = '{$safe}' THEN b.qty ELSE 0 END) AS `{$safe}`";
        }

        $sql = "
            SELECT 
                d.buyer,
                d.kpno,
                d.styleno,
                mi.itemdesc,
                b.color,
                " . implode(",", $cases) . ",
                SUM(b.qty) AS total_qty,
                round(SUM(b.qty) * a.berat_garment,2) AS total_nw,
                round(SUM(b.qty) * a.berat_karton,2) AS total_gw
            FROM packing_out_h a
            JOIN packing_out_det b ON b.no_bppb = a.no_bppb
            JOIN masteritem mi ON mi.id_item = b.id_item
            INNER JOIN (
                SELECT id_jo,kpno,styleno, supplier buyer 
                FROM act_costing ac 
                INNER JOIN so ON ac.id=so.id_cost 
                INNER JOIN jo_det jod ON so.id=jod.id_so 
                INNER JOIN mastersupplier mb ON mb.id_supplier = ac.id_buyer 
                GROUP BY id_jo
            ) d ON d.id_jo=b.id_jo
            WHERE a.id = ? 
              AND b.status='Y'
            GROUP BY d.buyer,d.kpno,d.styleno,mi.itemdesc,b.color
            ORDER BY d.buyer,d.kpno,b.color
        ";

        $data = DB::connection('mysql_sb')->select($sql, [$this->id]);
        $rows = array_map(fn($r)=> (array)$r, $data);

        $header = DB::connection('mysql_sb')->selectOne("SELECT a.no_bppb, a.berat_garment, a.berat_karton, ms.supplier, mi.itemdesc FROM packing_out_h a INNER JOIN mastersupplier ms ON ms.id_supplier = a.id_supplier INNER JOIN packing_out_det b on b.no_bppb = a.no_bppb INNER JOIN masteritem mi on mi.id_item = b.id_item
    WHERE a.id = ?
", [$this->id]);

        return view('packing-subcont.pl-packing-out', [
            'sizes'   => $sizeCols,
            'rows'    => $rows,
            'header'    => $header,
        ]);
    }
}
