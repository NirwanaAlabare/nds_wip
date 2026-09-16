{{--
    Form + tabel Master Lokasi.
    Dipakai di halaman Master Lokasi dan di modal popup halaman lain,
    jadi semua id/fungsinya diberi prefix MasterLokasi supaya tidak bentrok.
--}}
@once
    <style>
        /* Select2 bukan elemen form-control asli, jadi flex-nya diatur manual biar
           tidak gepeng saat berada di dalam input-group. */
        .input-group .select2-container {
            flex: 1 1 auto;
            width: 1% !important;
        }

        .input-group .select2-container--bootstrap4 .select2-selection {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #tblMasterLokasi td,
        #tblMasterLokasi th {
            vertical-align: middle;
        }
    </style>
@endonce

<div class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-4">
        <label for="cbomain_lokasi" class="mb-1"><small><b>Main Lokasi :</b></small></label>
        <div class="input-group input-group-sm">
            <select id="cbomain_lokasi" name="cbomain_lokasi"
                class="form-control form-control-sm select2bs4 select2-master-lokasi border-primary">
                <option value="">-- Pilih Main Lokasi --</option>
                @foreach ($mainLokasiList as $row)
                    <option value="{{ $row->id }}">{{ $row->main_lokasi }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-outline-primary btn-sm"
                onclick="openMasterLokasiChildModal('CreateMainLokasiModal');" title="Tambah Main Lokasi">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <label for="txtsub_lokasi" class="mb-1"><small><b>Sub Lokasi :</b></small></label>
        <input type="text" id="txtsub_lokasi" name="txtsub_lokasi" class="form-control form-control-sm"
            autocomplete="off" list="subLokasiSuggestions" style="text-transform: uppercase;"
            oninput="this.value = this.value.toUpperCase();">
    </div>
    <div class="col-6 col-md-3">
        <label for="txtdivisi" class="mb-1"><small><b>Divisi :</b></small></label>
        <input type="text" id="txtdivisi" name="txtdivisi" class="form-control form-control-sm" autocomplete="off"
            list="divisiSuggestions" style="text-transform: uppercase;"
            oninput="this.value = this.value.toUpperCase();">
    </div>
    <div class="col-12 col-md-2 d-grid">
        <button type="button" class="btn btn-success btn-sm" id="saveLokasiButton" onclick="saveMasterLokasiDet();">
            <i class="fas fa-save"></i> Save
        </button>
    </div>
</div>

<div class="table-responsive">
    <datalist id="subLokasiSuggestions">
        @foreach ($subLokasiList as $row)
            <option value="{{ $row->sub_lokasi }}">
        @endforeach
    </datalist>
    <datalist id="divisiSuggestions">
        @foreach ($divisiList as $row)
            <option value="{{ $row->divisi }}">
        @endforeach
    </datalist>

    <table id="tblMasterLokasi" class="table table-bordered table-hover table-sm align-middle w-100">
        <thead class="bg-sb">
            <tr>
                <th scope="col" class="text-center align-middle">Main Lokasi</th>
                <th scope="col" class="text-center align-middle">Sub Lokasi</th>
                <th scope="col" class="text-center align-middle">Divisi</th>
                <th scope="col" class="text-center align-middle">Act</th>
            </tr>
        </thead>
    </table>
</div>
