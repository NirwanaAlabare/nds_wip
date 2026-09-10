<?php

namespace App\Http\Controllers\Cutting;

use App\Http\Controllers\Controller;
use App\Models\Cutting\FormCutScrap;
use App\Models\Cutting\FormCutScrapDetail;
use App\Models\Cutting\FormCutScrapFormPart;
use App\Models\Cutting\FormCutScrapPart;
use App\Models\Cutting\FormCutScrapSize;
use App\Models\Cutting\ScannedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use \avadim\FastExcelLaravel\Excel as FastExcel;
use DB;

class CuttingFormScrapController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $formCutScrap = FormCutScrap::with(["formCutScrapFormParts", "formCutScrapDetails", "formCutScrapDetails.formCutScrapParts", "formCutScrapDetails.formCutScrapParts.formCutScrapSizes"])->selectRaw("
                    form_cut_scrap.*,
                    COUNT(DISTINCT form_cut_scrap_detail.id) total_roll
                ")->
                leftJoin("form_cut_scrap_detail", "form_cut_scrap_detail.form_scrap_id", "=", "form_cut_scrap.id")->
                groupBy("form_cut_scrap.id");

            return DataTables::eloquent($formCutScrap)->
                // parts column
                addColumn('parts', function ($row) {
                    $parts = $row->formCutScrapFormParts;

                    $partArr = [];
                    foreach ($parts as $part) {
                        $currentPart = $part->partDetail ? $part->partDetail->masterPart : null;

                        if ($currentPart) {
                            if ( !in_array($currentPart->nama_part, $partArr) ) {
                                array_push($partArr, $currentPart->nama_part);
                            }
                        }
                    }

                    return implode(" , ", $partArr);
                })->
                // size qty column
                addColumn('size_qty', function ($row) {
                    $sizeQtyArr = [];

                    foreach ($row->formCutScrapDetails as $detail) {
                        foreach ($detail->formCutScrapParts as $part) {
                            foreach ($part->formCutScrapSizes as $size) {
                                $key = $size->size;
                                if (!isset($sizeQtyArr[$key])) {
                                    $sizeQtyArr[$key] = 0;
                                }
                                $sizeQtyArr[$key] += $size->qty;
                            }
                        }
                    }

                    $result = [];
                    foreach ($sizeQtyArr as $size => $qty) {
                        $result[] = $size . ': ' . $qty;
                    }

                    return implode(' , ', $result);
                })->
                addColumn('total_qty_roll', function ($row) {
                    $qtyRollArr = [];

                    foreach ($row->formCutScrapDetails as $detail) {
                        $unit = $detail->unit;
                        if (!isset($qtyRollArr[$unit])) {
                            $qtyRollArr[$unit] = 0;
                        }
                        $qtyRollArr[$unit] += $detail->qty_roll;
                    }

                    $result = [];
                    foreach ($qtyRollArr as $unit => $qty) {
                        $result[] = $qty . ' ' . $unit;
                    }

                    return implode(' , ', $result);
                })->
                filter(function ($query) use ($request) {
                    if ($request->dateFrom) {
                        $query->whereRaw("form_cut_scrap.tanggal >= '".$request->dateFrom."'");
                    }

                    if ($request->dateTo) {
                        $query->whereRaw("form_cut_scrap.tanggal <= '".$request->dateTo."'");
                    }
                }, true)->
                order(function ($query) {
                    $query->orderBy("form_cut_scrap.tanggal", "desc")->orderBy("form_cut_scrap.id", "desc");
                })->
                toJson();
        }

        return view('cutting.cutting-form-scrap.cutting-form-scrap', [
            "page" => "dashboard-cutting",
            "subPageGroup" => "proses-cutting",
            "subPage" => "cutting-scrap",
        ]);
    }

    /**
     * Halaman input. Form yang belum selesai dilanjutkan lewat process() supaya
     * satu operator tidak meninggalkan form menggantung, sama seperti cutting piece.
     */
    public function create()
    {
        if (session('currentFormCutScrap')) {
            return redirect()->route('process-cutting-scrap', ["id" => session('currentFormCutScrap')]);
        }

        return view('cutting.cutting-form-scrap.create-cutting-form-scrap', [
            "currentFormCutScrap" => null,
            "orders" => $this->orderList(),
            "page" => "dashboard-cutting",
            "subPageGroup" => "proses-cutting",
            "subPage" => "cutting-scrap",
        ]);
    }

    /**
     * Melanjutkan form yang sudah dibuat. View-nya sama dengan create, hanya saja
     * langkah yang sudah tersimpan langsung terbuka berdasarkan kolom "process".
     */
    public function process($id = 0)
    {
        $currentFormCutScrap = FormCutScrap::with([
            "formCutScrapFormParts",
            "formCutScrapDetails.formCutScrapParts.formCutScrapSizes",
            "formCutScrapDetails.formCutScrapParts.partDetail.masterPart",
        ])->find($id);

        if (!$currentFormCutScrap) {
            session()->forget('currentFormCutScrap');

            return redirect()->route('create-cutting-scrap');
        }

        session(['currentFormCutScrap' => $currentFormCutScrap->id]);

        return view('cutting.cutting-form-scrap.create-cutting-form-scrap', [
            "currentFormCutScrap" => $currentFormCutScrap,
            "orders" => $this->orderList(),
            "page" => "dashboard-cutting",
            "subPageGroup" => "proses-cutting",
            "subPage" => "cutting-scrap",
        ]);
    }

    public function createNew()
    {
        session()->forget('currentFormCutScrap');

        return redirect()->route('create-cutting-scrap');
    }

    /**
     * Nomor form dibuat saat header disimpan, bukan saat halaman dibuka, supaya
     * halaman yang hanya dibuka lalu ditinggal tidak meninggalkan form kosong.
     */
    private function generateNoForm()
    {
        // Format no_form : FS<bulan>-<tanggal>-<urutan>, urutannya mulai lagi tiap hari
        $prefix = "FS".date("m")."-".date("d")."-";

        $lastForm = FormCutScrap::select("no_form")->
            whereRaw("no_form LIKE '".$prefix."%'")->
            orderBy("id", "desc")->
            first();

        $urutan = $lastForm ? (intval(str_replace($prefix, "", $lastForm->no_form)) + 1) : 1;

        return $prefix.$urutan;
    }

    public function store(Request $request)
    {
        switch ($request->process) {
            // Langkah 1 : header form + part yang dipakai seluruh roll
            case 1:
                return $this->storeHeader($request);

            // Langkah 2 : verifikasi operator scrap
            case 2:
                return $this->storeOperator($request);

            // Langkah 3 : informasi barang / roll scrap
            case 3:
                return $this->storeRoll($request);

            // Langkah 4 : qty per size untuk roll yang sedang aktif
            case 4:
                return $this->storeSize($request);
        }

        return array(
            "status" => 400,
            "message" => "Proses tidak dikenali.",
            "additional" => [],
        );
    }

    private function storeHeader(Request $request)
    {
        $validatedRequest = $request->validate([
            "tanggal" => "required",
            "act_costing_id" => "required",
            "act_costing_ws" => "required",
            "color" => "required",
            "panel" => "required",
            "parts" => "required|array|min:1",
        ], [
            "parts.required" => "Harap pilih minimal satu part.",
        ]);

        // Form baru dibuat di langkah ini, langkah berikutnya tinggal memakai id-nya
        $formCutScrap = $request["id"] ? FormCutScrap::find($request["id"]) : null;

        if ($request["id"] && !$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        // Part header tidak boleh diubah lagi kalau roll-nya sudah terlanjur diisi,
        // karena qty size tiap roll disimpan mengikuti daftar part ini.
        $savedDetail = $formCutScrap && FormCutScrapDetail::where("form_scrap_id", $formCutScrap->id)->
            where("status", "complete")->
            exists();

        $newParts = array_values(array_unique(array_filter($validatedRequest["parts"])));
        $oldParts = FormCutScrapFormPart::where("form_scrap_id", $formCutScrap ? $formCutScrap->id : 0)->
            pluck("part_detail_id")->
            map(function ($partDetailId) {
                return strval($partDetailId);
            })->
            toArray();

        sort($newParts);
        sort($oldParts);

        if ($savedDetail && $newParts != $oldParts) {
            return array(
                "status" => 400,
                "message" => "Part tidak bisa diubah karena sudah ada roll yang tersimpan.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            $headerArr = [
                "tanggal" => $validatedRequest["tanggal"],
                "act_costing_id" => $validatedRequest["act_costing_id"],
                "act_costing_ws" => $validatedRequest["act_costing_ws"],
                "style" => $request["style"],
                "color" => $validatedRequest["color"],
                "panel" => $validatedRequest["panel"],
                "ket" => $request["ket"],
            ];

            if ($formCutScrap) {
                $formCutScrap->update(array_merge($headerArr, [
                    "process" => max(1, intval($formCutScrap->process)),
                ]));
            } else {
                $formCutScrap = FormCutScrap::create(array_merge($headerArr, [
                    "no_form" => $this->generateNoForm(),
                    "waktu_mulai" => Carbon::now(),
                    "process" => 1,
                    "status" => "incomplete",
                    "created_by" => Auth::user()->username,
                    "created_by_id" => Auth::user()->id,
                ]));
            }

            $this->syncFormParts($formCutScrap, $newParts);

            session(['currentFormCutScrap' => $formCutScrap->id]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Header Form Cut Scrap ".$formCutScrap->no_form." berhasil disimpan.",
                "additional" => $formCutScrap->refresh()->load("formCutScrapFormParts"),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    private function storeOperator(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "employee_id" => "required",
            "employee_nik" => "required",
            "employee_name" => "required",
        ], [
            "employee_id.required" => "Harap verifikasi operator scrap terlebih dahulu.",
            "employee_nik.required" => "Harap verifikasi operator scrap terlebih dahulu.",
            "employee_name.required" => "Harap verifikasi operator scrap terlebih dahulu.",
        ]);

        $formCutScrap = FormCutScrap::find($validatedRequest["id"]);

        if (!$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        if (intval($formCutScrap->process) < 1) {
            return array(
                "status" => 400,
                "message" => "Harap simpan header form terlebih dahulu.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            $formCutScrap->update([
                "created_by_id" => $validatedRequest["employee_id"],
                "created_by" => $validatedRequest["employee_name"],
                // Kolom operator dipertahankan supaya list & halaman edit tetap terbaca
                "operator" => $validatedRequest["employee_name"],
                "process" => max(2, intval($formCutScrap->process)),
            ]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Operator ".$validatedRequest["employee_name"]." berhasil diverifikasi.",
                "additional" => $formCutScrap->refresh(),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    private function storeRoll(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "id_item" => "required",
            "itemdesc" => "required",
            "unit" => "required",
            "qty_roll" => "required|numeric|gt:0",
        ], [
            "id_item.required" => "Harap pilih item fabric.",
            "qty_roll.gt" => "Qty roll harus lebih dari 0.",
        ]);

        $formCutScrap = FormCutScrap::find($validatedRequest["id"]);

        if (!$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        if (intval($formCutScrap->process) < 2) {
            return array(
                "status" => 400,
                "message" => "Harap verifikasi operator terlebih dahulu.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            // Satu baris incomplete = roll yang sedang diisi. Kalau operator kembali
            // ke langkah ini tanpa sempat menyimpan size, barisnya dipakai ulang.
            $formCutScrapDetail = FormCutScrapDetail::where("form_scrap_id", $formCutScrap->id)->
                where("status", "incomplete")->
                first();

            $detailArr = [
                "form_scrap_id" => $formCutScrap->id,
                "no_form_scrap" => $formCutScrap->no_form,
                "method" => "select",
                "id_roll" => $request["id_roll"],
                "id_item" => $validatedRequest["id_item"],
                "itemdesc" => $validatedRequest["itemdesc"],
                "unit" => $validatedRequest["unit"],
                "qty_roll" => $validatedRequest["qty_roll"],
                "lot" => $request["lot"],
                "group_roll" => $request["group_roll"],
                "status" => "incomplete",
            ];

            if ($formCutScrapDetail) {
                $formCutScrapDetail->update($detailArr);
            } else {
                $formCutScrapDetail = FormCutScrapDetail::create(array_merge($detailArr, [
                    "created_by" => Auth::user()->username,
                    "created_by_id" => Auth::user()->id,
                ]));
            }

            $formCutScrap->update(["process" => 3]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Informasi barang scrap berhasil disimpan.",
                "additional" => $formCutScrapDetail->refresh(),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    /**
     * Qty per size diisi sekali lalu disalin ke seluruh part header, sesuai catatan
     * pada form : "berlaku sama untuk semua part terpilih".
     */
    private function storeSize(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "id_detail" => "required",
            "size" => "required|array|min:1",
        ], [
            "size.required" => "Harap isi minimal satu size.",
        ]);

        $formCutScrap = FormCutScrap::find($validatedRequest["id"]);
        $formCutScrapDetail = FormCutScrapDetail::where("form_scrap_id", $validatedRequest["id"])->
            where("id", $validatedRequest["id_detail"])->
            first();

        if (!$formCutScrap || !$formCutScrapDetail) {
            return array(
                "status" => 400,
                "message" => "Roll tidak ditemukan pada form ini.",
                "additional" => [],
            );
        }

        $formParts = FormCutScrapFormPart::where("form_scrap_id", $formCutScrap->id)->get();

        if ($formParts->count() < 1) {
            return array(
                "status" => 400,
                "message" => "Form belum memiliki part. Harap simpan header terlebih dahulu.",
                "additional" => [],
            );
        }

        // Size dengan qty 0 tidak disimpan supaya tabel size tidak penuh baris kosong
        $sizes = collect($validatedRequest["size"])->
            filter(function ($size) {
                return !empty($size["size"]) && floatval($size["qty"] ?? 0) > 0;
            })->
            values();

        if ($sizes->count() < 1) {
            return array(
                "status" => 400,
                "message" => "Harap isi qty minimal pada satu size.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            // Part ditulis ulang seluruhnya supaya hasilnya sama baik saat pertama
            // disimpan maupun saat roll yang sama diisi ulang.
            $this->destroyParts($formCutScrapDetail);

            foreach ($formParts as $formPart) {
                $formCutScrapPart = FormCutScrapPart::create([
                    "form_scrap_detail_id" => $formCutScrapDetail->id,
                    "part_detail_id" => $formPart->part_detail_id,
                    "ket" => $request["ket_part"],
                    "created_by" => Auth::user()->username,
                    "created_by_id" => Auth::user()->id,
                ]);

                foreach ($sizes as $size) {
                    FormCutScrapSize::create([
                        "form_scrap_part_id" => $formCutScrapPart->id,
                        "so_det_id" => $size["so_det_id"] ?? null,
                        "size" => $size["size"],
                        "qty" => $size["qty"],
                        "created_by" => Auth::user()->username,
                        "created_by_id" => Auth::user()->id,
                    ]);
                }
            }

            $formCutScrapDetail->update(["status" => "complete"]);

            // Balik ke langkah 3 supaya roll berikutnya bisa langsung diinput
            $formCutScrap->update(["process" => 2]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Qty size roll berhasil disimpan.",
                "additional" => FormCutScrapDetail::with("formCutScrapParts.formCutScrapSizes", "formCutScrapParts.partDetail.masterPart")->find($formCutScrapDetail->id),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    /**
     * Menutup form : roll yang belum sampai tahap part dibuang, lalu form dikunci
     * sebagai complete dan session dilepas.
     */
    public function finishProcess(Request $request)
    {
        $id = $request->id ?: session('currentFormCutScrap');

        $formCutScrap = FormCutScrap::find($id);

        if (!$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            // Roll tanpa part tidak membawa informasi apa pun, ikut pola cutting piece
            $incompleteDetails = FormCutScrapDetail::where("form_scrap_id", $formCutScrap->id)->
                where("status", "incomplete")->
                get();

            foreach ($incompleteDetails as $incompleteDetail) {
                $this->destroyParts($incompleteDetail);
                $incompleteDetail->delete();
            }

            if (!FormCutScrapDetail::where("form_scrap_id", $formCutScrap->id)->exists()) {
                DB::rollBack();

                return array(
                    "status" => 400,
                    "message" => "Form belum memiliki roll yang lengkap.",
                    "additional" => [],
                );
            }

            $formCutScrap->update([
                "waktu_selesai" => Carbon::now(),
                "status" => "complete",
                "process" => 4,
            ]);

            session()->forget('currentFormCutScrap');

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Form Cut Scrap ".$formCutScrap->no_form." selesai.",
                "redirect" => route("cutting-scrap"),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    public function deleteDetail(Request $request)
    {
        $formCutScrapDetail = FormCutScrapDetail::find($request->id_detail);

        if (!$formCutScrapDetail) {
            return array(
                "status" => 400,
                "message" => "Roll tidak ditemukan.",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            $this->destroyParts($formCutScrapDetail);
            $formCutScrapDetail->delete();

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Roll berhasil dihapus.",
                "additional" => [],
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    public function edit($id = 0)
    {
        $formCutScrap = FormCutScrap::with([
            "formCutScrapDetails.formCutScrapParts.formCutScrapSizes",
            "formCutScrapDetails.formCutScrapParts.partDetail.masterPart",
        ])->find($id);

        if (!$formCutScrap) {
            return redirect()->route('cutting-scrap')->with('error', 'Form Cut Scrap tidak ditemukan.');
        } else {
            if ($formCutScrap->status != 'complete') {
                return redirect()->route('process-cutting-scrap', ["id" => $formCutScrap->id]);
            }
        }

        return view('cutting.cutting-form-scrap.edit-cutting-form-scrap', [
            "formCutScrap" => $formCutScrap,
            "page" => "dashboard-cutting",
            "subPageGroup" => "proses-cutting",
            "subPage" => "cutting-scrap",
        ]);
    }

    public function update(Request $request)
    {
        $validatedRequest = $request->validate([
            "id" => "required",
            "tanggal" => "required",
        ]);

        $formCutScrap = FormCutScrap::find($validatedRequest["id"]);

        if (!$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        if ($formCutScrap->waktu_mulai > $request["waktu_selesai"]) {
            return array(
                "status" => 400,
                "message" => "Waktu selesai tidak bisa kurang dari <br> '".$formCutScrap->waktu_mulai."'",
                "additional" => [],
            );
        }

        if (checkClosingDate($formCutScrap->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Periode sudah ditutup",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            $formCutScrap->update([
                "tanggal" => $validatedRequest["tanggal"],
                "waktu_selesai" => $request["waktu_selesai"] ?: $formCutScrap->waktu_selesai,
                "operator" => $request["operator"],
                "ket" => $request["ket"],
                "edited_by" => Auth::user()->username,
                "edited_by_id" => Auth::user()->id,
                "edited_at" => Carbon::now(),
            ]);

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Form Cut Scrap ".$formCutScrap->no_form." berhasil diupdate.",
                "redirect" => route("cutting-scrap"),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    public function destroy($id = 0)
    {
        $formCutScrap = FormCutScrap::find($id);

        if (!$formCutScrap) {
            return array(
                "status" => 400,
                "message" => "Form Cut Scrap tidak ditemukan.",
                "additional" => [],
            );
        }

        if (checkClosingDate($formCutScrap->waktu_selesai)) {
            return array(
                "status" => 400,
                "message" => "Periode sudah ditutup",
                "additional" => [],
            );
        }

        DB::beginTransaction();
        try {
            $noForm = $formCutScrap->no_form;

            foreach ($formCutScrap->formCutScrapDetails as $formCutScrapDetail) {
                $this->destroyParts($formCutScrapDetail);
                $formCutScrapDetail->delete();
            }

            FormCutScrapFormPart::where("form_scrap_id", $formCutScrap->id)->delete();

            $formCutScrap->delete();

            if (session('currentFormCutScrap') == $id) {
                session()->forget('currentFormCutScrap');
            }

            DB::commit();

            return array(
                "status" => 200,
                "message" => "Form Cut Scrap ".$noForm." berhasil dihapus.",
                "redirect" => route("cutting-scrap"),
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return array(
                "status" => 400,
                "message" => $th->getMessage(),
                "additional" => [],
            );
        }
    }

    // Part (part_detail) berdasarkan WS, color & panel
    public function getParts(Request $request)
    {
        $parts = DB::table("part_detail")->
            selectRaw("
                part_detail.id part_detail_id,
                master_part.nama_part,
                part_detail.part_status
            ")->
            leftJoin("part", "part.id", "=", "part_detail.part_id")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            where("part.act_costing_ws", $request->act_costing_ws)->
            when($request->color, function ($query) use ($request) {
                // part.color bisa berisi gabungan beberapa color ("BLACK, WHITE")
                $query->whereRaw("FIND_IN_SET(?, REPLACE(part.color, ', ', ','))", [$request->color]);
            })->
            when($request->panel, function ($query) use ($request) {
                $query->where("part.panel", $request->panel);
            })->
            where("part_detail.status", "active")->
            orderBy("master_part.nama_part")->
            get();

        return response()->json($parts);
    }

    // Info roll dari scanned item
    public function getRoll(Request $request)
    {
        $roll = ScannedItem::select("id_roll", "id_item", "detail_item", "lot", "qty", "qty_stok", "unit")->
            where("id_roll", $request->id_roll)->
            first();

        return response()->json($roll);
    }

    private function orderList()
    {
        // Sama seperti CuttingFormPieceController::create() : WS dimuat langsung tanpa
        // perlu memilih buyer dulu, style/color/panel didapat dari WS-nya.
        return DB::connection("mysql_sb")->table("act_costing")->
            select("id", "kpno")->
            where("status", "!=", "CANCEL")->
            where("cost_date", ">=", "2023-01-01")->
            where("type_ws", "STD")->
            orderBy("cost_date", "desc")->
            orderBy("kpno", "asc")->
            groupBy("kpno")->
            get();
    }

    /**
     * Menyamakan daftar part header dengan pilihan terbaru. Nama part ikut disimpan
     * supaya badge di form & list tetap terbaca walau master part berubah.
     */
    private function syncFormParts($formCutScrap, $partDetailIds)
    {
        FormCutScrapFormPart::where("form_scrap_id", $formCutScrap->id)->delete();

        $partDetails = DB::table("part_detail")->
            selectRaw("part_detail.id, master_part.nama_part, part_detail.part_status")->
            leftJoin("master_part", "master_part.id", "=", "part_detail.master_part_id")->
            whereIn("part_detail.id", $partDetailIds)->
            get()->
            keyBy("id");

        foreach ($partDetailIds as $partDetailId) {
            $partDetail = $partDetails->get($partDetailId);

            FormCutScrapFormPart::create([
                "form_scrap_id" => $formCutScrap->id,
                "part_detail_id" => $partDetailId,
                "nama_part" => $partDetail ? $partDetail->nama_part : null,
                "part_status" => $partDetail ? $partDetail->part_status : null,
                "created_by" => Auth::user()->username,
                "created_by_id" => Auth::user()->id,
            ]);
        }
    }

    private function destroyParts($formCutScrapDetail)
    {
        $partIds = FormCutScrapPart::where("form_scrap_detail_id", $formCutScrapDetail->id)->pluck("id");

        FormCutScrapSize::whereIn("form_scrap_part_id", $partIds)->delete();
        FormCutScrapPart::whereIn("id", $partIds)->delete();
    }

    public function exportExcel(Request $request) {
        $dateFilter = "";

        if ($request->dateFrom) {
            $dateFilter .= " AND fcs.waktu_selesai >= '".$request->dateFrom." 00:00:00'";
        }

        if ($request->dateTo) {
            $dateFilter .= " AND fcs.waktu_selesai <= '".$request->dateTo." 23:59:59'";
        }

        $formCutScrap = DB::select("
            SELECT
                fcs.no_form,
                DATE_FORMAT(fcs.waktu_selesai, '%Y-%m-%d %H:%i:%s') AS tanggal_selesai,
                fcs.act_costing_ws AS ws,
                fcs.style,
                fcs.color,
                fss.size,
                (CASE WHEN COALESCE(pcust.set_part_status, pd.part_status) = 'complement' THEN COALESCE(p_com.panel, p.panel) ELSE p.panel END) panel,
                mp.nama_part AS part,
                fss.qty,
                fcsd.itemdesc AS nama_item,
                fcsd.id_item,
                fcsd.id_roll,
                fcsd.qty_roll,
                fcsd.unit,
                fcsd.lot,
                fcsd.`group_roll`,
                fcs.employee_nik,
                fcs.employee_name,
                fcs.status
            FROM form_cut_scrap fcs
            LEFT JOIN form_cut_scrap_detail fcsd ON fcs.id = fcsd.form_scrap_id
            LEFT JOIN form_cut_scrap_part fcsp ON fcsd.id = fcsp.form_scrap_detail_id
            LEFT JOIN part_detail pd ON fcsp.part_detail_id = pd.id
            left join part p on pd.part_id = p.id
            left join part_detail pd_com on pd_com.id = pd.from_part_detail
            left join part p_com on p_com.id = pd_com.part_id
            LEFT JOIN master_part mp ON pd.master_part_id = mp.id
            LEFT JOIN form_cut_scrap_size fss ON fcsp.id = fss.form_scrap_part_id
            LEFT JOIN master_sb_ws msb on msb.id_so_det = fss.so_det_id
            left join part_custom pcust on pcust.part_id = p.id and pcust.part_detail_id = pd.id and pcust.color = msb.color
            WHERE fcs.status = 'complete' ".$dateFilter."
        ");

        // Create Excel file using FastExcel
        $excel = FastExcel::create('data');
        $sheet = $excel->getSheet();

        // Title
        $sheet->writeTo('A1', 'Form Cut Scrap', ['font-size' => 16]);
        $sheet->mergeCells('A1:Q1');
        $sheet->writeTo('A2', 'Dari : '. ($request->dateFrom ?? "-"));
        $sheet->writeTo('B2', 'Sampai : '. ($request->dateTo ?? "-"));

        // Headers
        $sheet->writeTo('A3', 'No Form')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('B3', 'Tanggal Selesai')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('C3', 'WS')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('D3', 'Style')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('E3', 'Color')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('F3', 'Size')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('G3', 'Panel')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('H3', 'Part')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('I3', 'Qty')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('J3', 'Nama Item')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('K3', 'ID Item')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('L3', 'ID Roll')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('M3', 'Qty Roll')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('N3', 'Unit')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('O3', 'Lot')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('P3', 'Group Roll')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('Q3', 'Status')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('R3', 'User NIK')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->writeTo('S3', 'User')->applyFontStyleBold()->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        collect($formCutScrap)->chunk(1000)->each(function ($rows) use ($sheet) {
            $sheet->writeAreas();

            foreach ($rows as $row) {
                $rowArr = [
                    $row->no_form ?? "-",
                    $row->tanggal_selesai ?? "-",
                    $row->ws ?? "-",
                    $row->style ?? "-",
                    $row->color ?? "-",
                    $row->size ?? "-",
                    $row->panel ?? "-",
                    $row->part ?? "-",
                    $row->qty ?? "-",
                    $row->nama_item ?? "-",
                    $row->id_item ?? "-",
                    $row->id_roll ?? "-",
                    $row->qty_roll ?? "-",
                    $row->unit ?? "-",
                    $row->lot ?? "-",
                    $row->group_roll ?? "-",
                    $row->status ?? "-",
                    $row->employee_nik ?? "-",
                    $row->employee_name ?? "-",
                ];

                $sheet->writeRow($rowArr)->applyBorder(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        });

        $filename = 'Laporan Form Cut Scrap in ' . $request->dateFrom . ' - ' . $request->dateTo . ' (' . Carbon::now()->format('Y-m-d H:i:s') . ').xlsx';

        return $excel->download($filename);
    }
}
