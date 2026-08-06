<?php

namespace App\Http\Controllers\Part;

use App\Http\Controllers\Controller;
use App\Models\Part\PartCustom;
use App\Models\Part\PartDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PartToolsController extends Controller
{
    public function index(Request $request)
    {
        return view('part.tools.tools', ["page" => "dashboard-marker"]);
    }

    public function getPartCustom(Request $request, $id = 0)
    {
        $partId = $id ?: $request->part_id;

        $list = DB::select("
                SELECT
                    pc.id,
                    pc.id part_custom_id,
                    pc.part_id part_custom_part_id,
                    pc.part_detail_id part_custom_part_detail_id,
                    CONCAT(mp.nama_part, ' - ', mp.bag, ' - ', pd.part_status) nama_part,
                    pc.color part_custom_color,
                    pd.part_status part_status,
                    pc.set_part_status part_custom_status,
                    pc.notes part_custom_notes,
                    pc.created_by_username,
                    pc.updated_at
                FROM
                    part_custom pc
                    inner join part_detail pd on pd.id = pc.part_detail_id
                    inner join master_part mp on mp.id = pd.master_part_id
                WHERE
                    pc.part_id = ?
                ORDER BY
                    pc.id DESC
            ", [$partId]);

        return DataTables::of($list)->toJson();
    }

    public function storePartCustom(Request $request)
    {
        $validatedRequest = $request->validate([
            "part_custom_id" => "required|exists:part,id",
            "part_custom_color" => "required",
            "part_custom_part_detail_id" => "required",
            "part_custom_status" => "required",
        ]);

        $partDetail = PartDetail::find($request->part_custom_part_detail_id);

        $exists = PartCustom::where("part_detail_id", $partDetail->id)
            ->where("color", $request->color)
            ->exists();

        if ($exists) {
            return response()->json([
                "status" => 400,
                "message" => "Custom Part untuk warna ini sudah ada.",
            ]);
        }

        $partCustom = PartCustom::create([
            "part_id" => $validatedRequest['part_custom_id'],
            "part_detail_id" => $validatedRequest['part_custom_part_detail_id'],
            "color" => $validatedRequest['part_custom_color'],
            "part_status" => $partDetail->part_status,
            "set_part_status" => $validatedRequest['part_custom_status'],
            "notes" => $request['part_custom_notes'],
            "created_by" => Auth::user()->id,
            "created_by_username" => Auth::user()->username,
        ]);

        if ($partCustom) {
            return array(
                "status" => 201,
                "message" => "Custom Part berhasil disimpan",
                "table" => "datatable-part-custom",
                "additional" => [],
            );
        }

        return response()->json([
            "status" => 400,
            "message" => "Custom Part gagal disimpan.",
        ]);
    }

    public function updatePartCustom(Request $request)
    {
        $validatedRequest = $request->validate([
            "edit_part_custom_id" => "required|exists:part_custom,id",
            "edit_part_custom_part_detail_id" => "required",
            "edit_part_custom_color" => "required",
            "edit_part_custom_status" => "required",
        ]);

        $partCustom = PartCustom::find($request->edit_part_custom_id);

        if (!$partCustom) {
            return response()->json([
                "status" => 400,
                "message" => "Custom Part tidak ditemukan.",
            ]);
        }

        $partDetail = PartDetail::find($request->edit_part_custom_part_detail_id);

        if (!$partCustom) {
            return response()->json([
                "status" => 400,
                "message" => "Part tidak ditemukan.",
            ]);
        }

        $partCustomUpdate = $partCustom->update([
            "part_detail_id" => $validatedRequest['edit_part_custom_part_detail_id'],
            "color" => $validatedRequest['edit_part_custom_color'],
            "part_status" => $partDetail->part_status,
            "set_part_status" => $validatedRequest['edit_part_custom_status'],
            "notes" => $request['edit_part_custom_notes'],
        ]);

        if ($partCustomUpdate) {
            return response()->json([
                "status" => 200,
                "message" => "Custom Part berhasil diupdate.",
            ]);
        }

        return response()->json([
            "status" => 400,
            "message" => "Custom Part gagal diupdate.",
        ]);
    }

    public function destroyPartCustom($id = 0)
    {
        $partCustom = PartCustom::find($id);

        if (!$partCustom) {
            return response()->json([
                "status" => 400,
                "message" => "Custom Part tidak ditemukan.",
            ]);
        }

        if ($partCustom->delete()) {
            return response()->json([
                "status" => 200,
                "message" => "Custom Part berhasil dihapus.",
            ]);
        }

        return response()->json([
            "status" => 400,
            "message" => "Custom Part gagal dihapus.",
        ]);
    }
}
