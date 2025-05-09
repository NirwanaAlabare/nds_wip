@extends('layouts.index')

@section('custom-link')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

    <style>
        .image-frame {
            width: 100%;
            min-height: 300px;
            border: 2px dashed #ccc;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-family: sans-serif;
            background-color: #f9f9f9;
            padding: 20px;
        }

        /* Defect Image */
        .scroll-defect-area-img {
            max-width: 100%;
            overflow: auto;
        }

        .all-defect-area-img-container {
            position: relative;
            display: inline-block;
            justify-content: center;
            align-items: center;
            margin: auto;
            width: 100%;
            overflow: hidden;
        }

        .all-defect-area-img {
            width: 100%;
            height: auto;
        }

        .all-defect-area-img-point {
            position: absolute;
            width: 50px;
            height: 50px;
            background-color: var(--defect-color);
            border: 3px solid var(--defect-color-sec);
            border-radius: 50%;
            opacity: 80%;
        }
    </style>
@endsection

@section('content')
    <div class="card card-sb">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fa-solid fa-map-pin"></i> Defect Map
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <label class="form-label">Dari </label>
                    <input type="date" class="form-control" id="dateFrom" name="dateFrom" value="{{ date("Y-m-d") }}" onchange="updateFilterOption();">
                </div>
                <div>
                    <label class="form-label">Sampai </label>
                    <input type="date" class="form-control" id="dateTo" name="dateTo" value="{{ date("Y-m-d") }}" onchange="updateFilterOption();">
                </div>
                <div>
                    <label class="form-label">Defect Type</label>
                    <select class="form-select form-select-sm select2bs4" name="defect_types[]" id="defect_types" multiple="multiple">
                        <option value="">Pilih Defect Type</option>
                        @foreach ($defectTypes as $defectType)
                            <option value="{{ $defectType->id }}">{{ $defectType->id." - ".$defectType->defect_type }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Defect Area</label>
                    <select class="form-select form-select-sm select2bs4" name="defect_areas[]" id="defect_areas" multiple="multiple">
                        <option value="">Pilih Defect Area</option>
                        @foreach ($defectAreas as $defectArea)
                            <option value="{{ $defectArea->id }}">{{ $defectArea->id." - ".$defectArea->defect_area }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="btn btn-sb-secondary" data-bs-toggle="modal" data-bs-target="#filterModal"><i class="fa fa-filter"></i></button>
                <button class="btn btn-primary" onclick="showDefectMap()"><i class="fa fa-search"></i></button>
                <button class="btn btn-danger" onclick="exportToPDF()"><i class="fa fa-file-pdf"></i></button>
                {{-- <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#reportDefectModal"><i class="fa fa-file-excel"></i></button> --}}
            </div>
            <div class="d-flex justify-content-center align-items-center gap-3 my-3">
                <div>
                    <select class="form-select" name="department" id="department" onchange="showDefectMap()">
                        <option value="" selected>END-LINE</option>
                        <option value="packing">PACKING-LINE</option>
                    </select>
                </div>
            </div>
            <div class="row g-3 mt-3" id="defect-map-images">
                <div class="col-md-8">
                    <div class="image-frame" id="defect-map-placeholder">
                        Defect Map
                    </div>
                    <div class="scroll-defect-area-img" id="defect-map-image">
                        <div class="all-defect-area-img-container">
                            {{-- <div class="all-defect-area-img-point" data-x="{{ floatval($defectPosition->defect_area_x) }}" data-y="{{ floatval($defectPosition->defect_area_y) }}"></div>
                            <img src="http://10.10.5.62:8080/erp/pages/prod_new/upload_files/{{ $allDefectImage->gambar }}" class="all-defect-area-img" id="all-defect-area-img" alt="defect image"> --}}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5 class="text-sb">Defect List</h5>
                    <ol>
                        <li>...</li>
                        <li>...</li>
                        <li>...</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="reportDefectModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="filterModalLabel"><i class="fa fa-filter"></i> Filter</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Defect Status</label>
                        <select class="select2bs4filter" name="defect_status[]" multiple="multiple" id="defect_status">
                            <option value="">SEMUA</option>
                            <option value="defect">DEFECT</option>
                            <option value="reworked">REWORKED</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sewing Line</label>
                        <select class="select2bs4filter" name="sewing_line[]" multiple="multiple" id="sewing_line">
                            <option value="">SEMUA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Buyer</label>
                        <select class="select2bs4filter" name="buyer[]" multiple="multiple" id="buyer">
                            <option value="">Buyer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. WS</label>
                        <select class="select2bs4filter" name="ws[]" multiple="multiple" id="ws">
                            <option value="">SEMUA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Style</label>
                        <select class="select2bs4filter" name="style[]" multiple="multiple" id="style">
                            <option value="">SEMUA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Color</label>
                        <select class="select2bs4filter" name="color[]" multiple="multiple" id="color">
                            <option value="">SEMUA</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Size</label>
                        <select class="select2bs4filter" name="size[]" multiple="multiple" id="size">
                            <option value="">SEMUA</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Bersihkan <i class="fa-solid fa-broom"></i></button>
                    <button type="button" class="btn btn-success" onclick="showDefectMap()">Simpan <i class="fa-solid fa-check"></i></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Defect -->
    <div class="modal fade" id="reportDefectModal" tabindex="-1" aria-labelledby="reportDefectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-sb">
                    <h1 class="modal-title fs-5" id="reportDefectModalLabel">Report Defect</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label">Report Type</label>
                    <select class="select2bs4report" name="report_type[]" id="report_type" multiple="multiple">
                        <option value="defect_rate">Defect Rate</option>
                        <option value="top_defect">Top Defect</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="exportExcel(this)"><i class="fa fa-file-excel"></i> Export</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    <!-- DataTables & Plugins -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables-rowsgroup/dataTables.rowsGroup.js') }}"></script>

    <!-- Select2 -->
    <script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>

    {{-- PDF --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        $(document).ready(function () {
            updateFilterOption();
        });

        // Initialize Select2 Elements
        $('.select2').select2();

        // Initialize Select2BS4 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4',
        });

        // Initialize Select2BS4 Elements Filter Modal
        $('.select2bs4filter').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#filterModal")
        });

        $('.select2bs4report').select2({
            theme: 'bootstrap4',
            dropdownParent: $("#reportDefectModal",)
        });

        function rainbowStop(h) {
            let f = (n, k = (n + h * 12) % 12) => 0.5 - 0.5 * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            let rgb2hex = (r, g, b) =>
                "#" +
                [r, g, b]
                .map(x =>
                    Math.round(x * 255)
                    .toString(16)
                    .padStart(2, 0)
                )
                .join("");

            return rgb2hex(f(0), f(8), f(4));
        }

        async function updateFilterOption() {
            document.getElementById('loading').classList.remove('d-none');

            $.ajax({
                url: '{{ route('filter-defect') }}',
                dataType: 'json',
                dataSrc: 'data',
                data: {
                    dateFrom : $('#dateFrom').val(),
                    dateTo : $('#dateTo').val()
                },
                success: function(response) {
                    document.getElementById('loading').classList.add('d-none');

                    if (response) {
                        // lines options
                        if (response.lines && response.lines.length > 0) {
                            let lines = response.lines;
                            $('#sewing_line').empty();
                            $.each(lines, function(index, value) {
                                $('#sewing_line').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // suppliers option
                        if (response.suppliers && response.suppliers.length > 0) {
                            let suppliers = response.suppliers;
                            $('#buyer').empty();
                            $.each(suppliers, function(index, value) {
                                $('#buyer').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // orders option
                        if (response.orders && response.orders.length > 0) {
                            let orders = response.orders;
                            $('#ws').empty();
                            $.each(orders, function(index, value) {
                                $('#ws').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // styles option
                        if (response.styles && response.styles.length > 0) {
                            let styles = response.styles;
                            $('#style').empty();
                            $.each(styles, function(index, value) {
                                $('#style').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // colors option
                        if (response.colors && response.colors.length > 0) {
                            let colors = response.colors;
                            $('#color').empty();
                            $.each(colors, function(index, value) {
                                $('#color').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                        // sizes option
                        if (response.sizes && response.sizes.length > 0) {
                            let sizes = response.sizes;
                            $('#size').empty();
                            $.each(sizes, function(index, value) {
                                $('#size').append('<option value="'+value+'">'+value+'</option>');
                            });
                        }
                    }
                },
                error: function(jqXHR) {
                    document.getElementById('loading').classList.add('d-none');

                    console.error(jqXHR);
                },
            })
        }

        async function showDefectMap() {
            document.getElementById("loading").classList.remove("d-none");

            await $.ajax({
                url: "{{ route('defect-map-data') }}",
                type: "get",
                data: {
                    dateFrom: $('#dateFrom').val(),
                    dateTo: $('#dateTo').val(),
                    defect_types: $('#defect_types').val(),
                    defect_areas: $('#defect_areas').val(),
                    defect_status : $('#defect_status').val(),
                    sewing_line : $('#sewing_line').val(),
                    buyer : $('#buyer').val(),
                    ws : $('#ws').val(),
                    style : $('#style').val(),
                    color : $('#color').val(),
                    size : $('#size').val(),
                    department : $('#department').val(),
                },
                dataType: "json",
                success: async function (response) {
                    await placeDefectPoint(response);

                    // After image loaded
                    let defectAreaImagePoints = document.getElementsByClassName("all-defect-area-img-point");
                    for (let i = 0; i < defectAreaImagePoints.length; i++) {
                        // Get the parent
                        const parent = defectAreaImagePoints[i].parentElement;

                        // Search inside the parent for the desired class
                        const image = await parent.querySelector('.all-defect-area-img');

                        if (image.complete && image.naturalWidth !== 0) {
                            console.log("✅ Image loaded successfully");

                            let rect = image.getBoundingClientRect();
                            // Image point
                            defectAreaImagePoints[i].style.width = 0.03 * rect.width+'px';
                            defectAreaImagePoints[i].style.height = defectAreaImagePoints[i].style.width;
                            defectAreaImagePoints[i].style.left = (defectAreaImagePoints[i].offsetLeft - (0.015 * rect.width))+'px';
                            defectAreaImagePoints[i].style.top = (defectAreaImagePoints[i].offsetTop - (0.015 * rect.width))+'px';
                        } else {
                            console.log("❌ Image not loaded or failed");
                        }
                    }
                }
            });

            document.getElementById("loading").classList.add("d-none");
        }

        // async function placeDefectPoint(data) {
        //     let dataGroup = Object.entries(
        //         Object.groupBy(data, ({ styleno, gambar }) => `${styleno}||${gambar}`)
        //     ).map(([key, items]) => {
        //         const [styleno, gambar] = key.split('||');
        //         return {
        //             styleno,
        //             gambar,
        //             items
        //         };
        //     });

        //     let parentElement = document.getElementById("defect-map-images");
        //     parentElement.innerHTML = '';

        //     dataGroup.forEach(async function (item) {
        //         // Title
        //         let defectAreaImageTitle = document.createElement('h3');
        //         defectAreaImageTitle.classList.add('text-sb');
        //         defectAreaImageTitle.classList.add('fw-bold');
        //         defectAreaImageTitle.innerHTML = item.styleno;

        //         // Defect Area Image
        //         let scrollDefectAreaImageContainer = document.createElement('div');
        //         scrollDefectAreaImageContainer.classList.add("col-md-8");

        //         let scrollDefectAreaImage = document.createElement('div');
        //         scrollDefectAreaImage.classList.add("scroll-defect-area-img");
        //         scrollDefectAreaImage.classList.add("image-frame");

        //         let defectAreaImageContainer = document.createElement('div');
        //         defectAreaImageContainer.classList.add("all-defect-area-img-container");

        //         let defectAreaImage = new Image();
        //         defectAreaImage.classList.add("all-defect-area-img");
        //         defectAreaImage.src = 'http://10.10.5.62:8080/erp/pages/prod_new/upload_files/'+item.gambar;
        //         defectAreaImageContainer.appendChild(defectAreaImage);

        //         // List
        //         let defectTypeGroup = Object.entries(
        //             Object.groupBy(item.items, ({ defect_type, }) => defect_type )).map(([defect_type, items]) => ({
        //                 defect_type: defect_type,
        //                 items
        //             })
        //         ).sort((a, b) => b.items.length - a.items.length);

        //         let listContainer = document.createElement('div');
        //         listContainer.classList.add("col-md-4");
        //         let listTitle = document.createElement('h5');
        //         listTitle.classList.add("text-sb");
        //         listTitle.innerHTML = 'Defect List';
        //         let listOrder = document.createElement('ol');

        //         defectTypeGroup.forEach(item => {
        //             let list = document.createElement('li');
        //             list.innerHTML =  item.defect_type+" ("+item.items.length+")";

        //             listOrder.appendChild(list);
        //         });

        //         listContainer.appendChild(listTitle);
        //         listContainer.appendChild(listOrder);

        //         // Append to Parent
        //         parentElement.appendChild(defectAreaImageTitle);
        //         parentElement.appendChild(scrollDefectAreaImageContainer);
        //         parentElement.appendChild(listContainer);

        //         defectAreaImage.onload = function () {
        //             console.log(defectAreaImage);
        //             let rect = defectAreaImage.getBoundingClientRect();

        //             // You can now safely use rect
        //             console.log("Image dimensions:", rect.width, rect.height, defectAreaImage);

        //             // For Defect Items
        //             for(i = 0; i < item.items.length; i++) {
        //                 let defectAreaImagePoint = document.createElement('img');
        //                 defectAreaImagePoint.classList.add("all-defect-area-img-point");

        //                 defectAreaImagePoint.style.width = 0.03 * rect.width+'px';
        //                 defectAreaImagePoint.style.height = defectAreaImagePoint.style.width;
        //                 defectAreaImagePoint.style.left =  'calc('+item.items[i].defect_area_x+'% - '+0.015 * rect.width+'px)';
        //                 defectAreaImagePoint.style.top =  'calc('+item.items[i].defect_area_y+'% - '+0.015 * rect.width+'px)';

        //                 defectAreaImageContainer.appendChild(defectAreaImagePoint);
        //             }
        //             scrollDefectAreaImage.appendChild(defectAreaImageContainer);
        //             scrollDefectAreaImageContainer.appendChild(scrollDefectAreaImage);
        //         };

        //         // If the image is already cached (i.e., complete), trigger the onload manually
        //         if (defectAreaImage.complete && defectAreaImage.naturalWidth !== 0) {
        //             defectAreaImage.onload(); // Trigger the onload event manually if the image is already cached
        //         }
        //     });
        // }

        async function placeDefectPoint(data) {
            let dataGroup = Object.entries(
                Object.groupBy(data, ({ styleno, gambar }) => `${styleno}||${gambar}`)
            ).map(([key, items]) => {
                const [styleno, gambar] = key.split('||');
                return {
                    styleno,
                    gambar,
                    items
                };
            });

            let parentElement = document.getElementById("defect-map-images");
            parentElement.innerHTML = ''; // Clear previous content

            var colorList = ['#e31010', '#104fe3', '#ebcd0c', '#830ceb', '#12e02a', '#ed790c', '#f54980', '#10ded7', '#854008', '#bdbdbd'];

            for (const item of dataGroup) {
                // Title
                let defectAreaImageTitle = document.createElement('h3');
                defectAreaImageTitle.classList.add('text-sb', 'fw-bold');
                defectAreaImageTitle.innerHTML = item.styleno;

                // Defect Area Image
                let scrollDefectAreaImageContainer = document.createElement('div');
                scrollDefectAreaImageContainer.classList.add("col-md-8");

                let scrollDefectAreaImage = document.createElement('div');
                scrollDefectAreaImage.classList.add("scroll-defect-area-img", "image-frame");

                let defectAreaImageContainer = document.createElement('div');
                defectAreaImageContainer.classList.add("all-defect-area-img-container");

                let defectAreaImage = new Image();
                defectAreaImage.classList.add("all-defect-area-img");
                defectAreaImage.src = '/proxy/?url=http://10.10.5.62:8080/erp/pages/prod_new/upload_files/' + item.gambar;

                defectAreaImageContainer.appendChild(defectAreaImage);

                // List
                let defectTypeGroup = Object.entries(
                    Object.groupBy(item.items, ({ defect_type, defect_type_id }) => `${defect_type}||${defect_type_id}`)
                ).map(([key, items]) => {
                    const [defect_type, defect_type_id] = key.split('||');
                    return {
                        defect_type,
                        defect_type_id,
                        items
                    };
                }).sort((a, b) => b.items.length - a.items.length);

                let listContainer = document.createElement('div');
                listContainer.classList.add("col-md-4");

                let listTitle = document.createElement('h5');
                listTitle.classList.add("text-sb");
                listTitle.innerHTML = 'Defect List';

                let listOrder = document.createElement('ol');
                defectTypeGroup.forEach((item, index) => {
                    if (index <= 9) {
                        let list = document.createElement('li');
                        list.innerHTML = `${item.defect_type} (${item.items.length})`;

                        let badge = document.createElement('span');
                        badge.style.display = 'inline-block';
                        badge.style.width = '15px';
                        badge.style.height = '15px';
                        badge.style.borderRadius = '50%';
                        badge.style.background = index > 9 ? '#bdbdbd' : colorList[index];
                        badge.style.borderColor = index > 9 ? '#bdbdbd' : colorList[index];
                        badge.style.margin = '0 3px';
                        badge.style.position = 'relative';
                        badge.style.top = '2px';
                        badge.style.opacity = '90%';

                        list.appendChild(badge);
                        listOrder.appendChild(list);
                    }
                });

                listContainer.appendChild(listTitle);
                listContainer.appendChild(listOrder);

                // Append to Parent
                parentElement.appendChild(defectAreaImageTitle);
                parentElement.appendChild(scrollDefectAreaImageContainer);
                parentElement.appendChild(listContainer);

                // Wait for the image to load
                await new Promise(resolve => {
                    defectAreaImage.onload = function () {
                        let rect = defectAreaImage.getBoundingClientRect();
                        console.log("Image dimensions:", rect.width, rect.height, defectAreaImage);

                        // For Defect Items
                        for(let i = 0; i < item.items.length; i++) {
                            let defectAreaImagePoint = document.createElement('img');
                            defectAreaImagePoint.classList.add("all-defect-area-img-point");

                            let defectTypeIndexes = defectTypeGroup.map((type, index) => { return type.defect_type_id == item.items[i].defect_type_id ? index : -1; }).filter(index => index >= 0);

                            if (defectTypeIndexes.length > 0) {
                                defectAreaImagePoint.setAttribute('defect_type', item.items[i].defect_type_id);
                                defectAreaImagePoint.style.background = defectTypeIndexes[0] > 9 ? '#bdbdbd' : colorList[defectTypeIndexes[0]];
                                defectAreaImagePoint.style.borderColor = defectTypeIndexes[0] > 9 ? '#bdbdbd' : colorList[defectTypeIndexes[0]];
                                defectAreaImagePoint.style.width = 0.03 * rect.width + 'px';
                                defectAreaImagePoint.style.height = defectAreaImagePoint.style.width;
                                defectAreaImagePoint.style.left = `calc(${item.items[i].defect_area_x}% - ${0.015 * rect.width}px)`;
                                defectAreaImagePoint.style.top = `calc(${item.items[i].defect_area_y}% - ${0.015 * rect.width}px)`;

                                defectAreaImageContainer.appendChild(defectAreaImagePoint);
                            }
                        };

                        scrollDefectAreaImage.appendChild(defectAreaImageContainer);
                        scrollDefectAreaImageContainer.appendChild(scrollDefectAreaImage);
                        resolve(); // Resolve the promise once loading is done
                    };

                    // Trigger onload manually if image is already cached
                    if (defectAreaImage.complete && defectAreaImage.naturalWidth !== 0) {
                        defectAreaImage.onload(); // Trigger the onload event if cached
                    }
                });
            }
        }

        async function exportToPDF() {
            document.getElementById("loading").classList.remove("d-none");

            const { jsPDF } = window.jspdf;

            const element = document.getElementById('defect-map-images');
            const canvas = await html2canvas(element, { useCORS: true, allowTaint: true });
            const imgData = canvas.toDataURL('image/png');

            const imgWidth = canvas.width;
            const imgHeight = canvas.height;
            const aspectRatio = imgWidth / imgHeight;

            // A4 standard sizes
            const a4Portrait = { width: 210, height: 297 };
            const a4Landscape = { width: 297, height: 210 };

            // Choose orientation based on aspect ratio
            const isLandscape = aspectRatio > 1;

            // Set base PDF size
            let pdfWidth = isLandscape ? a4Landscape.width : a4Portrait.width;
            let pdfHeight = pdfWidth / aspectRatio;

            // If height exceeds page size, scale based on height
            const maxPdfHeight = isLandscape ? a4Landscape.height : a4Portrait.height;
            if (pdfHeight > maxPdfHeight) {
                pdfHeight = maxPdfHeight;
                pdfWidth = pdfHeight * aspectRatio;
            }

            // Enforce a minimum width so narrow PDFs look okay
            const minPdfWidth = 210; // keep it readable
            if (pdfWidth < minPdfWidth) {
                pdfWidth = minPdfWidth;
                pdfHeight = pdfWidth / aspectRatio;
            }

            // Create the PDF with calculated size and orientation
            const pdf = new jsPDF(isLandscape ? 'l' : 'p', 'mm', [pdfWidth, pdfHeight]);

            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save('defect-map.pdf');

            document.getElementById("loading").classList.add("d-none");
        }


        async function exportToImage() {
            const element = document.getElementById('defect-map-images');
            const canvas = await html2canvas(element);
            const image = canvas.toDataURL('image/png');

            const link = document.createElement('a');
            link.href = image;
            link.download = 'defect-map.png';
            link.click();
        }

        // async function exportToPDF() {
        //     const element = document.getElementById('defect-map-images');

        //     // Opsi untuk meningkatkan kualitas PDF
        //     var opt = {
        //         margin: [15, 0, 15, 0],
        //         filename: 'defect map.pdf',
        //         image: { type: 'jpeg', quality: 1 },
        //         html2canvas: {
        //             dpi: 192, // Resolusi DPI
        //             scale: 4, // Skala untuk meningkatkan kualitas
        //             letterRendering: true,
        //             useCORS: true // Mengizinkan penggunaan CORS
        //         },
        //         jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        //     };

        //     // Menghasilkan PDF dengan opsi yang ditentukan
        //     html2pdf()
        //         .from(element)
        //         .set(opt) // Mengatur opsi
        //         .save()
        //         .then(function() {
        //         window.close(); // Tutup jendela setelah mengunduh PDF
        //     });
        // }
    </script>
@endsection
