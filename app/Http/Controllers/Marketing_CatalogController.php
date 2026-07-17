<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Marketing_CatalogController extends Controller
{
    // public function index(Request $request)
    // {
    //     $page = 'dashboard-marketing';
    //     $subPageGroup = 'marketing-master';
    //     $subPage = 'marketing-master-catalog';

    //     $search = $request->input('search');

    //     $query = DB::connection('mysql_sb')
    //         ->table('so_det')
    //         ->select(
    //             'act_costing.styleno',
    //             'masteritem.itemdesc as itemname',
    //             'so_det.color as colors',
    //             DB::raw('GROUP_CONCAT(DISTINCT so_det.size SEPARATOR ", ") as sizes'),
    //             DB::raw('MIN(bom_jo_item.id_item) as id_item')
    //         )
    //         ->leftJoin('so', 'so_det.id_so', '=', 'so.id')
    //         ->leftJoin('act_costing', 'so.id_cost', '=', 'act_costing.id')
    //         ->leftJoin('bom_jo_item', 'so_det.id', '=', 'bom_jo_item.id_so_det')
    //         ->leftJoin('masteritem', 'bom_jo_item.id_item', '=', 'masteritem.id_item')
    //         ->groupBy('act_costing.styleno', 'itemname', 'so_det.color')
    //         ->first();

    //     if ($search) {
    //         $query->where(function($q) use ($search) {
    //             $q->where('itemname', 'like', "%{$search}%")
    //               ->orWhere('act_costing.styleno', 'like', "%{$search}%")
    //               ->orWhere('so_det.color', 'like', "%{$search}%");
    //         });
    //     }

    //     $styles = $query->groupBy('act_costing.styleno', 'itemname', 'so_det.color')->paginate(24);

    //     return view('marketing.catalog.index', [
    //         'page' => $page,
    //         'subPageGroup' => $subPageGroup,
    //         'subPage' => $subPage,
    //         'styles' => $styles,
    //         'containerFluid' => true,
    //     ]);
    // }

    public function index(Request $request)
    {
        $page = 'dashboard-marketing';
        $subPageGroup = 'marketing-master';
        $subPage = 'marketing-master-catalog';

        $search = $request->input('search');
        $perPage = 24;
        $currentPage = $request->input('page', 1);
        $offset = ($currentPage - 1) * $perPage;

        $searchSql = '';
        $searchBindings = [];
        if ($search) {
            $searchSql = "AND act_costing.styleno LIKE ?";
            $searchBindings = ["%{$search}%"];
        }

        $sql = "
            SELECT
                act_costing.styleno,
                GROUP_CONCAT(DISTINCT so_det.color ORDER BY so_det.color SEPARATOR ', ') AS colors,
                GROUP_CONCAT(DISTINCT so_det.size  ORDER BY so_det.size  SEPARATOR ', ') AS sizes,
                acn.foto AS image,
                so_det.dest AS destinations,
                ms.Supplier AS buyer_name
            FROM so
            INNER JOIN act_costing ON so.id_cost = act_costing.id
            INNER JOIN so_det ON so_det.id_so = so.id AND so_det.cancel = 'N'
            INNER JOIN mastersupplier ms ON act_costing.id_buyer = ms.Id_Supplier
            LEFT JOIN bom_marketing ON so.id_bom = bom_marketing.id
            LEFT JOIN act_costing_new acn ON bom_marketing.id_costing = acn.id
            WHERE act_costing.styleno IS NOT NULL
              AND act_costing.styleno > ''
              AND act_costing.styleno != '-'
              {$searchSql}
            GROUP BY act_costing.styleno, acn.foto, ms.Supplier
            ORDER BY act_costing.styleno ASC
            LIMIT {$perPage} OFFSET {$offset}
        ";



        $countSql = "
            SELECT COUNT(*) as total FROM (
                SELECT act_costing.styleno
                FROM so
                INNER JOIN act_costing ON so.id_cost = act_costing.id
                INNER JOIN so_det ON so_det.id_so = so.id AND so_det.cancel = 'N'
                INNER JOIN mastersupplier ms ON act_costing.id_buyer = ms.Id_Supplier
                LEFT JOIN bom_marketing ON so.id_bom = bom_marketing.id
                LEFT JOIN act_costing_new acn ON bom_marketing.id_costing = acn.id
                WHERE act_costing.styleno IS NOT NULL
                  AND act_costing.styleno > ''
                  AND act_costing.styleno != '-'
                  {$searchSql}
                GROUP BY act_costing.styleno, acn.foto, ms.Supplier
            ) sub
        ";

        $rows  = DB::connection('mysql_sb')->select($sql, $searchBindings);
        // dd($rows);

        $total = DB::connection('mysql_sb')->select($countSql, $searchBindings)[0]->total ?? 0;

        $styles = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('marketing.catalog.index', [
            'page'          => $page,
            'subPageGroup'  => $subPageGroup,
            'subPage'       => $subPage,
            'styles'        => $styles,
            'containerFluid'=> true,
        ]);
    }

    public function catalogDetail($styleno)
    {
        $page = 'dashboard-marketing';
        $subPageGroup = 'marketing-master';
        $subPage = 'marketing-master-catalog';

        $styleno = urldecode($styleno);

        $styleData = DB::connection('mysql_sb')->selectOne("
            SELECT
                act_costing.styleno,
                GROUP_CONCAT(DISTINCT so_det.color ORDER BY so_det.color SEPARATOR ', ') AS colors,
                GROUP_CONCAT(DISTINCT so_det.size  ORDER BY so_det.size  SEPARATOR ', ') AS sizes,
                acn.foto AS image,
                so_det.dest AS destinations,
                ms.Supplier AS buyer_name
            FROM so
            INNER JOIN act_costing ON so.id_cost = act_costing.id
            INNER JOIN so_det ON so_det.id_so = so.id AND so_det.cancel = 'N'
            INNER JOIN mastersupplier ms ON act_costing.id_buyer = ms.Id_Supplier
            LEFT JOIN bom_marketing ON so.id_bom = bom_marketing.id
            LEFT JOIN act_costing_new acn ON bom_marketing.id_costing = acn.id
            WHERE act_costing.styleno = ?
            GROUP BY act_costing.styleno, acn.foto, ms.Supplier
            LIMIT 1
        ", [$styleno]);

        if (!$styleData) {
            return redirect()->route('master-marketing-catalog')->with('error', 'Style tidak ditemukan.');
        }

        $soHistory = DB::connection('mysql_sb')->select("
            SELECT
                so.so_date,
                so.so_no,
                ms.Supplier as buyer_name,
                so_det.dest,
                so.cancel_h,
                SUM(so_det.qty) as qty
            FROM so
            INNER JOIN act_costing ON so.id_cost = act_costing.id
            INNER JOIN so_det ON so_det.id_so = so.id AND so_det.cancel = 'N'
            LEFT JOIN mastersupplier ms ON act_costing.id_buyer = ms.Id_Supplier
            WHERE act_costing.styleno = ?
            GROUP BY so.id, so.so_date, so.so_no, ms.Supplier, so_det.dest, so.cancel_h
            ORDER BY so.so_date DESC
        ", [$styleno]);

        $bomData = DB::connection('mysql_sb')->select("
            SELECT DISTINCT
                mi.mattype,
                mi.itemdesc,
                bji.cons,
                bji.unit
            FROM so
            INNER JOIN act_costing ON so.id_cost = act_costing.id
            INNER JOIN so_det ON so_det.id_so = so.id AND so_det.cancel = 'N'
            INNER JOIN bom_jo_item bji ON bji.id_so_det = so_det.id AND bji.cancel = 'N'
            INNER JOIN masteritem mi ON mi.id_gen = bji.id_item
            WHERE act_costing.styleno = ?
            ORDER BY mi.mattype, mi.itemdesc
        ", [$styleno]);

        return view('marketing.catalog.detail', [
            'page'          => $page,
            'subPageGroup'  => $subPageGroup,
            'subPage'       => $subPage,
            'styleData'     => $styleData,
            'soHistory'     => $soHistory,
            'bomData'       => $bomData,
            'containerFluid'=> true,
        ]);
    }
}
