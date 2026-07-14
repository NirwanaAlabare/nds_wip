@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs { border-bottom: none; }
    .nav-tabs .nav-item { margin-bottom: 0; margin-right: 5px; }
    .nav-tabs .nav-link {
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 8px 15px;
        font-size: 13px;
        transition: all 0.3s ease;
    }
    .nav-tabs .nav-link.active {
        font-weight: bold;
        background-color: #003366 !important;
        color: #ffffff !important;
        border-color: #003366 !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .nav-tabs .nav-link.active::after { display: none; }
    .nav-tabs .nav-link:not(.active) {
        color: #000000 !important;
        background-color: #ffffff;
    }
    .nav-tabs .nav-link:not(.active):hover {
        background-color: #f8f9fa;
        border-color: #ddd;
        color: #000000 !important;
    }
    .form-group label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 0.2rem;
    }
    .form-control-sm { font-size: 13px; }
    .section-title {
        font-size: 14px;
        font-weight: bold;
        color: #333;
        border-bottom: 2px solid #ddd;
        padding-bottom: 5px;
        margin-bottom: 15px;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
@php
    /**
     * sv() = "safe value"
     * Guards every Blade echo / attribute value against the classic
     * "htmlspecialchars(): Argument #1 ($string) must be of type string, array given"
     * error. Anywhere a value MIGHT come back as an array (relation instead of
     * scalar column, double-encoded JSON draft, duplicated form field names, etc.)
     * wrap it with sv() before echoing.
     *
     * - array with 'value' key   -> returns that value (recursively sv()'d)
     * - array with numeric keys  -> returns first element (recursively sv()'d)
     * - array (assoc, no match)  -> returns json_encode() so you SEE the problem
     *                               instead of a fatal error (easy to spot & fix data)
     * - object with __toString   -> cast to string
     * - object without __toString-> returns $default (avoids fatal)
     * - null / empty             -> returns $default
     */
    function sv($val, $default = '') {
        if ($val === null || $val === '') {
            return $default;
        }
        if (is_array($val)) {
            if (array_key_exists('value', $val)) {
                return sv($val['value'], $default);
            }
            if (array_key_exists(0, $val)) {
                return sv($val[0], $default);
            }
            // Unexpected associative array shape - don't fatal, surface it visibly
            return json_encode($val);
        }
        if (is_object($val)) {
            if (method_exists($val, '__toString')) {
                return (string) $val;
            }
            return $default;
        }
        return $val;
    }

    $listJenisKemasan = ['1A' => 'DRUM, STEEL', '1B' => 'DRUM, ALUMINIUM', '1D' => 'DRUM, PLYWOOD', '1F' => 'CONTAINER, FLEXIBLE', '1G' => 'DRUM, FIBRE', '1W' => 'DRUM, WOODEN', '2C' => 'BARREL, WOODEN', '3A' => 'JERRICAN, STEEL', '3H' => 'JERRICAN, PLASTIC', '43' => 'Bag, super bulk', '44' => 'Bag, polybag', '4A' => 'Box, steel', '4B' => 'Box, aluminium', '4C' => 'Box, natural wood', '4D' => 'Box, plywood', '4F' => 'Box, reconstituted wood', '4G' => 'Box, fibreboard', '4H' => 'Box, plastic', '5H' => 'Bag, woven plastic', '5L' => 'Bag, textile', '5M' => 'Bag, paper', '6H' => 'Composite packaging, plastic receptacle', '6P' => 'Composite packaging, glass receptacle', '7A' => 'Case, car', '7B' => 'Case, wooden', '8A' => 'Pallet, wooden', '8B' => 'Crate, wooden', '8C' => 'Bundle, wooden', 'AA' => 'Intermediate bulk container, rigid plastic', 'AB' => 'Receptacle, fibre', 'AC' => 'Receptacle, paper', 'AD' => 'Receptacle, wooden', 'AE' => 'Aerosol', 'AF' => 'Pallet, modular, collars 80cms * 60cms', 'AG' => 'Pallet, shrinkwrapped', 'AH' => 'Pallet, 100cms * 110cms', 'AI' => 'Clamshell', 'AJ' => 'Cone', 'AL' => 'Ball', 'AM' => 'Ampoule, non protected', 'AP' => 'Ampoule, protected', 'AT' => 'Atomizer', 'AV' => 'Capsule', 'B4' => 'Belt', 'BA' => 'Barrel', 'BB' => 'Bobbin', 'BC' => 'Bottlecrate, bottlerack', 'BD' => 'Board', 'BE' => 'Bundle', 'BF' => 'Balloon, non-protected', 'BG' => 'Bag', 'BH' => 'Bunch', 'BI' => 'Bin', 'BJ' => 'Bucket', 'BK' => 'Basket', 'BL' => 'Bale, compressed', 'BM' => 'Basin', 'BN' => 'Bale, non -compressed', 'BO' => 'Bottle, non-protected, cylindrical', 'BP' => 'Balloon, protected', 'BQ' => 'Bottle, protected cylindrical', 'BR' => 'Bar', 'BS' => 'Bottle, non-protected, bulbous', 'BT' => 'Bolt', 'BU' => 'Butt', 'BV' => 'Bottle, protected bulbous', 'BW' => 'Box, for liquids', 'BX' => 'Box', 'BY' => 'Board, in bundle/bunch/truss', 'BZ' => 'Bars, in bundle/bunch/truss', 'CA' => 'Can, rectangular', 'CB' => 'Beer crate', 'CC' => 'Churn', 'CD' => 'Can, with handle and spout', 'CE' => 'Creel', 'CF' => 'Coffer', 'CG' => 'Cage', 'CH' => 'Chest', 'CI' => 'Canister', 'CJ' => 'Coffin', 'CK' => 'Cask', 'CL' => 'Coil', 'CM' => 'Card', 'CN' => 'Cont,not otherwise specfied as transport equipment', 'CO' => 'Carboy, non-protected', 'CP' => 'Carboy, protected', 'CQ' => 'Cartridge', 'CR' => 'Crate', 'CS' => 'Case', 'CT' => 'Carton', 'CU' => 'Cup', 'CV' => 'Cover', 'CW' => 'Cage, roll', 'CX' => 'Can, cylindical', 'CY' => 'Cylinder', 'CZ' => 'Canvas', 'DA' => 'Crate, multiple layer, plastic', 'DB' => 'Crate, multiple layer, wooden', 'DC' => 'Crate, multiple layer, cardboard', 'DG' => 'Cage, Commonwealth Handling Equipment Pool (CHEP)', 'DH' => 'Box,Commnwealth Hndling Equipmnt Pool/CHEP,Eurobox', 'DI' => 'Drum, iron', 'DJ' => 'Demijohn, non-protected', 'DK' => 'Crate, bulk, cardboard', 'DL' => 'Crate, bulk, plastic', 'DM' => 'Crate, bulk, wooden', 'DN' => 'Dispenser', 'DP' => 'Demijohn, protected', 'DR' => 'Drum', 'DS' => 'Tray, one layer no cover, plastic', 'DT' => 'Tray, one layer no cover, wooden', 'DU' => 'Tray, one layer no cover, polystyrene', 'DV' => 'Tray, one layer no cover, cardboard', 'DW' => 'Tray, two layers no cover, plastic tray', 'DX' => 'Tray, two layers no cover, wooden', 'DY' => 'Tray, two layers no cover, cardboard', 'EC' => 'Bag, plastic', 'ED' => 'Case, with pallet base', 'EE' => 'Case, with pallet base, wooden', 'EF' => 'Case, with pallet base, cardboard', 'EG' => 'Case, with pallet base, plastic', 'EH' => 'Case, with pallet base, metal', 'EI' => 'Case, isothermic', 'EN' => 'Envelope', 'FB' => 'Flexibag', 'FC' => 'Fruit crate', 'FD' => 'Framed crate', 'FE' => 'Flexitank', 'FI' => 'Firkin', 'FL' => 'Flask', 'FO' => 'Footlocker', 'FP' => 'Filmpack', 'FR' => 'Frame', 'FT' => 'Foodtainer', 'FW' => 'Cart, flatbed', 'FX' => 'Bag, flexible container', 'GB' => 'Gas bottle', 'GI' => 'Girder', 'GL' => 'Container, gallon', 'GR' => 'Receptacle, glass', 'GU' => 'Tray, containing horizontally stacked flat items', 'GY' => 'Bag, gunny', 'GZ' => 'Girders, in bundle/bunch/truss', 'HA' => 'Basket, with handle, plastic', 'HB' => 'Basket, with handle, wooden', 'HC' => 'Basket, with handle, cardboard', 'HG' => 'Hogshead', 'HN' => 'Hanger', 'HR' => 'Hamper', 'HZ' => 'bukan kaleng kaleng', 'IA' => 'Package, display, wooden', 'IB' => 'Package, display, cardboard', 'IC' => 'Package, display, plastic', 'ID' => 'Package, display, metal', 'IE' => 'Package, show', 'IF' => 'Package, flow', 'IG' => 'Package, paper wrapped', 'IH' => 'Drum, plastic', 'IK' => 'Package, cardboard, with bottle grip-holes', 'IL' => 'Tray, rigid, lidded stackable (CEN TS 14482:2002)', 'IN' => 'Ingot', 'IZ' => 'ingots, in bundle/bunch/truss', 'JB' => 'Bag, jumbo', 'JC' => 'Jerrican, rectangular', 'JG' => 'Jug', 'JR' => 'Jar', 'JT' => 'Jutebag', 'JY' => 'Jerrican, cylindrical', 'KG' => 'Keg', 'KI' => 'Kit', 'KR' => 'karung', 'LE' => 'Luggage', 'LG' => 'Log', 'LT' => 'Lot', 'LU' => 'Lug', 'LV' => 'Liftvan', 'LZ' => 'Logs, in bundle/bunch/truss', 'MA' => 'Crate, metal', 'MB' => 'Multiply bag', 'MC' => 'milk crate', 'ME' => 'Container, metal', 'MR' => 'Receptacle, metal', 'MS' => 'Multiwall sack', 'MT' => 'Mat', 'MW' => 'Receptacle, plastic wrapped', 'MX' => 'Macontoh box', 'NA' => 'Not available', 'NE' => 'Unpacked or unpackaged', 'NF' => 'Unpacked or unpackaged, single unit', 'NG' => 'Unpacked or unpackaged, multiple units', 'NS' => 'Nest', 'NT' => 'Net', 'NU' => 'Net, tube, plastic', 'NV' => 'Net, tube, textile', 'OA' => 'Pallet, CHEP 40 cm x 60 cm', 'OB' => 'Pallet, CHEP 80 cm x 120 cm', 'OC' => 'Pallet, CHEP 100 cm x 120 cm', 'OD' => 'Pallet, AS 4068-1993', 'OE' => 'Pallet, ISO T11', 'OF' => 'Platform, unspecified weight or dimension', 'OK' => 'Block', 'OT' => 'Octabin', 'OU' => 'Container, outer', 'P2' => 'Pan', 'PA' => 'Packet', 'PB' => 'Pallet, box Combined open-ended box and pallet', 'PC' => 'Parcel', 'PD' => 'Pallet, modular, collars 80cms * 100cms', 'PE' => 'Pallet, modular, collars 80cms * 120cms', 'PF' => 'Pen', 'PG' => 'Plate', 'PH' => 'Pitcher', 'PI' => 'Pipe', 'PJ' => 'Punnet', 'PK' => 'Package', 'PL' => 'Pail', 'PN' => 'Plank', 'PO' => 'Pouch', 'PP' => 'Piece', 'PR' => 'Receptacle, plastic', 'PT' => 'Pot', 'PU' => 'Tray', 'PV' => 'Pipes, in bundle/bunch/truss', 'PX' => 'Pallet', 'PY' => 'Plates, in bundle/bunch/truss', 'PZ' => 'Pipes, in bundle/bunch/truss', 'QA' => 'Drum, steel, non-removable head', 'QB' => 'Drum, steel, removable head', 'QC' => 'Drum, aluminium, non-removable head', 'QD' => 'Drum, aluminium, removable head', 'QF' => 'Drum, plastic, non-removable head', 'QG' => 'Drum, plastic, removable head', 'QH' => 'Barrel, wooden, bung type', 'QJ' => 'Barrel, wooden, removable head', 'QK' => 'Jerrican, steel, non-removable head', 'QL' => 'Jerrican, steel, removable head', 'QM' => 'Jerrican, plastic, non-removable head', 'QN' => 'Jerrican, plastic, removable head', 'QP' => 'Box, wooden, natural wood, ordinary', 'QQ' => 'Box, wooden, natural wood, with sift proof walls', 'QR' => 'Box, plastic, expanded', 'QS' => 'Box, plastic, solid', 'RD' => 'Rod', 'RG' => 'Ring', 'RJ' => 'Rack, clothing hanger', 'RK' => 'Rack', 'RL' => 'Reel', 'RO' => 'Roll', 'RT' => 'Rednet', 'RZ' => 'Rods, in bundle/ bunch/truss', 'SA' => 'Sack', 'SB' => 'Slab', 'SC' => 'Shallow crate', 'SD' => 'Spindle', 'SE' => 'Sea-chest', 'SH' => 'Sachet', 'SI' => 'Skid', 'SK' => 'Skeleton case', 'SL' => 'Slipsheet', 'SM' => 'Sheetmetal', 'SO' => 'Spool', 'SP' => 'Sheet, plastic wrapping', 'SS' => 'Case, steel', 'ST' => 'Sheet', 'SU' => 'Suitcase', 'SV' => 'Envelope, steel', 'SW' => 'Shrinkwrapped', 'SX' => 'Set', 'SY' => 'Sleeve', 'SZ' => 'Sheets, in bundle/bunch/truss', 'T1' => 'Tablet', 'TB' => 'Tub', 'TC' => 'Tea-chest', 'TD' => 'Collapsible tube', 'TE' => 'Tyre', 'TG' => 'Tank container, generic', 'TI' => 'Tierce', 'TK' => 'Tank, rectangular', 'TL' => 'Tub, with lid', 'TN' => 'Tin', 'TO' => 'Tun', 'TP' => 'Tray', 'TR' => 'Trunk', 'TS' => 'Truss', 'TT' => 'Bag, tote', 'TU' => 'Tube', 'TV' => 'Tube, with nozzle', 'TW' => 'Pallet, triwall', 'TY' => 'Tank, cylindrical', 'TZ' => 'Tubes, in bundle/bunch/truss', 'UC' => 'Uncaged', 'UN' => 'Unpackage', 'VA' => 'Vat', 'VG' => 'Bulk, gas ( at 1031 mbar and 15C )', 'VI' => 'Vial', 'VK' => 'Vanpack', 'VL' => 'Bulk, liquid', 'VN' => 'Vehicle', 'VO' => 'Bulk, solid, large particles ("nodules")', 'VP' => 'Vacuumpacked', 'VQ' => 'Bulk,liquefied gas (at abnorml temprture/pressure)', 'VR' => 'Bulk, solid, granular particles ("grains")', 'VS' => 'Bulk, scrap metal', 'VY' => 'Bulk, solid, fine particles ("powders")', 'WA' => 'Intermediate bulk container', 'WB' => 'Wickerbottle', 'WC' => 'Intermediate bulk container, steel', 'WD' => 'Intermediate bulk container, aluminium', 'WF' => 'Intermediate bulk container, metal', 'WG' => 'Intermediate bulk cont,steel,pressurised >10 kpa', 'WH' => 'Intermedt bulk cont,aluminium,pressurised >10 kpa', 'WJ' => 'Intermediate bulk container,metal, pressure 10 kpa', 'WK' => 'Intermediate bulk container, steel, liquid', 'WL' => 'Intermediate bulk container, aluminium, liquid', 'WM' => 'Intermediate bulk container, metal, liquid', 'WN' => 'Intermd bulk cont,woven plastic,without coat/liner', 'WP' => 'Intermediate bulk container, woven plastic, coated', 'WQ' => 'Intermediate bulk cont,woven plastic,with liner', 'WR' => 'Intermedt bulk cont,woven plastic,coated and liner', 'WS' => 'Intermediate bulk container, plastic film', 'WT' => 'Intermediate bulk cont,textile with out coat/liner', 'WU' => 'Intermdte bulk cont,natural wood,with inner liner', 'WV' => 'Intermediate bulk container, textile, coated', 'WW' => 'Intermediate bulk container, textile, with liner', 'WX' => 'Intermediate bulk cont,textile,coated and liner', 'WY' => 'Intermediate bulk cont,plywood,with inner liner', 'WZ' => 'Intermd bulk cont,reconstttd wood,with inner liner', 'XA' => 'Bag, woven plastic, without inner coat/liner', 'XB' => 'Bag, woven plastic, sift proof', 'XC' => 'Bag, woven plastic, water resistant', 'XD' => 'Bag, plastics film', 'XF' => 'Bag, textile, without inner coat/liner', 'XG' => 'Bag, textile, sift proof', 'XH' => 'Bag, textile, water resistant', 'XJ' => 'Bag, paper, multi-wall', 'XK' => 'Bag, paper, multi-wall, water resistant', 'XN' => 'test', 'YA' => 'Compsite packging,plastic receptacle in steel drum', 'YB' => 'Compste packgng,plastc recptcle in steel crate box', 'YC' => 'Compste packgng,plastic recptcle in aluminium drum', 'YD' => 'Compste packgng,plastic recptcle in alumnium crate', 'YF' => 'Compsite packging,plastic receptacle in wooden box', 'YG' => 'Compste packgng,plastic receptacle in plywood drum', 'YH' => 'Compste packging,plastic receptacle in plywood box', 'YJ' => 'Compsite packging,plastic receptacle in fibre drum', 'YK' => 'Compste packgng,plastic recptcle in fibreboard box', 'YL' => 'Compste packgng,plastic receptacle in plastic drum', 'YM' => 'Compsite packgng,plstc recptcle in solid plstc box', 'YN' => 'Composite packaging,glass receptacle in steel drum', 'YP' => 'Compste packgng,glass recptacle in steel crate box', 'YQ' => 'Compste packgng,glass receptacle in aluminium drum', 'YR' => 'Compste packgng,glass recptacle in aluminium crate', 'YS' => 'Composite packaging,glass receptacle in wooden box', 'YT' => 'Compsite packging,glass receptacle in plywood drum', 'YV' => 'Compste packgng,glass recptcle in wickrwork hamper', 'YW' => 'Composite packaging,glass receptacle in fibre drum', 'YX' => 'Compste packgng,glass receptacle in fibreboard box', 'YY' => 'Compste pckgng,glss recptcl in expndbl plastc pack', 'YZ' => 'Compsite packgng,glass recptcle in solid plstc pck', 'ZA' => 'Intermediate bulk container, paper, multi-wall', 'ZB' => 'Bag, large', 'ZC' => 'Intermd bulk cont,paper,multi-wall,water resistant', 'ZD' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,solid', 'ZF' => 'Intermd bulk cont,rgid plstc,freestandng,solds', 'ZG' => 'Intermdbulk cnt,rgd plstc,w/strctrl equipm,pressrd', 'ZH' => 'Intermd bulk cont,rgd plstc,freestnd,pressurised', 'ZJ' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,lquid', 'ZK' => 'Intermd bulk cont,rigid plstc,freestanding,liquids', 'ZL' => 'Intermd bulk cont,composite,rigid plastic,solids', 'ZM' => 'Intermd bulk cont,compste,flexbl plastic, solids', 'ZN' => 'Intermd bulk cont,compsit,rgid plstc,pressurised', 'ZP' => 'Intermd bulk cont,compsit,flexbl plstc,pressurised', 'ZQ' => 'Intermd bulk cont,composite,rigid plastic,liquids', 'ZR' => 'Intermd bulk cont,compsite,flexible plastc,liquids', 'ZS' => 'Intermediate bulk container, composite', 'ZT' => 'Intermediate bulk container, fibreboard', 'ZU' => 'Intermediate bulk container, flexible', 'ZV' => 'Intermediate bulk container,metal,other than steel', 'ZW' => 'Intermediate bulk container, natural wood', 'ZX' => 'Intermediate bulk container, plywood', 'ZY' => 'Intermediate bulk container, reconstituted wood', 'ZZ' => 'Mutually defined'];

    $listSatuanBarang = ['6' => 'small spray', '8' => 'heat lot', '10' => 'group', '13' => 'ration', '14' => 'shot', '15' => 'stick, military', '16' => 'hundred fifteen kg drum', '17' => 'hundred lb drum', '18' => 'fiftyfive gallon (US) drum', '19' => 'tank truck', '20' => 'twenty foot container', '21' => 'forty foot container', '22' => 'decilitre per gram', '24' => 'theoretical pound', '26' => 'actual ton', '28' => 'kilogram per square metre', '29' => 'pound per thousand square foot', '30' => 'horse power day per air dry metric ton', '31' => 'catch weight', '32' => 'kilogram per air dry metric ton', '33' => 'kilopascal square metre per gram', '34' => 'kilopascal per millimetre', '35' => 'millilitre per square centimetre second', '36' => 'cubic foot per minute per square foot', '38' => 'ounce per square foot per 0,01inch', '40' => 'millilitre per second', '43' => 'super bulk bag', '44' => 'fivehundred kg bulk bag', '46' => 'fifty lb bulk bag', '47' => 'fifty lb bag', '48' => 'bulk car load', '53' => 'theoretical kilogram', '54' => 'theoretical tonne', '57' => 'mesh', '58' => 'net kilogram', '60' => 'percent weight', '61' => 'part per billion (US)', '62' => 'percent per 1000 hour', '63' => 'failure rate in time', '64' => 'pound per square inch, gauge', '66' => 'oersted', '71' => 'volt ampere per pound', '72' => 'watt per pound', '73' => 'ampere tum per centimetre', '78' => 'kilogauss', '84' => 'kilopound-force per square inch', '85' => 'foot pound-force', '89' => 'poise', '92' => 'calorie per cubic centimetre', '93' => 'calorie per gram', '94' => 'curl unit', '96' => 'ten thousand gallon (US) tankcar', '97' => 'ten kg drum', '98' => 'fifteen kg drum'];
@endphp
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc25', sv($header->bppbno)) }}" method="POST" id="form-edit-ceisa">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ sv($header->trx_no_par) }} |
                <strong>Supplier:</strong> {{ sv($header->supplier, '-') }}
                <input type="hidden" name="bpbno_int" value="{{ sv($header->bppbno_int) }}">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Header</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#tab-dokumen" role="tab">Dokumen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pengangkut-tab" data-toggle="tab" href="#tab-pengangkut" role="tab">Pengangkut</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="kemasan-tab" data-toggle="tab" href="#tab-kemasan" role="tab">Kemasan & Peti Kemas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#tab-transaksi" role="tab">Transaksi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Barang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pungutan-tab" data-toggle="tab" href="#tab-pungutan" role="tab">Pungutan</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pernyataan-tab" data-toggle="tab" href="#tab-pernyataan" role="tab">Pernyataan</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">

                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="section-title">Data Pengajuan</div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Nomor Aju</label>
                            <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ sv($nomorAju) }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kode Kantor</label>
                            <select name="kodeKantor" class="form-control form-control-sm select2bs4">
                                <option value="">Pilih Kantor</option>
                                @foreach($kantorList as $kantor)
                                    <option value="{{ $kantor['kode'] }}" {{ (isset($dataDetail['kodeKantor']) && $dataDetail['kodeKantor'] == $kantor['kode']) || (!isset($dataDetail['kodeKantor']) && $kantor['kode'] == '050500') ? 'selected' : '' }}>
                                        {{ $kantor['nama'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Jenis TPB</label>
                            <select name="jenisTPB" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih --</option>
                                @foreach($listJenisTpb as $k => $v)
                                    <option value="{{ $k }}" {{ ($dataDetail['jenisTPB'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tujuan Pengiriman</label>
                            @php
                                $listTujuan = [
                                    '1' => 'PENYERAHAN BKP', '2' => 'PENYERAHAN JKP', '3' => 'RETUR',
                                    '4' => 'NON PENYERAHAN', '5' => 'LAINNYA'
                                ];
                                $tujuanTerpilih = sv($dataDetail['kodeTujuanPengiriman'] ?? null, '1');
                            @endphp
                            <select name="kodeTujuanPengiriman" class="form-control form-control-sm select2bs4">
                                <option value="">Pilih Tujuan Pengiriman</option>
                                @foreach($listTujuan as $key => $text)
                                    <option value="{{ $key }}" {{ $tujuanTerpilih == $key ? 'selected' : '' }}>{{ $key }} - {{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Cara Pembayaran</label>
                            @php $caraBayar = sv($dataDetail['kodeCaraBayar'] ?? null, ''); @endphp
                            <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih --</option>
                                @foreach($listCaraPembayaran as $k => $v)
                                    <option value="{{ $k }}" {{ ($dataDetail['kodeCaraBayar'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="section-title"><i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)</div>

                    <div class="accordion" id="accordionBarang">
                        @foreach($items as $index => $item)
                        @php
                            $draftItem = $dataDetail['barang'][$index] ?? [];
                        @endphp

                        <div class="card mb-2 border">
                            <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold" style="font-size: 13px;">
                                        {{ sv($item->goods_code ?? $item->id_item) }} - {{ sv($item->itemdesc) }}
                                    </div>
                                    <i class="fas fa-chevron-down icon-collapse"></i>
                                </div>
                            </div>

                            <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                <div class="card-body py-3 px-3 bg-white">

                                    <input type="hidden" name="barang[{{ $index }}][kodeBarang]" value="{{ sv($draftItem['kodeBarang'] ?? ($item->goods_code ?? $item->id_item)) }}">
                                    <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                    <div class="row">

                                        <div class="col-md-3">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Dokumen Asal</h3>
                                                </div>
                                                <div class="card-body p-2">
                                                    @php
                                                        if(!isset($referensiDokumenTabBarang)) {
                                                             $referensiDokumenTabBarang = [
                                                                '10' => 'RKSP',
                                                                '11' => 'MANIFES',
                                                                '16' => 'BC 1.6 - PEMBERITAHUAN PABEAN PENGELUARAN BARANG DARI KAWASAN PABEAN UNTUK DITIMBUN DI PUSAT LOGISTIK BERIKAT',
                                                                '20' => 'BC 2.0 - PEMBERITAHUAN IMPOR BARANG',
                                                                '21' => 'PIBK/IMPOR KHUSUS',
                                                                '23' => 'BC 2.3 - PEMBERITAHUAN IMPOR BARANG UNTUK DITIMBUN DI TEMPAT PENIMBUNAN BERIKAT',
                                                                '25' => 'BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT',
                                                                '27' => 'BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA',
                                                                '28' => 'BC 2.8 - PEMBERITAHUAN IMPOR BARANG DARI PUSAT LOGISTIK BERIKAT',
                                                                '30' => 'BC 3.0 - PEMBERITAHUAN EKSPOR NARAMG',
                                                                '33' => 'BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT',
                                                                '40' => 'BC 4.0 - PEMBERITAHUAN PEMASUKAN BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN KE TEMPAT PENIMBUNAN BERIKAT',
                                                                '41' => 'BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT',
                                                                '50' => 'KITE',
                                                                '51' => 'FTZ 01',
                                                                '52' => 'FTZ 02',
                                                                '53' => 'FTZ 03',
                                                                '65' => 'BC 1.1 KONSOLIDASI PJT',
                                                                '111' => 'Bank Devisa Hasil Ekspor (DHE)',
                                                                '161' => 'PPB - PEMBERITAHUAN PERPINDAHAN BARANG ANTAR TEMPAT PENIMBUNAN DALAM SATU PUSAT LOGISTIK BERIKAT',
                                                                '202' => 'PENGELUARAN BAHAN BAKU DAN/ ATAU SISA BAHAN BAKU',
                                                                '203' => 'PENGELUARAN SEMENTARA - SUBKONTRAK',
                                                                '204' => 'PENGELUARAN SEMENTARA - PERBAIKAN/ REPARASI',
                                                                '205' => 'PENGELUARAN SEMENTARA - PEMINJAMAN BARANG MODAL UNTUK KEPERLUAN PRODUKSI',
                                                                '206' => 'PENGELUARAN SEMENTARA - PENGETESAN ATAU PENGEMBANGAN KUALITAS PRODUKSI',
                                                                '207' => 'PENGELUARAN SEMENTARA - PENGGUNAAN KEMASAN YANG DIPAKAI BERULANG (RETURNABLE PACKAGE)',
                                                                '208' => 'PENGELUARAN SEMENTARA - DIPAMERKAN',
                                                                '209' => 'PENGELUARAN SEMENTARA - TUJUAN LAIN DENGAN PERSETUJUAN KEPALA KANTOR PABEAN',
                                                                '210' => 'PENERIMAAN PEKERJAAN - SUBKONTRAK',
                                                                '211' => 'PENERIMAAN PEKERJAAN - PERBAIKAN/ REPARASI',
                                                                '212' => 'PENERIMAAN PEKERJAAN - PEKERJAAN LAIN',
                                                                '213' => 'PEMUSNAHAN BARANG DI KAWASAN BERIKAT',
                                                                '217' => 'PACKING LIST',
                                                                '246' => 'L/C',
                                                                '261' => 'BC 2.6.1 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
                                                                '262' => 'BC 2.6.2 - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG YANG DI KELUARKAN DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
                                                                '281' => 'PPK - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG ASAL PLB DARI LOKASI PENERIMA FASILITAS DI TEMPAT LAIN DALAM DAERAH PABEAN KE PLB',
                                                                '282' => 'DOKAP PLB - PEMBERITAHUAN PENGELUARAN DENGAN DOKUMEN PELENGKAP',
                                                                '302' => 'CN Ekspor',
                                                                '315' => 'KONTRAK',
                                                                '331' => 'P3BET - PEMBERITAHUAN PENGGABUNGAN DAN PEMECAHAN BARANG EKSPOR DAN TRANSHIPMENT',
                                                                '343' => 'SHIPING ORDER',
                                                                '380' => 'INVOICE',
                                                                '630' => 'SURAT JALAN',
                                                                '383' => 'SSTB',
                                                                '388' => 'FAKTUR PAJAK',
                                                                '410' => 'SURAT SANGGUP BAYAR / SSB',
                                                                '430' => 'BANK GARANSI',
                                                                '440' => 'SURAT TANDA BUKTI SETOR / STBS',
                                                                '454' => 'SSPCP / SSBC',
                                                                '455' => 'SURAT SETORAN PAJAK (SSP)',
                                                                '456' => 'SKB',
                                                                '457' => 'Surat Keterangan Bebas (SKB) PPh',
                                                                '458' => 'SURAT KETERANGAN TIDAK DIPUNGUT (SKTD) PPN',
                                                                '459' => 'Non SKB / SKTD',
                                                                '500' => 'MOU PDE (Eksportir)',
                                                                '511' => 'FTZ-01 PEMASUKAN DARI LUAR DAERAH PABEAN (IMPOR)',
                                                                '512' => 'FTZ-01 PENGELUARAN KE LUAR DAERAH PABEAN (EKSPOR)',
                                                                '513' => 'FTZ-01 PENGELUARAN KE TEMPAT LAIN DALAM DAERAH PABEAN',
                                                                '521' => 'FTZ-02 PEMASUKAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
                                                                '522' => 'FTZ-02 PENGELUARAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
                                                                '531' => 'FTZ-03 PEMASUKAN DARI TEMPAT LAIN DALAM DAERAH PABEAN',
                                                                '640' => 'DELIVERY ORDER',
                                                                '666' => 'Pengecualian Dengan Surat Keputusan',
                                                                '704' => 'MASTER B/L',
                                                                '705' => 'B/L',
                                                                '740' => 'AWB',
                                                                '741' => 'MASTER AWB',
                                                                '800' => 'SERTIFIKAT ALAT PERANGKAT TELEKOM/POSTEL',
                                                                '803' => 'SATS LN / DEPHUT',
                                                                '805' => 'REGISTRASI B3 / KLH',
                                                                '808' => 'IJIN IMPOR / POLRI',
                                                                '809' => 'SIE',
                                                                '810' => 'SM/SPM',
                                                                '811' => 'Sertifikat Legalitas Kayu (Dok.V-Legal)',
                                                                '812' => 'Dok. Impor (PIB)',
                                                                '813' => 'DOK. CUKAI (CK)',
                                                                '814' => 'SKEP IJIN EKSPOR BERKALA',
                                                                '815' => 'SKEP IJIN TATA NIAGA EKSPOR',
                                                                '816' => 'DOK. EKSPOR (PEB)',
                                                                '817' => 'Eksportir Terdaftar (ET) Depdag',
                                                                '818' => 'Endorsement BRIK',
                                                                '819' => 'Sertifikat Intan Kasar',
                                                                '820' => 'Surat Persetujuan Ekspor (SPE)',
                                                                '821' => 'Surat Tanda Registrasi UPPB',
                                                                '822' => 'Srt Tanda Pendaftaran Pedagang Bokor SIR',
                                                                '834' => 'SNI GULA KRISTAL MENTAH / DEPTAN',
                                                                '835' => 'IZIN DAN/ATAU PENDAFT PESTISIDA / DEPTAN',
                                                                '836' => 'IZIN IMPOR / DEPTAN',
                                                                '842' => 'SNI / ESDM',
                                                                '843' => 'NOMOR PELUMAS TERDAFTAR / ESDM',
                                                                '844' => 'IJIN USAHA NIAGA/IU NIAGA TERBATAS/ESDM',
                                                                '845' => 'REKOMENDASI IMPOR PELUMAS',
                                                                '846' => 'SKEM',
                                                                '851' => 'SURAT IJIN KARANTINA TANAMAN',
                                                                '853' => 'SURAT IJIN KARANTINA HEWAN / IKAN',
                                                                '854' => 'SURAT PERSETUJUAN MUAT BPOM',
                                                                '856' => 'LAP. PEMERIKSAAN SURVEYOR (LPS-E)',
                                                                '857' => 'FUMIGATION CERTIFICATE',
                                                                '858' => 'CITES CERTIFICATE',
                                                                '860' => 'Electronic Certificate Of Origin (E-CO)',
                                                                '861' => 'CERTIFICATE OF ORIGIN (CO)',
                                                                '862' => 'SKEP USDFS',
                                                                '871' => 'Nomor Pendaftaran Alat Kesehatan/Depkes',
                                                                '872' => 'LAPORAN SURVEYOR DEPKES',
                                                                '873' => 'IP (NARKTK, PREKURSOR & PSIKOTR)/DEPKES',
                                                                '874' => 'IT (PREKURSOR & PSIKOTR)/DEPKES',
                                                                '875' => 'SPI (NARKTK, PREKURSOR & PSIKOTR)/DEPKES',
                                                                '876' => 'Ijin Pembawaan UKA',
                                                                '877' => 'Ijin Persetujuan Pembawaan UKA',
                                                                '878' => 'Ijin Pelaporan Pembawaan UKA',
                                                                '888' => 'PENGECUALIAN PERIJINAN',
                                                                '902' => 'IJIN BAPETEN',
                                                                '911' => 'SURAT KEPUTUSAN',
                                                                '912' => 'SKEP FASILITAS BKPM',
                                                                '913' => 'SKEP FASILITAS PERTAMBANGAN',
                                                                '914' => 'KITE IKM',
                                                                '915' => 'Skep Fasilitas Impor Sementara',
                                                                '917' => 'BPBC / BPPAI',
                                                                '918' => 'SK LABEL BAHASA INDONESIA',
                                                                '919' => 'SK Bermotor',
                                                                '920' => 'SKEP TPB',
                                                                '936' => 'KH-9a/Izin Impor Karantina Hewan',
                                                                '937' => 'KH-14/Izin Impor Karantina Hewan',
                                                                '938' => 'KH-17/Izin Impor Karantina Hewan',
                                                                '939' => 'KT-5/Izin Impor Karantina Pertanian',
                                                                '940' => 'KT-9/Izin Impor Karantina Pertanian',
                                                                '941' => 'KT-13/Izin Impor Karantina Pertanian',
                                                                '942' => 'IZIN IMPOR KARANTINA TUMBUHAN',
                                                                '943' => 'KH-5 / IZIN IMPOR KARANTINA HEWAN',
                                                                '944' => 'KH-7 / IZIN IMPOR KARANTINA HEWAN',
                                                                '945' => 'KH-12 / IZIN IMPOR KARANTINA HEWAN',
                                                                '946' => 'KID-3 / IZIN IMPOR KARANTINA IKAN',
                                                                '947' => 'KID-15 / IZIN IMPOR KARANTINA IKAN',
                                                                '948' => 'NPIK',
                                                                '949' => 'PENGAKUAN SBG IMPORTIR PRODUSEN',
                                                                '950' => 'KID-4/IZIN KARANTINA IKAN',
                                                                '951' => 'HC (HEALTH CERTIFICATE)',
                                                                '956' => 'PENGAKUAN SBG IMPORTIR TERDAFTAR',
                                                                '957' => 'SNI/SPB/DEPDAG',
                                                                '958' => 'LAPORAN SURVEYOR / DEPDAG',
                                                                '959' => 'SURAT PERSETUJUAN IMPOR DEP.DAG',
                                                                '960' => '3D/PC dan/atau PFP',
                                                                '961' => 'Hasil Lab',
                                                                '993' => 'SURAT IJIN MENTERI PERTANIAN',
                                                                '994' => 'BUKTI PENERIMAAN JAMINAN (BPJ)',
                                                                '995' => 'STBS / SSP-E (PAJAK EKSPOR)',
                                                                '996' => 'SRT SANGGUP BAYAR (SSB)',
                                                                '997' => 'COSTOMS BOND / STTJ',
                                                                '998' => 'SKEP FASILITAS KEMUDAHAN EKSPOR',
                                                                '999' => 'LAINNYA',
                                                                '03001' => 'Izin Prinsip Pendirian Kawasan Berikat Sebelum Fisik Bangunan Berdiri',
                                                                '03002' => 'Keputusan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat',
                                                                '03003' => 'Persetujuan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat Sekaligus Izin Pengusaha Kawasan Berikat',
                                                                '03004' => 'Izin PDKB',
                                                                '03005' => 'Perpanjangan Penetapan Tempat Sebagai Kawasan Berikat Dan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB Sebelum Jangka Waktu Izin Tersebut Berakhir',
                                                                '03006' => 'Perubahan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB (Terdapat Perubahan Nama Perusahaan Yang Bukan Dikarenakan Merger Atau Diakuisisi, Jenis Hasil Produksi, Atau Luas Kawasan Berikat)',
                                                                '03007' => 'Perubahan Keputusan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB',
                                                                '03008' => 'Pemberian Izin Penambahan Pintu Khusus Pemasukan Dan Pengeluaran Barang Di Kawasan Berikat',
                                                                '03009' => 'Pemberian Izin Penambahan Pintu Khusus Orang Di Kawasan Berikat',
                                                                '03010' => 'Persetujuan Pemasukan Barang Dari Kawasan Bebas Ke Kawasan Berikat',
                                                                '03011' => 'Persetujuan Pemasukan Barang Modal Dari Luar Daerah Pabean',
                                                                '03012' => 'Persetujuan Pemasukan Barang Modal Dari Kawasan Berikat Lain',
                                                                '03013' => 'Persetujuan Pemasukan Barang Jadi Asal Luar Daerah Pabean Untuk Digabungkan Dengan Hasil Produksi Utama Kawasan Berikat',
                                                                '03014' => 'Persetujuan Pemasukan Peralatan Perkantoran Asal Luar Daerah Pabean Ke Kawasan Berikat',
                                                                '03015' => 'Persetujuan Pemasukan Barang Contoh Asal Luar Daerah Pabean',
                                                                '03016' => 'Persetujuan Pembebasan Bea Masuk Untuk Barang Contoh Yang Akan Dikeluarkan Ke Tempat Lain Dalam Daerah Pabean',
                                                                '03017' => 'Persetujuan Mengeluarkan Hasil Produksi Kawasan Berikat Ke Tempat Penyelenggaraan Pameran Berikat (TPPB)',
                                                                '03018' => 'Persetujuan Untuk Mengeluarkan Bahan Baku Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Yang Sama Sekali Tidak Diproses Ke Gudang Berikat Asal Barang',
                                                                '03019' => 'Persetujuan Untuk Mengeluarkan Barang Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Asal Tlddp Ke TLDDP',
                                                                '03020' => 'Persetujuan Pengeluaran Bahan Baku/Sisa Bahan Baku Asal Impor Untuk Direekspor',
                                                                '03021' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Kawasan Berikat Lain',
                                                                '03022' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Perusahaan Industri Di TLDDP',
                                                                '03023' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu Manajemen',
                                                                '03024' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu PKB',
                                                                '03025' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lainnya',
                                                                '03026' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Dibayar BM-nya Untuk Direekspor',
                                                                '03027' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM-nya Ke Kawasan Berikat Lain Setelah Jangka Waktu 2 (Dua) Tahun Sejak Diimpor Dan Telahdipergunakan Di Kawasan Berikat',
                                                                '03028' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM Ke Tempat Lain Dalam Daerah Pabean Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                                                '03029' => 'Keputusan Pembebasan BM Atas Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                                                '03030' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Lunas BM Untuk Direekspor',
                                                                '03031' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke Kawasan Berikat Lain Setelah Dipergunakan Di Kawasan Berikat',
                                                                '03032' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat Yang Bersangkutan',
                                                                '03033' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                                                '03034' => 'Persetujuan Untuk Memindahtangankan Barang Modal Dan/Atau Peralatan Perkantoran Yang Telah Dilunasi BM Dan PDRI Pada Saat Pemasukan Ke Kawasan Berikat',
                                                                '03035' => 'Persetujuan Untuk Memindahtangankan Barang Modal Asal Tempat Lain Dalam Daerah Pabean',
                                                                '03036' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke Luar Daerah Pabean',
                                                                '03037' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke TLDDP',
                                                                '03038' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke KB Lain',
                                                                '03039' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke TLDDP',
                                                                '03040' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke KB Lain',
                                                                '03041' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke TLDDP',
                                                                '03042' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke PDKB Lain',
                                                                '03043' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Dalam Rangka Subkontrak',
                                                                '03044' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Bukan Dalam Rangka Subkontrak',
                                                                '03045' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak',
                                                                '03046' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Bukan Dalam Rangka Subkontrak',
                                                                '03047' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Dalam Rangka Subkontrak',
                                                                '03048' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Bukan Dalam Rangka Subkontrak',
                                                                '03049' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak',
                                                                '03050' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Selain Dalam Rangka Subkontrak',
                                                                '03051' => 'Persetujuan Peminjaman Mesin Atau Cetakan (Moulding) Yang Melebihi Jangka Waktu',
                                                                '03052' => 'Persetujuan Pemusnahan Atas Barangbarang Yang Busuk Dan/Atau Yang Karena Sifat Dan Bentuknya Dapat Dimusnahkan',
                                                                '03053' => 'Persetujuan Perusakan Atas Barang Asal Luar Daerah Pabean Yang Karena Sifat Dan Bentuknya Tidak Dapat Dimusnahkan',
                                                                '03054' => 'Persetujuan Menerima Subkontrak Dari TLDDP',
                                                                '03055' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Dalam Rangka Subkontrak',
                                                                '03056' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Bukan Dalam Rangka Subkontrak',
                                                                '03057' => 'Persetujuan Peminjaman Mesin/Peralatan Pabrik Dari TLDDP',
                                                                '03060' => 'Persetujuan Pemasukan Barang Modal Berupa Peralatan Pabrik Dari Luar Daerah Pabean',
                                                                '03061' => 'Persetujuan Pemasukan Barang Modal Berupa Suku Cadang Dari Luar Daerah Pabean Yang Dimasukkan Tidak Bersamaan Dengan Barang Modal',
                                                                '03062' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Hasil Produksi Asal TPB',
                                                                '03063' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Modal Setelah Perbaikan/Reparasi Dari Luar Daerah Pabean',
                                                                '03064' => 'Persetujuan Perpanjangan Jangka Waktu Pengeluaran Barang Modal Keperluan Perbaikan/Reparasi Tujuan TLDDP',
                                                                '03065' => 'Persetujuan Pengeluaran Barang Contoh/Sampel KB Dengan Tujuan TLDDP',
                                                                '03066' => 'Rekomendasi Meminjamkan Barang Modal Ke TLDDP Dalam Rangka Subkontrak Atau Bukan Lebih Dari 6 Bulan'
                                                            ];
                                                        }
                                                    @endphp
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Kode Kantor</label>
                                                        <select name="barang[{{ $index }}][dokumenAsal][kodeKantor]" class="form-control form-control-sm select2bs4">
                                                            <option value="">-- Pilih Kode Kantor --</option>
                                                            @foreach($kantorList as $kantor)
                                                                @php $kKode = is_array($kantor) ? ($kantor['kode'] ?? '') : $kantor; $kNama = is_array($kantor) ? ($kantor['nama'] ?? '') : $kantor; @endphp
                                                                <option value="{{ $kKode }}" {{ ($draftItem['dokumenAsal']['kodeKantor'] ?? '') == $kKode ? 'selected' : '' }}>{{ $kKode }} - {{ $kNama }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Dokumen Asal</label>
                                                        <select name="barang[{{ $index }}][dokumenAsal][jenisDokumen]" class="form-control form-control-sm select2bs4">
                                                            <option value="">-- Pilih Dokumen --</option>
                                                            @foreach($referensiDokumenTabBarang as $rKode => $rNama)
                                                                <option value="{{ $rKode }}" {{ ($draftItem['dokumenAsal']['jenisDokumen'] ?? '') == $rKode ? 'selected' : '' }}>{{ $rKode }} - {{ $rNama }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Nomor Pengajuan</label>
                                                        <input type="text" name="barang[{{ $index }}][dokumenAsal][nomorPengajuan]" class="form-control form-control-sm" value="{{ sv($draftItem['dokumenAsal']['nomorPengajuan'] ?? null) }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Nomor Daftar</label>
                                                        <input type="text" name="barang[{{ $index }}][dokumenAsal][nomorDaftar]" class="form-control form-control-sm" value="{{ sv($draftItem['dokumenAsal']['nomorDaftar'] ?? null) }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Tanggal Daftar</label>
                                                        <input type="date" name="barang[{{ $index }}][dokumenAsal][tanggalDaftar]" class="form-control form-control-sm" value="{{ sv($draftItem['dokumenAsal']['tanggalDaftar'] ?? null) }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small mb-0">Seri Barang Asal</label>
                                                        <input type="text" name="barang[{{ $index }}][dokumenAsal][seriBarangAsal]" class="form-control form-control-sm" value="{{ sv($draftItem['dokumenAsal']['seriBarangAsal'] ?? null) }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-5">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jenis</h3>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Seri</label>
                                                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $index + 1 }}" readonly>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Pos Tarif/HS <i class="fas fa-info-circle text-primary"></i></label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ sv($draftItem['posTarif'] ?? null, '') }}" placeholder="Pos Tarif">
                                                            <div class="input-group-append">
                                                                <button class="btn btn-primary" type="button"><i class="fas fa-search"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Kode Barang</label>
                                                        <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ sv($draftItem['kodeBarang'] ?? ($item->goods_code ?? $item->id_item)) }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0 d-flex justify-content-between">Uraian Jenis Barang <span class="badge badge-primary py-1 px-2" style="font-size: 10px;">Sesuai Hs</span></label>
                                                        <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="2" placeholder="Uraian kosong">{{ sv($draftItem['uraian'] ?? $item->itemdesc) }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Merek</label>
                                                        <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ sv($draftItem['merk'] ?? null, '-') }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Tipe</label>
                                                        <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ sv($draftItem['tipe'] ?? null, '-') }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Ukuran</label>
                                                        <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ sv($draftItem['ukuran'] ?? null, '-') }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small mb-0">Spesifikasi Lain</label>
                                                        <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ sv($draftItem['spesifikasiLain'] ?? ($item->remark ?? null), '-') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-4">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Keterangan Lainnya</h3>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Penggunaan</label>
                                                        <select name="barang[{{ $index }}][kodePenggunaan]" class="form-control form-control-sm select2bs4">
                                                            <option value="">-- Pilih Penggunaan --</option>
                                                            <option value="0" {{ ($draftItem['kodePenggunaan'] ?? '') == '0' ? 'selected' : '' }}>0 - BARANG BERHUBUNGAN LANGSUNG</option>
                                                            <option value="1" {{ ($draftItem['kodePenggunaan'] ?? '') == '1' ? 'selected' : '' }}>1 - TIDAK BERHUBUNGAN LANGSUNG</option>
                                                            <option value="2" {{ ($draftItem['kodePenggunaan'] ?? '') == '2' ? 'selected' : '' }}>2 - BARANG KONSUMSI</option>
                                                            <option value="3" {{ ($draftItem['kodePenggunaan'] ?? '') == '3' ? 'selected' : '' }}>3 - BARANG HASIL OLAHAN</option>
                                                            <option value="4" {{ ($draftItem['kodePenggunaan'] ?? '') == '4' ? 'selected' : '' }}>4 - BARANG LAINNYA</option>
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Kategori Barang</label>
                                                        <select name="barang[{{ $index }}][kodeKategoriBarang]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Kategori --</option>
                                                @foreach($listKategoriBarang as $k => $v)
                                                    <option value="{{ $k }}" {{ ($item->kodeKategoriBarang ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Kondisi Barang</label>
                                                        <select name="barang[{{ $index }}][kodeKondisiBarang]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Kondisi --</option>
                                                @foreach($listKondisiBarang as $k => $v)
                                                    <option value="{{ $k }}" {{ ($item->kodeKondisiBarang ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small mb-0">Jangka Waktu</label>
                                                        <div class="form-check mt-1">
                                                            <input class="form-check-input" type="checkbox" name="barang[{{ $index }}][jangkaWaktu]" value="> 4 Tahun" {{ !empty($draftItem['jangkaWaktu']) ? 'checked' : '' }}>
                                                            <label class="form-check-label small" style="margin-top: 1px;">> 4 Tahun</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jumlah & Berat</h3>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Jumlah dan Satuan Barang</label>
                                                        <div class="row">
                                                            <div class="col-6 pr-1">
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm input-decimal" value="{{ sv($draftItem['jumlahSatuan'] ?? (float) $item->qty, '0.0000') }}">
                                                            </div>
                                                            <div class="col-6 pl-1">
                                                                <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Pilih Satuan --</option>
                                                                    @foreach($listSatuanBarang as $kSat => $vSat)
                                                                        <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Kemasan</label>
                                                        <div class="row">
                                                            <div class="col-4 pr-1">
                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ sv($draftItem['jumlahKemasan'] ?? null, 0) }}">
                                                            </div>
                                                            <div class="col-8 pl-1">
                                                                <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Jenis Kemasan --</option>
                                                                    @foreach($listJenisKemasan as $kKem => $vKem)
                                                                        <option value="{{ $kKem }}" {{ sv($draftItem['kodeJenisKemasan'] ?? null) == $kKem ? 'selected' : '' }}>{{ $kKem }} - {{ $vKem }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small mb-0">Berat Bersih (Kg)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][netto]" class="form-control form-control-sm input-decimal" value="{{ sv($draftItem['netto'] ?? null, '0.0000') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-md-4">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Harga</h3>
                                                </div>
                                                <div class="card-body p-2">
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">CIF</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][cif]" class="form-control form-control-sm input-decimal" value="{{ sv($draftItem['cif'] ?? null, '0.00') }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Nilai CIF</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiCif]" class="form-control form-control-sm input-decimal bg-light" value="{{ sv($draftItem['nilaiCif'] ?? null, '0.00') }}" readonly>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small mb-0">Nilai Pabean</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiPabean]" class="form-control form-control-sm input-decimal bg-light" value="{{ sv($draftItem['nilaiPabean'] ?? null, '0.00') }}" readonly>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small mb-0">Harga Penyerahan/Harga Jual</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm input-decimal" value="{{ sv($draftItem['hargaPenyerahan'] ?? (float) ($item->qty * $item->price), '0.00') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="col-md-8 hidden">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Dokumen Fasilitas/Lartas</h3>
                                                    <button type="button" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-2 d-flex align-items-center justify-content-center" style="min-height: 120px;">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-inbox fa-2x mb-2" style="color: #ddd;"></i>
                                                        <p class="small mb-0" style="color: #ccc;">No Data</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row hidden">

                                        <div class="col-md-12">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2 d-flex justify-content-between align-items-center" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Jenis Voluntary Declaration</h3>
                                                    <button type="button" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-2">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th class="small font-weight-bold border-bottom">Jenis Voluntary Declaration</th>
                                                                <th class="small font-weight-bold border-bottom">Settlement Date</th>
                                                                <th class="small font-weight-bold border-bottom">Nilai Barang Vd</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                                <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                                <td><input type="text" class="form-control form-control-sm bg-light" readonly></td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                    <div class="text-center text-muted mt-2 mb-1">
                                                        <i class="fas fa-inbox fa-2x mb-1" style="color: #ddd;"></i>
                                                        <p class="small mb-0" style="color: #ccc;">No Data</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">

                                        <div class="col-md-12">
                                            <div class="card shadow-none border mb-3">
                                                <div class="card-header bg-light p-2" style="font-size: 13px;">
                                                    <h3 class="card-title mb-0" style="font-size: 13px; font-weight: bold;">Pungutan</h3>
                                                </div>
                                                <div class="card-body p-2 bg-light">
                                                    <div class="row">

                                                        <div class="col-md-6">
                                                            <div class="card shadow-none border mb-2">
                                                                <div class="card-body p-2">
                                                                    <div class="row mb-1">
                                                                        <div class="col-4 pr-1">
                                                                            <select name="barang[{{ $index }}][pungutan][bm][kodeJenis]" class="form-control form-control-sm">
                                                                                <option value="BM" {{ ($draftItem['pungutan']['bm']['kodeJenis'] ?? 'BM') == 'BM' ? 'selected' : '' }}>BM</option>
                                                                                <option value="BMKITE" {{ ($draftItem['pungutan']['bm']['kodeJenis'] ?? '') == 'BMKITE' ? 'selected' : '' }}>BMKITE</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4 px-1">
                                                                            <select name="barang[{{ $index }}][pungutan][bm][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                <option value="">-- Jenis Tarif --</option>
                                                                                @foreach($listJenisTarif as $k => $v)
                                                                                    <option value="{{ $k }}" {{ ($pungutanBarang['bm']['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4 pl-1" id="bmTarifAdval{{ $index }}" style="{{ ($draftItem['pungutan']['bm']['kodeJenisTarif'] ?? '1') == '1' ? '' : 'display:none;' }}">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarif]" class="form-control form-control-sm" placeholder="Tarif" value="{{ sv($draftItem['pungutan']['bm']['tarif'] ?? null, '0') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1" id="bmSpesifikRow{{ $index }}" style="{{ ($draftItem['pungutan']['bm']['kodeJenisTarif'] ?? '1') == '2' ? '' : 'display:none;' }}">
                                                                        <div class="col-4 pr-1">
                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][jumlahSatuan]" class="form-control form-control-sm" placeholder="Jml Satuan" value="{{ sv($draftItem['pungutan']['bm']['jumlahSatuan'] ?? null) }}">
                                                                        </div>
                                                                        <div class="col-4 px-1">
                                                                            <select name="barang[{{ $index }}][pungutan][bm][kodeSatuanBarang]" class="form-control form-control-sm">
                                                                                <option value="">-- Pilih Satuan --</option>
                                                                                @foreach($listSatuanBarang as $kSat => $vSat)
                                                                                    <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4 pl-1">
                                                                            <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarifSpesifik]" class="form-control form-control-sm" placeholder="Tarif" value="{{ sv($draftItem['pungutan']['bm']['tarifSpesifik'] ?? null) }}">
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-8 pr-1">
                                                                            <select name="barang[{{ $index }}][pungutan][bm][kodeFasilitas]" class="form-control form-control-sm select2bs4">
                                                                                <option value="">-- Fasilitas --</option>
                                                                                @foreach($listFasilitasTarif as $k => $v)
                                                                                    <option value="{{ $k }}" {{ ($pungutanBarang['bm']['kodeFasilitas'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-4 pl-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bm][tarifFasilitas]" class="form-control form-control-sm" placeholder="Fas %" value="{{ sv($draftItem['pungutan']['bm']['tarifFasilitas'] ?? null, '0') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="card shadow-none border mb-2">
                                                                <div class="card-body p-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input bmt-toggle-{{ $index }}" type="checkbox" id="checkBmt{{ $index }}" name="barang[{{ $index }}][pungutan][bmt][aktif]" value="1" {{ !empty($draftItem['pungutan']['bmt']['aktif']) ? 'checked' : '' }} onchange="toggleBmt{{ $index }}(this)">
                                                                        <label class="form-check-label small fw-bold" for="checkBmt{{ $index }}" style="margin-top: 1px;">BMT (Bea Masuk Tambahan)</label>
                                                                    </div>
                                                                    <div id="bmtPanel{{ $index }}" style="{{ !empty($draftItem['pungutan']['bmt']['aktif']) ? '' : 'display:none;' }}">
                                                                        @php $bmtTypes = ['BMAD', 'BMTP', 'BMI', 'BMP']; @endphp
                                                                        @foreach($bmtTypes as $bIdx => $bmtType)
                                                                        @php
                                                                            $bmtData = $draftItem['pungutan']['bmt'][$bmtType] ?? [];
                                                                        @endphp
                                                                        <div class="row align-items-center mb-1 border-top pt-1">
                                                                            <div class="col-2 small fw-bold" style="font-size:11px;">{{ $bmtType }}<br><span class="text-muted" style="font-size:10px;">Sementara</span></div>
                                                                            <div class="col-3">
                                                                                <select name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                    <option value="">-- Jenis Tarif --</option>
                                                                                    @foreach($listJenisTarif as $k => $v)
                                                                                        <option value="{{ $k }}" {{ ($bmtVal['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-2">
                                                                                <div class="input-group input-group-sm">
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][tarif]" class="form-control form-control-sm" value="{{ sv($bmtData['tarif'] ?? null, '0') }}" style="font-size:11px;">
                                                                                    <div class="input-group-append"><span class="input-group-text px-1" style="font-size:10px;">%</span></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-3">
                                                                                <select name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][kodeFasilitas]" class="form-control form-control-sm select2bs4">
                                                                                    <option value="">-- Fasilitas --</option>
                                                                                    @foreach($listFasilitasTarif as $k => $v)
                                                                                        <option value="{{ $k }}" {{ ($bmtVal['kodeFasilitas'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="col-2">
                                                                                <div class="input-group input-group-sm">
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][bmt][{{ $bmtType }}][tarifFasilitas]" class="form-control form-control-sm" value="{{ sv($bmtData['tarifFasilitas'] ?? null, '0') }}" style="font-size:11px;">
                                                                                    <div class="input-group-append"><span class="input-group-text px-1" style="font-size:10px;">%</span></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>


                                                            <div class="card shadow-none border mb-2">
                                                                <div class="card-body p-2">
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" id="checkCukai{{ $index }}" name="barang[{{ $index }}][pungutan][cukai][aktif]" value="1" {{ !empty($draftItem['pungutan']['cukai']['aktif']) ? 'checked' : '' }} onchange="toggleCukai{{ $index }}(this)">
                                                                        <label class="form-check-label small fw-bold" for="checkCukai{{ $index }}" style="margin-top: 1px;">Cukai</label>
                                                                    </div>
                                                                    <div id="cukaiPanel{{ $index }}" style="{{ !empty($draftItem['pungutan']['cukai']['aktif']) ? '' : 'display:none;' }}">
                                                                        @php $cukaiData = $draftItem['pungutan']['cukai'] ?? []; @endphp
                                                                        <div class="row">
                                                                            <div class="col-6">
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Komoditi</label>
                                                                                    <div class="input-group input-group-sm">
                                                                                        <select name="barang[{{ $index }}][pungutan][cukai][kodeKomoditi]" class="form-control form-control-sm select2bs4">
                                                                                            <option value="">-- Komoditi --</option>
                                                                                            @foreach($listKomoditiCukai as $k => $v)
                                                                                                <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeKomoditi'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                        <select name="barang[{{ $index }}][pungutan][cukai][kodeGolongan]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                            <option value="">Gol</option>
                                                                                            <option value="A" {{ ($cukaiData['kodeGolongan'] ?? '') == 'A' ? 'selected' : '' }}>A</option>
                                                                                            <option value="B" {{ ($cukaiData['kodeGolongan'] ?? '') == 'B' ? 'selected' : '' }}>B</option>
                                                                                            <option value="C" {{ ($cukaiData['kodeGolongan'] ?? '') == 'C' ? 'selected' : '' }}>C</option>
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Jenis Tarif</label>
                                                                                    <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisTarif]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">-- Jenis Tarif --</option>
                                                                                        @foreach($listJenisTarif as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeJenisTarif'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Besar Tarif</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][besarTarif]" class="form-control form-control-sm" value="{{ sv($cukaiData['besarTarif'] ?? null, '0.00') }}" style="font-size:11px;">
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Jumlah</label>
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahSatuan]" class="form-control form-control-sm" value="{{ sv($cukaiData['jumlahSatuan'] ?? null, '0.0000') }}" style="font-size:11px;" placeholder="Jml Satuan">
                                                                                        <select name="barang[{{ $index }}][pungutan][cukai][kodeSatuanCukai]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                            <option value="">-- Pilih Satuan --</option>
                                                                                            @foreach($listSatuanBarang as $kSat => $vSat)
                                                                                                <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group mb-0">
                                                                                    <label class="small mb-0" style="font-size:11px;">Nilai Cukai</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][nilaiCukai]" class="form-control form-control-sm bg-light" value="{{ sv($cukaiData['nilaiCukai'] ?? null, '0.00') }}" style="font-size:11px;" readonly>
                                                                                </div>
                                                                                <div class="form-group mb-0 mt-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Jenis Tarif (2)</label>
                                                                                    <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisTarif2]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">-- Jenis Tarif --</option>
                                                                                        @foreach($listJenisTarif as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($pungutanBarang['cukai']['kodeJenisTarif2'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-6">
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">HJE RP</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][hjeRp]" class="form-control form-control-sm" value="{{ sv($cukaiData['hjeRp'] ?? null, '0.00') }}" style="font-size:11px;">
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Satuan Kemasan</label>
                                                                                    <div class="input-group input-group-sm">
                                                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahKemasan]" class="form-control form-control-sm" value="{{ sv($cukaiData['jumlahKemasan'] ?? null, 0) }}" style="font-size:11px;" placeholder="Jml">
                                                                                        <select name="barang[{{ $index }}][pungutan][cukai][kodeJenisKemasan]" class="form-control form-control-sm" style="font-size:11px;">
                                                                                            <option value="">-- Pilih Kemasan --</option>
                                                                                            @foreach($listJenisKemasan as $kKem => $vKem)
                                                                                                <option value="{{ $kKem }}" {{ ($cukaiData['kodeJenisKemasan'] ?? '') == $kKem ? 'selected' : '' }}>{{ $kKem }} - {{ $vKem }}</option>
                                                                                            @endforeach
                                                                                        </select>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Isi Per Kemasan</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][isiPerKemasan]" class="form-control form-control-sm" value="{{ sv($cukaiData['isiPerKemasan'] ?? null, '0.00') }}" style="font-size:11px;">
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Jumlah Pita Cukai</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][jumlahPitaCukai]" class="form-control form-control-sm" value="{{ sv($cukaiData['jumlahPitaCukai'] ?? null, '0.00') }}" style="font-size:11px;">
                                                                                </div>
                                                                                <div class="form-group mb-2">
                                                                                    <label class="small mb-0" style="font-size:11px;">Saldo Awal</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][saldoAwal]" class="form-control form-control-sm" value="{{ sv($cukaiData['saldoAwal'] ?? null, '0.00') }}" style="font-size:11px;">
                                                                                </div>
                                                                                <div class="form-group mb-0">
                                                                                    <label class="small mb-0" style="font-size:11px;">Saldo Akhir</label>
                                                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][cukai][saldoAkhir]" class="form-control form-control-sm bg-light" value="{{ sv($cukaiData['saldoAkhir'] ?? null, '0.00') }}" style="font-size:11px;" readonly>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>


                                                        <div class="col-md-6">
                                                            <div class="card shadow-none border mb-0 h-100">
                                                                <div class="card-body p-2 pt-3">
                                                                    <label class="small fw-bold mb-2">PDRI</label>

                                                                    <div class="row mb-2 align-items-center">
                                                                        <div class="col-3 small">PPN</div>
                                                                        <div class="col-5 pr-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppn][tarif]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['ppn']['tarif'] ?? null, '11') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-4 pl-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppn][tarifFasilitas]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['ppn']['tarifFasilitas'] ?? null, '0') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-2 align-items-center">
                                                                        <div class="col-3 small">PPNBM</div>
                                                                        <div class="col-5 pr-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppnbm][tarif]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['ppnbm']['tarif'] ?? null, '0') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-4 pl-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][ppnbm][tarifFasilitas]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['ppnbm']['tarifFasilitas'] ?? null, '0') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row mb-1 align-items-center">
                                                                        <div class="col-3 small">PPH</div>
                                                                        <div class="col-3 pr-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][pph][tarif]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['pph']['tarif'] ?? null, '2.5') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-3 px-1">
                                                                            <select name="barang[{{ $index }}][pungutan][pph][caraBayar]" class="form-control form-control-sm select2bs4">
                                                                                <option value="">-- Cara Bayar --</option>
                                                                                @foreach($listCaraPembayaran as $k => $v)
                                                                                    <option value="{{ $k }}" {{ ($pungutanBarang['pph']['caraBayar'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-3 pl-1">
                                                                            <div class="input-group input-group-sm">
                                                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][pungutan][pph][tarifFasilitas]" class="form-control form-control-sm" value="{{ sv($draftItem['pungutan']['pph']['tarifFasilitas'] ?? null, '100') }}">
                                                                                <div class="input-group-append"><span class="input-group-text bg-white px-1">%</span></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="section-title mt-0"><i class="fas fa-building"></i> Entitas Pengusaha TPB (Kode: 3)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['namaEntitas'] ?? null, 'NIRWANA ALABARE GARMENT') }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['nomorIdentitas'] ?? null, '0745406926444000000000') }}"></div>
                        <div class="col-md-4 form-group"><label>NIB</label><input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['nibEntitas'] ?? null, '0220103231143') }}"></div>
                        <div class="col-md-8 form-group"><label>Alamat</label><input type="text" name="entitas[3][alamatEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['alamatEntitas'] ?? null, 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007') }}"></div>
                        <div class="col-md-2 form-group"><label>No. Izin TPB</label><input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['nomorIjinEntitas'] ?? null, '16/MK/WBC.09/2026') }}"></div>
                        <div class="col-md-2 form-group"><label>&nbsp;</label><input type="date" name="entitas[3][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][3]['tanggalIjinEntitas'] ?? null, '2026-01-20') }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-truck-loading"></i> Entitas Pembeli / Penerima (Kode: 8)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][8]['namaEntitas'] ?? sv($header->supplier ?? null)) }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[8][nomorIdentitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][8]['nomorIdentitas'] ?? sv($header->npwp_supplier ?? null)) }}"></div>
                        <div class="col-md-4 form-group"><label>Alamat</label><input type="text" name="entitas[8][alamatEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][8]['alamatEntitas'] ?? sv($header->alamat_supplier ?? null)) }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-user-tag"></i> Entitas Pemilik Barang (Kode: 7)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][7]['namaEntitas'] ?? null) }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][7]['nomorIdentitas'] ?? null) }}"></div>
                        <div class="col-md-4 form-group"><label>Alamat</label><input type="text" name="entitas[7][alamatEntitas]" class="form-control form-control-sm" value="{{ sv($dataDetail['entitas'][7]['alamatEntitas'] ?? null) }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">

                    <div class="section-title mt-0">Dokumen Pendukung</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php



                            @endphp
                            <table class="table table-sm table-bordered" id="table-dokumen">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="40%">Kode Dokumen</th>
                                        <th width="30%">Nomor Dokumen</th>
                                        <th width="15%">Tgl Dokumen</th>
                                        <th width="10%"><button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                    @php
                                        $dok = is_array($dok) ? $dok : [];
                                        $dokKodeTerpilih = sv($dok['kodeDokumen'] ?? null);
                                    @endphp
                                    <tr>
                                        <td>
                                            <select name="dokumen[{{ $index }}][kodeDokumen]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Kode --</option>
                                                @foreach($referensiDokumen as $val => $text)
                                                    <option value="{{ $val }}" {{ $dokKodeTerpilih == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                @endforeach
                                                @if($dokKodeTerpilih !== '' && !array_key_exists($dokKodeTerpilih, $referensiDokumen))
                                                    <option value="{{ $dokKodeTerpilih }}" selected>{{ $dokKodeTerpilih }} - Custom</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" name="dokumen[{{ $index }}][nomorDokumen]" class="form-control form-control-sm" value="{{ sv($dok['nomorDokumen'] ?? null) }}"></td>
                                        <td><input type="date" name="dokumen[{{ $index }}][tanggalDokumen]" class="form-control form-control-sm" value="{{ sv($dok['tanggalDokumen'] ?? null) }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="section-title mt-0">Pengangkut</div>
                    @php
                        $pengangkut0 = $dataDetail['pengangkut'][0] ?? [];
                        $pengangkut0 = is_array($pengangkut0) ? $pengangkut0 : [];
                        $pengangkutFlat = $dataDetail['pengangkut'] ?? [];
                        $pengangkutFlat = is_array($pengangkutFlat) ? $pengangkutFlat : [];

                        $caraAngkut = sv($pengangkut0['kodeCaraAngkut'] ?? null, '3');
                        $namaPengangkutVal = sv($pengangkut0['namaPengangkut'] ?? ($pengangkutFlat['nama'] ?? null), 'TRUK');
                        $nomorPengangkutVal = sv($pengangkut0['nomorPengangkut'] ?? ($pengangkutFlat['nomor'] ?? sv($header->nomor_mobil ?? null)));
                    @endphp
                    <div class="row mb-3">
                        <div class="col-md-3 form-group">
                            <label>Cara Angkut</label>
                            <select name="pengangkut[0][kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Cara Angkut</option>
                                                @foreach($listCaraAngkut as $k => $v)
                                                    <option value="{{ $k }}" {{ ($dataDetail['pengangkut'][0]['kodeCaraAngkut'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                        </div>
                        <div class="col-md-5 form-group hidden">
                            <label>Keterangan Sarana Angkut Lainnya (Nama)</label>
                            <input type="text" name="pengangkut[0][namaPengangkut]" class="form-control form-control-sm" value="{{ $namaPengangkutVal }}">
                        </div>
                        <div class="col-md-4 form-group hidden">
                            <label>Nomor Polisi</label>
                            <input type="text" name="pengangkut[0][nomorPengangkut]" class="form-control form-control-sm" value="{{ $nomorPengangkutVal }}">
                            <input type="hidden" name="pengangkut[0][seriPengangkut]" value="1">
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-2 rounded">
                        <div class="font-weight-bold" style="font-size: 14px; color: #003366;">Pungutan</div>
                        <button type="button" class="btn btn-sm btn-outline-primary bg-white hidden"><i class="fas fa-sync-alt"></i> Generate Pungutan</button>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th class="align-middle">Pungutan</th>
                                    <th class="align-middle">Ditangguhkan</th>
                                    <th class="align-middle">Sudah Dilunasi</th>
                                    <th class="align-middle">Dibebaskan</th>
                                    <th class="align-middle">Tidak Dipungut</th>
                                </tr>

                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-left font-weight-bold" style="color: #666; font-size: 12px; padding-left: 15px;">PPN</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-ditangguhkan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-sudah-dilunasi">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-dibebaskan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-ppn-tidak-dipungut">Rp 0,00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                    <div id="hidden-pungutan-container">

                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="section-title mt-0">Data Nilai & Fisik</div>
                    <div class="row">
                        <div class="col-md-2 form-group"><label>Bruto (Kg)</label><input type="text" inputmode="decimal" name="bruto" class="form-control form-control-sm input-decimal" value="{{ sv($dataDetail['bruto'] ?? sv($header->berat_kotor ?? null)) }}" placeholder="contoh: 125.5000"></div>
                        <div class="col-md-2 form-group"><label>Netto (Kg)</label><input type="text" id="totalNetto" inputmode="decimal" name="netto" class="form-control form-control-sm input-decimal bg-light" value="{{ sv($dataDetail['netto'] ?? sv($header->berat_bersih ?? null)) }}" readonly></div>
                        <div class="col-md-2 form-group"><label>Volume (M3)</label><input type="text" id="totalVolume" inputmode="decimal" name="volume" class="form-control form-control-sm input-decimal bg-light" value="{{ sv($dataDetail['volume'] ?? null) }}" readonly></div>
                        <div class="col-md-3 form-group"><label>Harga Penyerahan (Rp)</label><input type="text" id="totalHargaPenyerahan" inputmode="decimal" name="hargaPenyerahan" class="form-control form-control-sm input-decimal bg-light" value="{{ sv($dataDetail['hargaPenyerahan'] ?? null) }}" readonly></div>
                        <div class="col-md-3 form-group"><label>CIF (Rp)</label><input type="text" inputmode="decimal" name="cif" class="form-control form-control-sm input-decimal" value="{{ sv($dataDetail['cif'] ?? null) }}" placeholder="contoh: 5000000.00"></div>
                    </div>

                    <div class="row">
                        <div class="col-md-2 form-group"><label>Biaya Pengurang (Rp)</label><input type="text" inputmode="decimal" name="biayaPengurang" class="form-control form-control-sm input-decimal" value="{{ sv($dataDetail['biayaPengurang'] ?? null) }}" placeholder="contoh: 0.00"></div>
                        <div class="col-md-2 form-group"><label>Uang Muka (Rp)</label><input type="text" inputmode="decimal" name="uangMuka" class="form-control form-control-sm input-decimal" value="{{ sv($dataDetail['uangMuka'] ?? null) }}" placeholder="contoh: 0.00"></div>
                        <div class="col-md-2 form-group"><label>Nilai Jasa (Rp)</label><input type="text" inputmode="decimal" name="nilaiJasa" class="form-control form-control-sm input-decimal" value="{{ sv($dataDetail['nilaiJasa'] ?? null) }}" placeholder="contoh: 0.00"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="section-title mt-0">Penandatangan</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ sv($dataDetail['namaTtd'] ?? null) }}"></div>
                        <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ sv($dataDetail['jabatanTtd'] ?? null) }}"></div>
                        <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ sv($dataDetail['kotaTtd'] ?? null) }}"></div>
                        <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ sv($dataDetail['tanggalTtd'] ?? null, date('Y-m-d')) }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="section-title mt-0">Data Kemasan</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $kemasans = $dataDetail['kemasan'] ?? [];
                                $kemasans = is_array($kemasans) ? $kemasans : [];
                                if (empty($kemasans)) {
                                    $kemasans[] = ['jumlahKemasan' => sv($header->qty_karton ?? null), 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                }
                            @endphp
                            <table class="table table-sm table-bordered" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="20%">Jumlah Kemasan</th>
                                        <th width="40%">Jenis Kemasan</th>
                                        <th width="30%">Merek</th>
                                        <th width="10%">
                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $index => $kemasan)
                                    @php
                                        $kemasan = is_array($kemasan) ? $kemasan : [];
                                        $kemasanKodeTerpilih = sv($kemasan['kodeJenisKemasan'] ?? ($kemasan['kode'] ?? null));
                                    @endphp
                                    <tr>
                                        <td><input type="text" inputmode="decimal" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ sv($kemasan['jumlahKemasan'] ?? ($kemasan['jumlah'] ?? null)) }}" placeholder="contoh: 10"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ $kemasanKodeTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm" value="{{ sv($kemasan['merkKemasan'] ?? ($kemasan['merk'] ?? null), '-') }}"></td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="section-title">Data Kontainer / Peti Kemas</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $kontainers = $dataDetail['kontainer'] ?? [];
                                $kontainers = is_array($kontainers) ? $kontainers : [];
                                $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                $listTipeKontainer = [
                                    '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                    '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                    '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                ];
                                $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                            @endphp
                            <table class="table table-sm table-bordered" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Nomor Kontainer</th>
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="10%">
                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    @php
                                        $kont = is_array($kont) ? $kont : [];
                                        $kontJenisTerpilih = sv($kont['kodeJenisKontainer'] ?? null);
                                        $kontTipeTerpilih = sv($kont['kodeTipeKontainer'] ?? null);
                                        $kontUkuranTerpilih = sv($kont['kodeUkuranKontainer'] ?? null);
                                    @endphp
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ sv($kont['nomorKontainer'] ?? null) }}" placeholder="Contoh: TGHU1234567"></td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ $kontJenisTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ $kontTipeTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ $kontUkuranTerpilih == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer" title="Hapus"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                </div>
            </div>
        </div>

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', tags: true });

        $(document).on('input', '.input-decimal', function () {
            let val = $(this).val();
            val = val.replace(/[^0-9.]/g, '');
            const parts = val.split('.');
            if (parts.length > 2) val = parts[0] + '.' + parts.slice(1).join('');
            $(this).val(val);
        });
        $(document).on('blur', '.input-decimal', function () {
            let val = $(this).val().replace(/^\./, '').replace(/\.$/, '');
            $(this).val(val);
        });
        $(document).on('keypress', '.input-decimal', function (e) {
            const allowed = /[0-9.]/;
            const char = String.fromCharCode(e.which);
            if (!allowed.test(char)) e.preventDefault();
            if (char === '.' && $(this).val().includes('.')) e.preventDefault();
        });

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text)
                <option value="{{ $val }}">{{ $val }} - {{ $text }}</option>
            @endforeach
        `;
        let dokIndex = {{ count($dokumens) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><select name="dokumen[${dokIndex}][kodeDokumen]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dokumen[${dokIndex}][nomorDokumen]" class="form-control form-control-sm" value=""></td>
                    <td><input type="date" name="dokumen[${dokIndex}][tanggalDokumen]" class="form-control form-control-sm" value=""></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dokumen[${dokIndex}][kodeDokumen]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() {
            $(this).closest('tr').remove();
        });

        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            @foreach($listJenisKemasan as $kKem => $vKem)
            <option value="{{ $kKem }}">{{ $kKem }} - {{ $vKem }}</option>
            @endforeach
        `;
        let kemasanIndex = {{ count($kemasans) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" inputmode="decimal" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="0" placeholder="contoh: 10"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-" placeholder="contoh: KARTON / -"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() {
            $(this).closest('tr').remove();
        });


        const optJenisKontainer = `<option value="">-- Pilih --</option><option value="4">4 - Empty</option><option value="7">7 - LCL</option><option value="8">8 - FCL</option>`;
        const optTipeKontainer = `<option value="">-- Pilih --</option><option value="1">1 - General/Dry Cargo</option><option value="2">2 - Tunnel Type</option><option value="3">3 - Open Top Steel</option><option value="4">4 - Flat Rack</option><option value="5">5 - Reefer/Refrigerated</option><option value="6">6 - Barge Container</option><option value="7">7 - Bulk Container</option><option value="8">8 - Isotank</option><option value="99">99 - Lain-lain</option>`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option><option value="20">20 Feet</option><option value="40">40 Feet</option><option value="45">45 Feet</option> <option value="60">60 Feet</option>`;

        let kontainerIndex = {{ count($kontainers) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase" placeholder="contoh: TGHU1234567"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optTipeKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optUkuranKontainer}</select></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-kontainer').append(htmlTr);
            $(`select[name^="kontainer[${kontainerIndex}]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() {
            $(this).closest('tr').remove();
        });


        function validasiBC25() {
            let errors = [];
            let firstTab = null;

            $('#form-edit-ceisa').find('input, select, textarea').each(function() {
                let el = $(this);

                if (el.is(':disabled') || el.is('[readonly]') || el.attr('type') === 'hidden' || el.attr('type') === 'button' || el.attr('type') === 'submit') {
                    return;
                }

                let val = el.val();
                let isEmpty = !val || val.toString().trim() === '';

                if (isEmpty) {
                    let labelText = el.closest('.form-group').find('label').first().text().trim();
                    if (!labelText) labelText = el.attr('name');

                    errors.push(labelText);
                    el.addClass('border-danger');

                    if (!firstTab) {
                        let tabPane = el.closest('.tab-pane');
                        if (tabPane.length) {
                            firstTab = '#' + tabPane.attr('id');
                        }
                    }
                } else {
                    el.removeClass('border-danger');
                }
            });

            if (errors.length > 0) {
                if (firstTab) {
                    let tabId = firstTab.replace('#tab-', '');
                    $('#' + tabId + '-tab').tab('show');
                }

                let uniqueErrors = [...new Set(errors)];

                Swal.fire({
                    title: 'Field Wajib Belum Diisi!',
                    html: '<div style="text-align:left; font-size:14px; max-height: 250px; overflow-y: auto;">' +
                          'Terdapat inputan yang masih kosong. Silakan isi <b>-</b> untuk teks kosong, <b>0</b> untuk angka, dan jangan biarkan dropdown default:<br><ul style="margin-top:8px">' +
                          uniqueErrors.map(e => '<li><b>' + e + '</b></li>').join('') +
                          '</ul></div>',
                    icon: 'error',
                    confirmButtonColor: '#003366'
                });
                return false;
            }
            return true;
        }

        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();

            // if (!validasiBC25()) return; // Validasi dimatikan sesuai request user

            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data akan dikirim tanpa memuat ulang halaman.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: $(this).attr('action'),
                        type: $(this).attr('method') || 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Data telah diperbarui.',
                                icon: 'success'
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: 'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        $('.btn-collapse-barang').on('click', function() {
            let targetId = $(this).data('target');
            let icon = $(this).find('.icon-collapse');
            let isExpanded = $(targetId).hasClass('show');

            $('.collapse').collapse('hide');
            $('.icon-collapse').removeClass('fa-chevron-up').addClass('fa-chevron-down');

            if (!isExpanded) {
                $(targetId).collapse('show');
                icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
            }
        });

        // Auto Calculation Logic
        function calculateTotals() {
            let totalHarga = 0;
            let totalNetto = 0;
            let totalVolume = 0;

            let dataPPN = {
                '3': 0, // Ditangguhkan
                '5': 0, // Dibebaskan
                '6': 0, // Tidak Dipungut
                '7': 0  // Sudah Dilunasi
            };

            $('#accordionBarang .card').each(function(index) {
                let row = $(this);

                let inputHarga = row.find('input[name$="[hargaPenyerahan]"]');
                if (inputHarga.length > 0) {
                    let valHarga = parseFloat(inputHarga.val().replace(/,/g, '')) || 0;
                    totalHarga += valHarga;

                    let selectFasilitas = row.find('select[name$="[barangTarif][kodeFasilitasTarif]"]');
                    let kodeFasilitas = selectFasilitas.val();

                    let inputTarif = row.find('input[name$="[barangTarif][tarif]"]');
                    let tarif = parseFloat(inputTarif.val()) || 0;

                    let ppnRow = valHarga * (tarif / 100);

                    if (dataPPN[kodeFasilitas] !== undefined) {
                        dataPPN[kodeFasilitas] += ppnRow;
                    }
                }

                let inputNetto = row.find('input[name$="[netto]"]');
                if (inputNetto.length > 0) {
                    let valNetto = parseFloat(inputNetto.val().replace(/,/g, '')) || 0;
                    totalNetto += valNetto;
                }

                let inputVolume = row.find('input[name$="[volume]"]');
                if (inputVolume.length > 0) {
                    let valVolume = parseFloat(inputVolume.val().replace(/,/g, '')) || 0;
                    totalVolume += valVolume;
                }
            });

            let formatDecimal = function(num) {
                if(num % 1 === 0) return num.toString() + '.0000';
                return num.toFixed(4);
            };

            let formatIdr = function(num) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
            };

            $('#totalHargaPenyerahan').val(formatDecimal(totalHarga));
            $('#totalNetto').val(formatDecimal(totalNetto));
            $('#totalVolume').val(formatDecimal(totalVolume));

            let inputBruto = $('input[name="bruto"]');
            let currentBruto = parseFloat(inputBruto.val().replace(/,/g, '')) || 0;
            if (currentBruto < totalNetto) {
                inputBruto.val(formatDecimal(totalNetto));
            }

            $('#text-ppn-ditangguhkan').text(formatIdr(dataPPN['3']));
            $('#text-ppn-dibebaskan').text(formatIdr(dataPPN['5']));
            $('#text-ppn-tidak-dipungut').text(formatIdr(dataPPN['6']));
            $('#text-ppn-sudah-dilunasi').text(formatIdr(dataPPN['7']));

            let hiddenContainer = $('#hidden-pungutan-container');
            hiddenContainer.empty();

            let arrayIndex = 0;
            for (let kode in dataPPN) {
                if (dataPPN[kode] > 0) {
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeFasilitasTarif]" value="${kode}">`);
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeJenisPungutan]" value="PPN">`);
                    hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][nilaiPungutan]" value="${formatDecimal(dataPPN[kode])}">`);
                    arrayIndex++;
                }
            }
        }

        $(document).on('input change', 'input[name$="[hargaPenyerahan]"], input[name$="[netto]"], input[name$="[volume]"], select[name$="[barangTarif][kodeFasilitasTarif]"], input[name$="[barangTarif][tarif]"]', function() {
            if($(this).attr('name').indexOf('barang[') === 0) {
                calculateTotals();
            }
        });

        calculateTotals();

        $('.column-search').on('keyup', function() {
            $('#tab-pungutan tbody tr').show();

            $('.column-search').each(function() {
                let val = $(this).val().toLowerCase();
                let colIdx = $(this).data('column');

                if (val) {
                    $('#tab-pungutan tbody tr').each(function() {
                        let cellText = $(this).find('td').eq(colIdx).text().toLowerCase();
                        if (cellText.indexOf(val) === -1) {
                            $(this).hide();
                        }
                    });
                }
            });
        });

    });
</script>

{{-- Toggle BMT & Cukai per barang item --}}
@foreach($items as $index => $item)
<script>
    function toggleBmSpesifik{{ $index }}(select) {
        let isSpesifik = (select.value === '2');
        document.getElementById('bmTarifAdval{{ $index }}').style.display = isSpesifik ? 'none' : '';
        document.getElementById('bmSpesifikRow{{ $index }}').style.display = isSpesifik ? '' : 'none';
    }
    function toggleBmt{{ $index }}(cb) {
        document.getElementById('bmtPanel{{ $index }}').style.display = cb.checked ? '' : 'none';
    }
    function toggleCukai{{ $index }}(cb) {
        document.getElementById('cukaiPanel{{ $index }}').style.display = cb.checked ? '' : 'none';
    }
</script>
@endforeach

@endsection
