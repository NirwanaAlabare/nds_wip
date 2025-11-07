@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Data Master Lokasi</h5>
    </div>
    <div class="card-body">
  <div class="row align-items-end mb-3">
    <div class="col-md-5">
      <label for="area" class="form-label mb-1 fw-bold">Area Lokasi</label>
      <select class="form-select form-select-sm select2master" id="area" name="area">
        <option selected value="ALL">ALL</option>
        @foreach ($arealok as $alok)
          <option value="{{ $alok->area }}">{{ $alok->area }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-md-7 d-flex gap-2 mt-3">
      <button class="btn btn-primary btn-sm" onclick="dataTableReload()">
        <i class="fas fa-search"></i> Search
      </button>
      <button class="btn btn-info btn-sm" onclick="tambahdata()">
        <i class="fas fa-plus"></i> Add Data
      </button>
      <!-- <button class="btn btn-success btn-sm" onclick="printAllLokasi()">
    <i class="fas fa-print"></i> Print All
  </button> -->
    </div>
  </div>

  <div class="table-responsive">
    <table id="datatable" class="table table-bordered table-striped w-100">
      <thead class="text-center">
        <tr>
          <th>Area Lokasi</th>
          <th>Kode Lokasi</th>
          <th>Satuan</th>
          <th>Kapasitas</th>
          <th>User</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

</div>

<div class="modal fade" id="modal-active-lokasi">
    <form action="{{ route('updatestatus') }}" method="post" onsubmit="submitForm(this, event)">
         @method('GET')
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-sb text-light">
                    <h4 class="modal-title">Confirm Dialog</h4>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!--  -->
                    <div class="form-group row">
                        <label for="id_inv" class="col-sm-12 col-form-label" >Sure Change Status Master Location :</label>
                        <br>
                        <div class="col-sm-4">
                        </div>
                        <div class="col-sm-4">
                            <input type="text" class="form-control" id="txt_kode_lok" name="txt_kode_lok" style="border:none;text-align: center;" readonly>
                        </div>
                    </div>
                    <!-- Hidden Text -->
                    <input type="hidden" id="id_lok" name="id_lok" readonly>
                    <input type="hidden" id="status_lok" name="status_lok" readonly>
                    <!--  -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fa fa-window-close" aria-hidden="true"></i> Close</button>
                    <button type="submit" class="btn btn-primary toastsDefaultDanger"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Change Status</button>
                </div>
            </div>
        </div>
    </form>
</div>


 <div class="modal fade" id="modal-edit-lokasi" tabindex="-1" aria-hidden="true">
  <form action="{{ route('simpan-edit') }}" method="post" onsubmit="submitForm(this, event)">
    @csrf
    @method('GET')

    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <!-- Header -->
        <div class="modal-header bg-sb text-light">
          <h5 class="modal-title mb-0">
            <i class="fas fa-map-marker-alt me-2"></i> Edit Lokasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body bg-light">
          <div class="row g-3">
            <!-- Kiri -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="kode_lok" class="form-label fw-bold">Kode Lokasi</label>
                <input type="text" class="form-control" id="kode_lok_edit" name="kode_lok_edit" value="" readonly>
                <input type="hidden" class="form-control" id="txt_id" name="txt_id" value="" readonly>
              </div>

              <div class="form-group mb-3">
                <label for="txt_area" class="form-label fw-bold">Area Lokasi</label>
                <select class="form-control select2bs4" id="txt_area" name="txt_area" style="width: 100%;">
                  <option selected value="">Pilih Area</option>
                  @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">{{ $alok->area }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group mb-3">
                <label for="txt_inisial_new" class="form-label fw-bold">Inisial Penyimpanan</label>
                <input type="text" class="form-control" id="txt_inisial" name="txt_inisial" oninput="this.value = this.value.toUpperCase(); setinisial_edit();">
              </div>

              <div class="form-group mb-3">
                <label for="txt_baris_new" class="form-label fw-bold">Baris</label>
                <input type="number" class="form-control" id="txt_baris" name="txt_baris" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial_edit(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_level_new" class="form-label fw-bold">Tingkat</label>
                <input type="number" class="form-control" id="txt_level" name="txt_level" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial_edit(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>
            </div>

            <!-- Kanan -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="txt_subbaris_new" class="form-label fw-bold">Sub Baris</label>
                <input type="number" class="form-control" id="txt_subbaris" name="txt_subbaris" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial_edit(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_sublevel_new" class="form-label fw-bold">Sub Tingkat</label>
                <input type="number" class="form-control" id="txt_sublevel" name="txt_sublevel" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial_edit(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_capacity_new" class="form-label fw-bold">Kapasitas</label>
                <input type="number" class="form-control" id="txt_capacity" name="txt_capacity" min="0">
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Tutup
          </button>
          <button type="submit" class="btn btn-sb toastsDefaultDanger">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </div>
    </div>
  </form>
</div>


<!-- Modal Tambah Lokasi -->
<div class="modal fade" id="modal-tambah-lokasi" tabindex="-1" aria-hidden="true">
  <form action="{{ route('store-lokasi') }}" method="post" onsubmit="submitForm(this, event)">
    @csrf
    @method('POST')

    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <!-- Header -->
        <div class="modal-header bg-sb text-light">
          <h5 class="modal-title mb-0">
            <i class="fas fa-map-marker-alt me-2"></i> Tambah Lokasi
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body bg-light">
          <div class="row g-3">
            <!-- Kiri -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="kode_lok" class="form-label fw-bold">Kode Lokasi</label>
                <input type="text" class="form-control" id="kode_lok" name="kode_lok" value="" readonly>
              </div>

              <div class="form-group mb-3">
                <label for="txt_area_new" class="form-label fw-bold">Area Lokasi</label>
                <select class="form-control select2bs4" id="txt_area_new" name="txt_area_new" style="width: 100%;">
                  <option selected value="">Pilih Area</option>
                  @foreach ($arealok as $alok)
                    <option value="{{ $alok->area }}">{{ $alok->area }}</option>
                  @endforeach
                </select>
              </div>

              <div class="form-group mb-3">
                <label for="txt_inisial_new" class="form-label fw-bold">Inisial Penyimpanan</label>
                <input type="text" class="form-control" id="txt_inisial_new" name="txt_inisial_new" oninput="this.value = this.value.toUpperCase(); setinisial();">
              </div>

              <div class="form-group mb-3">
                <label for="txt_baris_new" class="form-label fw-bold">Baris</label>
                <input type="number" class="form-control" id="txt_baris_new" name="txt_baris_new" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_level_new" class="form-label fw-bold">Tingkat</label>
                <input type="number" class="form-control" id="txt_level_new" name="txt_level_new" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>
            </div>

            <!-- Kanan -->
            <div class="col-md-6">
              <div class="form-group mb-3">
                <label for="txt_subbaris_new" class="form-label fw-bold">Sub Baris</label>
                <input type="number" class="form-control" id="txt_subbaris_new" name="txt_subbaris_new" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_sublevel_new" class="form-label fw-bold">Sub Tingkat</label>
                <input type="number" class="form-control" id="txt_sublevel_new" name="txt_sublevel_new" min="0" max="99" onfocus="clearIfZero(this)" oninput="setinisial(); limitTwoDigits(this);" onblur="padZero(this)">
              </div>

              <div class="form-group mb-3">
                <label for="txt_capacity_new" class="form-label fw-bold">Kapasitas</label>
                <input type="number" class="form-control" id="txt_capacity_new" name="txt_capacity_new" min="0">
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-white border-top">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
            <i class="fa fa-times"></i> Tutup
          </button>
          <button type="submit" class="btn btn-sb toastsDefaultDanger">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
          </button>
        </div>
      </div>
    </div>
  </form>
</div>

@endsection

@section('custom-script')
<!-- DataTables & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

<!-- Select2 -->
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script>
$('.select2master').select2({
            theme: 'bootstrap4'
        })
$('.select2bs4').select2({
            theme: 'bootstrap4'
})

$('.select2roll').select2({
            theme: 'bootstrap4'
})
</script>

<script type="text/javascript">
    function setinisial() {
  const ini = $('#txt_inisial_new').val().toUpperCase().trim();
  const row = $('#txt_baris_new').val().trim().slice(0, 2);
  const level = $('#txt_level_new').val().trim().slice(0, 2);
  const subrow = $('#txt_subbaris_new').val().trim().slice(0, 2);
  const sublevel = $('#txt_sublevel_new').val().trim().slice(0, 2);

  const parts = [ini, row, level, subrow, sublevel].filter(Boolean);
  $('#kode_lok').val(parts.join('.'));
  $('#txt_inisial_new').val(ini);
}

    function setinisial_edit() {
  const ini = $('#txt_inisial').val().toUpperCase().trim();
  const row = $('#txt_baris').val().trim().slice(0, 2);
  const level = $('#txt_level').val().trim().slice(0, 2);
  const subrow = $('#txt_subbaris').val().trim().slice(0, 2);
  const sublevel = $('#txt_sublevel').val().trim().slice(0, 2);

  const parts = [ini, row, level, subrow, sublevel].filter(Boolean);
  $('#kode_lok_edit').val(parts.join('.'));
  $('#txt_inisial').val(ini);
}


</script>

<script>
function clearIfZero(el) {
  if (el.value === '') {
    el.value = '';
  }
}

function limitTwoDigits(el) {
  let val = el.value.replace(/\D/g, '');
  if (val.length > 2) val = val.slice(0, 2);
  el.value = val;
}

function padZero(el) {
  if (el.value === '') {
    el.value = '';
  }
}
</script>

<script>
    let datatable = $("#datatable").DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        ajax: {
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: '{{ route('master-lokasi') }}',
            dataType: 'json',
            dataSrc: 'data',
            data: function(d) {
                d.area = $('#area').val();
            },
        },
        columns: [{
                data: 'area_lok'
            },
            {
                data: 'kode_lok'
            },
            {
                data: 'unit'
            },
            {
                data: 'kapasitas'
            },
            {
                data: 'create_user'
            },
            {
                data: 'status'
            },
            {
                data: 'id'
            },

        ],
        columnDefs: [{
                targets: [2],
                visible: false,
                render: (data, type, row, meta) => data ? data.toUpperCase() : "-"
            },
            {
                targets: [6],
                render: (data, type, row, meta) => {
                    console.log(row);
                    if (row.status == 'Active') {
                    return `<div class='d-flex gap-1 justify-content-center'>
                   <button type='button' class='btn btn-sm btn-warning' href='javascript:void(0)' onclick='editdata("` + row.id + `","` + row.kapasitas + `","` + row.inisial_lok + `","` + row.baris_lok + `","` + row.level_lok + `","` + row.no_lok + `","` + row.area_lok + `","` + row.unit + `","` + row.unit_roll + `","` + row.unit_bundle + `","` + row.unit_box + `","` + row.unit_pack + `","` + row.kode_lok + `","` + row.subbaris_lok + `","` + row.sublevel_lok + `")'><i class="fa-solid fa-pen-to-square"></i></button>
                    <button type='button' class='btn btn-sm btn-info' onclick='printlokasi("` + row.id + `")'><i class='fa fa-file-pdf'></i></button>
                    <button type='button' class='btn btn-sm btn-success' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-unlock-alt'></i></button>
                    </div>`;
                    }else{
                        return `<div class='d-flex gap-1 justify-content-center'>
                    <button type='button' class='btn btn-sm btn-danger' href='javascript:void(0)' onclick='nonactive_lokasi("` + row.id + `","` + row.status + `","` + row.kode_lok + `")'><i class='fa fa-lock'></i></button>
                    </div>`;
                    }
                }
            }
        ]
    });

    function dataTableReload() {
        datatable.ajax.reload();
    }
</script>
<script type="text/javascript">
    function nonactive_lokasi($id,$status,$kode_lok){
        // alert($id);
        let id  = $id;
        let status  = $status;
        let kode  = $kode_lok;
        let idnya  = $id;

    $('#txt_kode_lok').val(kode);
    $('#id_lok').val(idnya);
    $('#status_lok').val(status);
    $('#modal-active-lokasi').modal('show');
    }


    function editdata($id,$kapasitas,$inisial_lok,$baris,$level,$nomor,$area,$unit,$u_roll,$u_bundle,$u_box,$u_pack,$kode_lok,$subbaris,$sublevel){
        // alert($id);
        $("#ROLL_edit").prop("checked", false);
        $("#BUNDLE_edit").prop("checked", false);
        $("#BOX_edit").prop("checked", false);
        $("#PACK_edit").prop("checked", false);
        let kapasitas  = $kapasitas;
        let inisial_lok  = $inisial_lok;
        let idnya  = $id;
        let baris  = $baris;
        let level  = $level;
        let nomor  = $nomor;
        let area  = $area;
        let unit  = $unit;
        let u_roll  = $u_roll;
        let u_bundle  = $u_bundle;
        let u_box  = $u_box;
        let u_pack  = $u_pack;
        let kode_lok  = $kode_lok;
        let subbaris  = $subbaris;
        let sublevel  = $sublevel;

        console.log(u_roll);

        if (u_roll == 'ROLL') {
            $("#ROLL_edit").prop("checked", true);
        }

        if (u_bundle == 'BUNDLE') {
            $("#BUNDLE_edit").prop("checked", true);
        }

        if (u_box == 'BOX') {
            $("#BOX_edit").prop("checked", true);
        }

        if (u_pack == 'PACK') {
            $("#PACK_edit").prop("checked", true);
        }

    $('#kode_lok_edit').val(kode_lok);
    $('#txt_id').val(idnya);
    $('#txt_inisial').val(inisial_lok);
    $('#txt_capacity').val(kapasitas);
    $('#txt_baris').val(baris);
    $('#txt_level').val(level);
    $('#txt_subbaris').val(subbaris);
    $('#txt_sublevel').val(sublevel);
    const select = document.getElementById('txt_area');
select.value = area;
select.dispatchEvent(new Event('change'));
    $('#modal-edit-lokasi').modal('show');
    }

    function tambahdata(){
        $('#txt_baris_new').val('');
        $('#txt_level_new').val('');
        $('#txt_subbaris_new').val('');
        $('#txt_sublevel_new').val('');
        $('#txt_inisial_new').val('');
    $('#modal-tambah-lokasi').modal('show');
    }
</script>

<script type="text/javascript">
    function printlokasi(id) {
  $.ajax({
    url: '{{ route('print-lokasi') }}/' + id,
    type: 'post',
    processData: false,
    contentType: false,
    xhrFields: {
      responseType: 'blob'
    },
    success: function(res) {
      if (res) {
        const blob = new Blob([res], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        // buka di tab/jendela baru
        window.open(url, '_blank');
      }
    }
  });
}

function printAllLokasi() {
  Swal.fire({
    title: 'Mencetak...',
    text: 'Mohon tunggu, sedang menyiapkan data lokasi.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  $.ajax({
    url: '{{ route('print-lokasi-all') }}',
    type: 'POST',
    processData: false,
    contentType: false,
    xhrFields: { responseType: 'blob' },
    success: function(res) {
      Swal.close(); // Tutup loading Swal

      if (res) {
        const blob = new Blob([res], { type: 'application/pdf' });
        const url = window.URL.createObjectURL(blob);
        window.open(url, '_blank'); // buka di tab baru

        Swal.fire({
          icon: 'success',
          title: 'Berhasil!',
          text: 'File lokasi berhasil dicetak.',
          timer: 2000,
          showConfirmButton: false
        });
      }
    },
    error: function() {
      Swal.close();
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Gagal mencetak semua lokasi!',
      });
    }
  });
}



</script>
@endsection
