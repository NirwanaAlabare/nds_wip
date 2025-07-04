@extends('layouts.index')

@section('custom-link')
<!-- DataTables -->
<link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<!-- SweetAlert2 -->
<link rel="stylesheet" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
@endsection

@section('content')
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">Generate Roll</h5>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4" style="display: none;">
                <div class="form-group">
                    <label>ID WHS Lokasi Inmaterial</label>
                    <input type="text" class="form-control" id="id_whs_lokasi_inmaterial" col-md-4>
                </div>
            </div>
            <div class="col-md-4" style="display: none;">
                <div class="form-group">
                    <label>No Dok</label>
                    <input type="text" class="form-control" id="no_dok" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Tanggal PL</label>
                    <input type="text" class="form-control" id="tgl_pl" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>No PL</label>
                    <input type="text" class="form-control" id="no_pl" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>No Lot</label>
                    <input type="text" class="form-control" id="no_lot" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Color</label>
                    <input type="text" class="form-control" id="color" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>ID Item</label>
                    <input type="text" class="form-control" id="id_item" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Supplier</label>
                    <input type="text" class="form-control" id="supplier" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Buyer</label>
                    <input type="text" class="form-control" id="buyer" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Style</label>
                    <input type="text" class="form-control" id="style" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Qty Roll</label>
                    <input type="number" class="form-control" id="qty_roll" col-md-4>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>Notes</label>
                    <input type="text" class="form-control" id="notes" col-md-4>
                </div>
            </div>
            <div class="col-md-4 ">
                    <div class="form-group">
                               <button class="btn btn-success mt-4" id="generate-roll">
                                <i class="fas fa-cog"></i> Generate
                               </button>       
                </div>  
            </div>
                <div class="col-md-4 ">
                           <div class="form-group">
                               <a href="{{ route('qc-inspect-inmaterial-header') }}" class="btn btn-danger float-end mt-4">
                                <i class="fas fa-arrow-circle-left"></i> Kembali</a>
                                <div style="width:50px">
                                </div>   
                              
                         
                            </div>
                </div>             
            </div>
        </div>

    

        <div class="table-responsive mt-3">
            <table class="table table-bordered" id="rolls-table">
                <thead>
                    <tr>
                        <th>No Roll</th>
                        <th>No Barcode</th>
                        <th>Item</th>
                        <th>Color</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be loaded dynamically by DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<!-- DataTables & Plugins -->
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>

<!-- SweetAlert2 -->
<script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
$(document).ready(function() {
    // Get data from localStorage
    const inspectionData = JSON.parse(localStorage.getItem('selectedInspection'));

    console.log("inspectionData", inspectionData);
    
    // Populate the form fields
    if (inspectionData) {
        $('#id_whs_lokasi_inmaterial').val(inspectionData.id_whs_lokasi_inmaterial);
        $('#tgl_pl').val(inspectionData.tgl_pl);
        $('#no_pl').val(inspectionData.no_pl);
        $('#no_lot').val(inspectionData.no_lot);
        $('#color').val(inspectionData.color);
        $('#supplier').val(inspectionData.supplier);
        $('#buyer').val(inspectionData.buyer);
        $('#style').val(inspectionData.style);
        $('#qty_roll').val(inspectionData.qty_roll);
        $('#notes').val(inspectionData.notes);
        $('#id_item').val(inspectionData.id_item);
        $('#no_dok').val(inspectionData.no_dok);
    }
    
    // Initialize DataTable
    var rollsTable = $('#rolls-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('qc-inspect-inmaterial-getDataRolltables') }}",
            type: "GET",
            data: function(d) {
                d.no_pl = $('#no_pl').val();
                d.no_lot = $('#no_lot').val();
                d.id_item = $('#id_item').val();
            }
        },
        columns: [
            { data: 'no_roll', name: 'no_roll' },
            { data: 'no_barcode', name: 'no_barcode' },
            { data: 'itemdesc', name: 'b.itemdesc' },
            { data: 'color', name: 'b.color' },
            { 
                data: null,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-primary view-roll" data-id="'+row.no_roll+'">View</button>';
                }
            }
        ],
        responsive: true,
        lengthChange: false,
        autoWidth: false
    });
    
    // Generate button click handler
    $('#generate-roll').click(function() {
        // Reload the DataTable with the current filter values
        rollsTable.ajax.reload();
    });
    
    // Handle view button clicks
    $('#rolls-table').on('click', '.view-roll', function() {
        var rollId = $(this).data('id');
        // Implement your view functionality here
        alert('Viewing roll: ' + rollId);
    });
});
</script>
@endsection