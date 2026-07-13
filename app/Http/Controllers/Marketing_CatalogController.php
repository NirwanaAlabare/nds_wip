<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Marketing_CatalogController extends Controller
{
    public function index(Request $request)
    {
        $page = 'dashboard-marketing';
        $subPageGroup = 'marketing-master';
        $subPage = 'marketing-master-catalog';

        $search = $request->input('search');

        $query = DB::connection('mysql_sb')
            ->table('masterstyle')
            ->select(
                'Styleno',
                'itemname',
                'Color as colors',
                DB::raw('GROUP_CONCAT(DISTINCT size SEPARATOR ", ") as sizes'),
                DB::raw('MIN(id_item) as id_item')
            );

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('itemname', 'like', "%{$search}%")
                  ->orWhere('Styleno', 'like', "%{$search}%")
                  ->orWhere('Color', 'like', "%{$search}%");
            });
        }

        $styles = $query->groupBy('Styleno', 'itemname', 'Color')->paginate(24);

        return view('marketing.catalog.index', [
            'page' => $page,
            'subPageGroup' => $subPageGroup,
            'subPage' => $subPage,
            'styles' => $styles,
            'containerFluid' => true,
        ]);
    }

    public function catalogDetail($id_item)
    {
        $page = 'dashboard-marketing';
        $subPageGroup = 'marketing-master';
        $subPage = 'marketing-master-catalog';

        $baseStyle = DB::connection('mysql_sb')
            ->table('masterstyle')
            ->select('Styleno', 'Color')
            ->where('id_item', $id_item)
            ->first();

        if (!$baseStyle) {
            return redirect()->route('master-marketing-catalog')->with('error', 'Style tidak ditemukan.');
        }

        $styleData = DB::connection('mysql_sb')
            ->table('masterstyle')
            ->select(
                'Styleno', 
                'itemname', 
                'Color as colors', 
                DB::raw('GROUP_CONCAT(DISTINCT size SEPARATOR ", ") as sizes'),
                DB::raw('MIN(id_item) as id_item')
            )
            ->where('Styleno', $baseStyle->Styleno)
            ->where('Color', $baseStyle->Color)
            ->groupBy('Styleno', 'itemname', 'Color')
            ->first();

        $allIdItems = DB::connection('mysql_sb')
            ->table('masterstyle')
            ->where('Styleno', $baseStyle->Styleno)
            ->where('Color', $baseStyle->Color)
            ->pluck('id_item')
            ->toArray();

        $soHistory = DB::connection('mysql_sb')
            ->table('bom_jo_item')
            ->join('so_det', 'bom_jo_item.id_so_det', '=', 'so_det.id')
            ->join('so', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('act_costing', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('mastersupplier', 'act_costing.id_buyer', '=', 'mastersupplier.Id_Supplier')
            ->select('so.so_date', 'so.so_no', 'mastersupplier.supplier as buyer_name', 'so_det.dest', 'so.cancel_h', DB::raw('SUM(so_det.qty) as qty'))
            ->whereIn('bom_jo_item.id_item', $allIdItems)
            ->groupBy('so.id', 'so.so_date', 'so.so_no', 'mastersupplier.supplier', 'so_det.dest', 'so.cancel_h')
            ->orderBy('so.so_date', 'desc')
            ->get();

        $bomData = DB::connection('mysql_sb')
            ->table('masterstyle')
            ->join('bom_jo_item', 'masterstyle.id_so_det', '=', 'bom_jo_item.id_so_det')
            ->join('masteritem', 'bom_jo_item.id_item', '=', 'masteritem.id_gen')
            ->select(
                'masteritem.mattype',
                'masteritem.itemdesc',
                'bom_jo_item.cons',
                'bom_jo_item.unit'
            )
            ->whereIn('masterstyle.id_item', $allIdItems)
            ->where('bom_jo_item.cancel', 'N')
            ->groupBy('masteritem.mattype', 'masteritem.itemdesc', 'bom_jo_item.cons', 'bom_jo_item.unit')
            ->get();

        return view('marketing.catalog.detail', [
            'page' => $page,
            'subPageGroup' => $subPageGroup,
            'subPage' => $subPage,
            'styleData' => $styleData,
            'soHistory' => $soHistory,
            'bomData' => $bomData,
            'containerFluid' => true,
        ]);
    }
}
