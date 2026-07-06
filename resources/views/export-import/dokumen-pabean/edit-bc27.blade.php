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
            <i class="fas fa-edit"></i> BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc27', $header->bpbno) }}" method="POST" id="form-edit-ceisa">

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
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header"><h3 class="card-title font-weight-bold" style="font-size: 14px;">Pengajuan Asal</h3></div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Nomor Aju</label>
                                        <div class="col-sm-8">
                                            <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Asal</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantor" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Kantor Asal</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ (isset($dataDetail['kodeKantor']) && $dataDetail['kodeKantor'] == $val) || (!isset($dataDetail['kodeKantor']) && $val == '050500') ? 'selected' : '' }}>
                                                        {{ $val }} - {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis TPB Asal</label>
                                        <div class="col-sm-8">
                                            <select name="jenisTPB" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Jenis TPB Asal</option>
                                                @php $selectedJenisTPB = $dataDetail['jenisTPB'] ?? $dataDetail['kodeJenisTpb'] ?? $dataDetail['jenisTpb'] ?? '' @endphp
                                                <option value="1" {{ $selectedJenisTPB == '1' ? 'selected' : '' }}>KAWASAN BERIKAT</option>
                                                <option value="2" {{ $selectedJenisTPB == '2' ? 'selected' : '' }}>GUDANG BERIKAT</option>
                                                <option value="3" {{ $selectedJenisTPB == '3' ? 'selected' : '' }}>TPPB</option>
                                                <option value="4" {{ $selectedJenisTPB == '4' ? 'selected' : '' }}>TBB</option>
                                                <option value="5" {{ $selectedJenisTPB == '5' ? 'selected' : '' }}>TLB</option>
                                                <option value="6" {{ $selectedJenisTPB == '6' ? 'selected' : '' }}>KDUB</option>
                                                <option value="7" {{ $selectedJenisTPB == '7' ? 'selected' : '' }}>LAINNYA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-outline card-secondary">
                                <div class="card-header"><h3 class="card-title font-weight-bold" style="font-size: 14px;">Pengajuan Tujuan</h3></div>
                                <div class="card-body">
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Tujuan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorTujuan" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Kantor Tujuan</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ (isset($dataDetail['kodeKantorTujuan']) && $dataDetail['kodeKantorTujuan'] == $val) ? 'selected' : '' }}>
                                                        {{ $val }} - {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis TPB Tujuan</label>
                                        <div class="col-sm-8">
                                            <select name="jenisTpbTujuan" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Jenis TPB Tujuan</option>
                                                @php $selectedJenisTpbTujuan = $dataDetail['jenisTpbTujuan'] ?? '' @endphp
                                                <option value="1" {{ $selectedJenisTpbTujuan == '1' ? 'selected' : '' }}>KAWASAN BERIKAT</option>
                                                <option value="2" {{ $selectedJenisTpbTujuan == '2' ? 'selected' : '' }}>GUDANG BERIKAT</option>
                                                <option value="3" {{ $selectedJenisTpbTujuan == '3' ? 'selected' : '' }}>TPPB</option>
                                                <option value="4" {{ $selectedJenisTpbTujuan == '4' ? 'selected' : '' }}>TBB</option>
                                                <option value="5" {{ $selectedJenisTpbTujuan == '5' ? 'selected' : '' }}>TLB</option>
                                                <option value="6" {{ $selectedJenisTpbTujuan == '6' ? 'selected' : '' }}>KDUB</option>
                                                <option value="7" {{ $selectedJenisTpbTujuan == '7' ? 'selected' : '' }}>LAINNYA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-4 col-form-label text-sm">Tujuan Pengiriman</label>
                                        <div class="col-sm-8">
                                            <select name="kodeTujuanTpb" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Tujuan Pengiriman</option>
                                                @php $tujuanTpb = $dataDetail['kodeTujuanTpb'] ?? '' @endphp
                                                <option value="1" {{ $tujuanTpb == '1' ? 'selected' : '' }}>1 - PENYERAHAN BKP</option>
                                                <option value="2" {{ $tujuanTpb == '2' ? 'selected' : '' }}>2 - PENYERAHAN JKP</option>
                                                <option value="3" {{ $tujuanTpb == '3' ? 'selected' : '' }}>3 - RETUR</option>
                                                <option value="4" {{ $tujuanTpb == '4' ? 'selected' : '' }}>4 - NON PENYERAHAN</option>
                                                <option value="5" {{ $tujuanTpb == '5' ? 'selected' : '' }}>5 - LAINNYA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
                                        <!-- KOLOM 1: Data Barang -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Data Barang</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Pos Tarif/HS</label>
                                                        <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Masukkan Pos Tarif/HS">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Kode Barang</label>
                                                        <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->id_item ?? '' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Uraian Jenis Barang</label>
                                                        <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Merek</label>
                                                        <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Tipe</label>
                                                        <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? 'TIPE BARANG' }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Ukuran</label>
                                                        <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Spesifikasi Lain</label>
                                                        <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? $item->remark ?? '-' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- KOLOM 2: Jumlah & Berat -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Jumlah & Berat</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Jumlah dan Satuan Barang</label>
                                                        <div class="row">
                                                            <div class="col-7 pr-1">
                                                                <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                            </div>
                                                            <div class="col-5 pl-1">
                                                                <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                     @php $savedSatuan = $draftItem['kodeSatuanBarang'] ?? $item->unit ?? 'PCS'; @endphp
                                                                    <option value="">-- Pilih Satuan --</option>
                                                                    @foreach($listSatuanBarang as $kode => $label)
                                                                        <option value="{{ $kode }}" {{ $savedSatuan == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $label }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Volume (m3)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][volume]" class="form-control form-control-sm" value="{{ $draftItem['volume'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Berat Bersih / Netto (kg)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Berat Kotor / Bruto (kg)</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][bruto]" class="form-control form-control-sm" value="{{ $draftItem['bruto'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Kemasan</label>
                                                        <div class="row">
                                                            <div class="col-7 pr-1">
                                                                <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                            </div>
                                                            <div class="col-5 pl-1">
                                                                <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                    <option value="">-- Pilih Jenis Kemasan --</option>
                                                                    @foreach($listJenisKemasan as $k => $v)
                                                                        <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Keterangan Lainnya --}}
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Keterangan Lainnya</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Kategori Barang</label>
                                                        <select name="barang[{{ $index }}][kategoriBarang]" class="form-control form-control-sm">
                                                            <option value="">-- Pilih --</option>
                                                            <option value="1" {{ ($draftItem['kategoriBarang'] ?? '') == '1' ? 'selected' : '' }}>1 - Hasil Produksi</option>
                                                            <option value="10" {{ ($draftItem['kategoriBarang'] ?? '') == '10' ? 'selected' : '' }}>10 - Barang Contoh</option>
                                                            <option value="2" {{ ($draftItem['kategoriBarang'] ?? '') == '2' ? 'selected' : '' }}>2 - Bahan Baku</option>
                                                            <option value="3" {{ ($draftItem['kategoriBarang'] ?? '') == '3' ? 'selected' : '' }}>3 - Barang Modal</option>
                                                            <option value="4" {{ ($draftItem['kategoriBarang'] ?? '') == '4' ? 'selected' : '' }}>4 - Peralatan Kantor</option>
                                                            <option value="5" {{ ($draftItem['kategoriBarang'] ?? '') == '5' ? 'selected' : '' }}>5 - Sisa dari Proses Produksi/Limbah</option>
                                                            <option value="6" {{ ($draftItem['kategoriBarang'] ?? '') == '6' ? 'selected' : '' }}>6 - Barang yang Ditimbun untuk Dijual</option>
                                                            <option value="7" {{ ($draftItem['kategoriBarang'] ?? '') == '7' ? 'selected' : '' }}>7 - Barang yang Dipamerkan untuk Dijual</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- KOLOM 3: Harga Barang -->
                                        <div class="col-md-4">
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Harga Barang</div>
                                                <div class="card-body">
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Nilai CIF</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][cif]" class="form-control form-control-sm" value="{{ $draftItem['cif'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">CIF Rupiah</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][cifRupiah]" class="form-control form-control-sm" value="{{ $draftItem['cifRupiah'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Harga Penyerahan/Harga Jual</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm" value="{{ $draftItem['hargaPenyerahan'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Harga Pabrikasi</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][hargaPabrikasi]" class="form-control form-control-sm" value="{{ $draftItem['hargaPabrikasi'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Nilai Penggantian/Nilai Jasa</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][nilaiPenggantian]" class="form-control form-control-sm" value="{{ $draftItem['nilaiPenggantian'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">FOB</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][fob]" class="form-control form-control-sm" value="{{ $draftItem['fob'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-2">
                                                        <label class="small text-muted mb-0">Freight</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][freight]" class="form-control form-control-sm" value="{{ $draftItem['freight'] ?? 0 }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="small text-muted mb-0">Asuransi</label>
                                                        <input type="number" step="any" name="barang[{{ $index }}][asuransi]" class="form-control form-control-sm" value="{{ $draftItem['asuransi'] ?? 0 }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pungutan Tarif -->
                                            <div class="card shadow-sm mb-3">
                                                <div class="card-header bg-light fw-bold" style="font-size: 13px;">Pungutan (Tarif)</div>
                                                <div class="card-body p-2">
                                                    @php
                                                        $tarifList = $draftItem['barangTarif'] ?? [
                                                            ['kodeJenisPungutan' => 'BM',    'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPN',   'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPNBM', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                            ['kodeJenisPungutan' => 'PPH',   'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100],
                                                        ];
                                                        $tarifBM = $tarifPPN = $tarifPPNBM = $tarifPPH = null;
                                                        foreach($tarifList as $t) {
                                                            if(($t['kodeJenisPungutan']??'') == 'BM')    $tarifBM    = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPN')   $tarifPPN   = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPNBM') $tarifPPNBM = $t;
                                                            if(($t['kodeJenisPungutan']??'') == 'PPH')   $tarifPPH   = $t;
                                                        }
                                                        if(!$tarifBM)    $tarifBM    = ['kodeJenisPungutan' => 'BM',    'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPN)   $tarifPPN   = ['kodeJenisPungutan' => 'PPN',   'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPNBM) $tarifPPNBM = ['kodeJenisPungutan' => 'PPNBM', 'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        if(!$tarifPPH)   $tarifPPH   = ['kodeJenisPungutan' => 'PPH',   'tarif' => 0, 'kodeFasilitasTarif' => '3', 'tarifFasilitas' => 100];
                                                        $tarifList = [$tarifBM, $tarifPPN, $tarifPPNBM, $tarifPPH];
                                                    @endphp
                                                    <table class="table table-bordered table-sm mb-0" style="font-size:11px;">
                                                        <thead class="bg-light text-center">
                                                            <tr>
                                                                <th>Jenis</th>
                                                                <th>Tarif (%)</th>
                                                                <th>Fas. (%)</th>
                                                                <th>Fasilitas</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @foreach($tarifList as $tIdx => $tarif)
                                                        <tr>
                                                            <td class="text-center align-middle fw-bold">{{ $tarif['kodeJenisPungutan'] }}
                                                                <input type="hidden" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisPungutan]" value="{{ $tarif['kodeJenisPungutan'] }}">
                                                            </td>
                                                            <td><input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarif]" class="form-control form-control-sm input-decimal text-center" value="{{ $tarif['tarif'] }}" style="font-size:11px;"></td>
                                                            <td><input type="text" inputmode="decimal" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifFasilitas]" class="form-control form-control-sm input-decimal text-center" value="{{ $tarif['tarifFasilitas'] }}" style="font-size:11px;"></td>
                                                            <td>
                                                                <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeFasilitasTarif]" class="form-control form-control-sm" style="font-size:11px;">
                                                                    <option value="3" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '3' ? 'selected' : '' }}>3 - Ditangguhkan</option>
                                                                    <option value="5" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '5' ? 'selected' : '' }}>5 - Dibebaskan</option>
                                                                    <option value="6" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '6' ? 'selected' : '' }}>6 - Tdk Dipungut</option>
                                                                    <option value="7" {{ ($tarif['kodeFasilitasTarif'] ?? '') == '7' ? 'selected' : '' }}>7 - Dilunasi</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                        </tbody>
                                                        <tfoot class="bg-light">
                                                            <tr>
                                                                <td colspan="4" class="text-muted text-center" style="font-size:10px;">Total: BM + PPH + PPN dihitung di tab Pungutan</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>

                                                    <!-- BMT Toggle -->
                                                    <div class="mt-3 w-100">
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input bmt-toggle" id="bmtToggle{{$index}}">
                                                            <label class="custom-control-label fw-bold" for="bmtToggle{{$index}}" style="font-size:12px;">BMT (Bea Masuk Tambahan)</label>
                                                        </div>
                                                        <div class="bmt-container p-3 bg-light border rounded" style="display: none;">
                                                            @php
                                                                $bmtTypes = ['BMAD', 'BMTP', 'BMI', 'BMP'];
                                                                $bmtStartIndex = 4;
                                                            @endphp
                                                            @foreach($bmtTypes as $bIdx => $bmtType)
                                                            @php
                                                                $tIdx = $bmtStartIndex + $bIdx;
                                                                $bmtData = null;
                                                                foreach(($draftItem['barangTarif'] ?? []) as $t) {
                                                                    if(($t['kodeJenisPungutan']??'') == $bmtType) $bmtData = $t;
                                                                }
                                                                $isSementara = !empty($bmtData['sementara']) ? 'checked' : '';
                                                                $jenisTarif = $bmtData['kodeJenisTarif'] ?? '1';
                                                            @endphp
                                                            <div class="mb-3 bmt-row border-bottom pb-2">
                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-2" style="font-size: 11px;">
                                                                        <div class="fw-bold">{{ $bmtType }}</div>
                                                                        <div class="custom-control custom-checkbox">
                                                                            <input type="checkbox" class="custom-control-input" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][sementara]" id="sementara{{$bmtType}}{{$index}}" value="1" {{ $isSementara }}>
                                                                            <label class="custom-control-label text-muted" for="sementara{{$bmtType}}{{$index}}">Sementara</label>
                                                                        </div>
                                                                    </div>
                                                                    <input type="hidden" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisPungutan]" value="{{ $bmtType }}">
                                                                    <div class="col-md-3">
                                                                        <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeJenisTarif]" class="form-control form-control-sm bmt-jenis-tarif" style="font-size: 11px;">
                                                                            <option value="1" {{ $jenisTarif == '1' ? 'selected' : '' }}>Advalorum (%)</option>
                                                                            <option value="2" {{ $jenisTarif == '2' ? 'selected' : '' }}>Spesifik</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-7 bmt-inputs-container">
                                                                        <!-- Advalorum -->
                                                                        <div class="bmt-advalorum-inputs" style="{{ $jenisTarif == '1' ? '' : 'display:none;' }}">
                                                                            <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarif]" class="form-control form-control-sm" placeholder="%" value="{{ $jenisTarif == '1' ? ($bmtData['tarif'] ?? '') : '' }}" style="font-size: 11px;">
                                                                        </div>
                                                                        <!-- Spesifik -->
                                                                        <div class="bmt-spesifik-inputs row m-0" style="{{ $jenisTarif == '2' ? '' : 'display:none;' }}">
                                                                            <div class="col-6 pl-0 pr-1">
                                                                                <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][jumlahSatuan]" class="form-control form-control-sm" placeholder="Jml Satuan" value="{{ $bmtData['jumlahSatuan'] ?? '' }}" style="font-size: 11px;">
                                                                            </div>
                                                                            <div class="col-6 pr-0 pl-1">
                                                                                <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifSpesifik]" class="form-control form-control-sm" placeholder="Tarif" value="{{ $jenisTarif == '2' ? ($bmtData['tarif'] ?? '') : '' }}" style="font-size: 11px;">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row align-items-center">
                                                                    <div class="col-md-5"></div>
                                                                    <div class="col-md-4">
                                                                        <select name="barang[{{ $index }}][barangTarif][{{$tIdx}}][kodeFasilitasTarif]" class="form-control form-control-sm" style="font-size: 11px;">
                                                                            <option value="3" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '3' ? 'selected' : '' }}>3 - DTG - DITANGGUHKAN</option>
                                                                            <option value="5" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '5' ? 'selected' : '' }}>5 - BBS - DIBEBASKAN</option>
                                                                            <option value="6" {{ ($bmtData['kodeFasilitasTarif'] ?? '') == '6' ? 'selected' : '' }}>6 - TIDAK DIPUNGUT</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="col-md-3 pl-0">
                                                                        <div class="input-group input-group-sm">
                                                                            <input type="number" step="any" name="barang[{{ $index }}][barangTarif][{{$tIdx}}][tarifFasilitas]" class="form-control form-control-sm" placeholder="0" value="{{ $bmtData['tarifFasilitas'] ?? '0' }}" style="font-size: 11px;">
                                                                            <div class="input-group-append">
                                                                                <span class="input-group-text px-2" style="font-size: 11px;">%</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- end row --}}
                                    </div>
                                </div>{{-- card-body --}}
                            </div>{{-- collapse --}}
                        </div>{{-- accordion item --}}
                        @endforeach
                    </div>{{-- accordion --}}
                </div>{{-- tab-pane tab-barang --}}


                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">
                        {{-- Entitas 3: TPB Asal --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">TPB Asal Barang / Pengusaha Kena Pajak</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Identitas (NPWP)</label>
                                        <input type="text" name="entitas[3][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIdentitas'] ?? '0745406926444000000000' }}" placeholder="NPWP 15/16 digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NITKU</label>
                                        <input type="text" name="entitas[3][nitku]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nitku'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nama</label>
                                        <input type="text" name="entitas[3][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Alamat</label>
                                        <textarea name="entitas[3][alamatEntitas]" class="form-control form-control-sm" rows="2">{{ $dataDetail['entitas'][3]['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Izin TPB</label>
                                        <input type="text" name="entitas[3][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Tgl Izin TPB</label>
                                        <input type="date" name="entitas[3][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['tanggalIjinEntitas'] ?? '2026-01-20' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">NIB</label>
                                        <input type="text" name="entitas[3][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][3]['nibEntitas'] ?? '0220103231143' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Entitas 8: TPB Tujuan --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">TPB Tujuan / Penjual / Penerima Jasa / Penerima Barang</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Identitas (NPWP)</label>
                                        <input type="text" name="entitas[8][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['nomorIdentitas'] ?? $header->npwp_supplier ?? '' }}" placeholder="NPWP 15/16 digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NITKU</label>
                                        <input type="text" name="entitas[8][nitku]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['nitku'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nama</label>
                                        <input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['namaEntitas'] ?? $header->supplier ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Alamat</label>
                                        <textarea name="entitas[8][alamatEntitas]" class="form-control form-control-sm" rows="2">{{ $dataDetail['entitas'][8]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nomor Izin TPB</label>
                                        <input type="text" name="entitas[8][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['nomorIjinEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Tgl Izin TPB</label>
                                        <input type="date" name="entitas[8][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['tanggalIjinEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">NIB</label>
                                        <input type="text" name="entitas[8][nibEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][8]['nibEntitas'] ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Entitas 7: Pemilik Barang --}}
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Pemilik Barang</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NPWP</label>
                                        <input type="text" name="entitas[7][nomorIdentitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nomorIdentitas'] ?? '' }}" placeholder="NPWP 15/16 digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NITKU</label>
                                        <input type="text" name="entitas[7][nitku]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['nitku'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nama</label>
                                        <input type="text" name="entitas[7][namaEntitas]" class="form-control form-control-sm" value="{{ $dataDetail['entitas'][7]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Alamat</label>
                                        <textarea name="entitas[7][alamatEntitas]" class="form-control form-control-sm" rows="2">{{ $dataDetail['entitas'][7]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">

                    <div class="section-title mt-0">Dokumen Pendukung</div>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @php
                                $referensiDokumen = [
                                    '10' => 'RKSP', '11' => 'MANIFES', '16' => 'BC 1.6 - PEMBERITAHUAN PABEAN PENGELUARAN BARANG DARI KAWASAN PABEAN UNTUK DITIMBUN DI PUSAT LOGISTIK BERIKAT',
                                    '20' => 'BC 2.0 - PEMBERITAHUAN IMPOR BARANG', '21' => 'PIBK/IMPOR KHUSUS', '23' => 'BC 2.7 - PEMBERITAHUAN IMPOR BARANG UNTUK DITIMBUN DI TEMPAT PENIMBUNAN BERIKAT',
                                    '25' => 'BC 2.5 - PEMBERITAHUAN IMPOR BARANG DARI TEMPAT PENIMBUNAN BERIKAT', '27' => 'BC 2.7 - PEMBERITAHUAN PENGELUARAN UNTUK DIANGKUT DARI TEMPAT PENIMBUNAN BERIKAT KE TEMPAT PENIMBUNAN BERIKAT LAINNYA',
                                    '28' => 'BC 2.8 - PEMBERITAHUAN IMPOR BARANG DARI PUSAT LOGISTIK BERIKAT', '30' => 'BC 3.0 - PEMBERITAHUAN EKSPOR NARAMG',
                                    '33' => 'BC 3.3 - PEMBERITAHUAN EKSPOR BARANG MELALUI/DARI PUSAT LOGISTIK BERIKAT', '40' => 'BC 4.0 - PEMBERITAHUAN PEMASUKAN BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN KE TEMPAT PENIMBUNAN BERIKAT',
                                    '41' => 'BC 4.1 - PEMBERITAHUAN PENGELUARAN KEMBALI BARANG ASAL TEMPAT LAIN DALAM DAERAH PABEAN DARI TEMPAT PENIMBUNAN BERIKAT', '50' => 'KITE',
                                    '51' => 'FTZ 01', '52' => 'FTZ 02', '53' => 'FTZ 03', '65' => 'BC 1.1 KONSOLIDASI PJT',
                                    '111' => 'Bank Devisa Hasil Ekspor (DHE)', '161' => 'PPB - PEMBERITAHUAN PERPINDAHAN BARANG ANTAR TEMPAT PENIMBUNAN DALAM SATU PUSAT LOGISTIK BERIKAT',
                                    '202' => 'PENGELUARAN BAHAN BAKU DAN/ ATAU SISA BAHAN BAKU', '203' => 'PENGELUARAN SEMENTARA - SUBKONTRAK',
                                    '204' => 'PENGELUARAN SEMENTARA - PERBAIKAN/ REPARASI', '205' => 'PENGELUARAN SEMENTARA - PEMINJAMAN BARANG MODAL UNTUK KEPERLUAN PRODUKSI',
                                    '206' => 'PENGELUARAN SEMENTARA - PENGETESAN ATAU PENGEMBANGAN KUALITAS PRODUKSI', '207' => 'PENGELUARAN SEMENTARA - PENGGUNAAN KEMASAN YANG DIPAKAI BERULANG (RETURNABLE PACKAGE)',
                                    '208' => 'PENGELUARAN SEMENTARA - DIPAMERKAN', '209' => 'PENGELUARAN SEMENTARA - TUJUAN LAIN DENGAN PERSETUJUAN KEPALA KANTOR PABEAN',
                                    '210' => 'PENERIMAAN PEKERJAAN - SUBKONTRAK', '211' => 'PENERIMAAN PEKERJAAN - PERBAIKAN/ REPARASI',
                                    '212' => 'PENERIMAAN PEKERJAAN - PEKERJAAN LAIN', '213' => 'PEMUSNAHAN BARANG DI KAWASAN BERIKAT',
                                    // '215' => 'SURAT JALAN', '216' => 'SURAT JALAN',
                                    '217' => 'PACKING LIST', '246' => 'L/C', '261' => 'BC 2.6.1 - PEMBERITAHUAN PENGELUARAN BARANG DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
                                    '262' => 'BC 2.6.2 - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG YANG DI KELUARKAN DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN',
                                    '281' => 'PPK - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG ASAL PLB DARI LOKASI PENERIMA FASILITAS DI TEMPAT LAIN DALAM DAERAH PABEAN KE PLB',
                                    '282' => 'DOKAP PLB - PEMBERITAHUAN PENGELUARAN DENGAN DOKUMEN PELENGKAP', '302' => 'CN Ekspor', '315' => 'KONTRAK',
                                    '331' => 'P3BET - PEMBERITAHUAN PENGGABUNGAN DAN PEMECAHAN BARANG EKSPOR DAN TRANSHIPMENT', '343' => 'SHIPING ORDER',
                                    '380' => 'INVOICE', '630' => 'SURAT JALAN', '383' => 'SSTB', '388' => 'FAKTUR PAJAK', '410' => 'SURAT SANGGUP BAYAR / SSB',
                                    '430' => 'BANK GARANSI', '440' => 'SURAT TANDA BUKTI SETOR / STBS', '454' => 'SSPCP / SSBC', '455' => 'SURAT SETORAN PAJAK (SSP)',
                                    '456' => 'SKB', '457' => 'Surat Keterangan Bebas (SKB) PPh', '458' => 'SURAT KETERANGAN TIDAK DIPUNGUT (SKTD) PPN', '459' => 'Non SKB / SKTD',
                                    '500' => 'MOU PDE (Eksportir)', '511' => 'FTZ-01 PEMASUKAN DARI LUAR DAERAH PABEAN (IMPOR)',
                                    '512' => 'FTZ-01 PENGELUARAN KE LUAR DAERAH PABEAN (EKSPOR)', '513' => 'FTZ-01 PENGELUARAN KE TEMPAT LAIN DALAM DAERAH PABEAN',
                                    '521' => 'FTZ-02 PEMASUKAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT', '522' => 'FTZ-02 PENGELUARAN ANTAR FREE TRADE ZONE DAN KAWASAN BERIKAT',
                                    '531' => 'FTZ-03 PEMASUKAN DARI TEMPAT LAIN DALAM DAERAH PABEAN', '640' => 'DELIVERY ORDER', '666' => 'Pengecualian Dengan Surat Keputusan',
                                    '704' => 'MASTER B/L', '705' => 'B/L', '740' => 'AWB', '741' => 'MASTER AWB',
                                    '800' => 'SERTIFIKAT ALAT PERANGKAT TELEKOM/POSTEL', '803' => 'SATS LN / DEPHUT', '805' => 'REGISTRASI B3 / KLH', '808' => 'IJIN IMPOR / POLRI',
                                    '809' => 'SIE', '810' => 'SM/SPM', '811' => 'Sertifikat Legalitas Kayu (Dok.V-Legal)', '812' => 'Dok. Impor (PIB)',
                                    '813' => 'DOK. CUKAI (CK)', '814' => 'SKEP IJIN EKSPOR BERKALA', '815' => 'SKEP IJIN TATA NIAGA EKSPOR', '816' => 'DOK. EKSPOR (PEB)',
                                    '817' => 'Eksportir Terdaftar (ET) Depdag', '818' => 'Endorsement BRIK', '819' => 'Sertifikat Intan Kasar', '820' => 'Surat Persetujuan Ekspor (SPE)',
                                    '821' => 'Surat Tanda Registrasi UPPB', '822' => 'Srt Tanda Pendaftaran Pedagang Bokor SIR', '834' => 'SNI GULA KRISTAL MENTAH / DEPTAN',
                                    '835' => 'IZIN DAN/ATAU PENDAFT PESTISIDA / DEPTAN', '836' => 'IZIN IMPOR / DEPTAN', '842' => 'SNI / ESDM', '843' => 'NOMOR PELUMAS TERDAFTAR / ESDM',
                                    '844' => 'IJIN USAHA NIAGA/IU NIAGA TERBATAS/ESDM', '845' => 'REKOMENDASI IMPOR PELUMAS', '846' => 'SKEM', '851' => 'SURAT IJIN KARANTINA TANAMAN',
                                    '853' => 'SURAT IJIN KARANTINA HEWAN / IKAN', '854' => 'SURAT PERSETUJUAN MUAT BPOM', '856' => 'LAP. PEMERIKSAAN SURVEYOR (LPS-E)',
                                    '857' => 'FUMIGATION CERTIFICATE', '858' => 'CITES CERTIFICATE', '860' => 'Electronic Certificate Of Origin (E-CO)',
                                    '861' => 'CERTIFICATE OF ORIGIN (CO)', '862' => 'SKEP USDFS', '871' => 'Nomor Pendaftaran Alat Kesehatan/Depkes',
                                    '872' => 'LAPORAN SURVEYOR DEPKES', '873' => 'IP (NARKTK, PREKURSOR & PSIKOTR)/DEPKES', '874' => 'IT (PREKURSOR & PSIKOTR)/DEPKES',
                                    '875' => 'SPI (NARKTK, PREKURSOR & PSIKOTR)/DEPKES', '876' => 'Ijin Pembawaan UKA', '877' => 'Ijin Persetujuan Pembawaan UKA',
                                    '878' => 'Ijin Pelaporan Pembawaan UKA', '888' => 'PENGECUALIAN PERIJINAN', '902' => 'IJIN BAPETEN', '911' => 'SURAT KEPUTUSAN',
                                    '912' => 'SKEP FASILITAS BKPM', '913' => 'SKEP FASILITAS PERTAMBANGAN', '914' => 'KITE IKM', '915' => 'Skep Fasilitas Impor Sementara',
                                    '917' => 'BPBC / BPPAI', '918' => 'SK LABEL BAHASA INDONESIA', '919' => 'SK Bermotor', '920' => 'SKEP TPB',
                                    '936' => 'KH-9a/Izin Impor Karantina Hewan', '937' => 'KH-14/Izin Impor Karantina Hewan', '938' => 'KH-17/Izin Impor Karantina Hewan',
                                    '939' => 'KT-5/Izin Impor Karantina Pertanian', '940' => 'KT-9/Izin Impor Karantina Pertanian', '941' => 'KT-13/Izin Impor Karantina Pertanian',
                                    '942' => 'IZIN IMPOR KARANTINA TUMBUHAN', '943' => 'KH-5 / IZIN IMPOR KARANTINA HEWAN', '944' => 'KH-7 / IZIN IMPOR KARANTINA HEWAN',
                                    '945' => 'KH-12 / IZIN IMPOR KARANTINA HEWAN', '946' => 'KID-3 / IZIN IMPOR KARANTINA IKAN', '947' => 'KID-15 / IZIN IMPOR KARANTINA IKAN',
                                    '948' => 'NPIK', '949' => 'PENGAKUAN SBG IMPORTIR PRODUSEN', '950' => 'KID-4/IZIN KARANTINA IKAN', '951' => 'HC (HEALTH CERTIFICATE)',
                                    '956' => 'PENGAKUAN SBG IMPORTIR TERDAFTAR', '957' => 'SNI/SPB/DEPDAG', '958' => 'LAPORAN SURVEYOR / DEPDAG', '959' => 'SURAT PERSETUJUAN IMPOR DEP.DAG',
                                    '960' => '3D/PC dan/atau PFP', '961' => 'Hasil Lab', '993' => 'SURAT IJIN MENTERI PERTANIAN', '994' => 'BUKTI PENERIMAAN JAMINAN (BPJ)',
                                    '995' => 'STBS / SSP-E (PAJAK EKSPOR)', '996' => 'SRT SANGGUP BAYAR (SSB)', '997' => 'COSTOMS BOND / STTJ', '998' => 'SKEP FASILITAS KEMUDAHAN EKSPOR',
                                    '999' => 'LAINNYA', '03001' => 'Izin Prinsip Pendirian Kawasan Berikat Sebelum Fisik Bangunan Berdiri',
                                    '03002' => 'Keputusan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat',
                                    '03003' => 'Persetujuan Penetapan Tempat Sebagai Kawasan Berikat Dan Pemberian Izin Penyelenggara Kawasan Berikat Sekaligus Izin Pengusaha Kawasan Berikat',
                                    '03004' => 'Izin PDKB', '03005' => 'Perpanjangan Penetapan Tempat Sebagai Kawasan Berikat Dan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB Sebelum Jangka Waktu Izin Tersebut Berakhir',
                                    '03006' => 'Perubahan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB (Terdapat Perubahan Nama Perusahaan Yang Bukan Dikarenakan Merger Atau Diakuisisi, Jenis Hasil Produksi, Atau Luas Kawasan Berikat)',
                                    '03007' => 'Perubahan Keputusan Izin Penyelenggara Kawasan Berikat, Izin Pengusaha Kawasan Berikat, Atau Izin PDKB',
                                    '03008' => 'Pemberian Izin Penambahan Pintu Khusus Pemasukan Dan Pengeluaran Barang Di Kawasan Berikat', '03009' => 'Pemberian Izin Penambahan Pintu Khusus Orang Di Kawasan Berikat',
                                    '03010' => 'Persetujuan Pemasukan Barang Dari Kawasan Bebas Ke Kawasan Berikat', '03011' => 'Persetujuan Pemasukan Barang Modal Dari Luar Daerah Pabean',
                                    '03012' => 'Persetujuan Pemasukan Barang Modal Dari Kawasan Berikat Lain', '03013' => 'Persetujuan Pemasukan Barang Jadi Asal Luar Daerah Pabean Untuk Digabungkan Dengan Hasil Produksi Utama Kawasan Berikat',
                                    '03014' => 'Persetujuan Pemasukan Peralatan Perkantoran Asal Luar Daerah Pabean Ke Kawasan Berikat', '03015' => 'Persetujuan Pemasukan Barang Contoh Asal Luar Daerah Pabean',
                                    '03016' => 'Persetujuan Pembebasan Bea Masuk Untuk Barang Contoh Yang Akan Dikeluarkan Ke Tempat Lain Dalam Daerah Pabean', '03017' => 'Persetujuan Mengeluarkan Hasil Produksi Kawasan Berikat Ke Tempat Penyelenggaraan Pameran Berikat (TPPB)',
                                    '03018' => 'Persetujuan Untuk Mengeluarkan Bahan Baku Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Yang Sama Sekali Tidak Diproses Ke Gudang Berikat Asal Barang',
                                    '03019' => 'Persetujuan Untuk Mengeluarkan Barang Dan/Atau Bahan Rusak Dan/Atau Apkir (Reject) Asal Tlddp Ke TLDDP', '03020' => 'Persetujuan Pengeluaran Bahan Baku/Sisa Bahan Baku Asal Impor Untuk Direekspor',
                                    '03021' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Kawasan Berikat Lain',
                                    '03022' => 'Persetujuan Pengeluaran Bahan Baku Dan/Atau Sisa Bahan Baku Asal Luar Daerah Pabean Ke Perusahaan Industri Di TLDDP',
                                    '03023' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu Manajemen',
                                    '03024' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lain Dalam Satu PKB',
                                    '03025' => 'Persetujuan Pemindahtanganan Barang Selain Hasil Produksi Dalam Rangka Saling Melengkapi Kebutuhan Dalam Proses Produksi Atau Peningkatan Produksi Ke Kawasan Berikat Lainnya',
                                    '03026' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Dibayar BM-nya Untuk Direekspor',
                                    '03027' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM-nya Ke Kawasan Berikat Lain Setelah Jangka Waktu 2 (Dua) Tahun Sejak Diimpor Dan Telahdipergunakan Di Kawasan Berikat',
                                    '03028' => 'Persetujuan Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban BM Ke Tempat Lain Dalam Daerah Pabean Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                    '03029' => 'Keputusan Pembebasan BM Atas Pengeluaran Barang Modal Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                    '03030' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Lunas BM Untuk Direekspor', '03031' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke Kawasan Berikat Lain Setelah Dipergunakan Di Kawasan Berikat',
                                    '03032' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Sebelum Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat Yang Bersangkutan',
                                    '03033' => 'Persetujuan Pengeluaran Peralatan Perkantoran Asal Impor Yang Belum Diselesaikan Kewajiban Pembayaran Bm Ke TLDDP Setelah Jangka Waktu 4 (Empat) Tahun Sejak Diimpor, Dan Telah Dipergunakan Di Kawasan Berikat',
                                    '03034' => 'Persetujuan Untuk Memindahtangankan Barang Modal Dan/Atau Peralatan Perkantoran Yang Telah Dilunasi BM Dan PDRI Pada Saat Pemasukan Ke Kawasan Berikat',
                                    '03035' => 'Persetujuan Untuk Memindahtangankan Barang Modal Asal Tempat Lain Dalam Daerah Pabean', '03036' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke Luar Daerah Pabean',
                                    '03037' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke TLDDP', '03038' => 'Persetujuan Pengeluaran Barang Modal Untuk Perbaikan/Reparasi Ke KB Lain',
                                    '03039' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke TLDDP', '03040' => 'Persetujuan Subkontrak Kurang Dari 60 (Enam Puluh) Hari Ke KB Lain',
                                    '03041' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke TLDDP', '03042' => 'Persetujuan Subkontrak Lebih Dari 60 (Enam Puluh) Hari Ke PDKB Lain',
                                    '03043' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Dalam Rangka Subkontrak', '03044' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke KB Lain Bukan Dalam Rangka Subkontrak',
                                    '03045' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak', '03046' => 'Persetujuan Meminjamkan Mesin/Cetakan (Moulding) Ke TLDDP Bukan Dalam Rangka Subkontrak',
                                    '03047' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Dalam Rangka Subkontrak', '03048' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke PDKB Lain Bukan Dalam Rangka Subkontrak',
                                    '03049' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Dalam Rangka Subkontrak', '03050' => 'Persetujuan Perpanjangan Meminjamkan Mesin Dan/Atau Cetakan (Moulding) Ke TLDDP Selain Dalam Rangka Subkontrak',
                                    '03051' => 'Persetujuan Peminjaman Mesin Atau Cetakan (Moulding) Yang Melebihi Jangka Waktu', '03052' => 'Persetujuan Pemusnahan Atas Barangbarang Yang Busuk Dan/Atau Yang Karena Sifat Dan Bentuknya Dapat Dimusnahkan',
                                    '03053' => 'Persetujuan Perusakan Atas Barang Asal Luar Daerah Pabean Yang Karena Sifat Dan Bentuknya Tidak Dapat Dimusnahkan', '03054' => 'Persetujuan Menerima Subkontrak Dari TLDDP',
                                    '03055' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Dalam Rangka Subkontrak', '03056' => 'Persetujuan Peminjaman Mesin/Cetakan (Moulding) Dari TLDDP Bukan Dalam Rangka Subkontrak',
                                    '03057' => 'Persetujuan Peminjaman Mesin/Peralatan Pabrik Dari TLDDP', '03060' => 'Persetujuan Pemasukan Barang Modal Berupa Peralatan Pabrik Dari Luar Daerah Pabean',
                                    '03061' => 'Persetujuan Pemasukan Barang Modal Berupa Suku Cadang Dari Luar Daerah Pabean Yang Dimasukkan Tidak Bersamaan Dengan Barang Modal',
                                    '03062' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Hasil Produksi Asal TPB', '03063' => 'Persetujuan Pemasukan Kembali (Reimpor) Barang Modal Setelah Perbaikan/Reparasi Dari Luar Daerah Pabean',
                                    '03064' => 'Persetujuan Perpanjangan Jangka Waktu Pengeluaran Barang Modal Keperluan Perbaikan/Reparasi Tujuan TLDDP', '03065' => 'Persetujuan Pengeluaran Barang Contoh/Sampel KB Dengan Tujuan TLDDP',
                                    '03066' => 'Rekomendasi Meminjamkan Barang Modal Ke TLDDP Dalam Rangka Subkontrak Atau Bukan Lebih Dari 6 Bulan'
                                ];

                                $dokumens = [];
                                if (!empty($dataDetail['dok']) && count($dataDetail['dok']) > 0) {
                                    $dokumens = $dataDetail['dok'];
                                } else {
                                    $dokumens = [
                                        ['kode' => '380', 'nomor' => '', 'tgl' => ''],
                                        ['kode' => '705', 'nomor' => '', 'tgl' => '']
                                    ];
                                }
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

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-light fw-bold" style="font-size:13px;">Pengangkutan</div>
                        <div class="card-body">
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-0">Jenis Sarana Pengangkut</label>
                                <input type="text" name="pengangkut[nama]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nama'] ?? '' }}" placeholder="Jenis Sarana Pengangkut Kosong">
                            </div>
                            <div class="form-group mb-2">
                                <label class="small text-muted mb-0">Nomor Sarana Pengangkut</label>
                                <input type="text" name="pengangkut[nomor]" class="form-control form-control-sm" value="{{ $dataDetail['pengangkut']['nomor'] ?? $header->nomor_mobil ?? '' }}" placeholder="Nomor Sarana Pengangkut Kosong">
                            </div>
                            <!-- Hidden default value for required fields API -->
                            <input type="hidden" name="pengangkut[caraPengangkutan]" value="3">
                            <input type="hidden" name="pengangkut[kodeBendera]" value="ID">
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
                                @php
                                    $pungutanList = ['BM', 'BMAD', 'BMTP', 'BMI', 'BMP', 'CUKAI', 'PPN', 'PPNBM', 'PPH'];
                                @endphp
                                @foreach($pungutanList as $pung)
                                <tr>
                                    <td class="text-left font-weight-bold" style="color: #666; font-size: 12px; padding-left: 15px;">{{ $pung }}</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-{{ strtolower($pung) }}-ditangguhkan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-{{ strtolower($pung) }}-sudah-dilunasi">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-{{ strtolower($pung) }}-dibebaskan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 12px; color: #0000FF;" id="text-{{ strtolower($pung) }}-tidak-dipungut">Rp 0,00</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <td class="text-right font-weight-bold" style="color: #333; font-size: 13px; padding-right: 15px;">TOTAL</td>
                                    <td class="font-weight-bold" style="font-size: 13px; color: #cc0000;" id="text-total-ditangguhkan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 13px; color: #cc0000;" id="text-total-sudah-dilunasi">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 13px; color: #cc0000;" id="text-total-dibebaskan">Rp 0,00</td>
                                    <td class="font-weight-bold" style="font-size: 13px; color: #cc0000;" id="text-total-tidak-dipungut">Rp 0,00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Hidden inputs untuk format array/object CEISA -->
                    <div id="hidden-pungutan-container">
                        <!-- Digenerate via JS -->
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="$('#ceisaTab a[href=\'#tab-barang\']').tab('show');">Detail Barang</button>
                    </div>
                    <div class="row">
                        <!-- Harga -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Harga</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Jenis Valuta</label>
                                        <select name="kodeValuta" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Valuta</option>
                                            <option value="IDR" {{ ($dataDetail['kodeValuta'] ?? 'IDR') == 'IDR' ? 'selected' : '' }}>IDR - RUPIAH</option>
                                            <option value="USD" {{ ($dataDetail['kodeValuta'] ?? '') == 'USD' ? 'selected' : '' }}>USD - US DOLLAR</option>
                                            <option value="AUD" {{ ($dataDetail['kodeValuta'] ?? '') == 'AUD' ? 'selected' : '' }}>AUD - AUSTRALIAN DOLLAR</option>
                                            <option value="CNY" {{ ($dataDetail['kodeValuta'] ?? '') == 'CNY' ? 'selected' : '' }}>CNY - YUAN RENMINBI</option>
                                            <option value="EUR" {{ ($dataDetail['kodeValuta'] ?? '') == 'EUR' ? 'selected' : '' }}>EUR - EURO</option>
                                            <option value="GBP" {{ ($dataDetail['kodeValuta'] ?? '') == 'GBP' ? 'selected' : '' }}>GBP - POUND STERLING</option>
                                            <option value="JPY" {{ ($dataDetail['kodeValuta'] ?? '') == 'JPY' ? 'selected' : '' }}>JPY - JAPANESE YEN</option>
                                            <option value="MYR" {{ ($dataDetail['kodeValuta'] ?? '') == 'MYR' ? 'selected' : '' }}>MYR - MALAYSIAN RINGGIT</option>
                                            <option value="SGD" {{ ($dataDetail['kodeValuta'] ?? '') == 'SGD' ? 'selected' : '' }}>SGD - SINGAPORE DOLLAR</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">NDPBM</label>
                                        <input type="number" step="any" name="ndpbm" class="form-control form-control-sm" value="{{ $dataDetail['ndpbm'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nilai CIF</label>
                                        <input type="number" step="any" name="cif" class="form-control form-control-sm" value="{{ $dataDetail['cif'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nilai Pabean dalam Rupiah</label>
                                        <input type="number" step="any" name="nilaiPabean" class="form-control form-control-sm" value="{{ $dataDetail['nilaiPabean'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Harga Penyerahan/Harga Jual/Harga Barang</label>
                                        <input type="number" step="any" name="hargaPenyerahan" class="form-control form-control-sm" value="{{ $dataDetail['hargaPenyerahan'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">Nilai Barang</label>
                                        <input type="number" step="any" name="nilaiBarang" class="form-control form-control-sm" value="{{ $dataDetail['nilaiBarang'] ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Keperluan Pajak -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Data Untuk Keperluan Pajak</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nilai Jasa</label>
                                        <input type="number" step="any" name="nilaiJasa" class="form-control form-control-sm" value="{{ $dataDetail['nilaiJasa'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Nilai Uang Muka</label>
                                        <input type="number" step="any" name="nilaiUangMuka" class="form-control form-control-sm" value="{{ $dataDetail['nilaiUangMuka'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Diskon</label>
                                        <input type="number" step="any" name="diskon" class="form-control form-control-sm" value="{{ $dataDetail['diskon'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Dasar Pengenaan Pajak</label>
                                        <input type="number" step="any" name="dasarPengenaanPajak" class="form-control form-control-sm" value="{{ $dataDetail['dasarPengenaanPajak'] ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">PPN yang Dipungut (Tarif &amp; Nilai)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" name="tarifPPN" class="form-control" placeholder="%" value="{{ $dataDetail['tarifPPN'] ?? 0 }}" style="max-width:80px;">
                                            <div class="input-group-prepend input-group-append"><span class="input-group-text">%</span></div>
                                            <input type="number" step="any" name="nilaiPPN" class="form-control" placeholder="Nilai" value="{{ $dataDetail['nilaiPPN'] ?? 0 }}">
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small text-muted mb-0">PPnBM yang Dipungut (Tarif &amp; Nilai)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="any" name="tarifPPnBM" class="form-control" placeholder="%" value="{{ $dataDetail['tarifPPnBM'] ?? 0 }}" style="max-width:80px;">
                                            <div class="input-group-prepend input-group-append"><span class="input-group-text">%</span></div>
                                            <input type="number" step="any" name="nilaiPPnBM" class="form-control" placeholder="Nilai" value="{{ $dataDetail['nilaiPPnBM'] ?? 0 }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Berat -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3">
                                <div class="card-header bg-light fw-bold" style="font-size:13px;">Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Volume (M3)</label>
                                        <input type="number" step="any" id="totalVolume" name="volume" class="form-control form-control-sm" value="{{ $dataDetail['volume'] ?? $header->volume ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small text-muted mb-0">Berat Kotor (KDM)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? $header->berat_kotor ?? 0 }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="small text-muted mb-0">Berat Bersih (KDM)</label>
                                        <input type="number" step="any" id="totalNetto" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? $header->berat_bersih ?? 0 }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="section-title mt-0">Penandatangan</div>
                    <div class="row">
                        <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm" value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                        <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="section-title mt-0">Data Kemasan</div>
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
<script>
    $(document).ready(function() {

        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%', tags: true });


        $(document).on('change', '.bmt-toggle', function() {
            if($(this).is(':checked')) {
                $(this).closest('.mt-3').find('.bmt-container').slideDown();
            } else {
                $(this).closest('.mt-3').find('.bmt-container').slideUp();
            }
        });


        $('.bmt-container').each(function() {
            let hasData = false;
            $(this).find('input[type="number"]').each(function() {
                if($(this).val() && parseFloat($(this).val()) > 0) hasData = true;
            });
            if(hasData) {
                $(this).show();
                $(this).closest('.mt-3').find('.bmt-toggle').prop('checked', true);
            }
        });

        $(document).on('change', '.bmt-jenis-tarif', function() {
            let val = $(this).val();
            let container = $(this).closest('.bmt-row').find('.bmt-inputs-container');
            if (val === '1') {
                container.find('.bmt-advalorum-inputs').show();
                container.find('.bmt-spesifik-inputs').hide();
                container.find('.bmt-spesifik-inputs input').val('');
            } else {
                container.find('.bmt-advalorum-inputs').hide();
                container.find('.bmt-spesifik-inputs').show();
                container.find('.bmt-advalorum-inputs input').val('');
            }
        });


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


        function validasiBC27() {
            let errors = [];
            let firstTab = null;

            $('#form-edit-ceisa').find('input, select, textarea').each(function() {
                let el = $(this);

                if (el.is(':disabled') || el.is('[readonly]') || el.attr('type') === 'hidden' || el.attr('type') === 'button' || el.attr('type') === 'submit') {
                    return;
                }


                if (el.closest('.bmt-container').length) {
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

            if (!validasiBC27()) return;

            let invalidBarang = false;
            let invalidItemNames = [];


            $('input[name$="[cif]"], input[name$="[netto]"]').each(function() {
                let name = $(this).attr('name');
                if (name && name.indexOf('barang[') !== -1) {
                    let val = parseFloat($(this).val()) || 0;
                    if (val <= 0) {
                        invalidBarang = true;
                        let label = $(this).closest('.form-group').find('label').text() || name;
                        invalidItemNames.push(label);
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                }
            });

            if (invalidBarang) {
                $('#barang-tab').tab('show');
                Swal.fire({
                    title: 'Validasi Gagal!',
                    html: 'Terdapat item barang yang nilai <b>Harga CIF</b> atau <b>Berat Bersih (Netto)</b>-nya masih 0. <br><br>Harus lebih dari 0.',
                    icon: 'warning',
                    confirmButtonColor: '#003366'
                });
                return false;
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


        function calculateTotals() {
            let totalHarga = 0;
            let totalNetto = 0;
            let totalVolume = 0;

            let dataPungutan = {
                'BM':  { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPN': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPH': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'PPNBM': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMAD': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMTP': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMI': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'BMP': { '3': 0, '5': 0, '6': 0, '7': 0 },
                'CUKAI': { '3': 0, '5': 0, '6': 0, '7': 0 }
            };

            $('#accordionBarang .card').each(function() {
                let row = $(this);

                let valPabean = 0;
                let inputCifRupiah = row.find('input[name$="[cifRupiah]"]');
                let inputCif = row.find('input[name$="[cif]"]');
                if (inputCifRupiah.length > 0) {
                    valPabean = parseFloat(inputCifRupiah.val().replace(/,/g, '')) || 0;
                }
                if (valPabean === 0 && inputCif.length > 0) {
                    valPabean = parseFloat(inputCif.val().replace(/,/g, '')) || 0;
                }


                let inputNetto = row.find('input[name$="[netto]"]');
                if (inputNetto.length > 0) {
                    totalNetto += parseFloat(inputNetto.val().replace(/,/g, '')) || 0;
                }


                let inputVolume = row.find('input[name$="[volume]"]');
                if (inputVolume.length > 0) {
                    totalVolume += parseFloat(inputVolume.val().replace(/,/g, '')) || 0;
                }


                let totalBeaAmount = 0;


                row.find('input[name*="[kodeJenisPungutan]"]').each(function() {
                    let jenisInput = $(this);
                    let jenis = jenisInput.val();
                    if (['BM', 'BMAD', 'BMTP', 'BMI', 'BMP', 'CUKAI'].includes(jenis)) {
                        let container = jenisInput.closest('tr');
                        if (container.length === 0) container = jenisInput.closest('.bmt-row');
                        if (container.length === 0) return;

                        let tarif = parseFloat(container.find('input[name*="[tarif]"]').first().val()) || 0;
                        let fas = container.find('select[name*="[kodeFasilitasTarif]"]').val() || '3';
                        let amount = 0;


                        let jenisTarifSelect = container.find('select[name*="[kodeJenisTarif]"]');
                        if (jenisTarifSelect.length > 0 && jenisTarifSelect.val() === '2') {
                            let satuan = parseFloat(container.find('input[name*="[jumlahSatuan]"]').val()) || 0;
                            let tarifSpesifik = parseFloat(container.find('input[name*="[tarifSpesifik]"]').val()) || 0;
                            amount = satuan * tarifSpesifik;
                        } else {
                            amount = valPabean * (tarif / 100);
                        }

                        if (dataPungutan[jenis] && dataPungutan[jenis][fas] !== undefined) {
                            dataPungutan[jenis][fas] += amount;
                        }
                        totalBeaAmount += amount;
                    }
                });


                row.find('input[name*="[kodeJenisPungutan]"]').each(function() {
                    let jenisInput = $(this);
                    let jenis = jenisInput.val();
                    if (['PPN', 'PPNBM', 'PPH'].includes(jenis)) {
                        let container = jenisInput.closest('tr');
                        if (container.length === 0) return;
                        let tarif = parseFloat(container.find('input[name*="[tarif]"]').val()) || 0;
                        let fas = container.find('select[name*="[kodeFasilitasTarif]"]').val() || '3';
                        let amount = (valPabean + totalBeaAmount) * (tarif / 100);

                        if (dataPungutan[jenis] && dataPungutan[jenis][fas] !== undefined) {
                            dataPungutan[jenis][fas] += amount;
                        }
                    }
                });
            });

            let formatDecimal = function(num) {
                if (num % 1 === 0) return num.toString() + '.0000';
                return num.toFixed(4);
            };

            let formatIdr = function(num) {
                return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
            };

            $('#totalNetto').val(formatDecimal(totalNetto));
            $('#totalVolume').val(formatDecimal(totalVolume));


            let inputBruto = $('input[name="bruto"]');
            let currentBruto = parseFloat(inputBruto.val().replace(/,/g, '')) || 0;
            if (currentBruto < totalNetto) {
                inputBruto.val(formatDecimal(totalNetto));
            }


            let hiddenContainer = $('#hidden-pungutan-container');
            hiddenContainer.empty();
            let arrayIndex = 0;

            let total3 = 0, total5 = 0, total6 = 0, total7 = 0;

            ['BM', 'BMAD', 'BMTP', 'BMI', 'BMP', 'CUKAI', 'PPN', 'PPNBM', 'PPH'].forEach(function(jenis) {
                let idPrefix = '#text-' + jenis.toLowerCase() + '-';
                if ($(idPrefix + 'ditangguhkan').length) {
                    $(idPrefix + 'ditangguhkan').text(formatIdr(dataPungutan[jenis]['3']));
                    $(idPrefix + 'dibebaskan').text(formatIdr(dataPungutan[jenis]['5']));
                    $(idPrefix + 'tidak-dipungut').text(formatIdr(dataPungutan[jenis]['6']));
                    $(idPrefix + 'sudah-dilunasi').text(formatIdr(dataPungutan[jenis]['7']));
                }

                total3 += dataPungutan[jenis]['3'] || 0;
                total5 += dataPungutan[jenis]['5'] || 0;
                total6 += dataPungutan[jenis]['6'] || 0;
                total7 += dataPungutan[jenis]['7'] || 0;

                for (let fas in dataPungutan[jenis]) {
                    if (dataPungutan[jenis][fas] > 0) {
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeFasilitasTarif]" value="${fas}">`);
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][kodeJenisPungutan]" value="${jenis}">`);
                        hiddenContainer.append(`<input type="hidden" name="pungutan[${arrayIndex}][nilaiPungutan]" value="${formatDecimal(dataPungutan[jenis][fas])}">`);
                        arrayIndex++;
                    }
                }
            });

            $('#text-total-ditangguhkan').text(formatIdr(total3));
            $('#text-total-dibebaskan').text(formatIdr(total5));
            $('#text-total-tidak-dipungut').text(formatIdr(total6));
            $('#text-total-sudah-dilunasi').text(formatIdr(total7));
        }

        $(document).on('input change',
            'input[name$="[cif]"], input[name$="[cifRupiah]"], input[name$="[netto]"], input[name$="[volume]"], ' +
            'select[name*="[kodeFasilitasTarif]"], select[name*="[kodeJenisTarif]"], ' +
            'input[name*="[barangTarif]"][name$="[tarif]"], input[name*="[barangTarif]"][name$="[jumlahSatuan]"], input[name*="[barangTarif]"][name$="[tarifSpesifik]"]',
            function() {
                calculateTotals();
            }
        );


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
