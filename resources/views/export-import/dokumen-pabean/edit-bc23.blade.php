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
    $listJenisKemasan = ['1A' => 'DRUM, STEEL', '1B' => 'DRUM, ALUMINIUM', '1D' => 'DRUM, PLYWOOD', '1F' => 'CONTAINER, FLEXIBLE', '1G' => 'DRUM, FIBRE', '1W' => 'DRUM, WOODEN', '2C' => 'BARREL, WOODEN', '3A' => 'JERRICAN, STEEL', '3H' => 'JERRICAN, PLASTIC', '43' => 'Bag, super bulk', '44' => 'Bag, polybag', '4A' => 'Box, steel', '4B' => 'Box, aluminium', '4C' => 'Box, natural wood', '4D' => 'Box, plywood', '4F' => 'Box, reconstituted wood', '4G' => 'Box, fibreboard', '4H' => 'Box, plastic', '5H' => 'Bag, woven plastic', '5L' => 'Bag, textile', '5M' => 'Bag, paper', '6H' => 'Composite packaging, plastic receptacle', '6P' => 'Composite packaging, glass receptacle', '7A' => 'Case, car', '7B' => 'Case, wooden', '8A' => 'Pallet, wooden', '8B' => 'Crate, wooden', '8C' => 'Bundle, wooden', 'AA' => 'Intermediate bulk container, rigid plastic', 'AB' => 'Receptacle, fibre', 'AC' => 'Receptacle, paper', 'AD' => 'Receptacle, wooden', 'AE' => 'Aerosol', 'AF' => 'Pallet, modular, collars 80cms * 60cms', 'AG' => 'Pallet, shrinkwrapped', 'AH' => 'Pallet, 100cms * 110cms', 'AI' => 'Clamshell', 'AJ' => 'Cone', 'AL' => 'Ball', 'AM' => 'Ampoule, non protected', 'AP' => 'Ampoule, protected', 'AT' => 'Atomizer', 'AV' => 'Capsule', 'B4' => 'Belt', 'BA' => 'Barrel', 'BB' => 'Bobbin', 'BC' => 'Bottlecrate, bottlerack', 'BD' => 'Board', 'BE' => 'Bundle', 'BF' => 'Balloon, non-protected', 'BG' => 'Bag', 'BH' => 'Bunch', 'BI' => 'Bin', 'BJ' => 'Bucket', 'BK' => 'Basket', 'BL' => 'Bale, compressed', 'BM' => 'Basin', 'BN' => 'Bale, non -compressed', 'BO' => 'Bottle, non-protected, cylindrical', 'BP' => 'Balloon, protected', 'BQ' => 'Bottle, protected cylindrical', 'BR' => 'Bar', 'BS' => 'Bottle, non-protected, bulbous', 'BT' => 'Bolt', 'BU' => 'Butt', 'BV' => 'Bottle, protected bulbous', 'BW' => 'Box, for liquids', 'BX' => 'Box', 'BY' => 'Board, in bundle/bunch/truss', 'BZ' => 'Bars, in bundle/bunch/truss', 'CA' => 'Can, rectangular', 'CB' => 'Beer crate', 'CC' => 'Churn', 'CD' => 'Can, with handle and spout', 'CE' => 'Creel', 'CF' => 'Coffer', 'CG' => 'Cage', 'CH' => 'Chest', 'CI' => 'Canister', 'CJ' => 'Coffin', 'CK' => 'Cask', 'CL' => 'Coil', 'CM' => 'Card', 'CN' => 'Cont,not otherwise specfied as transport equipment', 'CO' => 'Carboy, non-protected', 'CP' => 'Carboy, protected', 'CQ' => 'Cartridge', 'CR' => 'Crate', 'CS' => 'Case', 'CT' => 'Carton', 'CU' => 'Cup', 'CV' => 'Cover', 'CW' => 'Cage, roll', 'CX' => 'Can, cylindical', 'CY' => 'Cylinder', 'CZ' => 'Canvas', 'DA' => 'Crate, multiple layer, plastic', 'DB' => 'Crate, multiple layer, wooden', 'DC' => 'Crate, multiple layer, cardboard', 'DG' => 'Cage, Commonwealth Handling Equipment Pool (CHEP)', 'DH' => 'Box,Commnwealth Hndling Equipmnt Pool/CHEP,Eurobox', 'DI' => 'Drum, iron', 'DJ' => 'Demijohn, non-protected', 'DK' => 'Crate, bulk, cardboard', 'DL' => 'Crate, bulk, plastic', 'DM' => 'Crate, bulk, wooden', 'DN' => 'Dispenser', 'DP' => 'Demijohn, protected', 'DR' => 'Drum', 'DS' => 'Tray, one layer no cover, plastic', 'DT' => 'Tray, one layer no cover, wooden', 'DU' => 'Tray, one layer no cover, polystyrene', 'DV' => 'Tray, one layer no cover, cardboard', 'DW' => 'Tray, two layers no cover, plastic tray', 'DX' => 'Tray, two layers no cover, wooden', 'DY' => 'Tray, two layers no cover, cardboard', 'EC' => 'Bag, plastic', 'ED' => 'Case, with pallet base', 'EE' => 'Case, with pallet base, wooden', 'EF' => 'Case, with pallet base, cardboard', 'EG' => 'Case, with pallet base, plastic', 'EH' => 'Case, with pallet base, metal', 'EI' => 'Case, isothermic', 'EN' => 'Envelope', 'FB' => 'Flexibag', 'FC' => 'Fruit crate', 'FD' => 'Framed crate', 'FE' => 'Flexitank', 'FI' => 'Firkin', 'FL' => 'Flask', 'FO' => 'Footlocker', 'FP' => 'Filmpack', 'FR' => 'Frame', 'FT' => 'Foodtainer', 'FW' => 'Cart, flatbed', 'FX' => 'Bag, flexible container', 'GB' => 'Gas bottle', 'GI' => 'Girder', 'GL' => 'Container, gallon', 'GR' => 'Receptacle, glass', 'GU' => 'Tray, containing horizontally stacked flat items', 'GY' => 'Bag, gunny', 'GZ' => 'Girders, in bundle/bunch/truss', 'HA' => 'Basket, with handle, plastic', 'HB' => 'Basket, with handle, wooden', 'HC' => 'Basket, with handle, cardboard', 'HG' => 'Hogshead', 'HN' => 'Hanger', 'HR' => 'Hamper', 'HZ' => 'bukan kaleng kaleng', 'IA' => 'Package, display, wooden', 'IB' => 'Package, display, cardboard', 'IC' => 'Package, display, plastic', 'ID' => 'Package, display, metal', 'IE' => 'Package, show', 'IF' => 'Package, flow', 'IG' => 'Package, paper wrapped', 'IH' => 'Drum, plastic', 'IK' => 'Package, cardboard, with bottle grip-holes', 'IL' => 'Tray, rigid, lidded stackable (CEN TS 14482:2002)', 'IN' => 'Ingot', 'IZ' => 'ingots, in bundle/bunch/truss', 'JB' => 'Bag, jumbo', 'JC' => 'Jerrican, rectangular', 'JG' => 'Jug', 'JR' => 'Jar', 'JT' => 'Jutebag', 'JY' => 'Jerrican, cylindrical', 'KG' => 'Keg', 'KI' => 'Kit', 'KR' => 'karung', 'LE' => 'Luggage', 'LG' => 'Log', 'LT' => 'Lot', 'LU' => 'Lug', 'LV' => 'Liftvan', 'LZ' => 'Logs, in bundle/bunch/truss', 'MA' => 'Crate, metal', 'MB' => 'Multiply bag', 'MC' => 'milk crate', 'ME' => 'Container, metal', 'MR' => 'Receptacle, metal', 'MS' => 'Multiwall sack', 'MT' => 'Mat', 'MW' => 'Receptacle, plastic wrapped', 'MX' => 'Macontoh box', 'NA' => 'Not available', 'NE' => 'Unpacked or unpackaged', 'NF' => 'Unpacked or unpackaged, single unit', 'NG' => 'Unpacked or unpackaged, multiple units', 'NS' => 'Nest', 'NT' => 'Net', 'NU' => 'Net, tube, plastic', 'NV' => 'Net, tube, textile', 'OA' => 'Pallet, CHEP 40 cm x 60 cm', 'OB' => 'Pallet, CHEP 80 cm x 120 cm', 'OC' => 'Pallet, CHEP 100 cm x 120 cm', 'OD' => 'Pallet, AS 4068-1993', 'OE' => 'Pallet, ISO T11', 'OF' => 'Platform, unspecified weight or dimension', 'OK' => 'Block', 'OT' => 'Octabin', 'OU' => 'Container, outer', 'P2' => 'Pan', 'PA' => 'Packet', 'PB' => 'Pallet, box Combined open-ended box and pallet', 'PC' => 'Parcel', 'PD' => 'Pallet, modular, collars 80cms * 100cms', 'PE' => 'Pallet, modular, collars 80cms * 120cms', 'PF' => 'Pen', 'PG' => 'Plate', 'PH' => 'Pitcher', 'PI' => 'Pipe', 'PJ' => 'Punnet', 'PK' => 'Package', 'PL' => 'Pail', 'PN' => 'Plank', 'PO' => 'Pouch', 'PP' => 'Piece', 'PR' => 'Receptacle, plastic', 'PT' => 'Pot', 'PU' => 'Tray', 'PV' => 'Pipes, in bundle/bunch/truss', 'PX' => 'Pallet', 'PY' => 'Plates, in bundle/bunch/truss', 'PZ' => 'Pipes, in bundle/bunch/truss', 'QA' => 'Drum, steel, non-removable head', 'QB' => 'Drum, steel, removable head', 'QC' => 'Drum, aluminium, non-removable head', 'QD' => 'Drum, aluminium, removable head', 'QF' => 'Drum, plastic, non-removable head', 'QG' => 'Drum, plastic, removable head', 'QH' => 'Barrel, wooden, bung type', 'QJ' => 'Barrel, wooden, removable head', 'QK' => 'Jerrican, steel, non-removable head', 'QL' => 'Jerrican, steel, removable head', 'QM' => 'Jerrican, plastic, non-removable head', 'QN' => 'Jerrican, plastic, removable head', 'QP' => 'Box, wooden, natural wood, ordinary', 'QQ' => 'Box, wooden, natural wood, with sift proof walls', 'QR' => 'Box, plastic, expanded', 'QS' => 'Box, plastic, solid', 'RD' => 'Rod', 'RG' => 'Ring', 'RJ' => 'Rack, clothing hanger', 'RK' => 'Rack', 'RL' => 'Reel', 'RO' => 'Roll', 'RT' => 'Rednet', 'RZ' => 'Rods, in bundle/ bunch/truss', 'SA' => 'Sack', 'SB' => 'Slab', 'SC' => 'Shallow crate', 'SD' => 'Spindle', 'SE' => 'Sea-chest', 'SH' => 'Sachet', 'SI' => 'Skid', 'SK' => 'Skeleton case', 'SL' => 'Slipsheet', 'SM' => 'Sheetmetal', 'SO' => 'Spool', 'SP' => 'Sheet, plastic wrapping', 'SS' => 'Case, steel', 'ST' => 'Sheet', 'SU' => 'Suitcase', 'SV' => 'Envelope, steel', 'SW' => 'Shrinkwrapped', 'SX' => 'Set', 'SY' => 'Sleeve', 'SZ' => 'Sheets, in bundle/bunch/truss', 'T1' => 'Tablet', 'TB' => 'Tub', 'TC' => 'Tea-chest', 'TD' => 'Collapsible tube', 'TE' => 'Tyre', 'TG' => 'Tank container, generic', 'TI' => 'Tierce', 'TK' => 'Tank, rectangular', 'TL' => 'Tub, with lid', 'TN' => 'Tin', 'TO' => 'Tun', 'TP' => 'Tray', 'TR' => 'Trunk', 'TS' => 'Truss', 'TT' => 'Bag, tote', 'TU' => 'Tube', 'TV' => 'Tube, with nozzle', 'TW' => 'Pallet, triwall', 'TY' => 'Tank, cylindrical', 'TZ' => 'Tubes, in bundle/bunch/truss', 'UC' => 'Uncaged', 'UN' => 'Unpackage', 'VA' => 'Vat', 'VG' => 'Bulk, gas ( at 1031 mbar and 15C )', 'VI' => 'Vial', 'VK' => 'Vanpack', 'VL' => 'Bulk, liquid', 'VN' => 'Vehicle', 'VO' => 'Bulk, solid, large particles ("nodules")', 'VP' => 'Vacuumpacked', 'VQ' => 'Bulk,liquefied gas (at abnorml temprture/pressure)', 'VR' => 'Bulk, solid, granular particles ("grains")', 'VS' => 'Bulk, scrap metal', 'VY' => 'Bulk, solid, fine particles ("powders")', 'WA' => 'Intermediate bulk container', 'WB' => 'Wickerbottle', 'WC' => 'Intermediate bulk container, steel', 'WD' => 'Intermediate bulk container, aluminium', 'WF' => 'Intermediate bulk container, metal', 'WG' => 'Intermediate bulk cont,steel,pressurised >10 kpa', 'WH' => 'Intermedt bulk cont,aluminium,pressurised >10 kpa', 'WJ' => 'Intermediate bulk container,metal, pressure 10 kpa', 'WK' => 'Intermediate bulk container, steel, liquid', 'WL' => 'Intermediate bulk container, aluminium, liquid', 'WM' => 'Intermediate bulk container, metal, liquid', 'WN' => 'Intermd bulk cont,woven plastic,without coat/liner', 'WP' => 'Intermediate bulk container, woven plastic, coated', 'WQ' => 'Intermediate bulk cont,woven plastic,with liner', 'WR' => 'Intermedt bulk cont,woven plastic,coated and liner', 'WS' => 'Intermediate bulk container, plastic film', 'WT' => 'Intermediate bulk cont,textile with out coat/liner', 'WU' => 'Intermdte bulk cont,natural wood,with inner liner', 'WV' => 'Intermediate bulk container, textile, coated', 'WW' => 'Intermediate bulk container, textile, with liner', 'WX' => 'Intermediate bulk cont,textile,coated and liner', 'WY' => 'Intermediate bulk cont,plywood,with inner liner', 'WZ' => 'Intermd bulk cont,reconstttd wood,with inner liner', 'XA' => 'Bag, woven plastic, without inner coat/liner', 'XB' => 'Bag, woven plastic, sift proof', 'XC' => 'Bag, woven plastic, water resistant', 'XD' => 'Bag, plastics film', 'XF' => 'Bag, textile, without inner coat/liner', 'XG' => 'Bag, textile, sift proof', 'XH' => 'Bag, textile, water resistant', 'XJ' => 'Bag, paper, multi-wall', 'XK' => 'Bag, paper, multi-wall, water resistant', 'XN' => 'test', 'YA' => 'Compsite packging,plastic receptacle in steel drum', 'YB' => 'Compste packgng,plastc recptcle in steel crate box', 'YC' => 'Compste packgng,plastic recptcle in aluminium drum', 'YD' => 'Compste packgng,plastic recptcle in alumnium crate', 'YF' => 'Compsite packging,plastic receptacle in wooden box', 'YG' => 'Compste packgng,plastic receptacle in plywood drum', 'YH' => 'Compste packging,plastic receptacle in plywood box', 'YJ' => 'Compsite packging,plastic receptacle in fibre drum', 'YK' => 'Compste packgng,plastic recptcle in fibreboard box', 'YL' => 'Compste packgng,plastic receptacle in plastic drum', 'YM' => 'Compsite packgng,plstc recptcle in solid plstc box', 'YN' => 'Composite packaging,glass receptacle in steel drum', 'YP' => 'Compste packgng,glass recptacle in steel crate box', 'YQ' => 'Compste packgng,glass receptacle in aluminium drum', 'YR' => 'Compste packgng,glass recptacle in aluminium crate', 'YS' => 'Composite packaging,glass receptacle in wooden box', 'YT' => 'Compsite packging,glass receptacle in plywood drum', 'YV' => 'Compste packgng,glass recptcle in wickrwork hamper', 'YW' => 'Composite packaging,glass receptacle in fibre drum', 'YX' => 'Compste packgng,glass receptacle in fibreboard box', 'YY' => 'Compste pckgng,glss recptcl in expndbl plastc pack', 'YZ' => 'Compsite packgng,glass recptcle in solid plstc pck', 'ZA' => 'Intermediate bulk container, paper, multi-wall', 'ZB' => 'Bag, large', 'ZC' => 'Intermd bulk cont,paper,multi-wall,water resistant', 'ZD' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,solid', 'ZF' => 'Intermd bulk cont,rgid plstc,freestandng,solds', 'ZG' => 'Intermdbulk cnt,rgd plstc,w/strctrl equipm,pressrd', 'ZH' => 'Intermd bulk cont,rgd plstc,freestnd,pressurised', 'ZJ' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,lquid', 'ZK' => 'Intermd bulk cont,rigid plstc,freestanding,liquids', 'ZL' => 'Intermd bulk cont,composite,rigid plastic,solids', 'ZM' => 'Intermd bulk cont,compste,flexbl plastic, solids', 'ZN' => 'Intermd bulk cont,compsit,rgid plstc,pressurised', 'ZP' => 'Intermd bulk cont,compsit,flexbl plstc,pressurised', 'ZQ' => 'Intermd bulk cont,composite,rigid plastic,liquids', 'ZR' => 'Intermd bulk cont,compsite,flexible plastc,liquids', 'ZS' => 'Intermediate bulk container, composite', 'ZT' => 'Intermediate bulk container, fibreboard', 'ZU' => 'Intermediate bulk container, flexible', 'ZV' => 'Intermediate bulk container,metal,other than steel', 'ZW' => 'Intermediate bulk container, natural wood', 'ZX' => 'Intermediate bulk container, plywood', 'ZY' => 'Intermediate bulk container, reconstituted wood', 'ZZ' => 'Mutually defined'];

    $listSatuanBarang = ['6' => 'small spray', '8' => 'heat lot', '10' => 'group', '13' => 'ration', '14' => 'shot', '15' => 'stick, military', '16' => 'hundred fifteen kg drum', '17' => 'hundred lb drum', '18' => 'fiftyfive gallon (US) drum', '19' => 'tank truck', '20' => 'twenty foot container', '21' => 'forty foot container', '22' => 'decilitre per gram', '24' => 'theoretical pound', '26' => 'actual ton', '28' => 'kilogram per square metre', '29' => 'pound per thousand square foot', '30' => 'horse power day per air dry metric ton', '31' => 'catch weight', '32' => 'kilogram per air dry metric ton', '33' => 'kilopascal square metre per gram', '34' => 'kilopascal per millimetre', '35' => 'millilitre per square centimetre second', '36' => 'cubic foot per minute per square foot', '38' => 'ounce per square foot per 0,01inch', '40' => 'millilitre per second', '43' => 'super bulk bag', '44' => 'fivehundred kg bulk bag', '46' => 'fifty lb bulk bag', '47' => 'fifty lb bag', '48' => 'bulk car load', '53' => 'theoretical kilogram', '54' => 'theoretical tonne', '57' => 'mesh', '58' => 'net kilogram', '60' => 'percent weight', '61' => 'part per billion (US)', '62' => 'percent per 1000 hour', '63' => 'failure rate in time', '64' => 'pound per square inch, gauge', '66' => 'oersted', '71' => 'volt ampere per pound', '72' => 'watt per pound', '73' => 'ampere tum per centimetre', '78' => 'kilogauss', '84' => 'kilopound-force per square inch', '85' => 'foot pound-force', '89' => 'poise', '92' => 'calorie per cubic centimetre', '93' => 'calorie per gram', '94' => 'curl unit', '96' => 'ten thousand gallon (US) tankcar', '97' => 'ten kg drum', '98' => 'fifteen kg drum', '--' => '--', '1C' => 'locomotive count', '1F' => 'train mile', '1I' => 'fixed rate', '1L' => 'total car count', '1M' => 'total car mile', '1X' => 'quarter mile', '2B' => 'radian per second squared', '2C' => 'roentgen', '2H' => 'volt DC', '2I' => 'British thermal unit(international table) per hour', '2J' => 'cubic centimetre per second', '2K' => 'cubic foot per hour', '2L' => 'cubic foot per minute', '2M' => 'centimetre per second', '2P' => 'kilobyte', '2Q' => 'kilobecquerel', '2R' => 'kilocurie', '2U' => 'megagram', '2V' => 'megagram per hour', '2W' => 'bin', '2X' => 'metre per minute', '2Z' => 'millivolt', '3E' => 'pound per pound of product', '3G' => 'pound per piece of product', '4A' => 'bobbin', '4G' => 'microlitre', '4H' => 'micrometre (micron)', '4L' => 'megabyte', '4M' => 'milligram per hour', '4O' => 'microfarad', '4P' => 'newton per metre', '4R' => 'ounce foot', '4T' => 'picofarad', '4U' => 'pound per hour', '4W' => 'ton (US) per hour', '5B' => 'batch', '5C' => 'gallon(US) per thousand', '5E' => 'MMSCF/day', '5G' => 'pump', '5H' => 'stage', '5K' => 'count per minute', '5P' => 'seismic level', '5Q' => 'seismic line', 'A1' => '15 C calorie', 'A11' => 'angstrom', 'A12' => 'astronomical unit', 'A13' => 'attojoule', 'A15' => 'barn per electronvolt', 'A2' => 'ampere per centimetre', 'A20' => 'British thermal unit/second squarefoot d/Rankine', 'A21' => 'British thermal unit (IT) per pound degree Rankine', 'A23' => 'Britishthermalunit/hour square foot degree Rankine', 'A24' => 'candela per square metre', 'A26' => 'coulomb metre', 'A27' => 'coulomb metre squared per volt', 'A28' => 'coulomb per cubic centimetre', 'A3' => 'ampere per millimetre', 'A32' => 'coulomb per mole', 'A33' => 'coulomb per square centimetre', 'A36' => 'cubic centimetre per mole', 'A38' => 'cubic metre per coulomb', 'A4' => 'ampere per square centimetre', 'A41' => 'ampere per square metre', 'A43' => 'deadweight tonnage', 'A44' => 'decalitre', 'A45' => 'decametre', 'A47' => 'decitex', 'A49' => 'denier', 'A51' => 'dyne second per centimetre', 'A52' => 'dyne second per centimetre to the fifth power', 'A56' => 'electronvolt square metre per kilogram', 'A58' => 'erg per centimetre', 'A59' => '8-part cloud cover', 'A6' => 'ampere per square metre kelvin squared', 'A60' => 'erg per cubic centimetre', 'A62' => 'erg per gram second', 'A64' => 'erg per second square centimetre', 'A65' => 'erg per square centimetre second', 'A66' => 'erg square centimetre', 'A67' => 'erg square centimetre per gram', 'A69' => 'farad per metre', 'A7' => 'ampere per square millimetre', 'A70' => 'femtojoule', 'A71' => 'femtometre', 'A73' => 'foot per second squared', 'A74' => 'foot pound-force per second', 'A76' => 'gal', 'A77' => 'Gaussian CGS unit of displacement', 'A78' => 'Gaussian CGS unit of electric current', 'A79' => 'Gaussian CGS unit of electric charge', 'A81' => 'Gaussian CGS unit of electric polarization', 'A82' => 'Gaussian CGS unit of electric potential', 'A83' => 'Gaussian CGS unit of magnetization', 'A85' => 'gigaelectronvolt', 'A87' => 'gigaohm', 'A88' => 'gigaohm metre', 'A9' => 'rate', 'A93' => 'gram per cubic metre', 'A95' => 'gray', 'A96' => 'gray per second', 'AA' => 'ball', 'AB' => 'bulk pack', 'ACR' => 'Acre (4840 yd2)', 'ACT' => 'activity', 'AD' => 'byte', 'AED' => 'United Arab Emirates Dirham', 'AFN' => 'Afghanistan Afghani', 'AH' => 'additional minute', 'AJ' => 'cop', 'AK' => 'fathom', 'AL' => 'access line', 'AMH' => 'Ampere-hour (3,6 kC)', 'ANG' => 'Netherlands Antilles Guilder', 'APZ' => 'Ounce GB,US (31,10348 g)', 'AQ' => 'anti-hemophilic factor (AHF) unit', 'ARE' => 'Are (100m2)', 'AS' => 'assortment', 'ASM' => 'alcoholic strength by mass', 'ASU' => 'alcoholic strength by volume', 'ATT' => 'Technical atmosphere (98066,5 Pa)', 'AUD' => 'Australia Dollar', 'AV' => 'capsule', 'AW' => 'powder filled vial', 'AY' => 'assembly', 'AZN' => 'Azerbaijan Manat', 'B0' => 'Btu per cubic foot', 'B10' => 'bit per second', 'B12' => 'joule per metre', 'B14' => 'joule per metre to the fourth power', 'B15' => 'joule per mole', 'B17' => 'credit', 'B19' => 'digit', 'B20' => 'joule square metre per kilogram', 'B21' => 'kelvin per watt', 'B22' => 'kiloampere', 'B26' => 'kilocoulomb', 'B27' => 'kilocoulomb per cubic metre', 'B29' => 'kiloelectronvolt', 'B34' => 'kilogram per cubic decimetre', 'B35' => 'kilogram per litre', 'B38' => 'kilogram-force metre', 'B39' => 'kilogram-force metre per second', 'B4' => 'barrel, imperial', 'B41' => 'kilojoule per kelvin', 'B43' => 'kilojoule per kilogram kelvin', 'B45' => 'kilomole', 'B46' => 'kilomole per cubic metre', 'B49' => 'kiloohm', 'B5' => 'billet', 'B50' => 'kiloohm metre', 'B51' => 'kilopond', 'B54' => 'kilosiemens per metre', 'B55' => 'kilovolt per metre', 'B56' => 'kiloweber per metre', 'B59' => 'lumen hour', 'B6' => 'bun', 'B60' => 'lumen per square metre', 'B64' => 'lux second', 'B65' => 'maxwell', 'B66' => 'megaampere per square metre', 'B67' => 'megabecquerel per kilogram', 'B68' => 'gigabit', 'B71' => 'megaelectronvolt', 'B74' => 'meganewton metre', 'B78' => 'megavolt', 'B79' => 'megavolt per metre', 'B81' => 'reciprocal metre squared reciprocal second', 'B85' => 'microbar', 'B86' => 'microcoulomb', 'B87' => 'microcoulomb per cubic metre', 'B88' => 'microcoulomb per square metre', 'B89' => 'microfarad per metre', 'B9' => 'batt', 'B90' => 'microhenry', 'B91' => 'microhenry per metre', 'B93' => 'micronewton metre', 'B99' => 'microsiemens', 'BAM' => 'Bosnia and Herzegovina Convertible Marka', 'BAR' => 'Bar', 'BB' => 'base box', 'BBD' => 'Barbados Dollar', 'BDT' => 'Bangladesh Taka', 'BFT' => 'board foot', 'BHD' => 'Bahrain Dinar', 'BHP' => 'brake horse power', 'BIF' => 'Burundi Franc', 'BL' => 'bale', 'BLD' => 'Dry barrel (115,627 dm3)', 'BMD' => 'Bermuda Dollar', 'BND' => 'Brunei Darussalam Dollar', 'BO' => 'bottle', 'BOB' => 'Bolivia Bolviano', 'BP' => 'hundred board foot', 'BPM' => 'beats per minute', 'BQL' => 'Becquerel', 'BR' => 'bar [unit of packaging]', 'BRL' => 'Brazil Real', 'BSD' => 'Bahamas Dollar', 'BT' => 'bolt', 'BTU' => 'British thermal unit', 'BUA' => 'Bushel (35,2391 dm3)', 'BUI' => 'Bushel (36,36874 dm3)', 'BW' => 'base weight', 'BWP' => 'Botswana Pula', 'BX' => 'box', 'BYN' => 'Belarus Ruble', 'BZ' => 'million BTUs', 'BZD' => 'Belize Dollar', 'C0' => 'call', 'C12' => 'milligram per metre', 'C13' => 'milligray', 'C14' => 'millihenry', 'C15' => 'millijoule', 'C2' => 'carset', 'C22' => 'millinewton per metre', 'C23' => 'milliohm metre', 'C24' => 'millipascal second', 'C26' => 'millisecond', 'C30' => 'millivolt per metre', 'C34' => 'mole', 'C35' => 'mole per cubic decimetre', 'C36' => 'mole per cubic metre', 'C38' => 'mole per litre', 'C4' => 'carload', 'C41' => 'nanofarad', 'C42' => 'nanofarad per metre', 'C43' => 'nanohenry', 'C47' => 'nanosecond', 'C49' => 'nanowatt', 'C5' => 'cost', 'C53' => 'newton metre second', 'C54' => 'newton metre squared kilogram squared', 'C56' => 'newton per square millimetre', 'C60' => 'ohm centimetre', 'C65' => 'pascal second', 'C66' => 'pascal second per cubic metre', 'C67' => 'pascal second per metre', 'C68' => 'petajoule', 'C69' => 'phon', 'C7' => 'centipoise', 'C73' => 'picohenry', 'C75' => 'picowatt', 'C79' => 'kilovolt ampere hour', 'C8' => 'millicoulomb per kilogram', 'C82' => 'radian square metre per mole', 'C85' => 'reciprocal angstrom', 'C88' => 'reciprocal electron volt per cubic metre', 'C90' => 'reciprocal joule per cubic metre', 'C92' => 'reciprocal metre', 'C94' => 'reciprocal minute', 'C95' => 'reciprocal mole', 'C99' => 'reciprocal second per metre squared', 'CAD' => 'Canada Dollar', 'CCT' => 'Carrying capacity in metric tonnes', 'CDF' => 'Congo/Kinshasa Franc', 'CEL' => 'Degree celcius', 'CEN' => 'Hundred', 'CG' => 'card', 'CGM' => 'centigram', 'CHF' => 'Switzerland Franc', 'CJ' => 'cone', 'CKG' => 'Coulomb per kilogram', 'CMT' => 'Centimetre', 'CNP' => 'Hundred packs', 'CNY' => 'China Yuan Renminbi', 'COP' => 'Colombia Peso', 'CR' => 'crate', 'CS' => 'case', 'CT' => 'carton', 'CTG' => 'content gram', 'CTM' => 'Metric carat (200 mg = 2.10-4 kg)', 'CUR' => 'Curie', 'CVE' => 'Cape Verde Escudo', 'CWA' => 'Hundredweight, US (45,3592 kg)', 'CY' => 'cylinder', 'D04' => 'lot [unit of weight]', 'D10' => 'siemens per metre', 'D11' => 'mebibit', 'D12' => 'siemens square metre per mole', 'D13' => 'sievert', 'D16' => 'square centimetre per erg', 'D17' => 'square centimetre per steradian erg', 'D18' => 'metre kelvin', 'D19' => 'square metre kelvin per watt', 'D2' => 'reciprocal second per steradian metre squared', 'D20' => 'square metre per joule', 'D22' => 'square metre per mole', 'D23' => 'pen gram (protein)', 'D25' => 'square metre per steradian joule', 'D27' => 'steradian', 'D28' => 'syphon', 'D29' => 'terahertz', 'D31' => 'terawatt', 'D36' => 'megabit', 'D37' => 'calorie (thermochemical) per gram kelvin', 'D42' => 'tropical year', 'D43' => 'unified atomic mass unit', 'D45' => 'volt squared per kelvin squared', 'D46' => 'volt - ampere', 'D49' => 'millivolt per kelvin', 'D5' => 'kilogram per square centimetre', 'D50' => 'volt per metre', 'D52' => 'watt per kelvin', 'D54' => 'watt per square metre', 'D56' => 'watt per square metre kelvin to the fourth power', 'D57' => 'watt per steradian', 'D58' => 'watt per steradian square metre', 'D59' => 'watt per metre', 'D6' => 'roentgen per second', 'D61' => 'minute [unit of angle]', 'D64' => 'block', 'D65' => 'round', 'D66' => 'cassette', 'D67' => 'dollar per hour', 'D69' => 'inch to the fourth power', 'D7' => 'sandwich', 'D70' => 'International Table (IT) calorie', 'D71' => 'IT calorie per second centimetre kelvin', 'D72' => 'IT calorie per second square centimetre kelvin', 'D74' => 'kilogram per mole', 'D75' => 'calorie (international table) per gram', 'D76' => 'calorie (international table) per gram kelvin', 'D79' => 'beam', 'D8' => 'draize score', 'D82' => 'microvolt', 'D83' => 'millinewton metre', 'D85' => 'microwatt per square metre', 'D88' => 'millicoulomb per cubic metre', 'D89' => 'millicoulomb per square metre', 'D90' => 'cubic metre (net)', 'D91' => 'rem', 'D94' => 'second per cubic metre radian', 'D95' => 'joule per gram', 'D97' => 'pallet/unit load', 'D99' => 'sleeve', 'DAA' => 'Decare', 'DAD' => 'Ten day', 'DB' => 'dry pound', 'DBC' => 'Decade (ten years)', 'DC' => 'disk (disc)', 'DD' => 'degree [unit of angle]', 'DE' => 'deal', 'DEC' => 'decade', 'DJF' => 'Djibouti Franc', 'DKK' => 'Denmark Krone', 'DMA' => 'cubic decametre', 'DMK' => 'Square decimetre', 'DMO' => 'standard kilolitre', 'DMQ' => 'Cubic decimetre', 'DN' => 'decinewton metre', 'DOP' => 'Dominican Republic Peso', 'DPC' => 'dozen piece', 'DPR' => 'Dozen pairs', 'DPT' => 'Displecement tonnege', 'DS' => 'display', 'DTN' => 'Centner, metric (100 kg)', 'DU' => 'dyne', 'DX' => 'dyne per centimetre', 'DZD' => 'Algeria Dinar', 'DZN' => 'Dozen', 'E01' => 'newton per square centimetre', 'E07' => 'megawatt hour per hour', 'E08' => 'megawatt per hertz', 'E09' => 'milliampere hour', 'E10' => 'degree day', 'E11' => 'gigacalorie', 'E12' => 'mille', 'E14' => 'kilocalorie (international table)', 'E16' => 'million Btu(IT) per hour', 'E17' => 'cubic foot per second', 'E2' => 'belt', 'E20' => 'megabit per second', 'E21' => 'shares', 'E23' => 'tyre', 'E25' => 'active unit', 'E31' => 'square metre per litre', 'E33' => 'foot per thousand', 'E34' => 'gigabyte', 'E35' => 'terabyte', 'E36' => 'petabyte', 'E4' => 'gross kilogram', 'E40' => 'part per hundred thousand', 'E42' => 'kilogram-force per square centimetre', 'E43' => 'joule per square centimetre', 'E44' => 'kilogram-force metre per square centimetre', 'E45' => 'milliohm', 'E46' => 'kilowatt hour per cubic metre', 'E47' => 'kilowatt hour per kelvin', 'E50' => 'accounting unit', 'E53' => 'test', 'E54' => 'trip', 'E55' => 'use', 'E56' => 'well', 'E57' => 'zone', 'E58' => 'exabit per second', 'E61' => 'tebibyte', 'E63' => 'mebibyte', 'E64' => 'kibibyte', 'E65' => 'exbibit per metre', 'E69' => 'gibibit per metre', 'E70' => 'gibibit per square metre', 'E71' => 'gibibit per cubic metre', 'E72' => 'kibibit per metre', 'E73' => 'kibibit per square metre', 'E74' => 'kibibit per cubic metre', 'E75' => 'mebibit per metre', 'E76' => 'mebibit per square metre', 'E77' => 'mebibit per cubic metre', 'E79' => 'petabit per second', 'E82' => 'pebibit per cubic metre', 'E85' => 'tebibit per metre', 'E87' => 'tebibit per square metre', 'E88' => 'bit per metre', 'E89' => 'bit per square metre', 'E90' => 'reciprocal centimetre', 'E92' => 'cubic decimetre per hour', 'E93' => 'kilogram per hour', 'E94' => 'kilomole per second', 'E96' => 'degree per second', 'E97' => 'millimetre per degree Celcius metre', 'E98' => 'degree celsius per kelvin', 'E99' => 'percent per bar', 'EA' => 'each', 'EB' => 'electronic mail box', 'EP' => 'eleven pack', 'EUR' => 'Euro Member Countries', 'F01' => 'bit per cubic metre', 'F02' => 'kelvin per kelvin', 'F04' => 'millibar per bar', 'F05' => 'megapascal per bar', 'F07' => 'pascal per bar', 'F08' => 'milliampere per inch', 'F10' => 'kelvin per hour', 'F11' => 'kelvin per minute', 'F12' => 'kelvin per second', 'F13' => 'slug', 'F14' => 'gram per kelvin', 'F17' => 'pound-force per foot', 'F18' => 'kilogram square centimetre', 'F19' => 'kilogram square millimetre', 'F23' => 'gram per cubic decimetre', 'F26' => 'gram per day', 'F27' => 'gram per hour', 'F29' => 'gram per second', 'F31' => 'kilogram per minute', 'F33' => 'milligram per minute', 'F35' => 'gram per day kelvin', 'F36' => 'gram per hour kelvin', 'F38' => 'gram per second kelvin', 'F39' => 'kilogram per day kelvin', 'F40' => 'kilogram per hour kelvin', 'F41' => 'kilogram per minute kelvin', 'F42' => 'kilogram per second kelvin', 'F43' => 'milligram per day kelvin', 'F44' => 'milligram per hour kelvin', 'F46' => 'milligram per second kelvin', 'F48' => 'pound-force per inch', 'F50' => 'micrometre per kelvin', 'F53' => 'millimetre per kelvin', 'F54' => 'milliohm per metre', 'F55' => 'ohm per mile', 'F56' => 'ohm per kilometre', 'F59' => 'milliampere per bar', 'F61' => 'kelvin per bar', 'F62' => 'gram per day bar', 'F63' => 'gram per hour bar', 'F64' => 'gram per minute bar', 'F65' => 'gram per second bar', 'F66' => 'kilogram per day bar', 'F67' => 'kilogram per hour bar', 'F69' => 'kilogram per second bar', 'F70' => 'milligram per day bar', 'F71' => 'milligram per hour bar', 'F72' => 'milligram per minute bar', 'F74' => 'gram per bar', 'F75' => 'milligram per bar', 'F76' => 'milliampere per millimetre', 'F77' => 'pascal second per kelvin', 'F78' => 'inch of water', 'F79' => 'inch of mercury', 'F80' => 'water horse power', 'F82' => 'hektopascal per kelvin', 'F83' => 'kilopascal per kelvin', 'F84' => 'millibar per kelvin', 'F85' => 'megapascal per kelvin', 'F86' => 'poise per kelvin', 'F89' => 'newton metre per degree', 'F9' => 'fibre per cubic centimetre of air', 'F90' => 'newton metre per ampere', 'F91' => 'bar litre per second', 'F92' => 'bar cubic metre per second', 'F94' => 'hektopascal cubic metre per second', 'F95' => 'millibar litre per second', 'F97' => 'megapascal litre per second', 'F99' => 'pascal litre per second', 'FAH' => 'degree Fahrenheit', 'FAR' => 'farad', 'FB' => 'field', 'FBM' => 'fibre metre', 'FC' => 'thousand cubic foot', 'FD' => 'million particle per cubic foot', 'FE' => 'track foot', 'FF' => 'hundred cubic metre', 'FG' => 'transdermal patch', 'FH' => 'micromole', 'FJD' => 'Fiji Dollar', 'FKP' => 'Falkland Islands (Malvinas) Pound', 'FL' => 'flake ton', 'FM' => 'million cubic foot', 'FOT' => 'Foot (0.3048 m)', 'FR' => 'foot per minute', 'FTK' => 'Square foot', 'FTQ' => 'Cubic foot', 'G01' => 'pascal cubic metre per second', 'G05' => 'metre per bar', 'G06' => 'millimetre per bar', 'G08' => 'square inch per second', 'G09' => 'square metre per second kelvin', 'G10' => 'stokes per kelvin', 'G11' => 'gram per cubic centimetre bar', 'G12' => 'gram per cubic decimetre bar', 'G16' => 'kilogram per cubic centimetre bar', 'G17' => 'kilogram per litre bar', 'G18' => 'kilogram per cubic metre bar', 'G19' => 'newton metre per kilogram', 'G2' => 'US gallon per minute', 'G20' => 'pound-force foot per pound', 'G21' => 'cup [unit of volume]', 'G23' => 'peck', 'G24' => 'tablespoon (US)', 'G25' => 'teaspoon (US)', 'G26' => 'stere', 'G27' => 'cubic centimetre per kelvin', 'G28' => 'litre per kelvin', 'G3' => 'Imperial gallon per minute', 'G30' => 'pH (potential of Hydrogen)', 'G31' => 'kilogram per cubic centimetre', 'G32' => 'ounce (avoirdupois) per cubic yard', 'G33' => 'gram per cubic centimetre kelvin', 'G34' => 'gram per cubic decimetre kelvin', 'G35' => 'gram per litre kelvin', 'G37' => 'gram per millilitre kelvin', 'G38' => 'kilogram per cubic centimetre kelvin', 'G39' => 'kilogram per litre kelvin', 'G41' => 'square metre per second bar', 'G42' => 'microsiemens per centimetre', 'G43' => 'microsiemens per metre', 'G44' => 'nanosiemens per centimetre', 'G46' => 'stokes per bar', 'G47' => 'cubic centimetre per day', 'G48' => 'cubic centimetre per hour', 'G49' => 'cubic centimetre per minute', 'G51' => 'litre per second', 'G52' => 'cubic metre per day', 'G54' => 'millilitre per day', 'G55' => 'millilitre per hour', 'G56' => 'cubic inch per hour', 'G57' => 'cubic inch per minute', 'G58' => 'cubic inch per second', 'G59' => 'milliampere per litre minute', 'G60' => 'volt per bar', 'G61' => 'cubic centimetre per day kelvin', 'G62' => 'cubic centimetre per hour kelvin', 'G63' => 'cubic centimetre per minute kelvin', 'G64' => 'cubic centimetre per second kelvin', 'G65' => 'litre per day kelvin', 'G66' => 'litre per hour kelvin', 'G67' => 'litre per minute kelvin', 'G68' => 'litre per second kelvin', 'G69' => 'cubic metre per day kelvin', 'G7' => 'microfiche sheet', 'G70' => 'cubic metre per hour kelvin', 'G71' => 'cubic metre per minute kelvin', 'G72' => 'cubic metre per second kelvin', 'G73' => 'millilitre per day kelvin', 'G74' => 'millilitre per hour kelvin', 'G75' => 'millilitre per minute kelvin', 'G76' => 'millilitre per second kelvin', 'G77' => 'millimetre to the fourth power', 'G78' => 'cubic centimetre per day bar', 'G79' => 'cubic centimetre per hour bar', 'G80' => 'cubic centimetre per minute bar', 'G81' => 'cubic centimetre per second bar', 'G82' => 'litre per day bar', 'G83' => 'litre per hour bar', 'G84' => 'litre per minute bar', 'G85' => 'litre per second bar', 'G86' => 'cubic metre per day bar', 'G87' => 'cubic metre per hour bar', 'G88' => 'cubic metre per minute bar', 'G89' => 'cubic metre per second bar', 'G90' => 'millilitre per day bar', 'G91' => 'millilitre per hour bar', 'G92' => 'millilitre per minute bar', 'G93' => 'millilitre per second bar', 'G94' => 'cubic centimetre per bar', 'G95' => 'litre per bar', 'G96' => 'cubic metre per bar', 'G97' => 'millilitre per bar', 'G98' => 'microhenry per kiloohm', 'G99' => 'microhenry per ohm', 'GB' => 'gallon (US) per day', 'GBP' => 'United Kingdom Pound', 'GBQ' => 'Gigabecquerel', 'GC' => 'gram per 100 gram', 'GD' => 'gross barrel', 'GDW' => 'gram, dry weight', 'GE' => 'pound per gallon (US)', 'GEL' => 'Georgia Lari', 'GF' => 'gram per metre (gram per 100 centimetres)', 'GFI' => 'gram of fissile isotope', 'GGP' => 'Guernsey Pound', 'GGR' => 'Great gross (12 gross)', 'GH' => 'half gallon (US)', 'GHS' => 'Ghana Cedi', 'GIA' => 'Gill (11,8294 cm3)', 'GIC' => 'gram, including container', 'GII' => 'Gill (0,142065 dm3)', 'GK' => 'gram per kilogram', 'GLD' => 'Dry gallon (4,404884 dm3)', 'GLI' => 'Gallon (4,546092 dm3)', 'GM' => 'gram per square metre', 'GMD' => 'Gambia Dalasi', 'GNF' => 'Guinea Franc', 'GO' => 'milligram per square metre', 'GP' => 'milligram per cubic metre', 'GQ' => 'microgram per cubic metre', 'GRN' => 'Grain GB,US (64,798910 mg)', 'GRO' => 'Gross', 'GRT' => 'Gross (register) ton', 'GTQ' => 'Guatemala Quetzal', 'GV' => 'gigajoule', 'GW' => 'gallon per thousand cubic foot', 'GWH' => 'Gigawatt-hour (1 million KW/h)', 'GY' => 'gross yard', 'GYD' => 'Guyana Dollar', 'H03' => 'henry per kiloohm', 'H04' => 'henry per ohm', 'H05' => 'millihenry per kiloohm', 'H06' => 'millihenry per ohm', 'H08' => 'microbecquerel', 'H09' => 'reciprocal year', 'H1' => 'half page - electronic', 'H11' => 'reciprocal month', 'H12' => 'degree Celsius per hour', 'H14' => 'degree Celsius per second', 'H16' => 'square decametre', 'H18' => 'square hectometre', 'H19' => 'cubic hectometre', 'H2' => 'half litre', 'H21' => 'blank', 'H22' => 'volt square inch per pound-force', 'H23' => 'volt per inch', 'H24' => 'volt per microsecond', 'H26' => 'ohm per metre', 'H29' => 'microgram per litre', 'H30' => 'square micrometre', 'H31' => 'ampere per kilogram', 'H32' => 'ampere squared second', 'H34' => 'hertz metre', 'H35' => 'kelvin metre per watt', 'H36' => 'megaohm per kilometre', 'H37' => 'megaohm per metre', 'H40' => 'newton per ampere', 'H41' => 'newton metre watt to the power minus 0,5', 'H42' => 'pascal per metre', 'H43' => 'siemens per centimetre', 'H44' => 'teraohm', 'H45' => 'volt second per metre', 'H46' => 'volt per second', 'H47' => 'watt per cubic metre', 'H48' => 'attofarad', 'H49' => 'centimetre per hour', 'H50' => 'reciprocal cubic centimetre', 'H51' => 'decibel per kilometre', 'H52' => 'decibel per metre', 'H53' => 'kilogram per bar', 'H54' => 'kilogram per cubic decimetre kelvin', 'H56' => 'kilogram per square metre second', 'H57' => 'inch per two pi radiant', 'H58' => 'metre per volt second', 'H60' => 'cubic metre per cubic metre', 'H62' => 'millivolt per minute', 'H63' => 'milligram per square centimetre', 'H65' => 'millilitre per cubic metre', 'H68' => 'millimole per gram', 'H69' => 'picopascal per kilometre', 'H70' => 'picosecond', 'H71' => 'percent per month', 'H74' => 'watt per metre', 'H76' => 'gram per millimetre', 'H77' => 'module width', 'H78' => 'conventional centimetre of water', 'H79' => 'French gauge', 'H80' => 'rack unit', 'H81' => 'millimetre per minute', 'H83' => 'litre per kilogram', 'H84' => 'gram millimetre', 'H85' => 'reciprocal week', 'H87' => 'piece', 'H88' => 'megaohm kilometre', 'H90' => 'percent per degree', 'H92' => 'percent per one hundred thousand', 'H93' => 'percent per hundred', 'H94' => 'percent per thousand', 'H95' => 'percent per volt', 'H96' => 'percent per bar', 'H98' => 'percent per inch', 'H99' => 'percent per metre', 'HA' => 'hank', 'HAR' => 'Hectare', 'HBA' => 'Hectobar', 'HBX' => 'hundred boxes', 'HC' => 'hundred count', 'HD' => 'half dozen', 'HDW' => 'hundred kilogram, dry weight', 'HE' => 'hundredth of a carat', 'HEA' => 'head', 'HF' => 'hundred foot', 'HH' => 'hundred cubic foot', 'HI' => 'hundred sheet', 'HIU' => 'Hundred intenational units', 'HJ' => 'metric horse power', 'HK' => 'hundred kilogram', 'HKD' => 'Hong Kong Dollar', 'HKM' => 'hundred kilogram, net mass', 'HL' => 'hundred foot (linear)', 'HLT' => 'Hectolitre', 'HM' => 'mile per hour', 'HMT' => 'Hectometre', 'HN' => 'conventional millimetre of mercury', 'HNL' => 'Honduras Lempira', 'HO' => 'hundred troy ounce', 'HPA' => 'Hectolitre of pure alcohol', 'HRK' => 'Croatia Kuna', 'HS' => 'hundred square foot', 'HT' => 'half hour', 'HTG' => 'Haiti Gourde', 'HTZ' => 'Hertz', 'HUF' => 'Hungary Forint', 'HUR' => 'Hour', 'HY' => 'hundred yard', 'IC' => 'count per inch', 'IDR' => 'Indonesia Rupiah', 'IE' => 'person', 'II' => 'column inch', 'IL' => 'inch per minute', 'ILS' => 'Israel Shekel', 'IM' => 'impression', 'IMP' => 'Isle of Man Pound', 'INH' => 'Inch (2.54 mm)', 'INK' => 'Square inch', 'INQ' => 'Cubic inch', 'INR' => 'India Rupee', 'ISK' => 'Iceland Krona', 'IU' => 'inch per second', 'IUG' => 'international unit per gram', 'IV' => 'inch per second squared', 'J12' => 'per mille per psi', 'J13' => 'degree API', 'J14' => 'degree Baume (origin scale)', 'J15' => 'degree Baume (US heavy)', 'J16' => 'degree Baume (US light)', 'J18' => 'degree Brix', 'J19' => 'd/Fhrnhet hoursquarefoot/Brtshthermlunt/thrmochemc', 'J2' => 'joule per kilogram', 'J20' => 'degree Fahrenheit per kelvin', 'J21' => 'degree Fahrenheit per bar', 'J22' => 'British thermalunit/hour square foot d/Fahrenheit', 'J25' => 'degree Fahrenheit per second', 'J26' => 'reciprocal degree Fahrenheit', 'J28' => 'degree Rankine per hour', 'J29' => 'degree Rankine per minute', 'J30' => 'degree Rankine per second', 'J31' => 'degree Twaddell', 'J32' => 'micropoise', 'J33' => 'microgram per kilogram', 'J34' => 'microgram per cubic metre kelvin', 'J35' => 'microgram per cubic metre bar', 'J36' => 'microlitre per litre', 'J38' => 'baud', 'J39' => 'British thermal unit (mean)', 'J40' => 'Brtish thermalunit foot/hoursquarefoot d/Fhrnheit', 'J41' => 'Brtishthermalunit inch/hour squarefoot d/Fahrnheit', 'J42' => 'Brtishthermalunit inch/scond squarefoot d/Fahrenht', 'J43' => 'British thermal unit per pound degree Fahrenheit', 'J44' => 'British thermal unit (international table) /minute', 'J45' => 'British thermal unit (international table) /second', 'J46' => 'Brtishthermalunit foot/hour squarefoot d/Fhrenheit', 'J47' => 'British thermal unit (thermochemical) per hour', 'J48' => 'Brtishthermalunit inch/hour squarefoot d/Fhrenheit', 'J49' => 'Brtishthrmalunit inch/scondsquarefoot d/Fahrnheit', 'J51' => 'British thermal unit (thermochemical) per minute', 'J53' => 'coulomb square metre per kilogram', 'J54' => 'megabaud', 'J55' => 'watt second', 'J56' => 'bar per bar', 'J57' => 'barrel (UK petroleum)', 'J58' => 'barrel (UK petroleum) per minute', 'J60' => 'barrel (UK petroleum) per hour', 'J61' => 'barrel (UK petroleum) per second', 'J63' => 'barrel (US petroleum) per second', 'J64' => 'bushel (UK) per day', 'J65' => 'bushel (UK) per hour', 'J66' => 'bushel (UK) per minute', 'J67' => 'bushel (UK) per second', 'J69' => 'bushel (US dry) per hour', 'J71' => 'bushel (US dry) per second', 'J72' => 'centinewton metre', 'J73' => 'centipoise per kelvin', 'J74' => 'centipoise per bar', 'J75' => 'calorie (mean)', 'J76' => 'calorie (international table) /gram degree Celsius', 'J79' => 'calorie (thermochemical) per gram degree Celsius', 'J81' => 'calorie (thermochemical) per minute', 'J82' => 'calorie (thermochemical) per second', 'J83' => 'clo', 'J84' => 'centimetre per second kelvin', 'J85' => 'centimetre per second bar', 'J89' => 'centimetre of mercury', 'J90' => 'cubic decimetre per day', 'J92' => 'cubic decimetre per minute', 'J93' => 'cubic decimetre per second', 'J94' => 'dyne centimetre', 'J95' => 'ounce (UK fluid) per day', 'J98' => 'ounce (UK fluid) per second', 'JB' => 'jumbo', 'JEP' => 'Jersey Pound', 'JK' => 'megajoule per kilogram', 'JM' => 'megajoule per cubic metre', 'JNT' => 'pipeline joint', 'JO' => 'joint', 'JOD' => 'Jordan Dinar', 'JPS' => 'hundred metre', 'JR' => 'jar', 'JWL' => 'number of jewels', 'K1' => 'kilowatt demand', 'K10' => 'ounce (US fluid) per hour', 'K11' => 'ounce (US fluid) per minute', 'K13' => 'foot per degree Fahrenheit', 'K14' => 'foot per hour', 'K15' => 'foot pound-force per hour', 'K16' => 'foot pound-force per minute', 'K17' => 'foot per psi', 'K19' => 'foot per second psi', 'K2' => 'kilovolt ampere reactive demand', 'K20' => 'reciprocal cubic foot', 'K21' => 'cubic foot per degree Fahrenheit', 'K22' => 'cubic foot per day', 'K23' => 'cubic foot per psi', 'K25' => 'foot of mercury', 'K26' => 'gallon (UK) per day', 'K27' => 'gallon (UK) per hour', 'K28' => 'gallon (UK) per second', 'K3' => 'kilovolt ampere reactive hour', 'K31' => 'gram-force per square centimetre', 'K32' => 'gill (UK) per day', 'K33' => 'gill (UK) per hour', 'K35' => 'gill (UK) per second', 'K37' => 'gill (US) per hour', 'K38' => 'gill (US) per minute', 'K39' => 'gill (US) per second', 'K41' => 'grain per gallon (US)', 'K42' => 'horsepower (boiler)', 'K43' => 'horsepower (electric)', 'K46' => 'inch per psi', 'K48' => 'inch per second psi', 'K49' => 'reciprocal cubic inch', 'K5' => 'kilovolt ampere (reactive)', 'K50' => 'kilobaud', 'K51' => 'kilocalorie (mean)', 'K52' => 'kilocalorie (IT) per hour metre degree Celsius', 'K53' => 'kilocalorie (thermochemical)', 'K54' => 'kilocalorie (thermochemical) per minute', 'K55' => 'kilocalorie (thermochemical) per second', 'K58' => 'kilomole per hour', 'K59' => 'kilomole per cubic metre kelvin', 'K6' => 'kilolitre', 'K60' => 'kilomole per cubic metre bar', 'K61' => 'kilomole per minute', 'K62' => 'litre per litre', 'K63' => 'reciprocal litre', 'K64' => 'pound (avoirdupois) per degree Fahrenheit', 'K65' => 'pound (avoirdupois) square foot', 'K67' => 'pound per foot hour', 'K68' => 'pound per foot second', 'K70' => 'pound (avoirdupois) per cubic foot psi', 'K71' => 'pound (avoirdupois) per gallon (UK)', 'K73' => 'pound (avoirdupois) per hour degree Fahrenheit', 'K74' => 'pound (avoirdupois) per hour psi', 'K75' => 'pound/avoirdupois per cubic inch degree Fahrenheit', 'K76' => 'pound (avoirdupois) per cubic inch psi', 'K77' => 'pound (avoirdupois) per psi', 'K78' => 'pound (avoirdupois) per minute', 'K79' => 'pound (avoirdupois) per minute degree Fahrenheit', 'K80' => 'pound (avoirdupois) per minute psi', 'K81' => 'pound (avoirdupois) per second', 'K83' => 'pound (avoirdupois) per second psi', 'K84' => 'pound per cubic yard', 'K85' => 'pound-force per square foot', 'K86' => 'pound-force per square inch degree Fahrenheit', 'K87' => 'psi cubic inch per second', 'K88' => 'psi litre per second', 'K89' => 'psi cubic metre per second', 'K90' => 'psi cubic yard per second', 'K92' => 'pound-force second per square inch', 'K93' => 'reciprocal psi', 'K95' => 'quart (UK liquid) per hour', 'K97' => 'quart (UK liquid) per second', 'K98' => 'quart (US liquid) per day', 'KA' => 'cake', 'KAT' => 'katal', 'KB' => 'kilocharacter', 'KBA' => 'Kilobar', 'KCC' => 'kilogram of choline chloride', 'KD' => 'kilogram decimal', 'KDW' => 'kilogram drained net weight', 'KEL' => 'Kelvin', 'KES' => 'Kenya Shilling', 'KF' => 'kilopacket', 'KGM' => 'Kilogram', 'KGS' => 'Kilogram Per Second', 'KHR' => 'Cambodia Riel', 'KI' => 'kilogram per millimetre width', 'KIC' => 'kilogram, including container', 'KIP' => 'kilogram, including inner packaging', 'KJO' => 'Kilojoule', 'KL' => 'kilogram per metre', 'KLK' => 'lactic dry material percentage', 'KMA' => 'kilogram of methylamine', 'KMF' => 'Comorian Franc', 'KMK' => 'Square kilometre', 'KMQ' => 'Kilogram per cubic meter', 'KNI' => 'Kilogram of nitrogen', 'KNM' => 'kilonewton per square metre', 'KNS' => 'Kilogram of named substance', 'KNT' => 'Knot ( 1 n mile oer hour', 'KO' => 'milliequivalence causticpotash per gram of product', 'KPA' => 'kilopascal', 'KPH' => 'Kilogram of potassium hydroxide (caustic potasn)', 'KPP' => 'Kgm of phosphorus pentoxide(phosphoric anhydride', 'KPW' => 'Korea (North) Won', 'KR' => 'kiloroentgen', 'KRW' => 'Korea (South) Won', 'KS' => 'thousand pound per square inch', 'KSD' => 'Kilogram of substance 90 per cent dry', 'KSH' => 'Kilogram of sodium hydyoxide (caustic soda)', 'KTM' => 'kilometre', 'KTN' => 'Kilotonne', 'KUR' => 'Kilogram of uranium', 'KVA' => 'Kilovolt - ampere', 'KVT' => 'kilovolt', 'KWT' => 'Kilowatt', 'KX' => 'millilitre per kilogram', 'KYD' => 'Cayman Islands Dollar', 'KZT' => 'Kazakhstan Tenge', 'L13' => 'metre per second bar', 'L14' => 'square metre hour degree Celsius per kilocalorie', 'L15' => 'millipascal second per kelvin', 'L16' => 'millipascal second per bar', 'L17' => 'milligram per cubic metre kelvin', 'L18' => 'milligram per cubic metre bar', 'L19' => 'millilitre per litre', 'L2' => 'litre per minute', 'L23' => 'mole per hour', 'L25' => 'mole per kilogram bar', 'L26' => 'mole per litre kelvin', 'L27' => 'mole per litre bar', 'L31' => 'milliroentgen aequivalent men', 'L32' => 'nanogram per kilogram', 'L33' => 'ounce (avoirdupois) per day', 'L34' => 'ounce (avoirdupois) per hour', 'L35' => 'ounce (avoirdupois) per minute', 'L36' => 'ounce (avoirdupois) per second', 'L37' => 'ounce (avoirdupois) per gallon (UK)', 'L39' => 'ounce (avoirdupois) per cubic inch', 'L43' => 'peck (UK)', 'L45' => 'peck (UK) per hour', 'L46' => 'peck (UK) per minute', 'L47' => 'peck (UK) per second', 'L51' => 'peck (US dry) per second', 'L52' => 'psi per psi', 'L53' => 'pint (UK) per day', 'L54' => 'pint (UK) per hour', 'L55' => 'pint (UK) per minute', 'L58' => 'pint (US liquid) per hour', 'L60' => 'pint (US liquid) per second', 'L61' => 'pint (US dry)', 'L63' => 'slug per day', 'L64' => 'slug per foot second', 'L65' => 'slug per cubic foot', 'L66' => 'slug per hour', 'L69' => 'tonne per kelvin', 'L72' => 'tonne per day kelvin', 'L73' => 'tonne per day bar', 'L74' => 'tonne per hour kelvin', 'L78' => 'tonne per minute', 'L81' => 'tonne per second', 'L82' => 'tonne per second kelvin', 'L83' => 'tonne per second bar', 'L84' => 'ton (UK shipping)', 'L85' => 'ton long per day', 'L87' => 'ton short per degree Fahrenheit', 'L88' => 'ton short per day', 'L89' => 'ton short per hour degree Fahrenheit', 'L92' => 'ton (UK long) per cubic yard', 'L94' => 'ton-force (US short)', 'L95' => 'common year', 'L96' => 'sidereal year', 'L99' => 'yard per psi', 'LA' => 'pound per cubic inch', 'LAC' => 'lactose excess percentage', 'LAK' => 'Laos Kip', 'LBP' => 'Lebanon Pound', 'LBR' => 'Pound GB,US (0,45359237 kg)', 'LC' => 'linear centimetre', 'LD' => 'litre per day', 'LEF' => 'leaf', 'LF' => 'linear foot', 'LH' => 'labour hour', 'LJ' => 'large spray', 'LK' => 'link', 'LKR' => 'Sri Lanka Rupee', 'LM' => 'linear metre', 'LN' => 'length', 'LO' => 'lot [unit of procurement]', 'LPA' => 'Litre of pure alcohol', 'LS' => 'lump sum', 'LSL' => 'Lesotho Loti', 'LUB' => 'metric ton, lubricating oil', 'LUM' => 'Lumen', 'LX' => 'linear yard per pound', 'LY' => 'linear yard', 'LYD' => 'Libya Dinar', 'M0' => 'magnetic tape', 'M1' => 'milligram per litre', 'M11' => 'cubic yard per degree Fahrenheit', 'M13' => 'cubic yard per hour', 'M15' => 'cubic yard per minute', 'M16' => 'cubic yard per second', 'M17' => 'kilohertz metre', 'M19' => 'Beaufort', 'M21' => 'reciprocal kilovolt - ampere hour', 'M22' => 'millilitre per square centimetre minute', 'M23' => 'newton per centimetre', 'M25' => 'percent per degree Celsius', 'M26' => 'gigaohm per metre', 'M30' => 'reciprocal volt - ampere second', 'M32' => 'pascal second per litre', 'M33' => 'millimole per litre', 'M34' => 'newton metre per square metre', 'M35' => 'millivolt - ampere', 'M39' => 'centimetre per second squared', 'M4' => 'monetary value', 'M40' => 'yard per second squared', 'M44' => 'revolution', 'M46' => 'revolution per minute', 'M49' => 'chain (based on U.S. survey foot)', 'M5' => 'microcurie', 'M50' => 'furlong', 'M51' => 'foot (U.S. survey)', 'M52' => 'mile (based on U.S. survey foot)', 'M55' => 'metre per radiant', 'M57' => 'mile per minute', 'M58' => 'mile per second', 'M59' => 'metre per second pascal', 'M60' => 'metre per hour', 'M61' => 'inch per year', 'M62' => 'kilometre per second', 'M63' => 'inch per minute', 'M64' => 'yard per second', 'M65' => 'yard per minute', 'M66' => 'yard per hour', 'M7' => 'micro-inch', 'M70' => 'ton, register', 'M71' => 'cubic metre per pascal', 'M73' => 'kilogram per cubic metre pascal', 'M74' => 'kilogram per pascal', 'M76' => 'poundal', 'M78' => 'pond', 'M81' => 'square centimetre per second', 'M82' => 'square metre per second pascal', 'M83' => 'denier', 'M84' => 'pound per yard', 'M87' => 'kilogram per second pascal', 'M89' => 'tonne per year', 'M90' => 'kilopound per hour', 'M95' => 'poundal foot', 'M97' => 'dyne metre', 'MA' => 'machine per unit', 'MAD' => 'Morocco Dirham', 'MAH' => 'megavolt ampere reactive hour', 'MAL' => 'Megalitre', 'MAR' => 'megavolt ampere reactive', 'MAW' => 'Megawatt', 'MBE' => 'thousand standard brick equivalent', 'MBF' => 'thousand board foot', 'MDL' => 'Moldova Leu', 'MF' => 'milligram per square foot per side', 'MGA' => 'Madagascar Ariary', 'MGM' => 'Milligram', 'MID' => 'Thousand', 'MIL' => 'thousand', 'MIN' => 'Minute', 'MKD' => 'Macedonia Denar', 'MLD' => 'Billion US', 'MMK' => 'Square millimetre', 'MMT' => 'Millimetre', 'MNT' => 'Mongolia Tughrik', 'MOP' => 'Macau Pataca', 'MPA' => 'megapascal', 'MQ' => 'thousand metre', 'MQH' => 'cubic metre per hour', 'MQS' => 'cubic metre per second', 'MT' => 'mat', 'MTK' => 'Square metre', 'MUR' => 'Mauritius Rupee', 'MWK' => 'Malawi Kwacha', 'MXN' => 'Mexico Peso', 'MYR' => 'Malaysia Ringgit', 'N10' => 'pound foot per second', 'N11' => 'pound inch per second', 'N17' => 'inch of mercury (60 F)', 'N18' => 'inch of water (39.2 F)', 'N19' => 'inch of water (60 F)', 'N2' => 'number of lines', 'N20' => 'kip per square inch', 'N21' => 'poundal per square foot', 'N22' => 'ounce (avoirdupois) per square inch', 'N23' => 'conventional metre of water', 'N24' => 'gram per square millimetre', 'N25' => 'pound per square yard', 'N27' => 'foot to the fourth power', 'N28' => 'cubic decimetre per kilogram', 'N3' => 'print point', 'N30' => 'cubic inch per pound', 'N31' => 'kilonewton per metre', 'N32' => 'poundal per inch', 'N33' => 'pound-force per yard', 'N36' => 'newton second per square metre', 'N37' => 'kilogram per metre second', 'N39' => 'kilogram per metre day', 'N40' => 'kilogram per metre hour', 'N42' => 'poundal second per square inch', 'N44' => 'pound per foot day', 'N46' => 'foot poundal', 'N49' => 'watt per square inch', 'N66' => 'British thermal unit (39 F)', 'N68' => 'British thermal unit (60 F)', 'N69' => 'calorie (20 C)', 'N71' => 'therm (EC)', 'N73' => 'British thermal unit (thermochemical) per pound', 'N79' => 'kelvin per pascal', 'N81' => 'kilowatt per metre kelvin', 'N82' => 'kilowatt per metre degree Celsius', 'N83' => 'metre per degree Celcius metre', 'N90' => 'kilofarad', 'N92' => 'picosiemens', 'N97' => 'gilbert', 'N98' => 'volt per pascal', 'N99' => 'picovolt', 'NA' => 'milligram per kilogram', 'NAD' => 'Namibia Dollar', 'NAR' => 'Number of articles', 'NB' => 'barge', 'NBB' => 'Number bobbins', 'NCL' => 'number of cells', 'NE' => 'net litre', 'NEW' => 'Newton', 'NG' => 'net gallon (us)', 'NGN' => 'Nigeria Naira', 'NH' => 'message hour', 'NI' => 'net imperial gallon', 'NIL' => 'nil', 'NIO' => 'Nicaragua Cordoba', 'NJ' => 'number of screens', 'NMB' => 'Number', 'NMI' => 'Nautical mile (1852 m)', 'NMP' => 'Number of packs', 'NOK' => 'Norway Krone', 'NPL' => 'Number of parcels', 'NPR' => 'number of pairs', 'NPT' => 'Number of parts', 'NQ' => 'mho', 'NR' => 'micromho', 'NRL' => 'Number of rolls', 'NT' => 'net ton', 'NTT' => 'Net (regirter) ton', 'NU' => 'newton metre', 'NV' => 'vehicle', 'NX' => 'part per thousand', 'NY' => 'pound per air dry metric ton', 'NZD' => 'New Zealand Dollar', 'OA' => 'panel', 'ODE' => 'ozone depletion equivalent', 'ODG' => 'ODS Grams', 'ODK' => 'ODS Kilograms', 'ODM' => 'ODS Milligrams', 'OHM' => 'Ohm', 'ON' => 'ounce per square yard', 'ONZ' => 'Ounce GB,US (28,349523 g)', 'OP' => 'two pack', 'OPM' => 'oscillations per minute', 'OT' => 'overtime hour', 'OZ' => 'ounce av', 'OZA' => 'Fluid ounce (29,5735 cm3)', 'OZI' => 'Fluid ounce (29,5735 cm3)', 'P0' => 'page - electronic', 'P1' => 'percent', 'P10' => 'coulomb per metre', 'P11' => 'kiloweber', 'P13' => 'kilotesla', 'P15' => 'joule per minute', 'P16' => 'joule per hour', 'P17' => 'joule per day', 'P18' => 'kilojoule per second', 'P19' => 'kilojoule per minute', 'P2' => 'pound per foot', 'P20' => 'kilojoule per hour', 'P21' => 'kilojoule per day', 'P22' => 'nanoohm', 'P24' => 'kilohenry', 'P25' => 'lumen per square foot', 'P26' => 'phot', 'P27' => 'footcandle', 'P28' => 'candela per square inch', 'P29' => 'footlambert', 'P3' => 'three pack', 'P32' => 'candela per square foot', 'P33' => 'kilocandela', 'P34' => 'millicandela', 'P39' => 'calorie (thermochemical) per square centimetre', 'P4' => 'four pack', 'P40' => 'langley', 'P42' => 'pascal squared second', 'P43' => 'bel per metre', 'P44' => 'pound mole', 'P45' => 'pound mole per second', 'P46' => 'pound mole per minute', 'P49' => 'newton square metre per ampere', 'P50' => 'weber metre', 'P53' => 'unit pole', 'P54' => 'milligray per second', 'P55' => 'microgray per second', 'P56' => 'nanogray per second', 'P59' => 'microgray per minute', 'P6' => 'six pack', 'P60' => 'nanogray per minute', 'P61' => 'gray per hour', 'P62' => 'milligray per hour', 'P64' => 'nanogray per hour', 'P65' => 'sievert per second', 'P66' => 'millisievert per second', 'P67' => 'microsievert per second', 'P69' => 'rem per second', 'P7' => 'seven pack', 'P70' => 'sievert per hour', 'P71' => 'millisievert per hour', 'P72' => 'microsievert per hour', 'P75' => 'millisievert per minute', 'P78' => 'reciprocal square inch', 'P79' => 'pascal square metre per kilogram', 'P8' => 'eight pack', 'P81' => 'kilopascal per metre', 'P83' => 'standard atmosphere per metre', 'P85' => 'torr per metre', 'P86' => 'psi per inch', 'P87' => 'cubic metre per second square metre', 'P88' => 'rhe', 'P89' => 'pound-force foot per inch', 'P9' => 'nine pack', 'P90' => 'pound-force inch per inch', 'P91' => 'perm (0 C)', 'P93' => 'byte per second', 'P94' => 'kilobyte per second', 'P97' => 'reciprocal radian', 'P98' => 'pascal to the power sum of stoichiometric numbers', 'PA' => 'packet', 'PAL' => 'Pascal', 'PB' => 'pair inch', 'PCE' => 'Piece', 'PD' => 'pad', 'PE' => 'pound equivalent', 'PEN' => 'Peru Sol', 'PFL' => 'proof litre', 'PG' => 'plate', 'PGK' => 'Papua New Guinea Kina', 'PHP' => 'Philippines Piso', 'PI' => 'pitch', 'PK' => 'pack', 'PLA' => 'degree Plato', 'PM' => 'pound percentage', 'PO' => 'pound per inch of length', 'PS' => 'pound-force per square inch', 'PT' => 'pint (US)', 'PU' => 'tray / tray pack', 'PV' => 'half pint (US)', 'PY' => 'peck dry (US)', 'PYG' => 'Paraguay Guarani', 'Q10' => 'joule per tesla', 'Q12' => 'octet', 'Q13' => 'octet per second', 'Q16' => 'natural unit of information', 'Q17' => 'shannon per second', 'Q19' => 'natural unit of information per second', 'Q22' => 'second per radian cubic metre', 'Q23' => 'weber to the power minus one', 'Q27' => 'newton metre per metre', 'Q29' => 'microgram per hectogram', 'Q3' => 'meal', 'Q30' => 'pH (potential of Hydrogen)', 'Q31' => 'kilojoule per gram', 'Q35' => 'megawatts per minute', 'Q36' => 'square metre per cubic metre', 'Q37' => 'Standard cubic metre per day', 'Q38' => 'Standard cubic metre per hour', 'Q39' => 'Normalized cubic metre per day', 'Q40' => 'Normalized cubic metre per hour', 'Q42' => 'Joule per standard cubic metre', 'QAN' => 'Quarter (of a year)', 'QD' => 'quarter dozen', 'QH' => 'quarter hour', 'QK' => 'quarter kilogram', 'QR' => 'quire', 'QT' => 'quart (US)', 'QTD' => 'Dry quart (1,101221 dm3)', 'QTI' => 'Quart (1,136523 dm3)', 'QTL' => 'Liquid quart (0,946353 dm3)', 'R1' => 'pica', 'R4' => 'calorie', 'R9' => 'thousand cubic metre', 'RG' => 'ring', 'RH' => 'running or operating hour', 'RK' => 'roll metric measure', 'RL' => 'reel', 'RM' => 'ream', 'RO' => 'roll', 'ROM' => 'room', 'RON' => 'Romania Leu', 'RP' => 'pound per ream', 'RPM' => 'Revolution per minute', 'RPS' => 'Revolution per second', 'RS' => 'reset', 'RT' => 'revenue ton mile', 'RUB' => 'Russia Ruble', 'RWF' => 'Rwanda Franc', 'S3' => 'square foot per second', 'S4' => 'square metre per second', 'S6' => 'session', 'S7' => 'storage unit', 'S8' => 'standard advertising unit', 'SA' => 'sack', 'SAN' => 'Half year (six Months)', 'SBD' => 'Solomon Islands Dollar', 'SCO' => 'Score', 'SCR' => 'Scruple GP,US (1,295982 g)', 'SD' => 'solid pound', 'SDG' => 'Sudan Pound', 'SE' => 'section', 'SEC' => 'Second', 'SEK' => 'Sweden Krona', 'SET' => 'Set', 'SG' => 'segment', 'SGD' => 'Singapore Dollar', 'SHP' => 'Saint Helena Pound', 'SHT' => 'Shipping ton', 'SIE' => 'Siemens', 'SK' => 'split tank truck', 'SL' => 'slipsheet', 'SLL' => 'Sierra Leone Leone', 'SM3' => 'Standard cubic metre', 'SMI' => 'Statute mile (1609.344 m)', 'SN' => 'square rod', 'SOS' => 'Somalia Shilling', 'SPL' => 'Seborga Luigino', 'SQ' => 'square', 'SQR' => 'square, roofing', 'SRD' => 'Suriname Dollar', 'SS' => 'sheet metric measure', 'ST' => 'sheet', 'STC' => 'stick', 'STI' => 'Stone GB (6,350293 kg)', 'STK' => 'stick, cigarette', 'STL' => 'standard litre', 'STN' => 'Short ton GB, US 2/ (0,90718474 t)', 'STW' => 'straw', 'SW' => 'skein', 'SX' => 'shipment', 'SZL' => 'Swaziland Lilangeni', 'T0' => 'telecommunication line in service', 'T1' => 'thousand pound gross', 'T3' => 'thousand piece', 'T5' => 'thousand casing', 'T6' => 'thousand gallon (US)', 'T7' => 'thousand impression', 'T8' => 'thousand linear inch', 'TA' => 'tenth cubic foot', 'TAH' => 'Thousand ampere-hour', 'TAN' => 'total acid number', 'TC' => 'truckload', 'TE' => 'tote', 'TF' => 'ten square yard', 'THB' => 'Thailand Baht', 'TI' => 'thousand square inch', 'TIP' => 'metric ton, including inner packaging', 'TJ' => 'thousand square centimetre', 'TK' => 'tank, rectangular', 'TL' => 'thousand foot (linear)', 'TMS' => 'kilogram of imported meat, less offal', 'TMT' => 'Turkmenistan Manat', 'TN' => 'tin', 'TND' => 'Tunisia Dinar', 'TOP' => 'Tonga Pa\'anga', 'TPI' => 'teeth per inch', 'TPR' => 'Ten pairs', 'TQ' => 'thousand foot', 'TQD' => 'thousand cubic metres per day', 'TR' => 'ten square foot', 'TRL' => 'Trillion Eur', 'TRY' => 'Turkey Lira', 'TS' => 'thousand square foot', 'TSH' => 'Ton of steam per hour', 'TST' => 'ten set', 'TT' => 'thousand linear metre', 'TTD' => 'Trinidad and Tobago Dollar', 'TTS' => 'ten thousand sticks', 'TU' => 'tube', 'TV' => 'thousand kilogram', 'TVD' => 'Tuvalu Dollar', 'TWD' => 'Taiwan New Dollar', 'TY' => 'tank, cylindrical', 'TZS' => 'Tanzania Shilling', 'U1' => 'treatment', 'U2' => 'tablet', 'UA' => 'torr', 'UAH' => 'Ukraine Hryvnia', 'UB' => 'telecommunication line in service average', 'UC' => 'telecommunication port', 'UD' => 'tenth minute', 'UE' => 'tenth hour', 'UF' => 'usage per telecommunication line average', 'UGX' => 'Uganda Shilling', 'UH' => 'ten thousand yard', 'UM' => 'million unit', 'USD' => 'United States Dollar', 'UYU' => 'Uruguay Peso', 'UZS' => 'Uzbekistan Som', 'VA' => 'volt - ampere per kilogram', 'VEF' => 'Venezuela Bolvar', 'VI' => 'vial', 'VK' => 'Vanpack', 'VL' => 'Bulk, liquid', 'VN' => 'Vehicle', 'VO' => 'Bulk, solid, large particles ("nodules")', 'VP' => 'Vacuumpacked', 'VQ' => 'Bulk,liquefied gas (at abnorml temprture/pressure)', 'VR' => 'Bulk, solid, granular particles ("grains")', 'VS' => 'Bulk, scrap metal', 'VY' => 'Bulk, solid, fine particles ("powders")', 'WA' => 'Intermediate bulk container', 'WB' => 'Wickerbottle', 'WC' => 'Intermediate bulk container, steel', 'WD' => 'Intermediate bulk container, aluminium', 'WF' => 'Intermediate bulk container, metal', 'WG' => 'Intermediate bulk cont,steel,pressurised >10 kpa', 'WH' => 'Intermedt bulk cont,aluminium,pressurised >10 kpa', 'WJ' => 'Intermediate bulk container,metal, pressure 10 kpa', 'WK' => 'Intermediate bulk container, steel, liquid', 'WL' => 'Intermediate bulk container, aluminium, liquid', 'WM' => 'Intermediate bulk container, metal, liquid', 'WN' => 'Intermd bulk cont,woven plastic,without coat/liner', 'WP' => 'Intermediate bulk container, woven plastic, coated', 'WQ' => 'Intermd bulk cont,woven plastic,with liner', 'WR' => 'Intermedt bulk cont,woven plastic,coated and liner', 'WS' => 'Intermediate bulk container, plastic film', 'WT' => 'Intermd bulk cont,textile with out coat/liner', 'WU' => 'Intermdte bulk cont,natural wood,with inner liner', 'WV' => 'Intermediate bulk container, textile, coated', 'WW' => 'Intermediate bulk container, textile, with liner', 'WX' => 'Intermediate bulk cont,textile,coated and liner', 'WY' => 'Intermd bulk cont,plywood,with inner liner', 'WZ' => 'Intermd bulk cont,reconstttd wood,with inner liner', 'XA' => 'Bag, woven plastic, without inner coat/liner', 'XB' => 'Bag, woven plastic, sift proof', 'XC' => 'Bag, woven plastic, water resistant', 'XD' => 'Bag, plastics film', 'XF' => 'Bag, textile, without inner coat/liner', 'XG' => 'Bag, textile, sift proof', 'XH' => 'Bag, textile, water resistant', 'XJ' => 'Bag, paper, multi-wall', 'XK' => 'Bag, paper, multi-wall, water resistant', 'XN' => 'test', 'YA' => 'Compsite packging,plastic receptacle in steel drum', 'YB' => 'Compste packgng,plastc recptcle in steel crate box', 'YC' => 'Compste packgng,plastic recptcle in aluminium drum', 'YD' => 'Compste packgng,plastic recptcle in alumnium crate', 'YF' => 'Compsite packging,plastic receptacle in wooden box', 'YG' => 'Compste packgng,plastic receptacle in plywood drum', 'YH' => 'Compste packging,plastic receptacle in plywood box', 'YJ' => 'Compsite packging,plastic receptacle in fibre drum', 'YK' => 'Compste packgng,plastic recptcle in fibreboard box', 'YL' => 'Compste packgng,plastic receptacle in plastic drum', 'YM' => 'Compsite packgng,plstc recptcle in solid plstc box', 'YN' => 'Composite packaging,glass receptacle in steel drum', 'YP' => 'Compste packgng,glass recptacle in steel crate box', 'YQ' => 'Compste packgng,glass receptacle in aluminium drum', 'YR' => 'Compste packgng,glass recptacle in aluminium crate', 'YS' => 'Composite packaging,glass receptacle in wooden box', 'YT' => 'Compsite packging,glass receptacle in plywood drum', 'YV' => 'Compste packgng,glass recptcle in wickrwork hamper', 'YW' => 'Composite packaging,glass receptacle in fibre drum', 'YX' => 'Compste packgng,glass receptacle in fibreboard box', 'YY' => 'Compste pckgng,glss recptcl in expndbl plastc pack', 'YZ' => 'Compsite packgng,glass recptcle in solid plstc pck', 'ZA' => 'Intermediate bulk container, paper, multi-wall', 'ZB' => 'Bag, large', 'ZC' => 'Intermd bulk cont,paper,multi-wall,water resistant', 'ZD' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,solid', 'ZF' => 'Intermd bulk cont,rgid plstc,freestandng,solds', 'ZG' => 'Intermdbulk cnt,rgd plstc,w/strctrl equipm,pressrd', 'ZH' => 'Intermd bulk cont,rgd plstc,freestnd,pressurised', 'ZJ' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,lquid', 'ZK' => 'Intermd bulk cont,rigid plstc,freestanding,liquids', 'ZL' => 'Intermd bulk cont,composite,rigid plastic,solids', 'ZM' => 'Intermd bulk cont,compste,flexbl plastic, solids', 'ZN' => 'Intermd bulk cont,compsit,rgid plstc,pressurised', 'ZP' => 'Intermd bulk cont,compsit,flexbl plstc,pressurised', 'ZQ' => 'Intermd bulk cont,composite,rigid plastic,liquids', 'ZR' => 'Intermd bulk cont,compsite,flexible plastc,liquids', 'ZS' => 'Intermediate bulk container, composite', 'ZT' => 'Intermediate bulk container, fibreboard', 'ZU' => 'Intermediate bulk container, flexible', 'ZV' => 'Intermediate bulk container,metal,other than steel', 'ZW' => 'Intermediate bulk container, natural wood', 'ZX' => 'Intermediate bulk container, plywood', 'ZY' => 'Intermediate bulk container, reconstituted wood', 'ZZ' => 'Mutually defined'];
@endphp
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> Edit Dokumen Pabean
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc23', $header->bpbno) }}" method="POST" id="form-edit-bc23">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ $header->trx_no_par }} |
                <strong>Supplier:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bpbno_int" value="{{ $header->bpbno_int }}">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Data Header & Nilai</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Daftar Barang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pendukung-tab" data-toggle="tab" href="#tab-pendukung" role="tab">Pendukung (Dokumen, Kemasan)</a>
                </li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">

                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="section-title">Data Pengajuan</div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Nomor Aju</label>
                            <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tanggal Aju</label>
                            <input type="date" name="tanggalAju" class="form-control form-control-sm" value="{{ $ceisaInfo->tanggal_aju ?? $header->tanggal_aju ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kode Kantor</label>
                            <input type="text" name="kodeKantor" class="form-control form-control-sm" value="{{ $dataDetail['kodeKantor'] ?? '050500' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kode Jenis TPB</label>
                            <input type="text" name="kodeJenisTpb" class="form-control form-control-sm" value="{{ $dataDetail['kodeJenisTpb'] ?? '1' }}">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tujuan</label>
                            <input type="text" name="kodeTujuanTpb" class="form-control form-control-sm" value="{{ $dataDetail['kodeTujuanTpb'] ?? '' }}" placeholder="contoh: 1 / 2 / dll">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kantor Bongkar</label>
                            <input type="text" name="kodeKantorBongkar" class="form-control form-control-sm" value="{{ $dataDetail['kodeKantorBongkar'] ?? '' }}" placeholder="Kode KPPBC Bongkar">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Pelabuhan Bongkar</label>
                            <select name="kodePelBongkar" class="form-control form-control-sm select2-pelabuhan">
                                @if(!empty($dataDetail['kodePelBongkar']))
                                    <option value="{{ $dataDetail['kodePelBongkar'] }}" selected>{{ $dataDetail['kodePelBongkar'] }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>NDPBM (Kurs)</label>
                            <input type="text" inputmode="decimal" name="ndpbm" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['ndpbm'] ?? '' }}" placeholder="contoh: 15000.0000">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Kode Tutup PU</label>
                            <select name="kodeTutupPu" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih --</option>
                                <option value="11" {{ ($dataDetail['kodeTutupPu'] ?? '') == '11' ? 'selected' : '' }}>BC 1.1</option>
                                <option value="12" {{ ($dataDetail['kodeTutupPu'] ?? '') == '12' ? 'selected' : '' }}>BC 1.2</option>
                                <option value="14" {{ ($dataDetail['kodeTutupPu'] ?? '') == '14' ? 'selected' : '' }}>BC 1.4</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Tanggal Tiba</label>
                            <input type="date" name="tanggalTiba" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTiba'] ?? '' }}">
                        </div>
                        {{-- <div class="col-md-3 form-group">
                            <label>Cara Bayar</label>
                            <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih Cara Bayar --</option>
                                <option value="1" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '1' ? 'selected' : '' }}>1 - BIASA/TUNAI</option>
                                <option value="2" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '2' ? 'selected' : '' }}>2 - BERKALA</option>
                                <option value="3" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '3' ? 'selected' : '' }}>3 - DENGAN JAMINAN</option>
                                <option value="4" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '4' ? 'selected' : '' }}>4 - PERHITUNGAN KEMUDIAN</option>
                                <option value="5" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '5' ? 'selected' : '' }}>5 - KONSINYASI (CONSIGNMENT)</option>
                                <option value="6" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '6' ? 'selected' : '' }}>6 - USANCE LETTER OF CREDIT</option>
                                <option value="7" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '7' ? 'selected' : '' }}>7 - RED CLAUSE LETTER OF CREDIT</option>
                                <option value="8" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '8' ? 'selected' : '' }}>8 - INTER-COMPANY ACCOUNT</option>
                                <option value="9" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '9' ? 'selected' : '' }}>9 - GABUNGAN/LAINNYA</option>
                                <option value="10" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '10' ? 'selected' : '' }}>10 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA BERTAHAP</option>
                                <option value="11" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '11' ? 'selected' : '' }}>11 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA TUNAI</option>
                                <option value="12" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '12' ? 'selected' : '' }}>12 - DILAKUKAN DI DN DENGAN PEMBAYARAN UANG TUNAI</option>
                                <option value="13" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '13' ? 'selected' : '' }}>13 - DILAKUKAN DI DN DENGAN PEMBAYARAN MELALUI TELEGRAPH</option>
                                <option value="14" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '14' ? 'selected' : '' }}>14 - DILAKUKAN TANPA PEMBAYARAN</option>
                                <option value="15" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '15' ? 'selected' : '' }}>15 - PEMBAYARAN DIMUKA (ADVANCE PAYMENT)</option>
                                <option value="16" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '16' ? 'selected' : '' }}>16 - SIGHT LETTER OF CREDIT</option>
                                <option value="17" {{ ($dataDetail['kodeCaraBayar'] ?? '') == '17' ? 'selected' : '' }}>17 - INKASO (COLLECTION DRAFT)</option>
                            </select>
                        </div> --}}
                        {{-- <div class="col-md-3 form-group">
                            <label>Jenis Prosedur</label>
                            <select name="kodeJenisProsedur" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih Jenis Prosedur --</option>
                                <option value="1" {{ ($dataDetail['kodeJenisProsedur'] ?? '') == '1' ? 'selected' : '' }}>1 - PROSEDUR BIASA</option>
                                <option value="2" {{ ($dataDetail['kodeJenisProsedur'] ?? '') == '2' ? 'selected' : '' }}>2 - PROSEDUR BERKALA</option>
                            </select>
                        </div> --}}
                    </div>

                    <div class="section-title">Dokumen BC 1.1</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nomor BC 1.1</label><input type="text" name="nomorBc11" class="form-control form-control-sm" value="{{ $dataDetail['nomorBc11'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Tanggal BC 1.1</label><input type="date" name="tanggalBc11" class="form-control form-control-sm" value="{{ $dataDetail['tanggalBc11'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Pos BC 1.1</label><input type="text" name="posBc11" class="form-control form-control-sm" value="{{ $dataDetail['posBc11'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Subpos BC 1.1</label><input type="text" name="subposBc11" class="form-control form-control-sm" value="{{ $dataDetail['subposBc11'] ?? '' }}"></div>
                    </div>

                    <div class="section-title">Data Nilai & Fisik</div>
                    <div class="row">
                        <div class="col-md-2 form-group">
                            <label>Kode Harga</label>
                            <select name="kodeIncoterm" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih --</option>
                                <option value="CFR" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'CFR' ? 'selected' : '' }}>CFR - Cost and Freight</option>
                                <option value="CIF" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'CIF' ? 'selected' : '' }}>CIF - Cost, Insurance and Freight</option>
                                <option value="CIP" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'CIP' ? 'selected' : '' }}>CIP - Carriage and Insurance Paid to</option>
                                <option value="CPT" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'CPT' ? 'selected' : '' }}>CPT - Carriage Paid To</option>
                                <option value="DAF" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DAF' ? 'selected' : '' }}>DAF - Delivered At Frontier</option>
                                <option value="DAP" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DAP' ? 'selected' : '' }}>DAP - Delivered At Place</option>
                                <option value="DAT" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DAT' ? 'selected' : '' }}>DAT - Delivered At Terminal</option>
                                <option value="DDP" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DDP' ? 'selected' : '' }}>DDP - Delivered Duty Paid</option>
                                <option value="DDU" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DDU' ? 'selected' : '' }}>DDU - Delivered Duty Unpaid</option>
                                <option value="DEQ" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DEQ' ? 'selected' : '' }}>DEQ - Delivered Ex Quay</option>
                                <option value="DES" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'DES' ? 'selected' : '' }}>DES - Delivered Ex Ship</option>
                                <option value="EXW" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'EXW' ? 'selected' : '' }}>EXW - Ex Works</option>
                                <option value="FAS" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'FAS' ? 'selected' : '' }}>FAS - Free Alongside Ship</option>
                                <option value="FCA" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'FCA' ? 'selected' : '' }}>FCA - Free Carrier</option>
                                <option value="FOB" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'FOB' ? 'selected' : '' }}>FOB - Free on Board</option>
                                <option value="LAN" {{ ($dataDetail['kodeIncoterm'] ?? '') == 'LAN' ? 'selected' : '' }}>LAN - LAINNYA</option>
                            </select>
                        </div>
                        <div class="col-md-2 form-group"><label>Bruto (Kg)</label><input type="text" inputmode="decimal" name="bruto" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? "" }}"></div>
                        <div class="col-md-2 form-group"><label>Netto (Kg)</label><input type="text" inputmode="decimal" name="netto" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? "" }}"></div>
                        {{-- <div class="col-md-2 form-group"><label>Volume (M3)</label><input type="text" inputmode="decimal" name="volume" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['volume'] ?? "" }}"></div> --}}
                        <div class="col-md-3 form-group"><label>FOB</label><input type="text" inputmode="decimal" name="fob" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['fob'] ?? "" }}"></div>
                        <div class="col-md-3 form-group"><label>Freight</label><input type="text" inputmode="decimal" name="freight" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['freight'] ?? "" }}"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2 form-group"><label>Asuransi</label><input type="text" inputmode="decimal" name="asuransi" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['asuransi'] ?? "" }}"></div>
                        <div class="col-md-2 form-group"><label>CIF</label><input type="text" inputmode="decimal" name="cif" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['cif'] ?? "" }}"></div>
                        <div class="col-md-2 form-group"><label>Harga Penyerahan</label><input type="text" inputmode="decimal" name="hargaPenyerahan" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['hargaPenyerahan'] ?? "" }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Tambahan</label><input type="text" inputmode="decimal" name="biayaTambahan" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['biayaTambahan'] ?? "" }}"></div>
                        <div class="col-md-2 form-group"><label>Biaya Pengurang</label><input type="text" inputmode="decimal" name="biayaPengurang" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['biayaPengurang'] ?? "" }}"></div>
                        {{-- <div class="col-md-2 form-group"><label>Uang Muka</label><input type="text" inputmode="decimal" name="uangMuka" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['uangMuka'] ?? "" }}"></div> --}}
                        {{-- <div class="col-md-2 form-group"><label>Nilai Jasa</label><input type="text" inputmode="decimal" name="nilaiJasa" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['nilaiJasa'] ?? "" }}"></div> --}}
                        <div class="col-md-2 form-group"><label>Nilai Barang</label><input type="text" inputmode="decimal" name="nilaiBarang" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['nilaiBarang'] ?? "" }}"></div>
                        {{-- <div class="col-md-2 form-group"><label>Diskon</label><input type="text" inputmode="decimal" name="diskon" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['diskon'] ?? "" }}"></div> --}}
                    </div>

                    <div class="section-title">Penandatangan</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
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
                                        {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                    </div>
                                    <i class="fas fa-chevron-down icon-collapse"></i>
                                </div>
                            </div>

                            <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                <div class="card-body py-3 px-3 bg-white">

                                    <input type="hidden" name="barang[{{ $index }}][kodeBarang]" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item }}">
                                    <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Jenis</div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Pos Tarif/HS</label>
                                                <select name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm select2bs4">
                                                    <option value="">Pilih Pos Tarif/HS</option>
                                                    <option value="01012100" {{ ($draftItem['posTarif'] ?? '') == '01012100' ? 'selected' : '' }}>01012100 - BIBIT</option>
                                                    <option value="01012900" {{ ($draftItem['posTarif'] ?? '') == '01012900' ? 'selected' : '' }}>01012900 - LAIN-LAIN</option>
                                                    <option value="01013010" {{ ($draftItem['posTarif'] ?? '') == '01013010' ? 'selected' : '' }}>01013010 - BIBIT</option>
                                                    <option value="01013090" {{ ($draftItem['posTarif'] ?? '') == '01013090' ? 'selected' : '' }}>01013090 - LAIN-LAIN</option>
                                                    <option value="01019000" {{ ($draftItem['posTarif'] ?? '') == '01019000' ? 'selected' : '' }}>01019000 - LAIN-LAIN</option>
                                                    <option value="01022100" {{ ($draftItem['posTarif'] ?? '') == '01022100' ? 'selected' : '' }}>01022100 - BIBIT</option>
                                                    <option value="01022910" {{ ($draftItem['posTarif'] ?? '') == '01022910' ? 'selected' : '' }}>01022910 - SAPI JANTAN</option>
                                                    <option value="01022911" {{ ($draftItem['posTarif'] ?? '') == '01022911' ? 'selected' : '' }}>01022911 - OXEN</option>
                                                    <option value="01022919" {{ ($draftItem['posTarif'] ?? '') == '01022919' ? 'selected' : '' }}>01022919 - LAIN-LAIN</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Kode Barang</label>
                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->id_item ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Negara Asal</label>
                                                <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm select2bs4" id="kode_negara">
                                                    <option value="">-- Pilih Negara --</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AD' ? 'selected' : '' }} value="AD">AD - ANDORRA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AE' ? 'selected' : '' }} value="AE">AE - UNITED ARAB EMIRATES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AF' ? 'selected' : '' }} value="AF">AF - AFGHANISTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AG' ? 'selected' : '' }} value="AG">AG - ANTIGUA AND BARBUDA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AI' ? 'selected' : '' }} value="AI">AI - ANGUILLA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AL' ? 'selected' : '' }} value="AL">AL - ALBANIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AM' ? 'selected' : '' }} value="AM">AM - ARMENIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AN' ? 'selected' : '' }} value="AN">AN - NETHERLANDS ANTILLES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AO' ? 'selected' : '' }} value="AO">AO - ANGOLA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AQ' ? 'selected' : '' }} value="AQ">AQ - ANTARCTICA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AR' ? 'selected' : '' }} value="AR">AR - ARGENTINA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AS' ? 'selected' : '' }} value="AS">AS - AMERICAN SAMOA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AT' ? 'selected' : '' }} value="AT">AT - AUSTRIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AU' ? 'selected' : '' }} value="AU">AU - AUSTRALIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AW' ? 'selected' : '' }} value="AW">AW - ARUBA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AX' ? 'selected' : '' }} value="AX">AX - Aland Islands</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'AZ' ? 'selected' : '' }} value="AZ">AZ - AZERBAIJAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BA' ? 'selected' : '' }} value="BA">BA - BOSNIA AND HERZEGOVINA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BB' ? 'selected' : '' }} value="BB">BB - BARBADOS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BD' ? 'selected' : '' }} value="BD">BD - BANGLADESH</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BE' ? 'selected' : '' }} value="BE">BE - BELGIUM</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BF' ? 'selected' : '' }} value="BF">BF - BURKINA FASO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BG' ? 'selected' : '' }} value="BG">BG - BULGARIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BH' ? 'selected' : '' }} value="BH">BH - BAHRAIN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BI' ? 'selected' : '' }} value="BI">BI - BURUNDI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BJ' ? 'selected' : '' }} value="BJ">BJ - BENIN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BL' ? 'selected' : '' }} value="BL">BL - Saint Barthelemy</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BM' ? 'selected' : '' }} value="BM">BM - BERMUDA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BN' ? 'selected' : '' }} value="BN">BN - BRUNEI DARUSSALAM</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BO' ? 'selected' : '' }} value="BO">BO - BOLIVIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BQ' ? 'selected' : '' }} value="BQ">BQ - Bonaire, Sint Eustatius and Saba</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BR' ? 'selected' : '' }} value="BR">BR - BRAZIL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BS' ? 'selected' : '' }} value="BS">BS - BAHAMAS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BT' ? 'selected' : '' }} value="BT">BT - BHUTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BV' ? 'selected' : '' }} value="BV">BV - BOUVET ISLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BW' ? 'selected' : '' }} value="BW">BW - BOTSWANA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BY' ? 'selected' : '' }} value="BY">BY - BELARUS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'BZ' ? 'selected' : '' }} value="BZ">BZ - BELIZE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CA' ? 'selected' : '' }} value="CA">CA - CANADA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CC' ? 'selected' : '' }} value="CC">CC - COCOS (KEELING) ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CD' ? 'selected' : '' }} value="CD">CD - CONGO, THE DEMOCRATIC REPUBLIC OF THE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CF' ? 'selected' : '' }} value="CF">CF - CENTRAL AFRICAN REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CG' ? 'selected' : '' }} value="CG">CG - CONGO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CH' ? 'selected' : '' }} value="CH">CH - SWITZERLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CI' ? 'selected' : '' }} value="CI">CI - COTE D'IVOIRE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CK' ? 'selected' : '' }} value="CK">CK - COOK ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CL' ? 'selected' : '' }} value="CL">CL - CHILE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CM' ? 'selected' : '' }} value="CM">CM - CAMEROON</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CN' ? 'selected' : '' }} value="CN">CN - CHINA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CO' ? 'selected' : '' }} value="CO">CO - COLOMBIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CR' ? 'selected' : '' }} value="CR">CR - COSTA RICA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CS' ? 'selected' : '' }} value="CS">CS - FORMER CZECHOSLOVAKIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CU' ? 'selected' : '' }} value="CU">CU - CUBA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CV' ? 'selected' : '' }} value="CV">CV - CAPE VERDE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CW' ? 'selected' : '' }} value="CW">CW - Curacao</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CX' ? 'selected' : '' }} value="CX">CX - CHRISTMAS ISLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CY' ? 'selected' : '' }} value="CY">CY - CYPRUS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'CZ' ? 'selected' : '' }} value="CZ">CZ - CZECH REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DE' ? 'selected' : '' }} value="DE">DE - GERMANY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DJ' ? 'selected' : '' }} value="DJ">DJ - DJIBOUTI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DK' ? 'selected' : '' }} value="DK">DK - DENMARK</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DM' ? 'selected' : '' }} value="DM">DM - DOMINICA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DO' ? 'selected' : '' }} value="DO">DO - DOMINICAN REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'DZ' ? 'selected' : '' }} value="DZ">DZ - ALGERIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'EC' ? 'selected' : '' }} value="EC">EC - ECUADOR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'EE' ? 'selected' : '' }} value="EE">EE - ESTONIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'EG' ? 'selected' : '' }} value="EG">EG - EGYPT</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'EH' ? 'selected' : '' }} value="EH">EH - WESTERN SAHARA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ER' ? 'selected' : '' }} value="ER">ER - ERITREA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ES' ? 'selected' : '' }} value="ES">ES - SPAIN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ET' ? 'selected' : '' }} value="ET">ET - ETHIOPIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FI' ? 'selected' : '' }} value="FI">FI - FINLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FJ' ? 'selected' : '' }} value="FJ">FJ - FIJI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FK' ? 'selected' : '' }} value="FK">FK - FALKLAND ISLANDS (MALVINAS)</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FM' ? 'selected' : '' }} value="FM">FM - MICRONESIA, FEDERATED STATES OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FO' ? 'selected' : '' }} value="FO">FO - FAROE ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'FR' ? 'selected' : '' }} value="FR">FR - FRANCE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GA' ? 'selected' : '' }} value="GA">GA - GABON</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GB' ? 'selected' : '' }} value="GB">GB - UNITED KINGDOM</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GD' ? 'selected' : '' }} value="GD">GD - GRENADA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GE' ? 'selected' : '' }} value="GE">GE - GEORGIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GF' ? 'selected' : '' }} value="GF">GF - FRENCH GUIANA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GG' ? 'selected' : '' }} value="GG">GG - Guernsey</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GH' ? 'selected' : '' }} value="GH">GH - GHANA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GI' ? 'selected' : '' }} value="GI">GI - GIBRALTAR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GL' ? 'selected' : '' }} value="GL">GL - GREENLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GM' ? 'selected' : '' }} value="GM">GM - GAMBIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GN' ? 'selected' : '' }} value="GN">GN - GUINEA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GP' ? 'selected' : '' }} value="GP">GP - GUADELOUPE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GQ' ? 'selected' : '' }} value="GQ">GQ - EQUATORIAL GUINEA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GR' ? 'selected' : '' }} value="GR">GR - GREECE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GS' ? 'selected' : '' }} value="GS">GS - SOUTH GEORGIA AND THE SOUTH SANDWICH ISL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GT' ? 'selected' : '' }} value="GT">GT - GUATEMALA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GU' ? 'selected' : '' }} value="GU">GU - GUAM</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GW' ? 'selected' : '' }} value="GW">GW - GUINEA-BISSAU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'GY' ? 'selected' : '' }} value="GY">GY - GUYANA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HK' ? 'selected' : '' }} value="HK">HK - HONG KONG</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HM' ? 'selected' : '' }} value="HM">HM - HEARD ISLAND AND MCDONALD ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HN' ? 'selected' : '' }} value="HN">HN - HONDURAS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HR' ? 'selected' : '' }} value="HR">HR - CROATIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HT' ? 'selected' : '' }} value="HT">HT - HAITI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'HU' ? 'selected' : '' }} value="HU">HU - HUNGARY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ID' ? 'selected' : '' }} value="ID">ID - INDONESIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IE' ? 'selected' : '' }} value="IE">IE - IRELAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IL' ? 'selected' : '' }} value="IL">IL - ISRAEL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IM' ? 'selected' : '' }} value="IM">IM - Isle of Man</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IN' ? 'selected' : '' }} value="IN">IN - INDIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IO' ? 'selected' : '' }} value="IO">IO - BRITISH INDIAN OCEAN TERRITORY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IQ' ? 'selected' : '' }} value="IQ">IQ - IRAQ</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IR' ? 'selected' : '' }} value="IR">IR - IRAN, ISLAMIC REPUBLIC OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IS' ? 'selected' : '' }} value="IS">IS - ICELAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'IT' ? 'selected' : '' }} value="IT">IT - ITALY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'JE' ? 'selected' : '' }} value="JE">JE - Jersey</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'JM' ? 'selected' : '' }} value="JM">JM - JAMAICA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'JO' ? 'selected' : '' }} value="JO">JO - JORDAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'JP' ? 'selected' : '' }} value="JP">JP - JAPAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KE' ? 'selected' : '' }} value="KE">KE - KENYA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KG' ? 'selected' : '' }} value="KG">KG - KYRGYZSTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KH' ? 'selected' : '' }} value="KH">KH - CAMBODIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KI' ? 'selected' : '' }} value="KI">KI - KIRIBATI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KM' ? 'selected' : '' }} value="KM">KM - COMOROS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KN' ? 'selected' : '' }} value="KN">KN - SAINT KITTS AND NEVIS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KP' ? 'selected' : '' }} value="KP">KP - KOREA, DEMOCRATIC PEOPLE'S REPUBLIC OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KR' ? 'selected' : '' }} value="KR">KR - KOREA, REPUBLIC OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KW' ? 'selected' : '' }} value="KW">KW - KUWAIT</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KY' ? 'selected' : '' }} value="KY">KY - CAYMAN ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'KZ' ? 'selected' : '' }} value="KZ">KZ - KAZAKSTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LA' ? 'selected' : '' }} value="LA">LA - LAO PEOPLE'S DEMOCRATIC REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LB' ? 'selected' : '' }} value="LB">LB - LEBANON</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LC' ? 'selected' : '' }} value="LC">LC - SAINT LUCIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LI' ? 'selected' : '' }} value="LI">LI - LIECHTENSTEIN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LK' ? 'selected' : '' }} value="LK">LK - SRI LANKA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LR' ? 'selected' : '' }} value="LR">LR - LIBERIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LS' ? 'selected' : '' }} value="LS">LS - LESOTHO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LT' ? 'selected' : '' }} value="LT">LT - LITHUANIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LU' ? 'selected' : '' }} value="LU">LU - LUXEMBOURG</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LV' ? 'selected' : '' }} value="LV">LV - LATVIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'LY' ? 'selected' : '' }} value="LY">LY - LIBYAN ARAB JAMAHIRIYA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MA' ? 'selected' : '' }} value="MA">MA - MOROCCO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MC' ? 'selected' : '' }} value="MC">MC - MONACO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MD' ? 'selected' : '' }} value="MD">MD - MOLDOVA, REPUBLIC OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ME' ? 'selected' : '' }} value="ME">ME - Montenegro</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MF' ? 'selected' : '' }} value="MF">MF - Saint Martin (French Part)</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MG' ? 'selected' : '' }} value="MG">MG - MADAGASCAR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MH' ? 'selected' : '' }} value="MH">MH - MARSHALL ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MK' ? 'selected' : '' }} value="MK">MK - MACEDONIA, THE FORMER YUGOSLAV REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ML' ? 'selected' : '' }} value="ML">ML - MALI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MM' ? 'selected' : '' }} value="MM">MM - MYANMAR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MN' ? 'selected' : '' }} value="MN">MN - MONGOLIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MO' ? 'selected' : '' }} value="MO">MO - MACAU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MP' ? 'selected' : '' }} value="MP">MP - NORTHERN MARIANA ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MQ' ? 'selected' : '' }} value="MQ">MQ - MARTINIQUE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MR' ? 'selected' : '' }} value="MR">MR - MAURITANIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MS' ? 'selected' : '' }} value="MS">MS - MONTSERRAT</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MT' ? 'selected' : '' }} value="MT">MT - MALTA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MU' ? 'selected' : '' }} value="MU">MU - MAURITIUS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MV' ? 'selected' : '' }} value="MV">MV - MALDIVES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MW' ? 'selected' : '' }} value="MW">MW - MALAWI</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MX' ? 'selected' : '' }} value="MX">MX - MEXICO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MY' ? 'selected' : '' }} value="MY">MY - MALAYSIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'MZ' ? 'selected' : '' }} value="MZ">MZ - MOZAMBIQUE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NA' ? 'selected' : '' }} value="NA">NA - NAMIBIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NC' ? 'selected' : '' }} value="NC">NC - NEW CALEDONIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NE' ? 'selected' : '' }} value="NE">NE - NIGER</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NF' ? 'selected' : '' }} value="NF">NF - NORFOLK ISLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NG' ? 'selected' : '' }} value="NG">NG - NIGERIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NI' ? 'selected' : '' }} value="NI">NI - NICARAGUA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NL' ? 'selected' : '' }} value="NL">NL - NETHERLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NO' ? 'selected' : '' }} value="NO">NO - NORWAY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NP' ? 'selected' : '' }} value="NP">NP - NEPAL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NR' ? 'selected' : '' }} value="NR">NR - NAURU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NU' ? 'selected' : '' }} value="NU">NU - NIUE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'NZ' ? 'selected' : '' }} value="NZ">NZ - NEW ZEALAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'OM' ? 'selected' : '' }} value="OM">OM - OMAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PA' ? 'selected' : '' }} value="PA">PA - PANAMA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PE' ? 'selected' : '' }} value="PE">PE - PERU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PF' ? 'selected' : '' }} value="PF">PF - FRENCH POLYNESIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PG' ? 'selected' : '' }} value="PG">PG - PAPUA NEW GUINEA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PH' ? 'selected' : '' }} value="PH">PH - PHILIPPINES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PK' ? 'selected' : '' }} value="PK">PK - PAKISTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PL' ? 'selected' : '' }} value="PL">PL - POLAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PM' ? 'selected' : '' }} value="PM">PM - SAINT PIERRE AND MIQUELON</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PN' ? 'selected' : '' }} value="PN">PN - PITCAIRN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PR' ? 'selected' : '' }} value="PR">PR - PUERTO RICO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PS' ? 'selected' : '' }} value="PS">PS - PALESTINIAN TERRITORY, OCCUPIED</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PT' ? 'selected' : '' }} value="PT">PT - PORTUGAL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PW' ? 'selected' : '' }} value="PW">PW - PALAU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'PY' ? 'selected' : '' }} value="PY">PY - PARAGUAY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'QA' ? 'selected' : '' }} value="QA">QA - QATAR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'RE' ? 'selected' : '' }} value="RE">RE - REUNION</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'RO' ? 'selected' : '' }} value="RO">RO - ROMANIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'RS' ? 'selected' : '' }} value="RS">RS - SERBIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'RU' ? 'selected' : '' }} value="RU">RU - RUSSIAN FEDERATION</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'RW' ? 'selected' : '' }} value="RW">RW - RWANDA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SA' ? 'selected' : '' }} value="SA">SA - SAUDI ARABIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SB' ? 'selected' : '' }} value="SB">SB - SOLOMON ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SC' ? 'selected' : '' }} value="SC">SC - SEYCHELLES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SD' ? 'selected' : '' }} value="SD">SD - SUDAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SE' ? 'selected' : '' }} value="SE">SE - SWEDEN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SG' ? 'selected' : '' }} value="SG">SG - SINGAPORE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SH' ? 'selected' : '' }} value="SH">SH - SAINT HELENA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SI' ? 'selected' : '' }} value="SI">SI - SLOVENIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SJ' ? 'selected' : '' }} value="SJ">SJ - SVALBARD AND JAN MAYEN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SK' ? 'selected' : '' }} value="SK">SK - SLOVAKIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SL' ? 'selected' : '' }} value="SL">SL - SIERRA LEONE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SM' ? 'selected' : '' }} value="SM">SM - SAN MARINO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SN' ? 'selected' : '' }} value="SN">SN - SENEGAL</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SO' ? 'selected' : '' }} value="SO">SO - SOMALIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SR' ? 'selected' : '' }} value="SR">SR - SURINAME</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SS' ? 'selected' : '' }} value="SS">SS - South Sudan</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ST' ? 'selected' : '' }} value="ST">ST - SAO TOME AND PRINCIPE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SV' ? 'selected' : '' }} value="SV">SV - EL SALVADOR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SX' ? 'selected' : '' }} value="SX">SX - Sint Maarten (Dutch Part)</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SY' ? 'selected' : '' }} value="SY">SY - SYRIAN ARAB REPUBLIC</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'SZ' ? 'selected' : '' }} value="SZ">SZ - SWAZILAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TC' ? 'selected' : '' }} value="TC">TC - TURKS AND CAICOS ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TD' ? 'selected' : '' }} value="TD">TD - CHAD</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TF' ? 'selected' : '' }} value="TF">TF - FRENCH SOUTHERN TERRITORIES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TG' ? 'selected' : '' }} value="TG">TG - TOGO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TH' ? 'selected' : '' }} value="TH">TH - THAILAND</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TJ' ? 'selected' : '' }} value="TJ">TJ - TAJIKISTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TK' ? 'selected' : '' }} value="TK">TK - TOKELAU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TL' ? 'selected' : '' }} value="TL">TL - Timor-Leste</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TM' ? 'selected' : '' }} value="TM">TM - TURKMENISTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TN' ? 'selected' : '' }} value="TN">TN - TUNISIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TO' ? 'selected' : '' }} value="TO">TO - TONGA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TP' ? 'selected' : '' }} value="TP">TP - EAST TIMOR</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TR' ? 'selected' : '' }} value="TR">TR - TURKEY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TT' ? 'selected' : '' }} value="TT">TT - TRINIDAD AND TOBAGO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TV' ? 'selected' : '' }} value="TV">TV - TUVALU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TW' ? 'selected' : '' }} value="TW">TW - TAIWAN, PROVINCE OF CHINA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'TZ' ? 'selected' : '' }} value="TZ">TZ - TANZANIA, UNITED REPUBLIC OF</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'UA' ? 'selected' : '' }} value="UA">UA - UKRAINE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'UG' ? 'selected' : '' }} value="UG">UG - UGANDA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'UM' ? 'selected' : '' }} value="UM">UM - UNITED STATES MINOR OUTLYING ISLANDS</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'US' ? 'selected' : '' }} value="US">US - UNITED STATES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'UY' ? 'selected' : '' }} value="UY">UY - URUGUAY</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'UZ' ? 'selected' : '' }} value="UZ">UZ - UZBEKISTAN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VA' ? 'selected' : '' }} value="VA">VA - HOLY SEE (VATICAN CITY STATE)</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VC' ? 'selected' : '' }} value="VC">VC - SAINT VINCENT AND THE GRENADINES</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VE' ? 'selected' : '' }} value="VE">VE - VENEZUELA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VG' ? 'selected' : '' }} value="VG">VG - VIRGIN ISLANDS, BRITISH</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VI' ? 'selected' : '' }} value="VI">VI - VIRGIN ISLANDS, U.S.</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VN' ? 'selected' : '' }} value="VN">VN - VIET NAM</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'VU' ? 'selected' : '' }} value="VU">VU - VANUATU</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'WF' ? 'selected' : '' }} value="WF">WF - WALLIS AND FUTUNA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'WS' ? 'selected' : '' }} value="WS">WS - SAMOA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'XZ' ? 'selected' : '' }} value="XZ">XZ - KOSOVO</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'YE' ? 'selected' : '' }} value="YE">YE - YEMEN</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'YT' ? 'selected' : '' }} value="YT">YT - MAYOTTE</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'YU' ? 'selected' : '' }} value="YU">YU - YUGOSLAVIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ZA' ? 'selected' : '' }} value="ZA">ZA - SOUTH AFRICA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ZM' ? 'selected' : '' }} value="ZM">ZM - ZAMBIA</option>
                                                    <option {{ ($draftItem['kodeNegaraAsal'] ?? '') == 'ZW' ? 'selected' : '' }} value="ZW">ZW - ZIMBABWE</option>
                                                    </select>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Uraian Jenis Barang</label>
                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="2">{{ $draftItem['uraian'] ?? $item->itemdesc }}</textarea>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Merek</label>
                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Tipe</label>
                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? 'TIPE BARANG' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Ukuran</label>
                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Spesifikasi Lain</label>
                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? $item->remark ?? '-' }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small mb-0">Asal Bahan Baku</label>
                                                <select name="barang[{{ $index }}][kodeAsalBahanBaku]" class="form-control form-control-sm">
                                                    <option value="0" {{ ($draftItem['kodeAsalBahanBaku'] ?? '0') == '0' ? 'selected' : '' }}>0 - Impor</option>
                                                    <option value="1" {{ ($draftItem['kodeAsalBahanBaku'] ?? '') == '1' ? 'selected' : '' }}>1 - Lokal</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-4 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Jumlah & Berat</div>
                                            <div class="row">
                                                <div class="col-4 form-group mb-2 pr-1">
                                                    <label class="small mb-0">Jml Satuan</label>
                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}" placeholder="contoh: 100">
                                                </div>
                                                <div class="col-8 form-group mb-2 pl-1">
                                                    <label class="small mb-0">Satuan</label>
                                                    <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih Satuan --</option>
                                                        @foreach($listSatuanBarang as $kSat => $vSat)
                                                            <option value="{{ $kSat }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $kSat ? 'selected' : '' }}>{{ $kSat }} - {{ $vSat }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-4 form-group mb-2 pr-1">
                                                    <label class="small mb-0">Jml Kemasan</label>
                                                    <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['jumlahKemasan'] ?? "" }}" placeholder="contoh: 10">
                                                </div>
                                                <div class="col-8 form-group mb-2 pl-1">
                                                    <label class="small mb-0">Kemasan</label>
                                                    <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                        <option value="">-- Pilih Kemasan --</option>
                                                        @foreach($listJenisKemasan as $kKem => $vKem)
                                                            <option value="{{ $kKem }}" {{ ($draftItem['kodeJenisKemasan'] ?? '') == $kKem ? 'selected' : '' }}>{{ $kKem }} - {{ $vKem }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- <div class="form-group mb-2">
                                                <label class="small mb-0">Volume (M3)</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][volume]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['volume'] ?? "" }}" placeholder="contoh: 0.0500">
                                            </div> --}}
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Berat Bersih / Netto (Kg)</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][netto]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['netto'] ?? "" }}">
                                            </div>
                                            <div class="form-group mb-0">
                                                <label class="small mb-0">Jumlah Bahan Baku</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][jumlahBahanBaku]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['jumlahBahanBaku'] ?? "" }}">
                                            </div>
                                        </div>

                                        <div class="col-md-2 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Harga</div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Harga Satuan</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][hargaSatuan]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['hargaSatuan'] ?? (float) $item->price }}" placeholder="contoh: 1500.0000">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0 fw-bold">Harga Penyerahan</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm fw-bold input-decimal" value="{{ $draftItem['hargaPenyerahan'] ?? (float) ($item->qty * $item->price) }}" placeholder="contoh: 150000.0000">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Harga Perolehan</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][hargaPerolehan]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['hargaPerolehan'] ?? '' }}" placeholder="contoh: 150000.0000">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">CIF</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][cif]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['cif'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">CIF Rupiah</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][cifRupiah]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['cifRupiah'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">FOB</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][fob]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['fob'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Freight</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][freight]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['freight'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Asuransi</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][asuransi]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['asuransi'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Diskon</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][diskon]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['diskon'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">NDPBM</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][ndpbm]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['ndpbm'] ?? '' }}">
                                            </div>
                                            <div class="form-group mb-2">
                                                <label class="small mb-0">Nilai Barang</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiBarang]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['nilaiBarang'] ?? '' }}">
                                            </div>
                                            {{-- <div class="form-group mb-0">
                                                <label class="small mb-0">Nilai Jasa</label>
                                                <input type="text" inputmode="decimal" name="barang[{{ $index }}][nilaiJasa]" class="form-control form-control-sm input-decimal" value="{{ $draftItem['nilaiJasa'] ?? '' }}">
                                            </div> --}}
                                        </div>

                                        <div class="col-md-3 border-left">
                                            <div class="border-bottom fw-bold mb-2 pb-1" style="font-size: 12px; color: #003366;">Pungutan (Tarif)</div>
                                            @php
                                                $tarif = $draftItem['barangTarif'] ?? [];
                                                // Defaults if not set
                                                $bm = $tarif[0] ?? ['kodeJenisPungutan' => 'BM', 'tarif' => 0, 'tarifFasilitas' => 100, 'kodeFasilitasTarif' => '3'];
                                                $pph = $tarif[1] ?? ['kodeJenisPungutan' => 'PPH', 'tarif' => 0, 'tarifFasilitas' => 100, 'kodeFasilitasTarif' => '3'];
                                                $ppn = $tarif[2] ?? ['kodeJenisPungutan' => 'PPN', 'tarif' => 11, 'tarifFasilitas' => 100, 'kodeFasilitasTarif' => '3'];
                                            @endphp

                                            <!-- Tarif BM -->
                                            <div class="mb-3 border-bottom pb-2">
                                                <label class="small mb-0 fw-bold">BM</label>
                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][0][kodeJenisPungutan]" value="BM">
                                                <select name="barang[{{ $index }}][barangTarif][0][kodeFasilitasTarif]" class="form-control form-control-sm mb-2 px-1 select2bs4">
                                                    <option value="" {{ ($bm['kodeFasilitasTarif'] ?? '') == '' ? 'selected' : '' }}>-- Pilih Fasilitas --</option>
                                                    <option value="3" {{ ($bm['kodeFasilitasTarif'] ?? '3') == '3' ? 'selected' : '' }}>3-Ditangguhkan</option>
                                                    <option value="5" {{ ($bm['kodeFasilitasTarif'] ?? '5') == '5' ? 'selected' : '' }}>5-Dibebaskan</option>
                                                    <option value="6" {{ ($bm['kodeFasilitasTarif'] ?? '6') == '6' ? 'selected' : '' }}>6-Tidak Dipungut</option>
                                                </select>
                                                <div class="row">
                                                    <div class="col-6 pr-1">
                                                        <label class="small mb-0">Tarif (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][0][tarif]" class="form-control form-control-sm input-decimal" value="{{ $bm['tarif'] ?? 0 }}">
                                                    </div>
                                                    <div class="col-6 pl-1">
                                                        <label class="small mb-0">Fas. (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][0][tarifFasilitas]" class="form-control form-control-sm input-decimal" value="{{ $bm['tarifFasilitas'] ?? 100 }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tarif PPH -->
                                            <div class="mb-3 border-bottom pb-2">
                                                <label class="small mb-0 fw-bold">PPH</label>
                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][1][kodeJenisPungutan]" value="PPH">
                                                <select name="barang[{{ $index }}][barangTarif][1][kodeFasilitasTarif]" class="form-control form-control-sm mb-2 px-1 select2bs4">
                                                    <option value="" {{ ($pph['kodeFasilitasTarif'] ?? '') == '' ? 'selected' : '' }}>-- Pilih Fasilitas --</option>
                                                    <option value="3" {{ ($pph['kodeFasilitasTarif'] ?? '3') == '3' ? 'selected' : '' }}>3-Ditangguhkan</option>
                                                    <option value="5" {{ ($pph['kodeFasilitasTarif'] ?? '5') == '5' ? 'selected' : '' }}>5-Dibebaskan</option>
                                                    <option value="6" {{ ($pph['kodeFasilitasTarif'] ?? '6') == '6' ? 'selected' : '' }}>6-Tidak Dipungut</option>
                                                </select>
                                                <div class="row">
                                                    <div class="col-6 pr-1">
                                                        <label class="small mb-0">Tarif (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][1][tarif]" class="form-control form-control-sm input-decimal" value="{{ $pph['tarif'] ?? 0 }}">
                                                    </div>
                                                    <div class="col-6 pl-1">
                                                        <label class="small mb-0">Fas. (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][1][tarifFasilitas]" class="form-control form-control-sm input-decimal" value="{{ $pph['tarifFasilitas'] ?? 100 }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tarif PPN -->
                                            <div class="mb-2">
                                                <label class="small mb-0 fw-bold">PPN</label>
                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][2][kodeJenisPungutan]" value="PPN">
                                                <select name="barang[{{ $index }}][barangTarif][2][kodeFasilitasTarif]" class="form-control form-control-sm mb-2 px-1 select2bs4">
                                                    <option value="" {{ ($ppn['kodeFasilitasTarif'] ?? '') == '' ? 'selected' : '' }}>-- Pilih Fasilitas --</option>
                                                    <option value="3" {{ ($ppn['kodeFasilitasTarif'] ?? '3') == '3' ? 'selected' : '' }}>3-Ditangguhkan</option>
                                                    <option value="5" {{ ($ppn['kodeFasilitasTarif'] ?? '5') == '5' ? 'selected' : '' }}>5-Dibebaskan</option>
                                                    <option value="6" {{ ($ppn['kodeFasilitasTarif'] ?? '6') == '6' ? 'selected' : '' }}>6-Tidak Dipungut</option>
                                                </select>
                                                <div class="row">
                                                    <div class="col-6 pr-1">
                                                        <label class="small mb-0">Tarif (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][2][tarif]" class="form-control form-control-sm input-decimal" value="{{ $ppn['tarif'] ?? 11 }}">
                                                    </div>
                                                    <div class="col-6 pl-1">
                                                        <label class="small mb-0">Fas. (%)</label>
                                                        <input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][2][tarifFasilitas]" class="form-control form-control-sm input-decimal" value="{{ $ppn['tarifFasilitas'] ?? 100 }}">
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
                    <div class="section-title"><i class="fas fa-building"></i> Entitas Pengusaha TPB (Kode: 3)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[0][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP (15/16 Digit)</label><input type="text" name="entitas[0][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['nomorIdentitas'] ?? '0745406926444000000000' }}"></div>
                        <div class="col-md-4 form-group"><label>NIB</label><input type="text" name="entitas[0][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['nibEntitas'] ?? '0220103231143' }}"></div>
                        <div class="col-md-8 form-group"><label>Alamat</label><input type="text" name="entitas[0][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}"></div>
                        <div class="col-md-4 form-group"><label>No. Izin TPB</label><input type="text" name="entitas[0][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}"></div>
                        <div class="col-md-4 form-group"><label>Tgl. Izin TPB</label><input type="date" name="entitas[0][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][0]['tanggalIjinEntitas'] ?? '2026-01-20' }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-truck-loading"></i> Entitas Pemasok (Kode: 5)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[1][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][1]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>Kode Negara</label><input type="text" name="entitas[1][kodeNegara]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][1]['kodeNegara'] ?? 'ID' }}"></div>
                        <div class="col-md-12 form-group"><label>Alamat</label><input type="text" name="entitas[1][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][1]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                    </div>

                    <div class="section-title"><i class="fas fa-user-tag"></i> Entitas Pemilik Barang (Kode: 7)</div>
                    <div class="row">
                        <div class="col-md-4 form-group"><label>Nama Entitas</label><input type="text" name="entitas[2][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['namaEntitas'] ?? $header->supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>NPWP</label><input type="text" name="entitas[2][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>Jenis API</label><input type="text" name="entitas[2][kodeJenisApi]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['kodeJenisApi'] ?? '2' }}"></div>
                        <div class="col-md-8 form-group"><label>Alamat</label><input type="text" name="entitas[2][alamatEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}"></div>
                        <div class="col-md-4 form-group"><label>Tgl Izin Entitas</label><input type="date" name="entitas[2][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][2]['tanggalIjinEntitas'] ?? '' }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pendukung" role="tabpanel">

                    <div class="section-title mt-0">Pengangkut & Pungutan</div>
                    <div class="row mb-3">
                        <div class="col-md-3 form-group"><label>Nama Pengangkut</label><input type="text" name="pengangkut[nama]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nama'] ?? 'TRUK' }}"></div>
                        <div class="col-md-3 form-group"><label>Nomor Polisi</label><input type="text" name="pengangkut[nomor]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nomor'] ?? $header->nomor_mobil ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Kode Bendera</label><input type="text" name="pengangkut[kodeBendera]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['kodeBendera'] ?? 'ID' }}" placeholder="contoh: ID"></div>
                        <div class="col-md-3 form-group"><label>Cara Angkut</label>
                            <select name="pengangkut[kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
                                <option value="">-- Pilih Cara Angkut --</option>
                                <option value="1" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                <option value="2" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '2' ? 'selected' : '' }}>2 - KERETA API</option>
                                <option value="3" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                <option value="4" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '4' ? 'selected' : '' }}>4 - UDARA</option>
                                <option value="5" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '5' ? 'selected' : '' }}>5 - POS</option>
                                <option value="6" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '6' ? 'selected' : '' }}>6 - MULTIMODA</option>
                                <option value="7" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '7' ? 'selected' : '' }}>7 - INSTALASI / PIPA</option>
                                <option value="8" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '8' ? 'selected' : '' }}>8 - PERAIRAN</option>
                                <option value="9" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '9' ? 'selected' : '' }}>9 - LAINNYA</option>
                                <option value="10" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '10' ? 'selected' : '' }}>10 - INSTALASI</option>
                                <option value="11" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '11' ? 'selected' : '' }}>11 - PIPA</option>
                                <option value="12" {{ ($dataDetail['pengangkut']['kodeCaraAngkut'] ?? '') == '12' ? 'selected' : '' }}>12 - TRANSMISI</option>
                            </select>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Jenis Pungutan</label>
                            @php
                                $listPungutan = [
                                    'PPN' => 'PPN Impor'
                                ];
                                $selectedPungutan = $dataDetail['pungutan']['jenis'] ?? 'PPN';
                            @endphp
                            <select name="pungutan[jenis]" class="form-control form-control-sm select2bs4">
                                @foreach($listPungutan as $kode => $nama)
                                    <option value="{{ $kode }}" {{ $selectedPungutan == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 form-group"><label>Nilai Pungutan</label><input type="text" inputmode="decimal" name="pungutan[nilai]" class="form-control form-control-sm input-decimal" value="{{ $dataDetail['pungutan']['nilai'] ?? "" }}"></div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4 form-group">
                            <label>Pelabuhan Muat</label>
                            <select name="kodePelMuat" class="form-control form-control-sm select2-pelabuhan">
                                @if(!empty($dataDetail['kodePelMuat']))
                                    <option value="{{ $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuat'] }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Pelabuhan Transit</label>
                            <select name="kodePelTransit" class="form-control form-control-sm select2-pelabuhan">
                                @if(!empty($dataDetail['kodePelTransit']))
                                    <option value="{{ $dataDetail['kodePelTransit'] }}" selected>{{ $dataDetail['kodePelTransit'] }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Tempat Penimbunan (TPS)</label>
                            <select name="kodeTps" class="form-control form-control-sm select2-pelabuhan">
                                @if(!empty($dataDetail['kodeTps']))
                                    <option value="{{ $dataDetail['kodeTps'] }}" selected>{{ $dataDetail['kodeTps'] }}</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="section-title">Data Kemasan</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $kemasans = $dataDetail['kemasan'] ?? [];

                                if (empty($kemasans)) {
                                    $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? "", 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
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
                                    <tr>
                                        <td><input type="text" inputmode="decimal" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? "" }}" placeholder="contoh: 10"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? '-' }}"></td>
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
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}" placeholder="Contoh: TGHU1234567"></td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $kIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
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

                    <div class="section-title">Dokumen Pendukung</div>
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $referensiDokumen = [
                                    '10' => 'RKSP', '11' => 'MANIFES', '16' => 'BC 1.6', '20' => 'BC 2.0 - PIB',
                                    '23' => 'BC 2.3', '25' => 'BC 2.5', '27' => 'BC 2.7', '30' => 'BC 3.0 - PEB',
                                    '40' => 'BC 4.0', '41' => 'BC 4.1', '217' => 'PACKING LIST', '380' => 'INVOICE', '388' => 'FAKTUR PAJAK'
                                ];

                                $dokumens = [];
                                if (!empty($dataDetail['dok']) && count($dataDetail['dok']) > 0) {
                                    $dokumens = $dataDetail['dok'];
                                }
                            @endphp
                            <table class="table table-sm table-bordered" id="table-dokumen">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="40%">Kode Dokumen</th>
                                        <th width="35%">Nomor Dokumen</th>
                                        <th width="15%">Tgl Dokumen</th>
                                        <th width="10%"><button type="button" class="btn btn-sm btn-primary py-0 px-2" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                    <tr>
                                        <td>
                                            <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Kode --</option>
                                                @foreach($referensiDokumen as $val => $text)
                                                    <option value="{{ $val }}" {{ ($dok['kode'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                @endforeach
                                                @if(!empty($dok['kode']) && !array_key_exists($dok['kode'], $referensiDokumen))
                                                    <option value="{{ $dok['kode'] }}" selected>{{ $dok['kode'] }} - Custom</option>
                                                @endif
                                            </select>
                                        </td>
                                        <td><input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomor'] ?? '' }}"></td>
                                        <td><input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tgl'] ?? '' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok" title="Hapus Baris"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="card-footer text-right bg-white border-top">
            <a href="{{ route('dokumen-pabean-index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-save"></i> Simpan Draft</button>
        </div>
    </form>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {

        function hitungNilaiPungutan() {
            let total = 0;
            $('input[name^="barang["][name$="][hargaPenyerahan]"]').each(function() {
                let val = parseFloat($(this).val()) || 0;
                total += val;
            });

            let formattedTotal = Number.isInteger(total) ? total : total.toFixed(2);

            $('input[name="pungutan[nilai]"]').val(formattedTotal);
            $('input[name="hargaPenyerahan"]').val(formattedTotal);
        }

        $(document).on('input', 'input[name^="barang["][name$="][hargaPenyerahan]"]', function() {
            hitungNilaiPungutan();
        });

        hitungNilaiPungutan();

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
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm" value=""></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm" value=""></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>
            `;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%', tags: true });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() {
            if ($('#tbody-dokumen tr').length > 1) { $(this).closest('tr').remove(); }
            else { Swal.fire('Info', 'Minimal sisakan 1 baris.', 'info'); }
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


        $('#form-edit-bc23').on('submit', function(e) {
            e.preventDefault();

            if($('input[name="pengangkut[nomor]"]').val() === ""){
                Swal.fire({
                    title: 'Error!',
                    text: 'No Polisi Pengangkut belum diisi.',
                    icon: 'error'
                });
                return;
            }

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

        $('#btn-send-ceisa-bc23').on('click', function() {
            let url = $(this).data('url');
            Swal.fire({
                title: 'Kirim ke CEISA?',
                text: "Pastikan draft sudah disimpan dan valid.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#003366',
                confirmButtonText: 'Ya, Kirim!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Mengirim...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Berhasil!', response.message, 'success').then(() => {
                                window.location.href = "{{ route('dokumen-pabean-index') }}";
                            });
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Gagal mengirim ke CEISA.';
                            Swal.fire('Gagal!', msg, 'error');
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

        $('.select2-pelabuhan').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Pelabuhan...',
            allowClear: true,
            ajax: {
                url: '{{ route("ceisa.pelabuhan") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        q: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.results
                    };
                },
                cache: true
            },
            minimumInputLength: 2
        });

    });
</script>
@endsection
