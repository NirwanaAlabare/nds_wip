@extends('layouts.index')

@section('custom-link')
<link rel="stylesheet" href="{{ asset('plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
<style>
    .nav-tabs { border-bottom: none; }
    .nav-tabs .nav-item { margin-bottom: 0; margin-right: 5px; }
    .nav-tabs .nav-link { border: 1px solid #ddd; border-radius: 4px; padding: 8px 15px; font-size: 13px; transition: all 0.3s ease; }
    .nav-tabs .nav-link.active { font-weight: bold; background-color: #003366 !important; color: #ffffff !important; border-color: #003366 !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .nav-tabs .nav-link.active::after { display: none; }
    .nav-tabs .nav-link:not(.active) { color: #000000 !important; background-color: #ffffff; }
    .nav-tabs .nav-link:not(.active):hover { background-color: #f8f9fa; border-color: #ddd; color: #000000 !important; }
    .form-group label { font-size: 13px; font-weight: 600; margin-bottom: 0.2rem; }
    .form-control-sm { font-size: 13px; }
    .section-title { font-size: 14px; font-weight: bold; color: #333; border-bottom: 2px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; margin-top: 20px; }
</style>
@endsection

@section('content')
@php
    $listJenisKemasan = ['1A' => 'DRUM, STEEL', '1B' => 'DRUM, ALUMINIUM', '1D' => 'DRUM, PLYWOOD', '1F' => 'CONTAINER, FLEXIBLE', '1G' => 'DRUM, FIBRE', '1W' => 'DRUM, WOODEN', '2C' => 'BARREL, WOODEN', '3A' => 'JERRICAN, STEEL', '3H' => 'JERRICAN, PLASTIC', '43' => 'Bag, super bulk', '44' => 'Bag, polybag', '4A' => 'Box, steel', '4B' => 'Box, aluminium', '4C' => 'Box, natural wood', '4D' => 'Box, plywood', '4F' => 'Box, reconstituted wood', '4G' => 'Box, fibreboard', '4H' => 'Box, plastic', '5H' => 'Bag, woven plastic', '5L' => 'Bag, textile', '5M' => 'Bag, paper', '6H' => 'Composite packaging, plastic receptacle', '6P' => 'Composite packaging, glass receptacle', '7A' => 'Case, car', '7B' => 'Case, wooden', '8A' => 'Pallet, wooden', '8B' => 'Crate, wooden', '8C' => 'Bundle, wooden', 'AA' => 'Intermediate bulk container, rigid plastic', 'AB' => 'Receptacle, fibre', 'AC' => 'Receptacle, paper', 'AD' => 'Receptacle, wooden', 'AE' => 'Aerosol', 'AF' => 'Pallet, modular, collars 80cms * 60cms', 'AG' => 'Pallet, shrinkwrapped', 'AH' => 'Pallet, 100cms * 110cms', 'AI' => 'Clamshell', 'AJ' => 'Cone', 'AL' => 'Ball', 'AM' => 'Ampoule, non protected', 'AP' => 'Ampoule, protected', 'AT' => 'Atomizer', 'AV' => 'Capsule', 'B4' => 'Belt', 'BA' => 'Barrel', 'BB' => 'Bobbin', 'BC' => 'Bottlecrate, bottlerack', 'BD' => 'Board', 'BE' => 'Bundle', 'BF' => 'Balloon, non-protected', 'BG' => 'Bag', 'BH' => 'Bunch', 'BI' => 'Bin', 'BJ' => 'Bucket', 'BK' => 'Basket', 'BL' => 'Bale, compressed', 'BM' => 'Basin', 'BN' => 'Bale, non -compressed', 'BO' => 'Bottle, non-protected, cylindrical', 'BP' => 'Balloon, protected', 'BQ' => 'Bottle, protected cylindrical', 'BR' => 'Bar', 'BS' => 'Bottle, non-protected, bulbous', 'BT' => 'Bolt', 'BU' => 'Butt', 'BV' => 'Bottle, protected bulbous', 'BW' => 'Box, for liquids', 'BX' => 'Box', 'BY' => 'Board, in bundle/bunch/truss', 'BZ' => 'Bars, in bundle/bunch/truss', 'CA' => 'Can, rectangular', 'CB' => 'Beer crate', 'CC' => 'Churn', 'CD' => 'Can, with handle and spout', 'CE' => 'Creel', 'CF' => 'Coffer', 'CG' => 'Cage', 'CH' => 'Chest', 'CI' => 'Canister', 'CJ' => 'Coffin', 'CK' => 'Cask', 'CL' => 'Coil', 'CM' => 'Card', 'CN' => 'Cont,not otherwise specfied as transport equipment', 'CO' => 'Carboy, non-protected', 'CP' => 'Carboy, protected', 'CQ' => 'Cartridge', 'CR' => 'Crate', 'CS' => 'Case', 'CT' => 'Carton', 'CU' => 'Cup', 'CV' => 'Cover', 'CW' => 'Cage, roll', 'CX' => 'Can, cylindical', 'CY' => 'Cylinder', 'CZ' => 'Canvas', 'DA' => 'Crate, multiple layer, plastic', 'DB' => 'Crate, multiple layer, wooden', 'DC' => 'Crate, multiple layer, cardboard', 'DG' => 'Cage, Commonwealth Handling Equipment Pool (CHEP)', 'DH' => 'Box,Commnwealth Hndling Equipmnt Pool/CHEP,Eurobox', 'DI' => 'Drum, iron', 'DJ' => 'Demijohn, non-protected', 'DK' => 'Crate, bulk, cardboard', 'DL' => 'Crate, bulk, plastic', 'DM' => 'Crate, bulk, wooden', 'DN' => 'Dispenser', 'DP' => 'Demijohn, protected', 'DR' => 'Drum', 'DS' => 'Tray, one layer no cover, plastic', 'DT' => 'Tray, one layer no cover, wooden', 'DU' => 'Tray, one layer no cover, polystyrene', 'DV' => 'Tray, one layer no cover, cardboard', 'DW' => 'Tray, two layers no cover, plastic tray', 'DX' => 'Tray, two layers no cover, wooden', 'DY' => 'Tray, two layers no cover, cardboard', 'EC' => 'Bag, plastic', 'ED' => 'Case, with pallet base', 'EE' => 'Case, with pallet base, wooden', 'EF' => 'Case, with pallet base, cardboard', 'EG' => 'Case, with pallet base, plastic', 'EH' => 'Case, with pallet base, metal', 'EI' => 'Case, isothermic', 'EN' => 'Envelope', 'FB' => 'Flexibag', 'FC' => 'Fruit crate', 'FD' => 'Framed crate', 'FE' => 'Flexitank', 'FI' => 'Firkin', 'FL' => 'Flask', 'FO' => 'Footlocker', 'FP' => 'Filmpack', 'FR' => 'Frame', 'FT' => 'Foodtainer', 'FW' => 'Cart, flatbed', 'FX' => 'Bag, flexible container', 'GB' => 'Gas bottle', 'GI' => 'Girder', 'GL' => 'Container, gallon', 'GR' => 'Receptacle, glass', 'GU' => 'Tray, containing horizontally stacked flat items', 'GY' => 'Bag, gunny', 'GZ' => 'Girders, in bundle/bunch/truss', 'HA' => 'Basket, with handle, plastic', 'HB' => 'Basket, with handle, wooden', 'HC' => 'Basket, with handle, cardboard', 'HG' => 'Hogshead', 'HN' => 'Hanger', 'HR' => 'Hamper', 'HZ' => 'bukan kaleng kaleng', 'IA' => 'Package, display, wooden', 'IB' => 'Package, display, cardboard', 'IC' => 'Package, display, plastic', 'ID' => 'Package, display, metal', 'IE' => 'Package, show', 'IF' => 'Package, flow', 'IG' => 'Package, paper wrapped', 'IH' => 'Drum, plastic', 'IK' => 'Package, cardboard, with bottle grip-holes', 'IL' => 'Tray, rigid, lidded stackable (CEN TS 14482:2002)', 'IN' => 'Ingot', 'IZ' => 'ingots, in bundle/bunch/truss', 'JB' => 'Bag, jumbo', 'JC' => 'Jerrican, rectangular', 'JG' => 'Jug', 'JR' => 'Jar', 'JT' => 'Jutebag', 'JY' => 'Jerrican, cylindrical', 'KG' => 'Keg', 'KI' => 'Kit', 'KR' => 'karung', 'LE' => 'Luggage', 'LG' => 'Log', 'LT' => 'Lot', 'LU' => 'Lug', 'LV' => 'Liftvan', 'LZ' => 'Logs, in bundle/bunch/truss', 'MA' => 'Crate, metal', 'MB' => 'Multiply bag', 'MC' => 'milk crate', 'ME' => 'Container, metal', 'MR' => 'Receptacle, metal', 'MS' => 'Multiwall sack', 'MT' => 'Mat', 'MW' => 'Receptacle, plastic wrapped', 'MX' => 'Macontoh box', 'NA' => 'Not available', 'NE' => 'Unpacked or unpackaged', 'NF' => 'Unpacked or unpackaged, single unit', 'NG' => 'Unpacked or unpackaged, multiple units', 'NS' => 'Nest', 'NT' => 'Net', 'NU' => 'Net, tube, plastic', 'NV' => 'Net, tube, textile', 'OA' => 'Pallet, CHEP 40 cm x 60 cm', 'OB' => 'Pallet, CHEP 80 cm x 120 cm', 'OC' => 'Pallet, CHEP 100 cm x 120 cm', 'OD' => 'Pallet, AS 4068-1993', 'OE' => 'Pallet, ISO T11', 'OF' => 'Platform, unspecified weight or dimension', 'OK' => 'Block', 'OT' => 'Octabin', 'OU' => 'Container, outer', 'P2' => 'Pan', 'PA' => 'Packet', 'PB' => 'Pallet, box Combined open-ended box and pallet', 'PC' => 'Parcel', 'PD' => 'Pallet, modular, collars 80cms * 100cms', 'PE' => 'Pallet, modular, collars 80cms * 120cms', 'PF' => 'Pen', 'PG' => 'Plate', 'PH' => 'Pitcher', 'PI' => 'Pipe', 'PJ' => 'Punnet', 'PK' => 'Package', 'PL' => 'Pail', 'PN' => 'Plank', 'PO' => 'Pouch', 'PP' => 'Piece', 'PR' => 'Receptacle, plastic', 'PT' => 'Pot', 'PU' => 'Tray', 'PV' => 'Pipes, in bundle/bunch/truss', 'PX' => 'Pallet', 'PY' => 'Plates, in bundle/bunch/truss', 'PZ' => 'Pipes, in bundle/bunch/truss', 'QA' => 'Drum, steel, non-removable head', 'QB' => 'Drum, steel, removable head', 'QC' => 'Drum, aluminium, non-removable head', 'QD' => 'Drum, aluminium, removable head', 'QF' => 'Drum, plastic, non-removable head', 'QG' => 'Drum, plastic, removable head', 'QH' => 'Barrel, wooden, bung type', 'QJ' => 'Barrel, wooden, removable head', 'QK' => 'Jerrican, steel, non-removable head', 'QL' => 'Jerrican, steel, removable head', 'QM' => 'Jerrican, plastic, non-removable head', 'QN' => 'Jerrican, plastic, removable head', 'QP' => 'Box, wooden, natural wood, ordinary', 'QQ' => 'Box, wooden, natural wood, with sift proof walls', 'QR' => 'Box, plastic, expanded', 'QS' => 'Box, plastic, solid', 'RD' => 'Rod', 'RG' => 'Ring', 'RJ' => 'Rack, clothing hanger', 'RK' => 'Rack', 'RL' => 'Reel', 'RO' => 'Roll', 'RT' => 'Rednet', 'RZ' => 'Rods, in bundle/ bunch/truss', 'SA' => 'Sack', 'SB' => 'Slab', 'SC' => 'Shallow crate', 'SD' => 'Spindle', 'SE' => 'Sea-chest', 'SH' => 'Sachet', 'SI' => 'Skid', 'SK' => 'Skeleton case', 'SL' => 'Slipsheet', 'SM' => 'Sheetmetal', 'SO' => 'Spool', 'SP' => 'Sheet, plastic wrapping', 'SS' => 'Case, steel', 'ST' => 'Sheet', 'SU' => 'Suitcase', 'SV' => 'Envelope, steel', 'SW' => 'Shrinkwrapped', 'SX' => 'Set', 'SY' => 'Sleeve', 'SZ' => 'Sheets, in bundle/bunch/truss', 'T1' => 'Tablet', 'TB' => 'Tub', 'TC' => 'Tea-chest', 'TD' => 'Collapsible tube', 'TE' => 'Tyre', 'TG' => 'Tank container, generic', 'TI' => 'Tierce', 'TK' => 'Tank, rectangular', 'TL' => 'Tub, with lid', 'TN' => 'Tin', 'TO' => 'Tun', 'TP' => 'Tray', 'TR' => 'Trunk', 'TS' => 'Truss', 'TT' => 'Bag, tote', 'TU' => 'Tube', 'TV' => 'Tube, with nozzle', 'TW' => 'Pallet, triwall', 'TY' => 'Tank, cylindrical', 'TZ' => 'Tubes, in bundle/bunch/truss', 'UC' => 'Uncaged', 'UN' => 'Unpackage', 'VA' => 'Vat', 'VG' => 'Bulk, gas ( at 1031 mbar and 15C )', 'VI' => 'Vial', 'VK' => 'Vanpack', 'VL' => 'Bulk, liquid', 'VN' => 'Vehicle', 'VO' => 'Bulk, solid, large particles ("nodules")', 'VP' => 'Vacuumpacked', 'VQ' => 'Bulk,liquefied gas (at abnorml temprture/pressure)', 'VR' => 'Bulk, solid, granular particles ("grains")', 'VS' => 'Bulk, scrap metal', 'VY' => 'Bulk, solid, fine particles ("powders")', 'WA' => 'Intermediate bulk container', 'WB' => 'Wickerbottle', 'WC' => 'Intermediate bulk container, steel', 'WD' => 'Intermediate bulk container, aluminium', 'WF' => 'Intermediate bulk container, metal', 'WG' => 'Intermediate bulk cont,steel,pressurised >10 kpa', 'WH' => 'Intermedt bulk cont,aluminium,pressurised >10 kpa', 'WJ' => 'Intermediate bulk container,metal, pressure 10 kpa', 'WK' => 'Intermediate bulk container, steel, liquid', 'WL' => 'Intermediate bulk container, aluminium, liquid', 'WM' => 'Intermediate bulk container, metal, liquid', 'WN' => 'Intermd bulk cont,woven plastic,without coat/liner', 'WP' => 'Intermediate bulk container, woven plastic, coated', 'WQ' => 'Intermd bulk cont,woven plastic,with liner', 'WR' => 'Intermedt bulk cont,woven plastic,coated and liner', 'WS' => 'Intermediate bulk container, plastic film', 'WT' => 'Intermd bulk cont,textile with out coat/liner', 'WU' => 'Intermdte bulk cont,natural wood,with inner liner', 'WV' => 'Intermediate bulk container, textile, coated', 'WW' => 'Intermediate bulk container, textile, with liner', 'WX' => 'Intermediate bulk cont,textile,coated and liner', 'WY' => 'Intermd bulk cont,plywood,with inner liner', 'WZ' => 'Intermd bulk cont,reconstttd wood,with inner liner', 'XA' => 'Bag, woven plastic, without inner coat/liner', 'XB' => 'Bag, woven plastic, sift proof', 'XC' => 'Bag, woven plastic, water resistant', 'XD' => 'Bag, plastics film', 'XF' => 'Bag, textile, without inner coat/liner', 'XG' => 'Bag, textile, sift proof', 'XH' => 'Bag, textile, water resistant', 'XJ' => 'Bag, paper, multi-wall', 'XK' => 'Bag, paper, multi-wall, water resistant', 'XN' => 'test', 'YA' => 'Compsite packging,plastic receptacle in steel drum', 'YB' => 'Compste packgng,plastc recptcle in steel crate box', 'YC' => 'Compste packgng,plastic recptcle in aluminium drum', 'YD' => 'Compste packgng,plastic recptcle in alumnium crate', 'YF' => 'Compsite packging,plastic receptacle in wooden box', 'YG' => 'Compste packgng,plastic receptacle in plywood drum', 'YH' => 'Compste packging,plastic receptacle in plywood box', 'YJ' => 'Compsite packging,plastic receptacle in fibre drum', 'YK' => 'Compste packgng,plastic recptcle in fibreboard box', 'YL' => 'Compste packgng,plastic receptacle in plastic drum', 'YM' => 'Compsite packgng,plstc recptcle in solid plstc box', 'YN' => 'Composite packaging,glass receptacle in steel drum', 'YP' => 'Compste packgng,glass recptacle in steel crate box', 'YQ' => 'Compste packgng,glass receptacle in aluminium drum', 'YR' => 'Compste packgng,glass recptacle in aluminium crate', 'YS' => 'Composite packaging,glass receptacle in wooden box', 'YT' => 'Compsite packging,glass receptacle in plywood drum', 'YV' => 'Compste packgng,glass recptcle in wickrwork hamper', 'YW' => 'Composite packaging,glass receptacle in fibre drum', 'YX' => 'Compste packgng,glass receptacle in fibreboard box', 'YY' => 'Compste pckgng,glss recptcl in expndbl plastc pack', 'YZ' => 'Compsite packgng,glass recptcle in solid plstc pck', 'ZA' => 'Intermediate bulk container, paper, multi-wall', 'ZB' => 'Bag, large', 'ZC' => 'Intermd bulk cont,paper,multi-wall,water resistant', 'ZD' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,solid', 'ZF' => 'Intermd bulk cont,rgid plstc,freestandng,solds', 'ZG' => 'Intermdbulk cnt,rgd plstc,w/strctrl equipm,pressrd', 'ZH' => 'Intermd bulk cont,rgd plstc,freestnd,pressurised', 'ZJ' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,lquid', 'ZK' => 'Intermd bulk cont,rigid plstc,freestanding,liquids', 'ZL' => 'Intermd bulk cont,composite,rigid plastic,solids', 'ZM' => 'Intermd bulk cont,compste,flexbl plastic, solids', 'ZN' => 'Intermd bulk cont,compsit,rgid plstc,pressurised', 'ZP' => 'Intermd bulk cont,compsit,flexbl plstc,pressurised', 'ZQ' => 'Intermd bulk cont,composite,rigid plastic,liquids', 'ZR' => 'Intermd bulk cont,compsite,flexible plastc,liquids', 'ZS' => 'Intermediate bulk container, composite', 'ZT' => 'Intermediate bulk container, fibreboard', 'ZU' => 'Intermediate bulk container, flexible', 'ZV' => 'Intermediate bulk container,metal,other than steel', 'ZW' => 'Intermediate bulk container, natural wood', 'ZX' => 'Intermediate bulk container, plywood', 'ZY' => 'Intermediate bulk container, reconstituted wood', 'ZZ' => 'Mutually defined'];

    $listSatuanBarang = [
        '6' => 'small spray',
        '8' => 'heat lot',
        '10' => 'group',
        '13' => 'ration',
        '14' => 'shot',
        '15' => 'stick, military',
        '16' => 'hundred fifteen kg drum',
        '17' => 'hundred lb drum',
        '18' => 'fiftyfive gallon (US) drum',
        '19' => 'tank truck',
        '20' => 'twenty foot container',
        '21' => 'forty foot container',
        '22' => 'decilitre per gram',
        '24' => 'theoretical pound',
        '26' => 'actual ton',
        '28' => 'kilogram per square metre',
        '29' => 'pound per thousand square foot',
        '30' => 'horse power day per air dry metric ton',
        '31' => 'catch weight',
        '32' => 'kilogram per air dry metric ton',
        '33' => 'kilopascal square metre per gram',
        '34' => 'kilopascal per millimetre',
        '35' => 'millilitre per square centimetre second',
        '36' => 'cubic foot per minute per square foot',
        '38' => 'ounce per square foot per 0,01inch',
        '40' => 'millilitre per second',
        '43' => 'super bulk bag',
        '44' => 'fivehundred kg bulk bag',
        '46' => 'fifty lb bulk bag',
        '47' => 'fifty lb bag',
        '48' => 'bulk car load',
        '53' => 'theoretical kilogram',
        '54' => 'theoretical tonne',
        '57' => 'mesh',
        '58' => 'net kilogram',
        '60' => 'percent weight',
        '61' => 'part per billion (US)',
        '62' => 'percent per 1000 hour',
        '63' => 'failure rate in time',
        '64' => 'pound per square inch, gauge',
        '66' => 'oersted',
        '71' => 'volt ampere per pound',
        '72' => 'watt per pound',
        '73' => 'ampere tum per centimetre',
        '78' => 'kilogauss',
        '84' => 'kilopound-force per square inch',
        '85' => 'foot pound-force',
        '89' => 'poise',
        '92' => 'calorie per cubic centimetre',
        '93' => 'calorie per gram',
        '94' => 'curl unit',
        '96' => 'ten thousand gallon (US) tankcar',
        '97' => 'ten kg drum',
        '98' => 'fifteen kg drum',
        '1C' => 'locomotive count',
        '1F' => 'train mile',
        '1I' => 'fixed rate',
        '1L' => 'total car count',
        '1M' => 'total car mile',
        '1X' => 'quarter mile',
        '2B' => 'radian per second squared',
        '2C' => 'roentgen',
        '2H' => 'volt DC',
        '2I' => 'British thermal unit(international table) per hour',
        '2J' => 'cubic centimetre per second',
        '2K' => 'cubic foot per hour',
        '2L' => 'cubic foot per minute',
        '2M' => 'centimetre per second',
        '2P' => 'kilobyte',
        '2Q' => 'kilobecquerel',
        '2R' => 'kilocurie',
        '2U' => 'megagram',
        '2V' => 'megagram per hour',
        '2W' => 'bin',
        '2X' => 'metre per minute',
        '2Z' => 'millivolt',
        '3E' => 'pound per pound of product',
        '3G' => 'pound per piece of product',
        '4A' => 'bobbin',
        '4G' => 'microlitre',
        '4H' => 'micrometre (micron)',
        '4L' => 'megabyte',
        '4M' => 'milligram per hour',
        '4O' => 'microfarad',
        '4P' => 'newton per metre',
        '4R' => 'ounce foot',
        '4T' => 'picofarad',
        '4U' => 'pound per hour',
        '4W' => 'ton (US) per hour',
        '5B' => 'batch',
        '5C' => 'gallon(US) per thousand',
        '5E' => 'MMSCF/day',
        '5G' => 'pump',
        '5H' => 'stage',
        '5K' => 'count per minute',
        '5P' => 'seismic level',
        '5Q' => 'seismic line',
        'A1' => '15 C calorie',
        'A11' => 'angstrom',
        'A12' => 'astronomical unit',
        'A13' => 'attojoule',
        'A15' => 'barn per electronvolt',
        'A2' => 'ampere per centimetre',
        'A20' => 'British thermal unit/second squarefoot d/Rankine',
        'A21' => 'British thermal unit (IT) per pound degree Rankine',
        'A23' => 'Britishthermalunit/hour square foot degree Rankine',
        'A24' => 'candela per square metre',
        'A26' => 'coulomb metre',
        'A27' => 'coulomb metre squared per volt',
        'A28' => 'coulomb per cubic centimetre',
        'A3' => 'ampere per millimetre',
        'A32' => 'coulomb per mole',
        'A33' => 'coulomb per square centimetre',
        'A36' => 'cubic centimetre per mole',
        'A38' => 'cubic metre per coulomb',
        'A4' => 'ampere per square centimetre',
        'A41' => 'ampere per square metre',
        'A43' => 'deadweight tonnage',
        'A44' => 'decalitre',
        'A45' => 'decametre',
        'A47' => 'decitex',
        'A49' => 'denier',
        'A51' => 'dyne second per centimetre',
        'A52' => 'dyne second per centimetre to the fifth power',
        'A56' => 'electronvolt square metre per kilogram',
        'A58' => 'erg per centimetre',
        'A59' => '8-part cloud cover',
        'A6' => 'ampere per square metre kelvin squared',
        'A60' => 'erg per cubic centimetre',
        'A62' => 'erg per gram second',
        'A64' => 'erg per second square centimetre',
        'A65' => 'erg per square centimetre second',
        'A66' => 'erg square centimetre',
        'A67' => 'erg square centimetre per gram',
        'A69' => 'farad per metre',
        'A7' => 'ampere per square millimetre',
        'A70' => 'femtojoule',
        'A71' => 'femtometre',
        'A73' => 'foot per second squared',
        'A74' => 'foot pound-force per second',
        'A76' => 'gal',
        'A77' => 'Gaussian CGS unit of displacement',
        'A78' => 'Gaussian CGS unit of electric current',
        'A79' => 'Gaussian CGS unit of electric charge',
        'A81' => 'Gaussian CGS unit of electric polarization',
        'A82' => 'Gaussian CGS unit of electric potential',
        'A83' => 'Gaussian CGS unit of magnetization',
        'A85' => 'gigaelectronvolt',
        'A87' => 'gigaohm',
        'A88' => 'gigaohm metre',
        'A9' => 'rate',
        'A93' => 'gram per cubic metre',
        'A95' => 'gray',
        'A96' => 'gray per second',
        'AA' => 'ball',
        'AB' => 'bulk pack',
        'ACR' => 'Acre (4840 yd2)',
        'ACT' => 'activity',
        'AD' => 'byte',
        'AED' => 'United Arab Emirates Dirham',
        'AFN' => 'Afghanistan Afghani',
        'AH' => 'additional minute',
        'AJ' => 'cop',
        'AK' => 'fathom',
        'AL' => 'access line',
        'AMH' => 'Ampere-hour (3,6 kC)',
        'ANG' => 'Netherlands Antilles Guilder',
        'APZ' => 'Ounce GB,US (31,10348 g)',
        'AQ' => 'anti-hemophilic factor (AHF) unit',
        'ARE' => 'Are (100m2)',
        'AS' => 'assortment',
        'ASM' => 'alcoholic strength by mass',
        'ASU' => 'alcoholic strength by volume',
        'ATT' => 'Technical atmosphere (98066,5 Pa)',
        'AUD' => 'Australia Dollar',
        'AV' => 'capsule',
        'AW' => 'powder filled vial',
        'AY' => 'assembly',
        'AZN' => 'Azerbaijan Manat',
        'B0' => 'Btu per cubic foot',
        'B10' => 'bit per second',
        'B12' => 'joule per metre',
        'B14' => 'joule per metre to the fourth power',
        'B15' => 'joule per mole',
        'B17' => 'credit',
        'B19' => 'digit',
        'B20' => 'joule square metre per kilogram',
        'B21' => 'kelvin per watt',
        'B22' => 'kiloampere',
        'B26' => 'kilocoulomb',
        'B27' => 'kilocoulomb per cubic metre',
        'B29' => 'kiloelectronvolt',
        'B34' => 'kilograme per cubic decimetre',
        'B35' => 'kilogram per litre',
        'B38' => 'kilogram-force metre',
        'B39' => 'kilogram-force metre per second',
        'B4' => 'barrel, imperial',
        'B41' => 'kilojoule per kelvin',
        'B43' => 'kilojoule per kilogram kelvin',
        'B45' => 'kilomole',
        'B46' => 'kilomole per cubic metre',
        'B49' => 'kiloohm',
        'B5' => 'billet',
        'B50' => 'kiloohm metre',
        'B51' => 'kilopond',
        'B54' => 'kilosiemens per metre',
        'B55' => 'kilovolt per metre',
        'B56' => 'kiloweber per metre',
        'B59' => 'lumen hour',
        'B6' => 'bun',
        'B60' => 'lumen per square metre',
        'B64' => 'lux second',
        'B65' => 'maxwell',
        'B66' => 'megaampere per square metre',
        'B67' => 'megabecquerel per kilogram',
        'B68' => 'gigabit',
        'B71' => 'megaelectronvolt',
        'B74' => 'meganewton metre',
        'B78' => 'megavolt',
        'B79' => 'megavolt per metre',
        'B81' => 'reciprocal metre squared reciprocal second',
        'B85' => 'microbar',
        'B86' => 'microcoulomb',
        'B87' => 'microcoulomb per cubic metre',
        'B88' => 'microcoulomb per square metre',
        'B89' => 'microfarad per metre',
        'B9' => 'batt',
        'B90' => 'microhenry',
        'B91' => 'microhenry per metre',
        'B93' => 'micronewton metre',
        'B99' => 'microsiemens',
        'BAM' => 'Bosnia and Herzegovina Convertible Marka',
        'BAR' => 'Bar',
        'BB' => 'base box',
        'BBD' => 'Barbados Dollar',
        'BDT' => 'Bangladesh Taka',
        'BFT' => 'board foot',
        'BHD' => 'Bahrain Dinar',
        'BHP' => 'brake horse power',
        'BIF' => 'Burundi Franc',
        'BL' => 'bale',
        'BLD' => 'Dry barrel (115,627 dm3)',
        'BMD' => 'Bermuda Dollar',
        'BND' => 'Brunei Darussalam Dollar',
        'BO' => 'bottle',
        'BOB' => 'Bolivia Bolíviano',
        'BP' => 'hundred board foot',
        'BPM' => 'beats per minute',
        'BQL' => 'Becquerel',
        'BR' => 'bar [unit of packaging]',
        'BRL' => 'Brazil Real',
        'BSD' => 'Bahamas Dollar',
        'BT' => 'bolt',
        'BTU' => 'British thermal unit',
        'BUA' => 'Bushel (35,2391 dm3)',
        'BUI' => 'Bushel (36,36874 dm3)',
        'BW' => 'base weight',
        'BWP' => 'Botswana Pula',
        'BX' => 'box',
        'BYN' => 'Belarus Ruble',
        'BZ' => 'million BTUs',
        'BZD' => 'Belize Dollar',
        'C0' => 'call',
        'C12' => 'milligram per metre',
        'C13' => 'milligray',
        'C14' => 'millihenry',
        'C15' => 'millijoule',
        'C2' => 'carset',
        'C22' => 'millinewton per metre',
        'C23' => 'milliohm metre',
        'C24' => 'millipascal second',
        'C26' => 'millisecond',
        'C30' => 'millivolt per metre',
        'C34' => 'mole',
        'C35' => 'mole per cubic decimetre',
        'C36' => 'mole per cubic metre',
        'C38' => 'mole per litre',
        'C4' => 'carload',
        'C41' => 'nanofarad',
        'C42' => 'nanofarad per metre',
        'C43' => 'nanohenry',
        'C47' => 'nanosecond',
        'C49' => 'nanowatt',
        'C5' => 'cost',
        'C53' => 'newton metre second',
        'C54' => 'newton metre squared kilogram squared',
        'C56' => 'newton per square millimetre',
        'C60' => 'ohm centimetre',
        'C65' => 'pascal second',
        'C66' => 'pascal second per cubic metre',
        'C67' => 'pascal second per metre',
        'C68' => 'petajoule',
        'C69' => 'phon',
        'C7' => 'centipoise',
        'C73' => 'picohenry',
        'C75' => 'picowatt',
        'C79' => 'kilovolt ampere hour',
        'C8' => 'millicoulomb per kilogram',
        'C82' => 'radian square metre per mole',
        'C85' => 'reciprocal angstrom',
        'C88' => 'reciprocal electron volt per cubic metre',
        'C90' => 'reciprocal joule per cubic metre',
        'C92' => 'reciprocal metre',
        'C94' => 'reciprocal minute',
        'C95' => 'reciprocal mole',
        'C99' => 'reciprocal second per metre squared',
        'CAD' => 'Canada Dollar',
        'CCT' => 'Carrying capacity in metric tonnes',
        'CDF' => 'Congo/Kinshasa Franc',
        'CEL' => 'Degree celcius',
        'CEN' => 'Hundred',
        'CG' => 'card',
        'CGM' => 'centigram',
        'CHF' => 'Switzerland Franc',
        'CJ' => 'cone',
        'CKG' => 'Coulomb per kilogram',
        'CMT' => 'Centimetre',
        'CNP' => 'Hundred packs',
        'CNY' => 'China Yuan Renminbi',
        'COP' => 'Colombia Peso',
        'CR' => 'crate',
        'CS' => 'case',
        'CT' => 'carton',
        'CTG' => 'content gram',
        'CTM' => 'Metric carat (200 mg = 2.10-4 kg)',
        'CUR' => 'Curie',
        'CVE' => 'Cape Verde Escudo',
        'CWA' => 'Hundredweight, US (45,3592 kg)',
        'CY' => 'cylinder',
        'D04' => 'lot [unit of weight]',
        'D10' => 'siemens per metre',
        'D11' => 'mebibit',
        'D12' => 'siemens square metre per mole',
        'D13' => 'sievert',
        'D16' => 'square centimetre per erg',
        'D17' => 'square centimetre per steradian erg',
        'D18' => 'metre kelvin',
        'D2' => 'reciprocal second per steradian metre squared',
        'D20' => 'square metre per joule',
        'D22' => 'square metre per mole',
        'D23' => 'pen gram (protein)',
        'D25' => 'square metre per steradian joule',
        'D27' => 'steradian',
        'D28' => 'syphon',
        'D29' => 'terahertz',
        'D31' => 'terawatt',
        'D36' => 'megabit',
        'D37' => 'calorie (thermochemical) per gram kelvin',
        'D42' => 'tropical year',
        'D43' => 'unified atomic mass unit',
        'D45' => 'volt squared per kelvin squared',
        'D46' => 'volt - ampere',
        'D49' => 'millivolt per kelvin',
        'D5' => 'kilogram per square centimetre',
        'D50' => 'volt per metre',
        'D52' => 'watt per kelvin',
        'D54' => 'watt per square metre',
        'D56' => 'watt per square metre kelvin to the fourth power',
        'D57' => 'watt per steradian',
        'D58' => 'watt per steradian square metre',
        'D59' => 'weber per metre',
        'D6' => 'roentgen per second',
        'D61' => 'minute [unit of angle]',
        'D64' => 'block',
        'D65' => 'round',
        'D66' => 'cassette',
        'D67' => 'dollar per hour',
        'D69' => 'inch to the fourth power',
        'D7' => 'sandwich',
        'D70' => 'International Table (IT) calorie',
        'D71' => 'IT calorie per second centimetre kelvin',
        'D72' => 'IT calorie per second square centimetre kelvin',
        'D74' => 'kilogram per mole',
        'D75' => 'calorie (international table) per gram',
        'D76' => 'calorie (international table) per gram kelvin',
        'D79' => 'beam',
        'D8' => 'draize score',
        'D82' => 'microvolt',
        'D83' => 'millinewton metre',
        'D85' => 'microwatt per square metre',
        'D88' => 'millicoulomb per cubic metre',
        'D89' => 'millicoulomb per square metre',
        'D90' => 'cubic metre (net)',
        'D91' => 'rem',
        'D94' => 'second per cubic metre radian',
        'D95' => 'joule per gram',
        'D97' => 'pallet/unit load',
        'D99' => 'sleeve',
        'DAA' => 'Decare',
        'DB' => 'dry pound',
        'DBC' => 'Decade (ten years)',
        'DC' => 'disk (disc)',
        'DD' => 'degree [unit of angle]',
        'DE' => 'deal',
        'DEC' => 'decade',
        'DJF' => 'Djibouti Franc',
        'DKK' => 'Denmark Krone',
        'DMA' => 'cubic decametre',
        'DMK' => 'Square decimetre',
        'DMO' => 'standard kilolitre',
        'DMQ' => 'Cubic decimetre',
        'DN' => 'decinewton metre',
        'DOP' => 'Dominican Republic Peso',
        'DPC' => 'dozen piece',
        'DPR' => 'Dozen pairs',
        'DS' => 'display',
        'DTN' => 'Centner, metric (100 kg)',
        'DU' => 'dyne',
        'DX' => 'dyne per centimetre',
        'DZD' => 'Algeria Dinar',
        'DZN' => 'Dozen',
        'E01' => 'newton per square centimetre',
        'E07' => 'megawatt hour per hour',
        'E08' => 'megawatt per hertz',
        'E09' => 'milliampere hour',
        'E10' => 'degree day',
        'E11' => 'gigacalorie',
        'E12' => 'mille',
        'E14' => 'kilocalorie (international table)',
        'E16' => 'million Btu(IT) per hour',
        'E17' => 'cubic foot per second',
        'E2' => 'belt',
        'E20' => 'megabit per second',
        'E21' => 'shares',
        'E23' => 'tyre',
        'E25' => 'active unit',
        'E31' => 'square metre per litre',
        'E33' => 'foot per thousand',
        'E34' => 'gigabyte',
        'E35' => 'terabyte',
        'E36' => 'petabyte',
        'E4' => 'gross kilogram',
        'E40' => 'part per hundred thousand',
        'E42' => 'kilogram-force per square centimetre',
        'E43' => 'joule per square centimetre',
        'E44' => 'kilogram-force metre per square centimetre',
        'E45' => 'milliohm',
        'E46' => 'kilowatt hour per cubic metre',
        'E47' => 'kilowatt hour per kelvin',
        'E50' => 'accounting unit',
        'E53' => 'test',
        'E54' => 'trip',
        'E55' => 'use',
        'E56' => 'well',
        'E57' => 'zone',
        'E58' => 'exabit per second',
        'E61' => 'tebibyte',
        'E63' => 'mebibyte',
        'E64' => 'kibibyte',
        'E65' => 'exbibit per metre',
        'E69' => 'gibibit per metre',
        'E70' => 'gibibit per square metre',
        'E71' => 'gibibit per cubic metre',
        'E72' => 'kibibit per metre',
        'E73' => 'kibibit per square metre',
        'E74' => 'kibibit per cubic metre',
        'E75' => 'mebibit per metre',
        'E76' => 'mebibit per square metre',
        'E77' => 'mebibit per cubic metre',
        'E79' => 'petabit per second',
        'E82' => 'pebibit per cubic metre',
        'E85' => 'tebibit per metre',
        'E87' => 'tebibit per square metre',
        'E88' => 'bit per metre',
        'E89' => 'bit per square metre',
        'E90' => 'reciprocal centimetre',
        'E92' => 'cubic decimetre per hour',
        'E93' => 'kilogram per hour',
        'E94' => 'kilomole per second',
        'E96' => 'degree per second',
        'E97' => 'millimetre per degree Celcius metre',
        'E98' => 'degree celsius per kelvin',
        'E99' => 'hektopascal per bar',
        'EA' => 'each',
        'EB' => 'electronic mail box',
        'EP' => 'eleven pack',
        'EUR' => 'Euro Member Countries',
        'F01' => 'bit per cubic metre',
        'F02' => 'kelvin per kelvin',
        'F04' => 'millibar per bar',
        'F05' => 'megapascal per bar',
        'F07' => 'pascal per bar',
        'F08' => 'milliampere per inch',
        'F10' => 'kelvin per hour',
        'F11' => 'kelvin per minute',
        'F12' => 'kelvin per second',
        'F13' => 'slug',
        'F14' => 'gram per kelvin',
        'F17' => 'pound-force per foot',
        'F18' => 'kilogram square centimetre',
        'F19' => 'kilogram square millimetre',
        'F23' => 'gram per cubic decimetre',
        'F26' => 'gram per day',
        'F27' => 'gram per hour',
        'F29' => 'gram per second',
        'F31' => 'kilogram per minute',
        'F33' => 'milligram per minute',
        'F35' => 'gram per day kelvin',
        'F36' => 'gram per hour kelvin',
        'F38' => 'gram per second kelvin',
        'F39' => 'kilogram per day kelvin',
        'F40' => 'kilogram per hour kelvin',
        'F41' => 'kilogram per minute kelvin',
        'F42' => 'kilogram per second kelvin',
        'F43' => 'milligram per day kelvin',
        'F44' => 'milligram per hour kelvin',
        'F46' => 'milligram per second kelvin',
        'F48' => 'pound-force per inch',
        'F50' => 'micrometre per kelvin',
        'F53' => 'millimetre per kelvin',
        'F54' => 'milliohm per metre',
        'F55' => 'ohm per mile',
        'F56' => 'ohm per kilometre',
        'F59' => 'milliampere per bar',
        'F61' => 'kelvin per bar',
        'F62' => 'gram per day bar',
        'F63' => 'gram per hour bar',
        'F64' => 'gram per minute bar',
        'F65' => 'gram per second bar',
        'F66' => 'kilogram per day bar',
        'F67' => 'kilogram per hour bar',
        'F69' => 'kilogram per second bar',
        'F70' => 'milligram per day bar',
        'F71' => 'milligram per hour bar',
        'F72' => 'milligram per minute bar',
        'F74' => 'gram per bar',
        'F75' => 'milligram per bar',
        'F76' => 'milliampere per millimetre',
        'F77' => 'pascal second per kelvin',
        'F78' => 'inch of water',
        'F79' => 'inch of mercury',
        'F80' => 'water horse power',
        'F82' => 'hektopascal per kelvin',
        'F83' => 'kilopascal per kelvin',
        'F84' => 'millibar per kelvin',
        'F85' => 'megapascal per kelvin',
        'F86' => 'poise per kelvin',
        'F89' => 'newton metre per degree',
        'F9' => 'fibre per cubic centimetre of air',
        'F90' => 'newton metre per ampere',
        'F91' => 'bar litre per second',
        'F92' => 'bar cubic metre per second',
        'F94' => 'hektopascal cubic metre per second',
        'F95' => 'millibar litre per second',
        'F97' => 'megapascal litre per second',
        'F99' => 'pascal litre per second',
        'FAH' => 'degree Fahrenheit',
        'FAR' => 'farad',
        'FB' => 'field',
        'FBM' => 'fibre metre',
        'FC' => 'thousand cubic foot',
        'FD' => 'million particle per cubic foot',
        'FE' => 'track foot',
        'FF' => 'hundred cubic metre',
        'FG' => 'transdermal patch',
        'FH' => 'micromole',
        'FJD' => 'Fiji Dollar',
        'FKP' => 'Falkland Islands (Malvinas) Pound',
        'FL' => 'flake ton',
        'FM' => 'million cubic foot',
        'FOT' => 'Foot (0.3048 m)',
        'FR' => 'foot per minute',
        'FTK' => 'Square foot',
        'FTQ' => 'Cubic foot',
        'G01' => 'pascal cubic metre per second',
        'G05' => 'metre per bar',
        'G06' => 'millimetre per bar',
        'G08' => 'square inch per second',
        'G09' => 'square metre per second kelvin',
        'G10' => 'stokes per kelvin',
        'G11' => 'gram per cubic centimetre bar',
        'G12' => 'gram per cubic decimetre bar',
        'G16' => 'kilogram per cubic centimetre bar',
        'G17' => 'kilogram per litre bar',
        'G18' => 'kilogram per cubic metre bar',
        'G19' => 'newton metre per kilogram',
        'G2' => 'US gallon per minute',
        'G3' => 'Imperial gallon per minute',
        'G20' => 'pound-force foot per pound',
        'G21' => 'cup [unit of volume]',
        'G23' => 'peck',
        'G24' => 'tablespoon (US)',
        'G25' => 'teaspoon (US)',
        'G26' => 'stere',
        'G27' => 'cubic centimetre per kelvin',
        'G28' => 'litre per kelvin',
        'G30' => 'millilitre per kelvin',
        'G31' => 'kilogram per cubic centimetre',
        'G32' => 'ounce (avoirdupois) per cubic yard',
        'G33' => 'gram per cubic centimetre kelvin',
        'G34' => 'gram per cubic decimetre kelvin',
        'G35' => 'gram per litre kelvin',
        'G37' => 'gram per millilitre kelvin',
        'G38' => 'kilogram per cubic centimetre kelvin',
        'G39' => 'kilogram per litre kelvin',
        'G41' => 'square metre per second bar',
        'G42' => 'microsiemens per centimetre',
        'G43' => 'microsiemens per metre',
        'G44' => 'nanosiemens per centimetre',
        'G46' => 'stokes per bar',
        'G47' => 'cubic centimetre per day',
        'G48' => 'cubic centimetre per hour',
        'G49' => 'cubic centimetre per minute',
        'G51' => 'litre per second',
        'G52' => 'cubic metre per day',
        'G54' => 'millilitre per day',
        'G55' => 'millilitre per hour',
        'G56' => 'cubic inch per hour',
        'G57' => 'cubic inch per minute',
        'G58' => 'cubic inch per second',
        'G59' => 'milliampere per litre minute',
        'G60' => 'volt per bar',
        'G61' => 'cubic centimetre per day kelvin',
        'G62' => 'cubic centimetre per hour kelvin',
        'G63' => 'cubic centimetre per minute kelvin',
        'G64' => 'cubic centimetre per second kelvin',
        'G65' => 'litre per day kelvin',
        'G66' => 'litre per hour kelvin',
        'G67' => 'litre per minute kelvin',
        'G68' => 'litre per second kelvin',
        'G69' => 'cubic metre per day kelvin',
        'G7' => 'microfiche sheet',
        'G70' => 'cubic metre per hour kelvin',
        'G71' => 'cubic metre per minute kelvin',
        'G72' => 'cubic metre per second kelvin',
        'G73' => 'millilitre per day kelvin',
        'G74' => 'millilitre per hour kelvin',
        'G75' => 'millilitre per minute kelvin',
        'G76' => 'millilitre per second kelvin',
        'G77' => 'millimetre to the fourth power',
        'G78' => 'cubic centimetre per day bar',
        'G79' => 'cubic centimetre per hour bar',
        'G80' => 'cubic centimetre per minute bar',
        'G81' => 'cubic centimetre per second bar',
        'G82' => 'litre per day bar',
        'G83' => 'litre per hour bar',
        'G84' => 'litre per minute bar',
        'G85' => 'litre per second bar',
        'G86' => 'cubic metre per day bar',
        'G87' => 'cubic metre per hour bar',
        'G88' => 'cubic metre per minute bar',
        'G89' => 'cubic metre per second bar',
        'G90' => 'millilitre per day bar',
        'G91' => 'millilitre per hour bar',
        'G92' => 'millilitre per minute bar',
        'G93' => 'millilitre per second bar',
        'G94' => 'cubic centimetre per bar',
        'G95' => 'litre per bar',
        'G96' => 'cubic metre per bar',
        'G97' => 'millilitre per bar',
        'G98' => 'microhenry per kiloohm',
        'G99' => 'microhenry per ohm',
        'GB' => 'gallon (US) per day',
        'GBP' => 'United Kingdom Pound',
        'GBQ' => 'Gigabecquerel',
        'GC' => 'gram per 100 gram',
        'GD' => 'gross barrel',
        'GDW' => 'gram, dry weight',
        'GE' => 'pound per gallon (US)',
        'GEL' => 'Georgia Lari',
        'GF' => 'gram per metre (gram per 100 centimetres)',
        'GFI' => 'gram of fissile isotope',
        'GGP' => 'Guernsey Pound',
        'GGR' => 'Great gross (12 gross)',
        'GH' => 'half gallon (US)',
        'GHS' => 'Ghana Cedi',
        'GIA' => 'Gill (11,8294 cm3)',
        'GIC' => 'gram, including container',
        'GII' => 'Gill (0,142065 dm3)',
        'GK' => 'gram per kilogram',
        'GLD' => 'Dry gallon (4,404884 dm3)',
        'GLI' => 'Gallon (4,546092 dm3)',
        'GM' => 'gram per square metre',
        'GMD' => 'Gambia Dalasi',
        'GNF' => 'Guinea Franc',
        'GO' => 'milligram per square metre',
        'GP' => 'milligram per cubic metre',
        'GQ' => 'microgram per cubic metre',
        'GRN' => 'Grain GB,US (64,798910 mg)',
        'GRO' => 'Gross',
        'GRT' => 'Gross (register) ton',
        'GTQ' => 'Guatemala Quetzal',
        'GV' => 'gigajoule',
        'GW' => 'gallon per thousand cubic foot',
        'GWH' => 'Gigawatt-hour (1 million KW/h)',
        'GY' => 'gross yard',
        'GYD' => 'Guyana Dollar',
        'H03' => 'henry per kiloohm',
        'H04' => 'henry per ohm',
        'H05' => 'millihenry per kiloohm',
        'H06' => 'millihenry per ohm',
        'H08' => 'microbecquerel',
        'H09' => 'reciprocal year',
        'H1' => 'half page - electronic',
        'H11' => 'reciprocal month',
        'H12' => 'degree Celsius per hour',
        'H14' => 'degree Celsius per second',
        'H16' => 'square decametre',
        'H18' => 'square hectometre',
        'H19' => 'cubic hectometre',
        'H2' => 'half litre',
        'H21' => 'blank',
        'H22' => 'volt square inch per pound-force',
        'H23' => 'volt per inch',
        'H24' => 'volt per microsecond',
        'H26' => 'ohm per metre',
        'H29' => 'microgram per litre',
        'H30' => 'square micrometre',
        'H31' => 'ampere per kilogram',
        'H32' => 'ampere squared second',
        'H34' => 'hertz metre',
        'H35' => 'kelvin metre per watt',
        'H36' => 'megaohm per kilometre',
        'H37' => 'megaohm per metre',
        'H40' => 'newton per ampere',
        'H41' => 'newton metre watt to the power minus 0,5',
        'H42' => 'pascal per metre',
        'H43' => 'siemens per centimetre',
        'H44' => 'teraohm',
        'H45' => 'volt second per metre',
        'H46' => 'volt per second',
        'H47' => 'watt per cubic metre',
        'H48' => 'attofarad',
        'H49' => 'centimetre per hour',
        'H50' => 'reciprocal cubic centimetre',
        'H51' => 'decibel per kilometre',
        'H52' => 'decibel per metre',
        'H53' => 'kilogram per bar',
        'H54' => 'kilogram per cubic decimetre kelvin',
        'H56' => 'kilogram per square metre second',
        'H57' => 'inch per two pi radiant',
        'H58' => 'metre per volt second',
        'H60' => 'cubic metre per cubic metre',
        'H62' => 'millivolt per minute',
        'H63' => 'milligram per square centimetre',
        'H65' => 'millilitre per cubic metre',
        'H68' => 'millimole per gram',
        'H69' => 'picopascal per kilometre',
        'H70' => 'picosecond',
        'H71' => 'percent per month',
        'H74' => 'watt per metre',
        'H76' => 'gram per millimetre',
        'H77' => 'module width',
        'H78' => 'conventional centimetre of water',
        'H79' => 'French gauge',
        'H80' => 'rack unit',
        'H81' => 'millimetre per minute',
        'H83' => 'litre per kilogram',
        'H84' => 'gram millimetre',
        'H85' => 'reciprocal week',
        'H87' => 'piece',
        'H88' => 'megaohm kilometre',
        'H90' => 'percent per degree',
        'H92' => 'percent per one hundred thousand',
        'H93' => 'percent per hundred',
        'H94' => 'percent per thousand',
        'H95' => 'percent per volt',
        'H96' => 'percent per bar',
        'H98' => 'percent per inch',
        'H99' => 'percent per metre',
        'HA' => 'hank',
        'HAR' => 'Hectare',
        'HBA' => 'Hectobar',
        'HBX' => 'hundred boxes',
        'HC' => 'hundred count',
        'HD' => 'half dozen',
        'HDW' => 'hundred kilogram, dry weight',
        'HE' => 'hundredth of a carat',
        'HEA' => 'head',
        'HF' => 'hundred foot',
        'HH' => 'hundred cubic foot',
        'HI' => 'hundred sheet',
        'HIU' => 'Hundred intenational units',
        'HJ' => 'metric horse power',
        'HK' => 'hundred kilogram',
        'HKD' => 'Hong Kong Dollar',
        'HKM' => 'hundred kilogram, net mass',
        'HL' => 'hundred foot (linear)',
        'HLT' => 'Hectolitre',
        'HM' => 'mile per hour',
        'HMT' => 'Hectometre',
        'HN' => 'conventional millimetre of mercury',
        'HNL' => 'Honduras Lempira',
        'HO' => 'hundred troy ounce',
        'HPA' => 'Hectolitre of pure alcohol',
        'HRK' => 'Croatia Kuna',
        'HS' => 'hundred square foot',
        'HT' => 'half hour',
        'HTG' => 'Haiti Gourde',
        'HTZ' => 'Hertz',
        'HUF' => 'Hungary Forint',
        'HUR' => 'Hour',
        'HY' => 'hundred yard',
        'IC' => 'count per inch',
        'IDR' => 'Indonesia Rupiah',
        'IE' => 'person',
        'II' => 'column inch',
        'IL' => 'inch per minute',
        'ILS' => 'Israel Shekel',
        'IM' => 'impression',
        'IMP' => 'Isle of Man Pound',
        'INH' => 'Inch (2.54 mm)',
        'INK' => 'Square inch',
        'INQ' => 'Cubic inch',
        'INR' => 'India Rupee',
        'ISK' => 'Iceland Krona',
        'IU' => 'inch per second',
        'IUG' => 'international unit per gram',
        'IV' => 'inch per second squared',
        'J12' => 'per mille per psi',
        'J13' => 'degree API',
        'J14' => 'degree Baume (origin scale)',
        'J15' => 'degree Baume (US heavy)',
        'J16' => 'degree Baume (US light)',
        'J18' => 'degree Brix',
        'J19' => 'degree Fahrenheit hoursquarefoot/Britishthermalunit/hour',
        'J2' => 'joule per kilogram',
        'J20' => 'degree Fahrenheit per kelvin',
        'J21' => 'degree Fahrenheit per bar',
        'J22' => 'British thermalunit/hour square foot degree Fahrenheit',
        'J25' => 'degree Fahrenheit per second',
        'J26' => 'reciprocal degree Fahrenheit',
        'J28' => 'degree Rankine per hour',
        'J29' => 'degree Rankine per minute',
        'J30' => 'degree Rankine per second',
        'J31' => 'degree Twaddell',
        'J32' => 'micropoise',
        'J33' => 'microgram per kilogram',
        'J34' => 'microgram per cubic metre kelvin',
        'J35' => 'microgram per cubic metre bar',
        'J36' => 'microlitre per litre',
        'J38' => 'baud',
        'J39' => 'British thermal unit (mean)',
        'J40' => 'British thermalunit foot/hour squarefoot degree Fahrenheit',
        'J41' => 'British thermalunit inch/hour squarefoot degree Fahrenheit',
        'J42' => 'British thermalunit inch/second squarefoot degree Fahrenheit',
        'J43' => 'British thermal unit per pound degree Fahrenheit',
        'J44' => 'British thermal unit (international table) /minute',
        'J45' => 'British thermal unit (international table) /second',
        'J46' => 'British thermalunit foot/hour squarefoot degree Fahrenheit',
        'J47' => 'British thermal unit (thermochemical) per hour',
        'J48' => 'British thermalunit inch/hour squarefoot degree Fahrenheit',
        'J49' => 'British thermalunit inch/second squarefoot degree Fahrenheit',
        'J51' => 'British thermal unit (thermochemical) per minute',
        'J53' => 'coulomb square metre per kilogram',
        'J54' => 'megabaud',
        'J55' => 'watt second',
        'J56' => 'bar per bar',
        'J57' => 'barrel (UK petroleum)',
        'J58' => 'barrel (UK petroleum) per minute',
        'J60' => 'barrel (UK petroleum) per hour',
        'J61' => 'barrel (UK petroleum) per second',
        'J63' => 'barrel (US petroleum) per second',
        'J64' => 'bushel (UK) per day',
        'J65' => 'bushel (UK) per hour',
        'J66' => 'bushel (UK) per minute',
        'J67' => 'bushel (UK) per second',
        'J69' => 'bushel (US dry) per hour',
        'J71' => 'bushel (US dry) per second',
        'J72' => 'centinewton metre',
        'J73' => 'centipoise per kelvin',
        'J74' => 'centipoise per bar',
        'J75' => 'calorie (mean)',
        'J76' => 'calorie (international table) /gram degree Celsius',
        'J79' => 'calorie (thermochemical) per gram degree Celsius',
        'J81' => 'calorie (thermochemical) per minute',
        'J82' => 'calorie (thermochemical) per second',
        'J83' => 'clo',
        'J84' => 'centimetre per second kelvin',
        'J85' => 'centimetre per second bar',
        'J89' => 'centimetre of mercury',
        'J90' => 'cubic decimetre per day',
        'J92' => 'cubic decimetre per minute',
        'J93' => 'cubic decimetre per second',
        'J94' => 'dyne centimetre',
        'J95' => 'ounce (UK fluid) per day',
        'J98' => 'ounce (UK fluid) per second',
        'JB' => 'jumbo',
        'JEP' => 'Jersey Pound',
        'JK' => 'megajoule per kilogram',
        'JM' => 'megajoule per cubic metre',
        'JNT' => 'pipeline joint',
        'JO' => 'joint',
        'JOD' => 'Jordan Dinar',
        'JPS' => 'hundred metre',
        'JR' => 'jar',
        'JWL' => 'number of jewels',
        'K1' => 'kilowatt demand',
        'K10' => 'ounce (US fluid) per hour',
        'K11' => 'ounce (US fluid) per minute',
        'K13' => 'foot per degree Fahrenheit',
        'K14' => 'foot per hour',
        'K15' => 'foot pound-force per hour',
        'K16' => 'foot pound-force per minute',
        'K17' => 'foot per psi',
        'K19' => 'foot per second psi',
        'K2' => 'kilovolt ampere reactive demand',
        'K20' => 'reciprocal cubic foot',
        'K21' => 'cubic foot per degree Fahrenheit',
        'K22' => 'cubic foot per day',
        'K23' => 'cubic foot per psi',
        'K25' => 'foot of mercury',
        'K26' => 'gallon (UK) per day',
        'K27' => 'gallon (UK) per hour',
        'K28' => 'gallon (UK) per second',
        'K3' => 'kilovolt ampere reactive hour',
        'K31' => 'gram-force per square centimetre',
        'K32' => 'gill (UK) per day',
        'K33' => 'gill (UK) per hour',
        'K35' => 'gill (UK) per second',
        'K37' => 'gill (US) per hour',
        'K38' => 'gill (US) per minute',
        'K39' => 'gill (US) per second',
        'K41' => 'grain per gallon (US)',
        'K42' => 'horsepower (boiler)',
        'K43' => 'horsepower (electric)',
        'K46' => 'inch per psi',
        'K48' => 'inch per second psi',
        'K49' => 'reciprocal cubic inch',
        'K5' => 'kilovolt ampere (reactive)',
        'K50' => 'kilobaud',
        'K51' => 'kilocalorie (mean)',
        'K52' => 'kilocalorie (IT) per hour metre degree Celsius',
        'K53' => 'kilocalorie (thermochemical)',
        'K54' => 'kilocalorie (thermochemical) per minute',
        'K55' => 'kilocalorie (thermochemical) per second',
        'K58' => 'kilomole per hour',
        'K59' => 'kilomole per cubic metre kelvin',
        'K6' => 'kilolitre',
        'K60' => 'kilomole per cubic metre bar',
        'K61' => 'kilomole per minute',
        'K62' => 'litre per litre',
        'K63' => 'reciprocal litre',
        'K64' => 'pound (avoirdupois) per degree Fahrenheit',
        'K65' => 'pound (avoirdupois) square foot',
        'K67' => 'pound per foot hour',
        'K68' => 'pound per foot second',
        'K70' => 'pound (avoirdupois) per cubic foot psi',
        'K71' => 'pound (avoirdupois) per gallon (UK)',
        'K73' => 'pound (avoirdupois) per hour degree Fahrenheit',
        'K74' => 'pound (avoirdupois) per hour psi',
        'K75' => 'pound/avoirdupois per cubic inch degree Fahrenheit',
        'K76' => 'pound (avoirdupois) per cubic inch psi',
        'K77' => 'pound (avoirdupois) per psi',
        'K78' => 'pound (avoirdupois) per minute',
        'K79' => 'pound (avoirdupois) per minute degree Fahrenheit',
        'K80' => 'pound (avoirdupois) per minute psi',
        'K81' => 'pound (avoirdupois) per second',
        'K83' => 'pound (avoirdupois) per second psi',
        'K84' => 'pound per cubic yard',
        'K85' => 'pound-force per square foot',
        'K86' => 'pound-force per square inch degree Fahrenheit',
        'K87' => 'psi cubic inch per second',
        'K88' => 'psi litre per second',
        'K89' => 'psi cubic metre per second',
        'K90' => 'psi cubic yard per second',
        'K92' => 'pound-force second per square inch',
        'K93' => 'reciprocal psi',
        'K95' => 'quart (UK liquid) per hour',
        'K97' => 'quart (UK liquid) per second',
        'K98' => 'quart (US liquid) per day',
        'KA' => 'cake',
        'KAT' => 'katal',
        'KB' => 'kilocharacter',
        'KBA' => 'Kilobar',
        'KCC' => 'kilogram of choline chloride',
        'KD' => 'kilogram decimal',
        'KDW' => 'kilogram drained net weight',
        'KEL' => 'Kelvin',
        'KES' => 'Kenya Shilling',
        'KF' => 'kilopacket',
        'KGM' => 'Kilogram',
        'KGS' => 'Kilogram Per Second',
        'KHR' => 'Cambodia Riel',
        'KI' => 'kilogram per millimetre width',
        'KIC' => 'kilogram, including container',
        'KIP' => 'kilogram, including inner packaging',
        'KJO' => 'Kilojoule',
        'KL' => 'kilogram per metre',
        'KLK' => 'lactic dry material percentage',
        'KMA' => 'kilogram of methylamine',
        'KMF' => 'Comorian Franc',
        'KMK' => 'Square kilometre',
        'KMQ' => 'Kilogram per cubic meter',
        'KNI' => 'Kilogram of nitrogen',
        'KNM' => 'kilonewton per square metre',
        'KNS' => 'Kilogram of named substance',
        'KNT' => 'Knot (1 nautical mile per hour)',
        'KO' => 'milliequivalence caustic potash per gram of product',
        'KPA' => 'kilopascal',
        'KPH' => 'Kilogram of potassium hydroxide (caustic potash)',
        'KPP' => 'Kilogram of phosphorus pentoxide (phosphoric anhydride)',
        'KPW' => 'Korea (North) Won',
        'KR' => 'kiloroentgen',
        'KRW' => 'Korea (South) Won',
        'KS' => 'thousand pound per square inch',
        'KSD' => 'Kilogram of substance 90 per cent dry',
        'KSH' => 'Kilogram of sodium hydroxide (caustic soda)',
        'KTM' => 'kilometre',
        'KTN' => 'Kilotonne',
        'KUR' => 'Kilogram of uranium',
        'KVA' => 'Kilovolt - ampere',
        'KVT' => 'kilovolt',
        'KW' => 'kilogram per millimetre',
        'KWD' => 'Kuwait Dinar',
        'KWN' => 'Kilowatt hour per normalized cubic metre',
        'KWS' => 'Kilowatt hour per standard cubic metre',
        'KWT' => 'Kilowatt',
        'KX' => 'millilitre per kilogram',
        'KYD' => 'Cayman Islands Dollar',
        'KZT' => 'Kazakhstan Tenge',
        'L13' => 'metre per second bar',
        'L14' => 'square metre hour degree Celsius per kilocalorie',
        'L15' => 'millipascal second per kelvin',
        'L16' => 'millipascal second per bar',
        'L17' => 'milligram per cubic metre kelvin',
        'L18' => 'milligram per cubic metre bar',
        'L19' => 'millilitre per litre',
        'L2' => 'litre per minute',
        'L23' => 'mole per hour',
        'L25' => 'mole per kilogram bar',
        'L26' => 'mole per litre kelvin',
        'L27' => 'mole per litre bar',
        'L31' => 'milliroentgen aequivalent men',
        'L32' => 'nanogram per kilogram',
        'L33' => 'ounce (avoirdupois) per day',
        'L34' => 'ounce (avoirdupois) per hour',
        'L35' => 'ounce (avoirdupois) per minute',
        'L36' => 'ounce (avoirdupois) per second',
        'L37' => 'ounce (avoirdupois) per gallon (UK)',
        'L39' => 'ounce (avoirdupois) per cubic inch',
        'L43' => 'peck (UK)',
        'L45' => 'peck (UK) per hour',
        'L46' => 'peck (UK) per minute',
        'L47' => 'peck (UK) per second',
        'L51' => 'peck (US dry) per second',
        'L52' => 'psi per psi',
        'L53' => 'pint (UK) per day',
        'L54' => 'pint (UK) per hour',
        'L55' => 'pint (UK) per minute',
        'L58' => 'pint (US liquid) per hour',
        'L60' => 'pint (US liquid) per second',
        'L61' => 'pint (US dry)',
        'L63' => 'slug per day',
        'L64' => 'slug per foot second',
        'L65' => 'slug per cubic foot',
        'L66' => 'slug per hour',
        'L69' => 'tonne per kelvin',
        'L72' => 'tonne per day kelvin',
        'L73' => 'tonne per day bar',
        'L74' => 'tonne per hour kelvin',
        'L78' => 'tonne per minute',
        'L81' => 'tonne per second',
        'L82' => 'tonne per second kelvin',
        'L83' => 'tonne per second bar',
        'L84' => 'ton (UK shipping)',
        'L85' => 'ton long per day',
        'L87' => 'ton short per degree Fahrenheit',
        'L88' => 'ton short per day',
        'L89' => 'ton short per hour degree Fahrenheit',
        'L92' => 'ton (UK long) per cubic yard',
        'L94' => 'ton-force (US short)',
        'L95' => 'common year',
        'L96' => 'sidereal year',
        'L99' => 'yard per psi',
        'LA' => 'pound per cubic inch',
        'LAC' => 'lactose excess percentage',
        'LAK' => 'Laos Kip',
        'LBP' => 'Lebanon Pound',
        'LBR' => 'Pound GB,US (0,45359237 kg)',
        'LC' => 'linear centimetre',
        'LD' => 'litre per day',
        'LEF' => 'leaf',
        'LF' => 'linear foot',
        'LH' => 'labour hour',
        'LJ' => 'large spray',
        'LK' => 'link',
        'LKR' => 'Sri Lanka Rupee',
        'LM' => 'linear metre',
        'LN' => 'length',
        'LO' => 'lot [unit of procurement]',
        'LPA' => 'Litre of pure alcohol',
        'LS' => 'lump sum',
        'LSL' => 'Lesotho Loti',
        'LUB' => 'metric ton, lubricating oil',
        'LUM' => 'Lumen',
        'LX' => 'linear yard per pound',
        'LY' => 'linear yard',
        'LYD' => 'Libya Dinar',
        'M0' => 'magnetic tape',
        'M1' => 'milligram per litre',
        'M11' => 'cubic yard per degree Fahrenheit',
        'M13' => 'cubic yard per hour',
        'M15' => 'cubic yard per minute',
        'M16' => 'cubic yard per second',
        'M17' => 'kilohertz metre',
        'M19' => 'Beaufort',
        'M21' => 'reciprocal kilovolt - ampere hour',
        'M22' => 'millilitre per square centimetre minute',
        'M23' => 'newton per centimetre',
        'M25' => 'percent per degree Celsius',
        'M26' => 'gigaohm per metre',
        'M30' => 'reciprocal volt - ampere second',
        'M32' => 'pascal second per litre',
        'M33' => 'millimole per litre',
        'M34' => 'newton metre per square metre',
        'M35' => 'millivolt - ampere',
        'M39' => 'centimetre per second squared',
        'M4' => 'monetary value',
        'M40' => 'yard per second squared',
        'M44' => 'revolution',
        'M46' => 'revolution per minute',
        'M49' => 'chain (based on U.S. survey foot)',
        'M5' => 'microcurie',
        'M50' => 'furlong',
        'M51' => 'foot (U.S. survey)',
        'M52' => 'mile (based on U.S. survey foot)',
        'M55' => 'metre per radiant',
        'M57' => 'mile per minute',
        'M58' => 'mile per second',
        'M59' => 'metre per second pascal',
        'M60' => 'metre per hour',
        'M61' => 'inch per year',
        'M62' => 'kilometre per second',
        'M63' => 'inch per minute',
        'M64' => 'yard per second',
        'M65' => 'yard per minute',
        'M66' => 'yard per hour',
        'M7' => 'micro-inch',
        'M70' => 'ton, register',
        'M71' => 'cubic metre per pascal',
        'M73' => 'kilogram per cubic metre pascal',
        'M74' => 'kilogram per pascal',
        'M76' => 'poundal',
        'M78' => 'pond',
        'M81' => 'square centimetre per second',
        'M82' => 'square metre per second pascal',
        'M83' => 'denier',
        'M84' => 'pound per yard',
        'M87' => 'kilogram per second pascal',
        'M89' => 'tonne per year',
        'M90' => 'kilopound per hour',
        'M95' => 'poundal foot',
        'M97' => 'dyne metre',
        'MA' => 'machine per unit',
        'MAD' => 'Morocco Dirham',
        'MAH' => 'megavolt ampere reactive hour',
        'MAL' => 'Megalitre',
        'MAR' => 'megavolt ampere reactive',
        'MAW' => 'Megawatt',
        'MBE' => 'thousand standard brick equivalent',
        'MBF' => 'thousand board foot',
        'MDL' => 'Moldova Leu',
        'FF' => 'milligram per square foot per side',
        'MGA' => 'Madagascar Ariary',
        'MGM' => 'Milligram',
        'MID' => 'Thousand',
        'MIL' => 'thousand',
        'MIN' => 'Minute',
        'MKD' => 'Macedonia Denar',
        'MLD' => 'Billion US',
        'MMK' => 'Square millimetre',
        'MMT' => 'Millimetre',
        'MNT' => 'Mongolia Tughrik',
        'MOP' => 'Macau Pataca',
        'MPA' => 'megapascal',
        'MQ' => 'thousand metre',
        'MQH' => 'cubic metre per hour',
        'MQS' => 'cubic metre per second',
        'MT' => 'mat',
        'MTK' => 'Square metre',
        'MUR' => 'Mauritius Rupee',
        'MWK' => 'Malawi Kwacha',
        'MXN' => 'Mexico Peso',
        'MYR' => 'Malaysia Ringgit',
        'N10' => 'pound foot per second',
        'N11' => 'pound inch per second',
        'N17' => 'inch of mercury (60 derajat F)',
        'N18' => 'inch of water (39.2 derajat F)',
        'N19' => 'inch of water (60 derajat F)',
        'N2' => 'number of lines',
        'N20' => 'kip per square inch',
        'N21' => 'poundal per square foot',
        'N22' => 'ounce (avoirdupois) per square inch',
        'N23' => 'conventional metre of water',
        'N24' => 'gram per square millimetre',
        'N25' => 'pound per square yard',
        'N27' => 'foot to the fourth power',
        'N28' => 'cubic decimetre per kilogram',
        'N3' => 'print point',
        'N30' => 'cubic inch per pound',
        'N31' => 'kilonewton per metre',
        'N32' => 'poundal per inch',
        'N33' => 'pound-force per yard',
        'N36' => 'newton second per square metre',
        'N37' => 'kilogram per metre second',
        'N39' => 'kilogram per metre day',
        'N40' => 'kilogram per metre hour',
        'N42' => 'poundal second per square inch',
        'N44' => 'pound per foot day',
        'N46' => 'foot poundal',
        'N49' => 'watt per square inch',
        'N66' => 'British thermal unit (39 derajat F)',
        'N68' => 'British thermal unit (60 derajat F)',
        'N69' => 'calorie (20 derajat C)',
        'N71' => 'therm (EC)',
        'N73' => 'British thermal unit (thermochemical) per pound',
        'N79' => 'kelvin per pascal',
        'N81' => 'kilowatt per metre kelvin',
        'N82' => 'kilowatt per metre degree Celsius',
        'N83' => 'metre per degree Celsius metre',
        'N90' => 'kilofarad',
        'N92' => 'picosiemens',
        'N97' => 'gilbert',
        'N98' => 'volt per pascal',
        'N99' => 'picovolt',
        'NA' => 'milligram per kilogram',
        'NAD' => 'Namibia Dollar',
        'NAR' => 'Number of articles',
        'NB' => 'barge',
        'NBB' => 'Number bobbins',
        'NCL' => 'number of cells',
        'NE' => 'net litre',
        'NEW' => 'Newton',
        'NG' => 'net gallon (us)',
        'NGN' => 'Nigeria Naira',
        'NH' => 'message hour',
        'NI' => 'net imperial gallon',
        'NIL' => 'nil',
        'NIO' => 'Nicaragua Cordoba',
        'NJ' => 'number of screens',
        'NMB' => 'Number',
        'NMI' => 'Nautical mile (1852 m)',
        'NMP' => 'Number of packs',
        'NOK' => 'Norway Krone',
        'NPL' => 'Number of parcels',
        'NPR' => 'number of pairs',
        'NPT' => 'Number of parts',
        'NQ' => 'mho',
        'NR' => 'micromho',
        'NRL' => 'Number of rolls',
        'NT' => 'net ton',
        'NTT' => 'Net (register) ton',
        'NU' => 'newton metre',
        'NV' => 'vehicle',
        'NX' => 'part per thousand',
        'NY' => 'pound per air dry metric ton',
        'NZD' => 'New Zealand Dollar',
        'OA' => 'panel',
        'ODE' => 'ozone depletion equivalent',
        'ODG' => 'ODS Grams',
        'ODK' => 'ODS Kilograms',
        'ODM' => 'ODS Milligrams',
        'OHM' => 'Ohm',
        'ON' => 'ounce per square yard',
        'ONZ' => 'Ounce GB,US (28,349523 g)',
        'OP' => 'two pack',
        'OPM' => 'oscillations per minute',
        'OT' => 'overtime hour',
        'OZ' => 'ounce av',
        'OZA' => 'Fluid ounce (29,5735 cm3)',
        'OZI' => 'Fluid ounce (29,5735 cm3)',
        'P0' => 'page - electronic',
        'P1' => 'percent',
        'P10' => 'coulomb per metre',
        'P11' => 'kiloweber',
        'P13' => 'kilotesla',
        'P15' => 'joule per minute',
        'P16' => 'joule per hour',
        'P17' => 'joule per day',
        'P18' => 'kilojoule per second',
        'P19' => 'kilojoule per minute',
        'P2' => 'pound per foot',
        'P20' => 'kilojoule per hour',
        'P21' => 'kilojoule per day',
        'P22' => 'nanoohm',
        'P24' => 'kilohenry',
        'P25' => 'lumen per square foot',
        'P26' => 'phot',
        'P27' => 'footcandle',
        'P28' => 'candela per square inch',
        'P29' => 'footlambert',
        'P3' => 'three pack',
        'P32' => 'candela per square foot',
        'P33' => 'kilocandela',
        'P34' => 'milliscandela',
        'P39' => 'calorie (thermochemical) per square centimetre',
        'P4' => 'four pack',
        'P40' => 'langley',
        'P42' => 'pascal squared second',
        'P43' => 'bel per metre',
        'P44' => 'pound mole',
        'P45' => 'pound mole per second',
        'P46' => 'pound mole per minute',
        'P49' => 'newton square metre per ampere',
        'P50' => 'weber metre',
        'P53' => 'unit pole',
        'P54' => 'milligray per second',
        'P55' => 'microgray per second',
        'P56' => 'nanogray per second',
        'P59' => 'microgray per minute',
        'P6' => 'six pack',
        'P60' => 'nanogray per minute',
        'P61' => 'gray per hour',
        'P62' => 'milligray per hour',
        'P64' => 'nanogray per hour',
        'P65' => 'sievert per second',
        'P66' => 'millisievert per second',
        'P67' => 'microsievert per second',
        'P69' => 'rem per second',
        'P7' => 'seven pack',
        'P70' => 'sievert per hour',
        'P71' => 'millisievert per hour',
        'P72' => 'microsievert per hour',
        'P75' => 'millisievert per minute',
        'P78' => 'reciprocal square inch',
        'P79' => 'pascal square metre per kilogram',
        'P8' => 'eight pack',
        'P81' => 'kilopascal per metre',
        'P83' => 'standard atmosphere per metre',
        'P85' => 'torr per metre',
        'P86' => 'psi per inch',
        'P87' => 'cubic metre per second square metre',
        'P88' => 'rhe',
        'P89' => 'pound-force foot per inch',
        'P9' => 'nine pack',
        'P90' => 'pound-force inch per inch',
        'P91' => 'perm (0 °C)',
        'P93' => 'byte per second',
        'P94' => 'kilobyte per second',
        'P97' => 'reciprocal radian',
        'P98' => 'pascal to the power sum of stoichiometric numbers',
        'PA' => 'packet',
        'PAL' => 'Pascal',
        'PB' => 'pair inch',
        'PCE' => 'Piece',
        'PD' => 'pad',
        'PE' => 'pound equivalent',
        'PEN' => 'Peru Sol',
        'PFL' => 'proof litre',
        'PG' => 'plate',
        'PGK' => 'Papua New Guinea Kina',
        'PHP' => 'Philippines Piso',
        'PI' => 'pitch',
        'PK' => 'pack',
        'PLA' => 'degree Plato',
        'PM' => 'pound percentage',
        'PO' => 'pound per inch of length',
        'PS' => 'pound-force per square inch',
        'PT' => 'pint (US)',
        'PU' => 'tray / tray pack',
        'PV' => 'half pint (US)',
        'PY' => 'peck dry (US)',
        'PYG' => 'Paraguay Guarani',
        'Q10' => 'joule per tesla',
        'Q12' => 'octet',
        'Q13' => 'octet per second',
        'Q16' => 'natural unit of information',
        'Q17' => 'shannon per second',
        'Q19' => 'natural unit of information per second',
        'Q22' => 'second per radian cubic metre',
        'Q23' => 'weber to the power minus one',
        'Q27' => 'newton metre per metre',
        'Q29' => 'microgram per hectogram',
        'Q3' => 'meal',
        'Q30' => 'pH (potential of Hydrogen)',
        'Q31' => 'kilojoule per gram',
        'Q35' => 'megawatts per minute',
        'Q36' => 'square metre per cubic metre',
        'Q37' => 'Standard cubic metre per day',
        'Q38' => 'Standard cubic metre per hour',
        'Q39' => 'Normalized cubic metre per day',
        'Q40' => 'Normalized cubic metre per hour',
        'Q42' => 'Joule per standard cubic metre',
        'QAN' => 'Quarter (of a year)',
        'QD' => 'quarter dozen',
        'QH' => 'quarter hour',
        'QK' => 'quarter kilogram',
        'QR' => 'quire',
        'QT' => 'quart (US)',
        'QTD' => 'Dry quart (1,101221 dm3)',
        'QTI' => 'Quart (1,136523 dm3)',
        'QTL' => 'Liquid quart (0,946353 dm3)',
        'R1' => 'pica',
        'R4' => 'calorie',
        'R9' => 'thousand cubic metre',
        'RG' => 'ring',
        'RH' => 'running or operating hour',
        'RK' => 'roll metric measure',
        'RL' => 'reel',
        'RM' => 'ream',
        'RO' => 'roll',
        'ROM' => 'room',
        'RON' => 'Romania Leu',
        'RP' => 'pound per ream',
        'RPM' => 'Revolution per minute',
        'RPS' => 'Revolution per second',
        'RS' => 'reset',
        'RT' => 'revenue ton mile',
        'RUB' => 'Russia Ruble',
        'RWF' => 'Rwanda Franc',
        'S3' => 'square foot per second',
        'S4' => 'square metre per second',
        'S6' => 'session',
        'S7' => 'storage unit',
        'S8' => 'standard advertising unit',
        'SA' => 'sack',
        'SAN' => 'Half year (six Months)',
        'SBD' => 'Solomon Islands Dollar',
        'SCO' => 'Score',
        'SCR' => 'Scruple GP,US (1,295982 g)',
        'SD' => 'solid pound',
        'SDG' => 'Sudan Pound',
        'SE' => 'section',
        'SEC' => 'Second',
        'SEK' => 'Sweden Krona',
        'SET' => 'Set',
        'SG' => 'segment',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'Saint Helena Pound',
        'SHT' => 'Shipping ton',
        'SIE' => 'Siemens',
        'SK' => 'split tank truck',
        'SL' => 'slipsheet',
        'SLL' => 'Sierra Leone Leone',
        'SM3' => 'Standard cubic metre',
        'SMI' => 'Statute mile (1609.344 m)',
        'SN' => 'square rod',
        'SOS' => 'Somalia Shilling',
        'SPL' => 'Seborga Luigino',
        'SQ' => 'square',
        'SQR' => 'square, roofing',
        'SRD' => 'Suriname Dollar',
        'SS' => 'sheet metric measure',
        'ST' => 'sheet',
        'STC' => 'stick',
        'STI' => 'Stone GB (6,350293 kg)',
        'STK' => 'stick, cigarette',
        'STL' => 'standard litre',
        'STN' => 'Short ton GB, US (0,90718474 t)',
        'STW' => 'straw',
        'SW' => 'skein',
        'SX' => 'shipment',
        'SZL' => 'Swaziland Lilangeni',
        'T0' => 'telecommunication line in service',
        'T1' => 'thousand pound gross',
        'T3' => 'thousand piece',
        'T5' => 'thousand casing',
        'T6' => 'thousand gallon (US)',
        'T7' => 'thousand impression',
        'T8' => 'thousand linear inch',
        'TA' => 'tenth cubic foot',
        'TAH' => 'Thousand ampere-hour',
        'TAN' => 'total acid number',
        'TC' => 'truckload',
        'TE' => 'tote',
        'TF' => 'ten square yard',
        'THB' => 'Thailand Baht',
        'TI' => 'thousand square inch',
        'TIP' => 'metric ton, including inner packaging',
        'TJ' => 'thousand square centimetre',
        'TK' => 'tank, rectangular',
        'TL' => 'thousand foot (linear)',
        'TMS' => 'kilogram of imported meat, less offal',
        'TMT' => 'Turkmenistan Manat',
        'TN' => 'tin',
        'TND' => 'Tunisia Dinar',
        'TOP' => 'Tonga Pa\'anga',
        'TPI' => 'teeth per inch',
        'TPR' => 'Ten pairs',
        'TQ' => 'thousand foot',
        'TQD' => 'thousand cubic metres per day',
        'TR' => 'ten square foot',
        'TRL' => 'Trillion Eur',
        'TRY' => 'Turkey Lira',
        'TS' => 'thousand square foot',
        'TSH' => 'Ton of steam per hour',
        'TST' => 'ten set',
        'TT' => 'thousand linear metre',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TTS' => 'ten thousand sticks',
        'TU' => 'tube',
        'TV' => 'thousand kilogram',
        'TVD' => 'Tuvalu Dollar',
        'TWD' => 'Taiwan New Dollar',
        'TY' => 'tank, cylindrical',
        'TZS' => 'Tanzania Shilling',
        'U1' => 'treatment',
        'U2' => 'tablet',
        'UA' => 'torr',
        'UAH' => 'Ukraine Hryvnia',
        'UB' => 'telecommunication line in service average',
        'UC' => 'telecommunication port',
        'UD' => 'tenth minute',
        'UE' => 'tenth hour',
        'UF' => 'usage per telecommunication line average',
        'UGX' => 'Uganda Shilling',
        'UH' => 'ten thousand yard',
        'UM' => 'million unit',
        'USD' => 'United States Dollar',
        'UYU' => 'Uruguay Peso',
        'UZS' => 'Uzbekistan Som',
        'VA' => 'volt - ampere per kilogram',
        'VEF' => 'Venezuela Bolivar',
        'VI' => 'vial',
        'VLT' => 'Volt',
        'VND' => 'Viet Nam Dong',
        'VP' => 'percent volume',
        'VQ' => 'bulk',
        'VS' => 'visit',
        'VUV' => 'Vanuatu Vatu',
        'W2' => 'wet kilo',
        'W4' => 'two week',
        'WCD' => 'Cord (3,63 m3)',
        'WE' => 'wet ton',
        'WEE' => 'Week',
        'WG' => 'wine gallon',
        'WHR' => 'Watt-hour',
        'WI' => 'weight per square inch',
        'WM' => 'working month',
        'WSD' => 'Standard',
        'WST' => 'Samoa Tala',
        'WW' => 'millilitre of water',
        'X1' => 'Gunters chain',
        'XAF' => 'Communauté Financière Africaine (BEAC) CFA Franc',
        'XCD' => 'East Caribbean Dollar',
        'XOF' => 'Communauté Financière Africaine (BCEAO) Franc',
        'XPF' => 'Comptoirs Français du Pacifique (CFP) Franc',
        'YDK' => 'Square yard',
        'YER' => 'Yemen Rial',
        'YL' => 'hundred linear yard',
        'YRD' => 'Yard (0.9144 m)',
        'Z1' => 'lift van',
        'Z11' => 'hanging container',
        'Z2' => 'chest',
        'Z3' => 'cask',
        'Z4' => 'hogshead',
        'Z6' => 'conference point',
        'Z8' => 'newspage agate line',
        'ZAR' => 'South Africa Rand',
        'ZMW' => 'Zambia Kwacha',
        'ZWD' => 'Zimbabwe Dollar',
        'ZZ' => 'mutually defined'
    ];

    $referensiDokumen = [
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

    $entitasAman = [];
    if (isset($dataDetail['entitas']) && is_array($dataDetail['entitas'])) {
        foreach ($dataDetail['entitas'] as $e) {
            if (isset($e['kodeEntitas'])) {
                $entitasAman[$e['kodeEntitas']] = $e;
            }
        }
        $dataDetail['entitas'] = $entitasAman;
    }

    if (isset($dataDetail['dokumen']) && is_array($dataDetail['dokumen'])) {
        $dokMap = [];
        foreach ($dataDetail['dokumen'] as $d) {
            $dokMap[] = [
                'kode'  => $d['kodeDokumen'] ?? '',
                'nomor' => $d['nomorDokumen'] ?? '',
                'tgl'   => $d['tanggalDokumen'] ?? '',
                'fileName'  => $d['fileName'] ?? null
            ];
        }
        $dataDetail['dok'] = $dokMap;
    }

    $mapNamaTps = [
        // Tanjung Priok / Jakarta (040300)
        'KOJA' => 'KOJA - KSO BPK KOJA',
        'JICT' => 'JICT - PT JAKARTA INTERNATIONAL CONTAINER TERMINAL',
        '3T01' => '3T01 - PT MUSTIKA ALAM LESTARI (MAL)',
        '1T01' => '1T01 - PT PELABUHAN INDONESIA II CABANG TANJUNG PRIOK',
        '1T02' => '1T02 - TERMINAL 3 TANJUNG PRIOK',
        'KJT1' => 'KJT1 - TERMINAL PETIKEMAS KOJA',
        'NPCT' => 'NPCT - NEW PRIOK CONTAINER TERMINAL ONE (NPCT1)',
        'DWKA' => 'DWKA - PT DWIPA KHARISMA MITRA TANJUNG PRIOK',
        'AGTP' => 'AGTP - PT AIRIN TANJUNG PRIOK',
        'MIPR' => 'MIPR - PT MULTI INTIPARNA TANJUNG PRIOK',

        // Soekarno-Hatta / Tangerang / Jakarta (050100)
        'JASA' => 'JASA - PT JASA ANGKASA SEMESTA (JAS) CARGO SOEKARNO HATTA',
        'GARU' => 'GARU - PT GARUDA INDONESIA CARGO SOEKARNO HATTA',
        'UNPA' => 'UNPA - PT UNIAIR INDOTAMA CARGO SOEKARNO HATTA',
        'FEDX' => 'FEDX - PT FEDERAL EXPRESS SOEKARNO HATTA',
        'DHLX' => 'DHLX - PT BIROTIKA SEMESTA (DHL EXPRESS) SOEKARNO HATTA',
        'UPSX' => 'UPSX - PT UPS CARDIG INTERNATIONAL SOEKARNO HATTA',
        'TNTX' => 'TNTX - PT SKYLIFT CONSOLIDATOR (TNT EXPRESS) SOEKARNO HATTA',
        'GAPU' => 'GAPU - PT GAPURA ANGKASA CARGO SOEKARNO HATTA',
        'ANGK' => 'ANGK - PT ANGKASA PURA II CARGO SOEKARNO HATTA',

        // Tanjung Perak / Surabaya (070100)
        'TPS1' => 'TPS1 - PT TERMINAL PETIKEMAS SURABAYA (TPS)',
        'BJTI' => 'BJTI - PT BERLIAN JASA TERMINAL INDONESIA',
        'TTL1' => 'TTL1 - PT TERMINAL TELUK LAMONG',
        'MTPS' => 'MTPS - PT MIRAH TERMINAL PETIKEMAS SURABAYA',
        'DWKS' => 'DWKS - PT DWIPA KHARISMA MITRA SURABAYA',
        'ISPS' => 'ISPS - PT INDOLINE SURABAYA',

        // Juanda / Sidoarjo / Surabaya (070200)
        'JASJ' => 'JASJ - PT JASA ANGKASA SEMESTA (JAS) CARGO JUANDA',
        'GAPJ' => 'GAPJ - PT GAPURA ANGKASA CARGO JUANDA',
        'GARJ' => 'GARJ - PT GARUDA INDONESIA CARGO JUANDA',
        'DHLJ' => 'DHLJ - PT BIROTIKA SEMESTA (DHL) JUANDA',

        // Tanjung Emas / Semarang (060100)
        'TPK2' => 'TPK2 - TERMINAL PETIKEMAS SEMARANG (TPKS)',
        'SRIS' => 'SRIS - PT SARI RANA INDAH SEMARANG',
        'DHLS' => 'DHLS - PT BIROTIKA SEMESTA SEMARANG',
        'GAPM' => 'GAPM - PT GAPURA ANGKASA CARGO AHMAD YANI SEMARANG',

        // Belawan / Medan (010700)
        'BICT' => 'BICT - BELAWAN INTERNATIONAL CONTAINER TERMINAL',
        'TPKB' => 'TPKB - TERMINAL PETIKEMAS BELAWAN',
        'BTLP' => 'BTLP - PT BELAWAN TERMINAL LOGISTIK PERSERO',

        // Kualanamu / Medan (010800)
        'JASK' => 'JASK - PT JASA ANGKASA SEMESTA CARGO KUALANAMU',
        'GAPK' => 'GAPK - PT GAPURA ANGKASA CARGO KUALANAMU',
        'GARK' => 'GARK - PT GARUDA INDONESIA CARGO KUALANAMU',

        // Ngurah Rai / Denpasar / Bali (080100)
        'JASD' => 'JASD - PT JASA ANGKASA SEMESTA CARGO NGURAH RAI',
        'GAPD' => 'GAPD - PT GAPURA ANGKASA CARGO NGURAH RAI',
        'GARD' => 'GARD - PT GARUDA INDONESIA CARGO NGURAH RAI',

        // Batam / Kepulauan Riau (020100)
        'BTBP' => 'BTBP - PT BATAM PERSERO BEKAS / BATU AMPAR',
        'BICT2' => 'BICT2 - BATAM INTERNATIONAL CONTAINER TERMINAL',
        'DHLB' => 'DHLB - PT BIROTIKA SEMESTA BATAM',
        'CGKB2' => 'CGKB2 - TPS CARGO BANDARA HANG NADIM BATAM',

        // Makassar / Sulawesi Selatan (100100)
        'TPKM' => 'TPKM - TERMINAL PETIKEMAS MAKASSAR (PELINDO IV)',
        'GAPG' => 'GAPG - PT GAPURA ANGKASA CARGO SULTAN HASANUDDIN MAKASSAR',
        'GARM' => 'GARM - PT GARUDA INDONESIA CARGO MAKASSAR',

        // Balikpapan / Kalimantan Timur (120100)
        'KKT1' => 'KKT1 - PT KALTIM KARIANGAU TERMINAL (KKT) BALIKPAPAN',
        'GAPB' => 'GAPB - PT GAPURA ANGKASA CARGO SEPINGGAN BALIKPAPAN',

        // Cikarang / Bekasi (050300)
        'CDP1' => 'CDP1 - CIKARANG DRY PORT (PT CIKARANG INLAND PORT)',
        'MTB1' => 'MTB1 - PT MITRA TATA BUANA CIKARANG',

        // Bandung (050500)
        'BDRB' => 'BDRB - PT BHANDA GHARA REKSA (BGR) GEDEBAGE BANDUNG',
        'GDBG' => 'GDBG - TPS GEDEBAGE BANDUNG',
        'PTKB' => 'PTKB - TPS PT POS INDONESIA BANDUNG',
        'CGKB' => 'CGKB - TPS CARGO BANDARA HUSEIN SASTRANEGARA BANDUNG',

        // Tangerang / Serpong (050200)
        'BSDT' => 'BSDT - TPS BSD TANGERANG KOTA',
        'IKGT' => 'IKGT - PT INDO KOR GUNA TANGERANG',

        // Merak / Banten (040100)
        'IKPT' => 'IKPT - PT INDAH KIAT PULP & PAPER MERAK BANTEN',
        'CMPT' => 'CMPT - PT CIWANDAN MULTI PURPOSES TERMINAL MERAK',

        // Tanjung Pinang / Kepri (020200)
        'TPTP' => 'TPTP - TERMINAL PETIKEMAS TANJUNG PINANG',
        'KIPT' => 'KIPT - KIJANG PORT TERMINAL',

        // Palembang (030100)
        'BMTP2' => 'BMTP2 - BOOM BARU TERMINAL PETIKEMAS PALEMBANG (PELINDO II)',
        'GAPP' => 'GAPP - PT GAPURA ANGKASA CARGO PALEMBANG',

        // Lampung / Panjang (030400)
        'TPKP' => 'TPKP - TERMINAL PETIKEMAS PANJANG (PELINDO II)',
        'PJPG' => 'PJPG - PELABUHAN PANJANG',

        // Pontianak (130100)
        'TPKN' => 'TPKN - TERMINAL PETIKEMAS PONTIANAK (PELINDO II)',
        'SUPN' => 'SUPN - TPS CARGO BANDARA SUPADIO PONTIANAK',

        // Banjarmasin (130300)
        'TPPB' => 'TPPB - TERMINAL PETIKEMAS TRISAKTI BANJARMASIN (PELINDO III)',
        'BDJB' => 'BDJB - TPS CARGO BANDARA SYAMSUDIN NOOR BANJARMASIN',

        // Samarinda (120200)
        'PSMD' => 'PSMD - PALARAN SAMARINDA CONTAINER TERMINAL (PT PSP)',

        // Bitung / Manado (110100)
        'TPBI' => 'TPBI - TERMINAL PETIKEMAS BITUNG (PELINDO IV)',
        'MDCB' => 'MDCB - TPS CARGO BANDARA SAM RATULANGI MANADO',

        // Ambon (140100)
        'TPKA' => 'TPKA - TERMINAL PETIKEMAS AMBON (PELINDO IV)',
        'AMQB' => 'AMQB - TPS CARGO BANDARA PATTIMURA AMBON',

        // Jayapura / Papua (140200)
        'TPKJ' => 'TPKJ - TERMINAL PETIKEMAS JAYAPURA (PELINDO IV)',
        'DJJB' => 'DJJB - TPS CARGO BANDARA SENTANI JAYAPURA',

        // Sorong (140400)
        'TPKS2' => 'TPKS2 - TERMINAL PETIKEMAS SORONG (PELINDO IV)',

        // Kupang / NTT (080300)
        'TPKK' => 'TPKK - TERMINAL PETIKEMAS TENAU KUPANG (PELINDO III)',

        // Mataram / Lembar / NTB (080200)
        'TPML' => 'TPML - TERMINAL PETIKEMAS LEMBAR MATARAM (PELINDO III)',
    ];
    $tpsCode = $dataDetail['kodeTps'] ?? '';
    $tpsLabel = $mapNamaTps[$tpsCode] ?? ($dataDetail['namaTps'] ?? $tpsCode);
@endphp

<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 3.0 - PEMBERITAHUAN EKSPOR BARANG
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc30', $header->bppbno ?? $header->trx_no_par) }}" method="POST" id="form-edit-ceisa" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ $header->trx_no_par }} |
                <strong>Supplier:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="bppbno_int" value="{{ $header->bppbno_int ?? '' }}">
                <input type="hidden" name="kodeDokumen" value="30">
                <input type="hidden" name="asalData" value="S">
                <input type="hidden" name="disclaimer" value="1">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Header & PKB</a></li>
                <li class="nav-item"><a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a></li>
                <li class="nav-item"><a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#tab-dokumen" role="tab">Dokumen Pelengkap</a></li>
                <li class="nav-item"><a class="nav-link" id="pengangkut-tab" data-toggle="tab" href="#tab-pengangkut" role="tab">Pengangkutan</a></li>
                <li class="nav-item"><a class="nav-link" id="kemasan-tab" data-toggle="tab" href="#tab-kemasan" role="tab">Kemasan & Peti Kemas</a></li>
                <li class="nav-item"><a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#tab-transaksi" role="tab">Transaksi & Keuangan</a></li>
                <li class="nav-item"><a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Data Barang</a></li>
                <li class="nav-item"><a class="nav-link" id="pungutan-tab" data-toggle="tab" href="#tab-pungutan" role="tab">Pungutan</a></li>
                <li class="nav-item"><a class="nav-link" id="pernyataan-tab" data-toggle="tab" href="#tab-pernyataan" role="tab">Pernyataan</a></li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">

                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Data Utama</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Nomor Aju</label>
                                        <div class="col-sm-8"><input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tanggal Aju</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalAju" class="form-control form-control-sm" value="{{ $ceisaInfo->tanggal_aju ?? date('Y-m-d') }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Pemuatan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorMuat" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Kantor Pabean Pemuatan</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorMuat'] ?? '050500') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Muat Ekspor </label>
                                        <div class="col-sm-8">
                                            <select name="kodePelEkspor" class="form-control form-control-sm select2-pelabuhan">
                                                <option value="">Pilih Pelabuhan Muat Ekspor</option>
                                                @if(!empty($dataDetail['kodePelEkspor']))
                                                    <option value="{{ $dataDetail['kodePelEkspor'] }}" selected>{{ $dataDetail['kodePelEkspor'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor Pabean Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorEkspor" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Kantor Pabean Ekspor</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorEkspor'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeJenisEkspor" class="form-control form-control-sm select2bs4">
                                                @php $jenisEkspor = $dataDetail['kodeJenisEkspor'] ?? '' @endphp
                                                <option value="">Pilih Jenis Ekspor</option>
                                                <option value="1" {{ $jenisEkspor == '1' ? 'selected' : '' }}>1 - Ekspor Biasa</option>
                                                <option value="2" {{ $jenisEkspor == '2' ? 'selected' : '' }}>2 - Ekspor Sementara</option>
                                                <option value="4" {{ $jenisEkspor == '4' ? 'selected' : '' }}>4 - Ekspor Barang yang akan Diimpor Kembali</option>
                                                <option value="5" {{ $jenisEkspor == '5' ? 'selected' : '' }}>5 - Re Ekspor Lainnya</option>
                                                <option value="6" {{ $jenisEkspor == '6' ? 'selected' : '' }}>6 - Ekspor Barang Eks Impor Sementara</option>
                                                <option value="7" {{ $jenisEkspor == '7' ? 'selected' : '' }}>7 - Ekspor Gabungan</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Kategori Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKategoriEkspor" class="form-control form-control-sm select2bs4">
                                                @php $katEkspor = $dataDetail['kodeKategoriEkspor'] ?? '' @endphp
                                                <option value="">Pilih Kategori Ekspor</option>
                                                <option {{ ($katEkspor ?? '') == '61' ? 'selected' : '' }} value="61">61 - BKC Yang Belum Dilunasi Cukainya</option>
                                                <option {{ ($katEkspor ?? '') == '51' ? 'selected' : '' }} value="51">51 - PLB</option>
                                                <option {{ ($katEkspor ?? '') == '46' ? 'selected' : '' }} value="46">46 - TPB Dari Kawasan Daur Ulang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '45' ? 'selected' : '' }} value="45">45 - TPB Dari Tempat Lelang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '44' ? 'selected' : '' }} value="44">44 - TPB Dari Toko Bebas Bea</option>
                                                <option {{ ($katEkspor ?? '') == '43' ? 'selected' : '' }} value="43">43 - TPB Dari Tempat Pameran Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '42' ? 'selected' : '' }} value="42">42 - TPB Dari Gudang Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '41' ? 'selected' : '' }} value="41">41 - TPB Dari Kawasan Berikat</option>
                                                <option {{ ($katEkspor ?? '') == '38' ? 'selected' : '' }} value="38">38 - Khusus Brg Keperluan Penelitian</option>
                                                <option {{ ($katEkspor ?? '') == '37' ? 'selected' : '' }} value="37">37 - Khusus Brg Contoh</option>
                                                <option {{ ($katEkspor ?? '') == '36' ? 'selected' : '' }} value="36">36 - Khusus Brg Cinderamata</option>
                                                <option {{ ($katEkspor ?? '') == '35' ? 'selected' : '' }} value="35">35 - Khusus Brg Keperluan Ibadah Utk Umum Sosial Pendidikan Budaya/Olahraga dan Bencana Alam</option>
                                                <option {{ ($katEkspor ?? '') == '34' ? 'selected' : '' }} value="34">34 - Khusus Brg Pindahan</option>
                                                <option {{ ($katEkspor ?? '') == '33' ? 'selected' : '' }} value="33">33 - Khusus Brg Kiriman</option>
                                                <option {{ ($katEkspor ?? '') == '32' ? 'selected' : '' }} value="32">32 - Khusus Brg Perwakilan Badan Internasional</option>
                                                <option {{ ($katEkspor ?? '') == '31' ? 'selected' : '' }} value="31">31 - Khusus Brg Perwakilan Negara Asing</option>
                                                <option {{ ($katEkspor ?? '') == '23' ? 'selected' : '' }} value="23">23 - KITE dengan pembebasan dan pengembalian</option>
                                                <option {{ ($katEkspor ?? '') == '22' ? 'selected' : '' }} value="22">22 - Yg pd saat imp mndpt fas pengembalian BM(NIPER dgn pengembalian)</option>
                                                <option {{ ($katEkspor ?? '') == '21' ? 'selected' : '' }} value="21">21 - Yg pd saat imp mndpt fas pembebasan BM(NIPER dgn pembebasan)</option>
                                                <option {{ ($katEkspor ?? '') == '10' ? 'selected' : '' }} value="10">10 - Umum</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Lokasi TPS</label>
                                        <div class="col-sm-8">
                                            <select name="kodeTps" class="form-control form-control-sm select2-tps">
                                                <option value="">Pilih Lokasi TPS</option>
                                                @foreach($mapNamaTps as $code => $label)
                                                    <option value="{{ $code }}" {{ $tpsCode == $code ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                                @if(!empty($tpsCode) && !isset($mapNamaTps[$tpsCode]))
                                                    <option value="{{ $tpsCode }}" selected>{{ $tpsLabel }}</option>
                                                @endif
                                            </select>
                                            <small class="text-muted">Ketik nama atau kode TPS (Contoh: KOJA, JICT, dll)</small>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Cara Perdagangan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeCaraDagang" class="form-control form-control-sm select2bs4">
                                                @php $caraDagang = $dataDetail['kodeCaraDagang'] ?? '' @endphp
                                                <option value="">Pilih Cara Perdagangan</option>
                                                <option value="1" {{ $caraDagang == '1' ? 'selected' : '' }}>1 - Biasa</option>
                                                <option value="2" {{ $caraDagang == '2' ? 'selected' : '' }}>2 - IMB - Imbal Dagang</option>
                                                <option value="3" {{ $caraDagang == '3' ? 'selected' : '' }}>3 - PMK - Pembayaran dimuka / Advance Payment</option>
                                                <option value="4" {{ $caraDagang == '4' ? 'selected' : '' }}>4 - KMD Bertahap - Pembayaran Kemudian / Open Account Tunai</option>
                                                <option value="5" {{ $caraDagang == '5' ? 'selected' : '' }}>5 - KMD Tunai - Pembayaran Kemudian / Open Account Tunai</option>
                                                <option value="6" {{ $caraDagang == '6' ? 'selected' : '' }}>6 - SLC - Sight Letter of Credit</option>
                                                <option value="7" {{ $caraDagang == '7' ? 'selected' : '' }}>7 - ULC - Usance Letter of Credit</option>
                                                <option value="8" {{ $caraDagang == '8' ? 'selected' : '' }}>8 - RLC - Red Clause Letter of Credit</option>
                                                <option value="9" {{ $caraDagang == '9' ? 'selected' : '' }}>9 - WSI - Wessel Inkaso / Collection Draft</option>
                                                <option value="10" {{ $caraDagang == '10' ? 'selected' : '' }}>10 - KON - Konsinyasi / Consignment</option>
                                                <option value="11" {{ $caraDagang == '11' ? 'selected' : '' }}>11 - ICA - Inter Company Account</option>
                                                <option value="12" {{ $caraDagang == '12' ? 'selected' : '' }}>12 - PDN Tunai - Pembayaran di Dalam Negeri Tunai</option>
                                                <option value="13" {{ $caraDagang == '13' ? 'selected' : '' }}>13 - TT - Pembayaran di Dalam Negeri melalui Telegraph Transfer</option>
                                                <option value="14" {{ $caraDagang == '14' ? 'selected' : '' }}>14 - NCV - Dilakukan tanpa pembayaran</option>
                                                <option value="15" {{ $caraDagang == '15' ? 'selected' : '' }}>15 - Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Cara Pembayaran</label>
                                        <div class="col-sm-8">
                                            <select name="kodeCaraBayar" class="form-control form-control-sm select2bs4">
                                                @php $caraBayar = $dataDetail['kodeCaraBayar'] ?? '' @endphp
                                                <option value="">Pilih Cara Pembayaran</option>
                                                <option value="1" {{ $caraBayar == '1' ? 'selected' : '' }}>1 - BIASA/TUNAI</option>
                                                <option value="2" {{ $caraBayar == '2' ? 'selected' : '' }}>2 - BERKALA</option>
                                                <option value="3" {{ $caraBayar == '3' ? 'selected' : '' }}>3 - DENGAN JAMINAN</option>
                                                <option value="4" {{ $caraBayar == '4' ? 'selected' : '' }}>4 - PERHITUNGAN KEMUDIAN</option>
                                                <option value="5" {{ $caraBayar == '5' ? 'selected' : '' }}>5 - KONSINYASI (CONSIGNMENT)</option>
                                                <option value="6" {{ $caraBayar == '6' ? 'selected' : '' }}>6 - USANCE LETTER OF CREDIT</option>
                                                <option value="7" {{ $caraBayar == '7' ? 'selected' : '' }}>7 - RED CLAUSE LETTER OF CREDIT</option>
                                                <option value="8" {{ $caraBayar == '8' ? 'selected' : '' }}>8 - INTER-COMPANY ACCOUNT</option>
                                                <option value="9" {{ $caraBayar == '9' ? 'selected' : '' }}>9 - GABUNGAN/LAINNYA</option>
                                                <option value="10" {{ $caraBayar == '10' ? 'selected' : '' }}>10 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA BERTAHAP</option>
                                                <option value="11" {{ $caraBayar == '11' ? 'selected' : '' }}>11 - PEMBAYARAN KEMUDIAN (OPEN ACCOUNT) SECARA TUNAI</option>
                                                <option value="12" {{ $caraBayar == '12' ? 'selected' : '' }}>12 - DILAKUKAN DI DN DENGAN PEMBAYARAN UANG TUNAI</option>
                                                <option value="13" {{ $caraBayar == '13' ? 'selected' : '' }}>13 - DILAKUKAN DI DN DENGAN PEMBAYARAN MELALUI TELEGRAPH</option>
                                                <option value="14" {{ $caraBayar == '14' ? 'selected' : '' }}>14 - DILAKUKAN TANPA PEMBAYARAN</option>
                                                <option value="15" {{ $caraBayar == '15' ? 'selected' : '' }}>15 - PEMBAYARAN DIMUKA (ADVANCE PAYMENT)</option>
                                                <option value="16" {{ $caraBayar == '16' ? 'selected' : '' }}>16 - SIGHT LETTER OF CREDIT</option>
                                                <option value="17" {{ $caraBayar == '17' ? 'selected' : '' }}>17 - INKASO (COLLECTION DRAFT)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Komoditas</label>
                                        <div class="col-sm-8">
                                            <select name="flagMigas" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Komoditas</option>
                                                <option value="2" {{ ($dataDetail['flagMigas'] ?? '') == '2' ? 'selected' : '' }}>2 - NON MIGAS</option>
                                                <option value="1" {{ ($dataDetail['flagMigas'] ?? '') == '1' ? 'selected' : '' }}>1 - MIGAS</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Barang Kiriman & Curah</label>
                                        <div class="col-sm-4 pr-1">
                                            <select name="flagBarkir" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Barang Kiriman</option>
                                                <option value="T" {{ ($dataDetail['flagBarkir'] ?? '') == 'T' ? 'selected' : '' }}>T - Non Kiriman</option>
                                                <option value="Y" {{ ($dataDetail['flagBarkir'] ?? '') == 'Y' ? 'selected' : '' }}>Y - Kiriman</option>
                                            </select>
                                        </div>
                                        <div class="col-sm-4 pl-1">
                                            <select name="flagCurah" class="form-control form-control-sm select2bs4">
                                                <option value="">Pilih Barang Curah</option>
                                                <option value="2" {{ ($dataDetail['flagCurah'] ?? '') == '2' ? 'selected' : '' }}>2 - Non Curah</option>
                                                <option value="1" {{ ($dataDetail['flagCurah'] ?? '') == '1' ? 'selected' : '' }}>1 - Curah</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 mt-4 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #0080ff;">Data PKB (Pemberitahuan Kesiapan Barang)</div>
                        <div class="card-body bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <fieldset class="border rounded px-3 pb-3 mb-3 bg-white">
                                        <legend class="w-auto px-2 mb-0 text-dark font-weight-bold" style="font-size: 13px;">Permintaan Pemeriksaan</legend>
                                        <div class="form-group row mb-2 mt-2">
                                            <label class="col-sm-4 col-form-label text-sm">Tanggal PKB</label>
                                            <div class="col-sm-8"><input type="date" class="form-control form-control-sm" name="kesiapanBarang[0][tanggalPkb]" value="{{ $dataDetail['kesiapanBarang'][0]['tanggalPkb'] ?? date('Y-m-d') }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Alamat Barang</label>
                                            <div class="col-sm-8"><textarea class="form-control form-control-sm " name="kesiapanBarang[0][alamat]" rows="2">{{ $dataDetail['kesiapanBarang'][0]['alamat'] ?? '' }}</textarea></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Contact Person (PIC)</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][namaPic]" value="{{ $dataDetail['kesiapanBarang'][0]['namaPic'] ?? '' }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">No. Telp PIC</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][nomorTelpPic]" value="{{ $dataDetail['kesiapanBarang'][0]['nomorTelpPic'] ?? '' }}"></div>
                                        </div>
                                    </fieldset>
                                </div>
                                <div class="col-md-6">
                                    <fieldset class="border rounded px-3 pb-3 mb-3 bg-white">
                                        <legend class="w-auto px-2 mb-0 text-dark font-weight-bold" style="font-size: 13px;">Kondisi & Tempat Siap Periksa</legend>
                                        <div class="form-group row mb-2 mt-2">
                                            <label class="col-sm-4 col-form-label text-sm">Waktu Siap Periksa</label>
                                            <div class="col-sm-8"><input type="datetime-local" class="form-control form-control-sm" name="kesiapanBarang[0][waktuSiapPeriksa]" value="{{ isset($dataDetail['kesiapanBarang'][0]['waktuSiapPeriksa']) ? date('Y-m-d H:i', strtotime($dataDetail['kesiapanBarang'][0]['waktuSiapPeriksa'])) : date('Y-m-d H:i') }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Lokasi Siap Periksa</label>
                                            <div class="col-sm-8"><input type="text" class="form-control form-control-sm " name="kesiapanBarang[0][lokasiSiapPeriksa]" value="{{ $dataDetail['kesiapanBarang'][0]['lokasiSiapPeriksa'] ?? '' }}"></div>
                                        </div>
                                        <div class="form-group row mb-2">
                                            <label class="col-sm-4 col-form-label text-sm">Jenis Gudang Simpan</label>
                                            <div class="col-sm-8">
                                                <select class="form-control form-control-sm select2bs4" name="kesiapanBarang[0][kodeJenisGudang]">
                                                    <option value="">Pilih Tempat Simpan</option>
                                                    <option value="2" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '2' ? 'selected' : '' }}>2 - GUDANG PABRIK</option>
                                                    <option value="1" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '1' ? 'selected' : '' }}>1 - GUDANG VEEM</option>
                                                    <option value="3" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '3' ? 'selected' : '' }}>3 - GUDANG KONSOLIDASI</option>
                                                    <option value="4" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisGudang'] ?? '') == '4' ? 'selected' : '' }}>4 - LAINNYA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group row mb-0">
                                            <label class="col-sm-4 col-form-label text-sm">Jenis Barang</label>
                                            <div class="col-sm-8">
                                                <select class="form-control form-control-sm select2bs4" name="kesiapanBarang[0][kodeJenisBarang]">
                                                    <option value="">Pilih Jenis Barang</option>
                                                    <option value="1" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisBarang'] ?? '') == '1' ? 'selected' : '' }}>1 - BARANG EKSPOR GABUNGAN</option>
                                                    <option value="2" {{ ($dataDetail['kesiapanBarang'][0]['kodeJenisBarang'] ?? '') == '2' ? 'selected' : '' }}>2 - BAHAN/BARANG ASAL IMP FASILITAS</option>
                                                </select>
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Eksportir</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[2][kodeEntitas]" value="2">
                                    <input type="hidden" name="entitas[2][seriEntitas]" value="1">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[2][kodeJenisIdentitas]" class="form-control form-control-sm">
                                                    <option value="6" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '6') == '6' ? 'selected' : '' }}>6 - NPWP 16 DIGIT</option>
                                                    <option value="5" {{ ($dataDetail['entitas'][2]['kodeJenisIdentitas'] ?? '') == '5' ? 'selected' : '' }}>5 - NPWP 15 DIGIT</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1">
                                                <input type="text" name="entitas[2][nomorIdentitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['nomorIdentitas'] ?? '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" name="entitas[2][nitku]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['nitku'] ?? '' }}" placeholder="NITKU 22 Digit">
                                            <div class="input-group-append"><button class="btn btn-primary" type="button"><i class="fas fa-sync-alt"></i></button></div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[2][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][2]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[2][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][2]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Status</label>
                                        <select name="entitas[2][statusEntitas]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Status</option>
                                            <option value="1" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '1' ? 'selected' : '' }}>KOPERASI</option>
                                            <option value="2" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '2' ? 'selected' : '' }}>PMDN (MIGAS)</option>
                                            <option value="3" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '3' ? 'selected' : '' }}>PMDN (NON MIGAS)</option>
                                            <option value="4" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '4' ? 'selected' : '' }}>PMA (MIGAS)</option>
                                            <option value="5" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '5' ? 'selected' : '' }}>PMA (NON MIGAS)</option>
                                            <option value="6" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '6' ? 'selected' : '' }}>BUMN</option>
                                            <option value="7" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '7' ? 'selected' : '' }}>BUMD</option>
                                            <option value="8" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '8' ? 'selected' : '' }}>PERORANGAN</option>
                                            <option value="9" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '9' ? 'selected' : '' }}>USAHA KECIL MIKRO DAN MENENGAH</option>
                                            <option value="10" {{ ($dataDetail['entitas'][2]['statusEntitas'] ?? '') == '10' ? 'selected' : '' }}>LAINNYA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                                    <span>Pembeli <i class="fas fa-question-circle text-light"></i></span>
                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2 ml-auto" id="btn-salin-penerima" title="Salin Data Penerima"><i class="fas fa-copy text-primary"></i> Salin Penerima</button>
                                </div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[6][kodeEntitas]" value="6">
                                    <input type="hidden" name="entitas[6][seriEntitas]" value="2">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[6][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][6]['namaEntitas'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[6][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][6]['alamatEntitas'] ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Negara Tujuan</label>
                                        <select name="entitas[6][kodeNegara]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Negara</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][6]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Penerima</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[8][kodeEntitas]" value="8">
                                    <input type="hidden" name="entitas[8][seriEntitas]" value="3">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama</label>
                                        <input type="text" name="entitas[8][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][8]['namaEntitas'] ?? $header->supplier ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[8][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][8]['alamatEntitas'] ?? $header->alamat_supplier ?? '' }}</textarea>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">Negara</label>
                                        <select name="entitas[8][kodeNegara]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Negara</option>
                                            @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['entitas'][8]['kodeNegara'] ?? ''])
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pihak Yang Melakukan Konsolidasi</div>
                                <div class="card-body">
                                    <input type="hidden" name="entitas[23][kodeEntitas]" value="23">
                                    <input type="hidden" name="entitas[23][seriEntitas]" value="4">
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Kategori</label>
                                        <select name="entitas[23][kodeKategoriKonsolidator]" class="form-control form-control-sm select2bs4 ">
                                            <option value="">Pilih Kategori</option>
                                            <option value="1" {{ ($dataDetail['entitas'][23]['kodeKategoriKonsolidator'] ?? '') == '1' ? 'selected' : '' }}>KONSOLIDATOR</option>
                                            <option value="2" {{ ($dataDetail['entitas'][23]['kodeKategoriKonsolidator'] ?? '') == '2' ? 'selected' : '' }}>EKSPORTIR MANDIRI</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas & NITKU</label>
                                        <div class="row">
                                            <div class="col-4 pr-1">
                                                <select name="entitas[23][kodeJenisIdentitas]" class="form-control form-control-sm">
                                                    <option value="6">NPWP 16</option>
                                                    <option value="5">NPWP 15</option>
                                                </select>
                                            </div>
                                            <div class="col-8 pl-1"><input type="text" name="entitas[23][nomorIdentitas]" class="form-control form-control-sm " placeholder="No. Identitas" value="{{ $dataDetail['entitas'][23]['nomorIdentitas'] ?? '' }}"></div>
                                        </div>
                                        <input type="text" name="entitas[23][nitku]" class="form-control form-control-sm  mt-1" placeholder="NITKU 22 Digit" value="{{ $dataDetail['entitas'][23]['nitku'] ?? '' }}">
                                    </div>
                                    <div class="form-group mb-2"><label class="small mb-0">Nama</label><input type="text" name="entitas[23][namaEntitas]" class="form-control form-control-sm " value="{{ $dataDetail['entitas'][23]['namaEntitas'] ?? '' }}"></div>
                                    <div class="form-group mb-0"><label class="small mb-0">Alamat Konsolidasi</label><textarea name="entitas[23][alamatEntitas]" class="form-control form-control-sm " rows="2">{{ $dataDetail['entitas'][23]['alamatEntitas'] ?? '' }}</textarea></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PEMILIK BARANG (APPEND INLINE) -->
                    <div class="card shadow-sm mt-2 border">
                        <div class="card-header text-dark fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                            <span>Pemilik Barang </span>
                            <button type="button" id="btn-add-pemilik" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Pemilik"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="bg-light text-center border-bottom">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="25%">Nomor Identitas</th>
                                        <th width="35%">Alamat</th>
                                        <th width="30%">Nama & Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-pemilik">
                                    @php $pemiliks = $dataDetail['pemilik'] ?? []; @endphp
                                    @forelse($pemiliks as $pIndex => $pem)
                                    <tr>
                                        <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $pIndex + 1 }}" readonly></td>
                                        <td class="p-2">
                                            <select name="pemilik[{{ $pIndex }}][jenisId]" class="form-control form-control-sm mb-1 ">
                                                <option value="6" {{ ($pem['jenisId'] ?? '') == '6' ? 'selected' : '' }}>NPWP 16 DIGIT</option>
                                                <option value="5" {{ ($pem['jenisId'] ?? '') == '5' ? 'selected' : '' }}>NPWP 15 DIGIT</option>
                                                <option value="2" {{ ($pem['jenisId'] ?? '') == '2' ? 'selected' : '' }}>Paspor</option>
                                                <option value="3" {{ ($pem['jenisId'] ?? '') == '3' ? 'selected' : '' }}>KTP</option>
                                            </select>
                                            <input type="text" name="pemilik[{{ $pIndex }}][noId]" class="form-control form-control-sm " value="{{ $pem['noId'] ?? '' }}" placeholder="No. Identitas">
                                        </td>
                                        <td class="p-2"><textarea name="pemilik[{{ $pIndex }}][alamat]" class="form-control form-control-sm " rows="2" placeholder="Alamat">{{ $pem['alamat'] ?? '' }}</textarea></td>
                                        <td class="p-2 align-middle">
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="pemilik[{{ $pIndex }}][nama]" class="form-control form-control-sm " value="{{ $pem['nama'] ?? '' }}" placeholder="Nama Pemilik">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-pemilik"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr class="no-data-row"><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>No Data</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Dokumen Pendukung</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="25%">Kode Dokumen</th>
                                    <th width="25%">Nomor Dokumen</th>
                                    <th width="15%">Tgl Dokumen</th>
                                    <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @php
                                        $dokumens = $dataDetail['dok'] ?? [];
                                    @endphp
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td>
                                                <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                    <option value="">-- Pilih Kode --</option>
                                                    @foreach($referensiDokumen as $val => $text)
                                                        {{-- Gunakan kodeDokumen (baru) atau kode (lama) --}}
                                                        <option value="{{ $val }}" {{ ($dok['kodeDokumen'] ?? $dok['kode'] ?? '') == $val ? 'selected' : '' }}>
                                                            {{ $val }} - {{ $text }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm"
                                                    value="{{ $dok['nomorDokumen'] ?? $dok['nomor'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm"
                                                    value="{{ $dok['tanggalDokumen'] ?? $dok['tgl'] ?? '' }}">
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pengangkut" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Rincian Rute Pengangkutan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tempat Penimbunan </label>
                                        <div class="col-sm-8">
                                            <input type="text" name="kodeTps" class="form-control form-control-sm " placeholder="Contoh: G001" value="{{ $dataDetail['kodeTps'] ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Muat Asal</label>
                                        <div class="col-sm-8">
                                            <select name="kodePelMuat" class="form-control form-control-sm select2-pelabuhan">
                                                @if(!empty($dataDetail['kodePelMuat']))
                                                    <option value="{{ $dataDetail['kodePelMuat'] }}" selected>{{ $dataDetail['kodePelMuat'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Jenis Pengangkutan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeJenisPengangkutan" class="form-control form-control-sm select2bs4 ">
                                                <option value="">-- Pilih Jenis Pengangkutan --</option>
                                                <option value="1" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '1' ? 'selected' : '' }}>1 - SATU SARANA ANGKUT</option>
                                                <option value="2" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '2' ? 'selected' : '' }}>2 - INSTALASI/PIPA/TRANSMISI</option>
                                                <option value="3" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '3' ? 'selected' : '' }}>3 - ANGKUT LANJUT</option>
                                                <option value="4" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '4' ? 'selected' : '' }}>4 - ANGKUT LANJUT MULTIMODA</option>
                                                <option value="5" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '5' ? 'selected' : '' }}>5 - BARANG BAWAAN PENUMPANG / AWAK SARKUT</option>
                                                <option value="6" {{ ($dataDetail['kodeJenisPengangkutan'] ?? '') == '6' ? 'selected' : '' }}>6 - SARANA ANGKUT LAINNYA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Pelabuhan Tujuan</label>
                                        <div class="col-sm-8">
                                            <select name="kodePelTujuan" class="form-control form-control-sm select2-pelabuhan">
                                                @if(!empty($dataDetail['kodePelTujuan']))
                                                    <option value="{{ $dataDetail['kodePelTujuan'] }}" selected>{{ $dataDetail['kodePelTujuan'] }}</option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Negara Tujuan Ekspor</label>
                                        <div class="col-sm-8">
                                            <select name="kodeNegaraTujuan" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Negara</option>
                                                @include('export-import.dokumen-pabean.options_negara', ['selected' => $dataDetail['kodeNegaraTujuan'] ?? ''])
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tgl Perkiraan Ekspor</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalEkspor" class="form-control form-control-sm " value="{{ $dataDetail['tanggalEkspor'] ?? '' }}"></div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Lokasi Pemeriksaan</label>
                                        <div class="col-sm-8">
                                            <select name="kodeLokasi" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Lokasi Pemeriksaan</option>
                                                <option value="1" {{ (old('kodeLokasi') == '1' || ($dataDetail['kodeLokasi'] ?? '') == '1') ? 'selected' : '' }}>1 - KAWASAN PABEAN TEMPAT PEMUATAN</option>
                                                <option value="2" {{ (old('kodeLokasi') == '2' || ($dataDetail['kodeLokasi'] ?? '') == '2') ? 'selected' : '' }}>2 - GUDANG EKSPORTIR</option>
                                                <option value="3" {{ (old('kodeLokasi') == '3' || ($dataDetail['kodeLokasi'] ?? '') == '3') ? 'selected' : '' }}>3 - TEMPAT LAIN YANG DIIZINKAN</option>
                                                <option value="4" {{ (old('kodeLokasi') == '4' || ($dataDetail['kodeLokasi'] ?? '') == '4') ? 'selected' : '' }}>4 - TEMPAT PENIMBUNAN SEMENTARA</option>
                                                <option value="5" {{ (old('kodeLokasi') == '5' || ($dataDetail['kodeLokasi'] ?? '') == '5') ? 'selected' : '' }}>5 - TEMPAT PENIMBUNAN PABEAN</option>
                                                <option value="6" {{ (old('kodeLokasi') == '6' || ($dataDetail['kodeLokasi'] ?? '') == '6') ? 'selected' : '' }}>6 - TEMPAT PENIMBUNAN BERIKAT</option>
                                                <option value="7" {{ (old('kodeLokasi') == '7' || ($dataDetail['kodeLokasi'] ?? '') == '7') ? 'selected' : '' }}>7 - TEMPAT PENIMBUTAN LAINNYA</option>
                                                <option value="8" {{ (old('kodeLokasi') == '8' || ($dataDetail['kodeLokasi'] ?? '') == '8') ? 'selected' : '' }}>8 - GUDANG KONSOLIDATOR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-2">
                                        <label class="col-sm-4 col-form-label text-sm">Tanggal Periksa</label>
                                        <div class="col-sm-8"><input type="date" name="tanggalPeriksa" class="form-control form-control-sm " value="{{ $dataDetail['tanggalPeriksa'] ?? '' }}"></div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <label class="col-sm-4 col-form-label text-sm">Kantor BC Pemeriksa</label>
                                        <div class="col-sm-8">
                                            <select name="kodeKantorPeriksa" class="form-control form-control-sm select2bs4 ">
                                                <option value="">Pilih Kantor</option>
                                                @foreach($kantorList as $val => $label)
                                                    <option value="{{ $val }}" {{ ($dataDetail['kodeKantorPeriksa'] ?? '050500') == $val ? 'selected' : '' }}>{{ $val }} - {{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Daftar Sarana Pengangkut</span>
                            <button type="button" id="btn-add-sarkut" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Sarana Angkut"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-light text-center" style="font-size: 12px;">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="30%">Nama Sarana Angkut</th>
                                        <th width="25%">No. Pengangkut (Voy/Flight)</th>
                                        <th width="20%">Cara Angkut</th>
                                        <th width="15%">Kode Bendera</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-sarkut">
                                    @php $pengangkuts = $dataDetail['pengangkut'] ?? []; @endphp
                                    @forelse($pengangkuts as $sIndex => $sarkut)
                                    <tr>
                                        <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $sIndex + 1 }}" readonly></td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][namaPengangkut]" class="form-control form-control-sm " value="{{ $sarkut['namaPengangkut'] ?? '' }}"></td>
                                        <td><input type="text" name="pengangkut[{{ $sIndex }}][nomorPengangkut]" class="form-control form-control-sm " value="{{ $sarkut['nomorPengangkut'] ?? '' }}"></td>
                                        <td>
                                            <select name="pengangkut[{{ $sIndex }}][kodeCaraAngkut]" class="form-control form-control-sm ">
                                                <option value="1" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '1' ? 'selected' : '' }}>1 - LAUT</option>
                                                <option value="4" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '4' ? 'selected' : '' }}>4 - UDARA</option>
                                                <option value="3" {{ ($sarkut['kodeCaraAngkut'] ?? '') == '3' ? 'selected' : '' }}>3 - DARAT</option>
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="pengangkut[{{ $sIndex }}][kodeBendera]" class="form-control form-control-sm  text-uppercase" value="{{ $sarkut['kodeBendera'] ?? '' }}">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty

                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Data Kemasan Ekspor</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="20%">Jumlah Kemasan</th>
                                        <th width="40%">Jenis Kemasan</th>
                                        <th width="30%">Merek Kemasan</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @php
                                        $kemasans = $dataDetail['kemasan'] ?? [];
                                        if (empty($kemasans)) {
                                            $kemasans[] = ['jumlahKemasan' => $header->qty_karton ?? "", 'kodeJenisKemasan' => 'CT', 'merkKemasan' => '-'];
                                        }
                                    @endphp
                                    @foreach($kemasans as $index => $kemasan)
                                    <tr>
                                        <td><input type="number" step="any" name="kemasan[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm  input-decimal" value="{{ $kemasan['jumlahKemasan'] ?? $kemasan['jumlah'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4 ">
                                                <option value="">-- Pilih --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? $kemasan['kode'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $index }}][merkKemasan]" class="form-control form-control-sm " value="{{ $kemasan['merkKemasan'] ?? $kemasan['merk'] ?? '-' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Data Peti Kemas / Kontainer</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $kontainers = $dataDetail['kontainer'] ?? [];



                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="30%">Nomor Kontainer</th>
                                        <th width="20%">Jenis</th>
                                        <th width="25%">Tipe</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $kIndex => $kont)
                                    <tr>
                                        <td><input type="text" name="kontainer[{{ $kIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
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
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Rincian Keuangan & Nilai Ekspor</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2"><label>Jenis Valuta</label>
                                        <select name="kodeValuta" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Valuta</option>
                                            @foreach($listValuta as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeValuta'] ?? 'IDR') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2"><label>NDPBM (Kurs Fiskal)</label><input type="number" step="any" name="ndpbm" class="form-control form-control-sm " value="{{ $dataDetail['ndpbm'] ?? '' }}"></div>
                                    <div class="form-group mb-2"><label>Cara Penyerahan (Incoterm)</label>
                                        <select name="kodeIncoterm" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih --</option>
                                            @foreach($listIncoterm as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['kodeIncoterm'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 border-left">
                                    <div class="form-group mb-2"><label>Nilai FOB Pengajuan</label><input type="number" step="any" name="fob" class="form-control form-control-sm " value="{{ $dataDetail['fob'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Freight</label><input type="number" step="any" name="freight" class="form-control form-control-sm " value="{{ $dataDetail['freight'] ?? '0.00' }}"></div>
                                    <div class="row">
                                        <div class="col-5 form-group mb-2 pr-1"><label>Tempat Asuransi</label>
                                            <select name="kodeAsuransi" class="form-control form-control-sm "><option value="LN" {{ ($dataDetail['kodeAsuransi'] ?? 'LN') == 'LN' ? 'selected' : '' }}>LUAR NEGERI</option><option value="DN" {{ ($dataDetail['kodeAsuransi'] ?? '') == 'DN' ? 'selected' : '' }}>DALAM NEGERI</option></select>
                                        </div>
                                        <div class="col-7 form-group mb-2 pl-1"><label>Nilai Asuransi</label><input type="number" step="any" name="asuransi" class="form-control form-control-sm " value="{{ $dataDetail['asuransi'] ?? '0.00' }}"></div>
                                    </div>
                                </div>
                                <div class="col-md-4 border-left">
                                    <div class="form-group mb-2"><label>Berat Kotor / Bruto (Kg)</label><input type="number" step="any" name="bruto" class="form-control form-control-sm " value="{{ $dataDetail['bruto'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Berat Bersih / Netto (Kg)</label><input type="number" step="any" name="netto" class="form-control form-control-sm " value="{{ $dataDetail['netto'] ?? '0.00' }}"></div>
                                    <div class="form-group mb-2"><label>Nilai Jasa Maklon</label><input type="number" step="any" name="nilaiMaklon" class="form-control form-control-sm" value="{{ $dataDetail['nilaiMaklon'] ?? '0.00' }}"></div>

                                    <div class="form-group mb-3 mt-3">
                                        <div class="row align-items-center">
                                            <div class="col-sm-6">
                                                <label class="text-sm font-weight-bold mb-0">
                                                    <input type="checkbox" id="check-pph" name="isNilaiPph" class="mr-1" {{ !empty($dataDetail['nilaiPph']) ? 'checked' : '' }}>
                                                    PPh Ps.22 Ekspor
                                                </label>
                                            </div>
                                            <div class="col-sm-6 text-right">
                                                <div class="fw-bold text-success mb-0" style="font-size: 14px;" id="text-nilai-pph">Rp 0,00</div>
                                                <input type="hidden" name="nilaiPph" id="val-nilai-pph" value="{{ $dataDetail['nilaiPph'] ?? '0.00' }}">
                                            </div>
                                            <div class="col-12 mt-1">
                                                <small class="text-muted font-italic" style="font-size: 10px;">* PPh = 1,5% x FOB x Kurs (NDPBM)</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0"><label>Total Pungutan Sawit</label><input type="number" step="any" name="totalDanaSawit" class="form-control form-control-sm" value="{{ $dataDetail['totalDanaSawit'] ?? '0.00' }}"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Bank Devisa Hasil Ekspor</span>
                            <button type="button" id="btn-add-bank" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto" title="Tambah Bank"><i class="fas fa-plus text-primary"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="bg-light text-center" style="font-size: 12px;">
                                    <tr>
                                        <th width="10%">Seri</th>
                                        <th width="30%">Kode Bank</th>
                                        <th width="60%">Nama Bank Devisa</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-bank">
                                    @php $banks = $dataDetail['bankDevisa'] ?? []; @endphp
                                    @forelse($banks as $bIndex => $bank)
                                    <tr>
                                        <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $bIndex + 1 }}" readonly></td>
                                        <td>
                                            <select name="bankDevisa[{{ $bIndex }}][kodeBank]" class="form-control form-control-sm select2bs4 select-bank">
                                                <option value="">Pilih Bank</option>
                                                @include('export-import.dokumen-pabean.options_bank', ['selected' => $bank['kodeBank'] ?? '', 'selectedName' => $bank['namaBank'] ?? ''])
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <input type="text" name="bankDevisa[{{ $bIndex }}][namaBank]" class="form-control form-control-sm input-nama-bank" value="{{ $bank['namaBank'] ?? '' }}" placeholder="Nama Bank">
                                                <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ================= TAB 7: BARANG ================= -->
                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionBarang">
                                @foreach($items as $index => $item)
                                @php $draftItem = $dataDetail['barang'][$index] ?? []; @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header bg-light py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold" style="font-size: 13px; color: white;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-muted icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">

                                            <!-- Hidden inputs wajib untuk API -->
                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">
                                            <input type="hidden" name="barang[{{ $index }}][kodeDokumen]" value="30">

                                            <!-- Layout 2 Kolom Sesuai Portal CEISA 4.0 -->
                                            <div class="row">
                                                <!-- KOLOM KIRI -->
                                                <div class="col-md-6 pr-md-4">
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Seri</label>
                                                        <div class="col-sm-8">
                                                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $index + 1 }}" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Pos Tarif/HS </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '48191000' }}" placeholder="Masukkan Pos Tarif/HS">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Kode Barang </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                            <input type="text" name="barang[{{ $index }}][idItem]" class="form-control form-control-sm hidden" value="{{ $item->id_item ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Uraian Jenis Barang </label>
                                                        <div class="col-sm-8">
                                                            <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm" rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Merek </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Tipe </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Ukuran </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Negara Asal Barang </label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeNegaraAsal]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Negara Asal Barang</option>
                                                                @include('export-import.dokumen-pabean.options_negara', ['selected' => $draftItem['kodeNegaraAsal'] ?? 'ID'])
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Daerah Asal Barang </label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeDaerahAsal]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Daerah Asal Barang</option>
                                                                @include('export-import.dokumen-pabean.options_daerah', ['selected' => $draftItem['kodeDaerahAsal'] ?? ''])
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- KOLOM KANAN -->
                                                <div class="col-md-6 pl-md-4">
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Jumlah dan Satuan Barang </label>
                                                        <div class="col-sm-4 pr-1">
                                                            <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                        </div>
                                                        <div class="col-sm-4 pl-1">
                                                            <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Kode Satuan</option>
                                                                @foreach($listSatuanBarang as $k => $v)
                                                                    <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit) == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Kemasan </label>
                                                        <div class="col-sm-4 pr-1">
                                                            <input type="number" step="any" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                        </div>
                                                        <div class="col-sm-4 pl-1">
                                                            <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Kode Jenis Kemasan</option>
                                                                @foreach($listJenisKemasan as $k => $v)
                                                                    <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Nilai Ekspor (Nilai FOB) </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][fob]" class="form-control form-control-sm" value="{{ $draftItem['fob'] ?? (float)($item->qty * $item->price) }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Berat Bersih (Kg) </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? (float) ($item->nw ?? $item->netto ?? 0) }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Nilai Ekspor per satuan barang </label>
                                                        <div class="col-sm-8">
                                                            <input type="number" step="any" name="barang[{{ $index }}][hargaEkspor]" class="form-control form-control-sm" value="{{ $draftItem['hargaEkspor'] ?? (float)$item->price }}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group mb-2 row">
                                                        <label class="col-sm-4 col-form-label small">Jenis Ekspor</label>
                                                        <div class="col-sm-8">
                                                            <select name="barang[{{ $index }}][kodeJenisEkspor]" class="form-control form-control-sm select2bs4">
                                                                <option value="">Pilih Jenis Ekspor</option>
                                                                @php $kJe = $draftItem['kodeJenisEkspor'] ?? '1'; @endphp
                                                                <option value="1" {{ $kJe == '1' ? 'selected' : '' }}>1 - Ekspor Biasa</option>
                                                                <option value="2" {{ $kJe == '2' ? 'selected' : '' }}>2 - Berkala</option>
                                                                <option value="3" {{ $kJe == '3' ? 'selected' : '' }}>3 - Fasilitas</option>
                                                                <option value="4" {{ $kJe == '4' ? 'selected' : '' }}>4 - Re-Import</option>
                                                                <option value="5" {{ $kJe == '5' ? 'selected' : '' }}>5 - Re-Ekspor</option>
                                                                <option value="6" {{ $kJe == '6' ? 'selected' : '' }}>6 - Ekspor Sementara</option>
                                                                <option value="7" {{ $kJe == '7' ? 'selected' : '' }}>7 - Ekspor Gabungan</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr class="mt-4 mb-4">

                                            <!-- TABEL DOKUMEN FASILITAS/LARTAS (DALAM BARANG) -->
                                            <div class="card shadow-sm mb-4 border">
                                                <div class="card-header text-dark fw-bold d-flex justify-content-between align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                                                    <span>Dokumen Fasilitas/Lartas</span>
                                                    <button type="button" class="btn btn-sm btn-primary py-0 px-2 btn-add-dok-fasilitas" data-itemidx="{{ $index }}"><i class="fas fa-plus"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless mb-0">
                                                            <thead class="bg-light text-center border-bottom">
                                                                <tr>
                                                                    <th width="5%">Seri</th>
                                                                    <th width="25%">Jenis</th>
                                                                    <th width="20%">Nomor</th>
                                                                    <th width="15%">Tanggal</th>
                                                                    <th width="15%">Fasilitas</th>
                                                                    <th width="15%">No Urut Izin</th>
                                                                    <th width="5%">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="tbody-dok-fasilitas-{{ $index }}">
                                                                @php $dokFasilitas = $draftItem['dokFasilitas'] ?? []; @endphp
                                                                @forelse($dokFasilitas as $fIndex => $fas)
                                                                <tr>
                                                                    <td class="text-center p-2"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $fIndex + 1 }}" readonly></td>
                                                                    <td class="p-2">
                                                                        <select name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][kodeDokumen]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">Pilih</option>
                                                                            @foreach($referensiDokumen as $val => $text)
                                                                                <option value="{{ $val }}" {{ ($fas['kodeDokumen'] ?? '') == $val ? 'selected' : '' }}>{{ $val }} - {{ $text }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][nomorDokumen]" class="form-control form-control-sm" value="{{ $fas['nomorDokumen'] ?? '' }}"></td>
                                                                    <td class="p-2"><input type="date" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][tanggalDokumen]" class="form-control form-control-sm" value="{{ $fas['tanggalDokumen'] ?? '' }}"></td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][kodeFasilitas]" class="form-control form-control-sm" value="{{ $fas['kodeFasilitas'] ?? '' }}"></td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][dokFasilitas][{{ $fIndex }}][seriIjin]" class="form-control form-control-sm" value="{{ $fas['seriIjin'] ?? '' }}"></td>
                                                                    <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-dok-fas"><i class="fas fa-trash-alt"></i></button></td>
                                                                </tr>
                                                                @empty
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- TABEL ENTITAS BARANG (DALAM BARANG) -->
                                            <div class="card shadow-sm mb-0 border">
                                                <div class="card-header text-dark fw-bold d-flex justify-content-between align-items-center px-3 py-2" style="font-size:13px; background-color: #f8f9fa;">
                                                    <span>Entitas Barang</span>
                                                    <button type="button" class="btn btn-sm btn-light border py-0 px-2 btn-add-entitas-barang" data-itemidx="{{ $index }}"><i class="fas fa-plus text-primary"></i> Tambah</button>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless mb-0">
                                                            <thead class="bg-light text-center border-bottom">
                                                                <tr>
                                                                    <th width="10%">Seri</th>
                                                                    <th width="25%">No Identitas</th>
                                                                    <th width="30%">Nama</th>
                                                                    <th width="30%">Alamat</th>
                                                                    <th width="5%">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="tbody-entitas-barang-{{ $index }}">
                                                                @php $entitasBarang = $draftItem['entitasBarang'] ?? []; @endphp
                                                                @forelse($entitasBarang as $ebIndex => $entBrg)
                                                                <tr>
                                                                    <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="{{ $ebIndex + 1 }}" readonly></td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entBrg['nomorIdentitas'] ?? '' }}"></td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][namaEntitas]" class="form-control form-control-sm" value="{{ $entBrg['namaEntitas'] ?? '' }}"></td>
                                                                    <td class="p-2"><input type="text" name="barang[{{ $index }}][entitasBarang][{{ $ebIndex }}][alamatEntitas]" class="form-control form-control-sm" value="{{ $entBrg['alamatEntitas'] ?? '' }}"></td>
                                                                    <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-entitas-brg"><i class="fas fa-trash-alt"></i></button></td>
                                                                </tr>
                                                                @empty
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
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

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Pungutan</span>
                            <button type="button" class="btn btn-sm btn-light btn-add-action py-0 px-2 ml-auto"><i class="fas fa-sync-alt text-primary"></i> Generate</button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0">
                                    <thead class="bg-light text-center border-bottom">
                                        <tr>
                                            <th class="align-middle">Pungutan</th>
                                            <th class="align-middle">Dibayar</th>
                                            <th class="align-middle">Ditanggung Pemerintah</th>
                                            <th class="align-middle">Ditunda</th>
                                            <th class="align-middle">Tidak Dipungut</th>
                                            <th class="align-middle">Dibebaskan</th>
                                            <th class="align-middle">Sudah Dilunasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                            <td class="text-center p-2"><input type="text" class="form-control form-control-sm" disabled></td>
                                        </tr>
                                        <tr><td colspan="7" class="text-center py-4 text-muted">No Data</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Penandatangan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm " value="{{ $dataDetail['namaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm " value="{{ $dataDetail['jabatanTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Kota TTD</label><input type="text" name="kotaTtd" class="form-control form-control-sm " value="{{ $dataDetail['kotaTtd'] ?? '' }}"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm " value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
                            </div>
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

<div class="modal fade" id="modal-tambah-pemilik" tabindex="-1" role="dialog" aria-labelledby="modalTambahPemilikTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title font-weight-bold" id="modalTambahPemilikTitle">Tambah Pemilik Barang</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-2">
                    <label class="small mb-0">Nomor Identitas:</label>
                    <div class="input-group input-group-sm">
                        <select id="modal-pemilik-jenis-identitas" class="form-control" style="max-width:160px;">
                            <option value="6">NPWP 16 DIGIT</option>
                            <option value="5">NPWP 15 DIGIT</option>
                            <option value="2">Paspor</option>
                            <option value="3">KTP</option>
                        </select>
                        <input type="text" id="modal-pemilik-nomor-identitas" class="form-control ">
                    </div>
                </div>
                <div class="form-group mb-2"><label class="small mb-0">Nama</label><input type="text" id="modal-pemilik-nama" class="form-control form-control-sm "></div>
                <div class="form-group mb-0"><label class="small mb-0">Alamat</label><textarea id="modal-pemilik-alamat" class="form-control form-control-sm " rows="3"></textarea></div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Batal</button>
                <button type="button" id="btn-simpan-pemilik" class="btn btn-sm btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="{{ asset('plugins/sweetalert/dist/sweetalert2.all.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

        $('#ceisaTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });

        // ================= DOKUMEN HANDLER =================
        const optDokumenHtml = `
            <option value="">-- Pilih Kode --</option>
            @foreach($referensiDokumen as $val => $text) <option value="{{ $val }}">{{ $val }} - {{ $text }}</option> @endforeach
        `;
        let dokIndex = {{ count($dokumens ?? []) }};
        $('#btn-add-dok').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic ">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm "></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm "></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            dokIndex++;
        });
        $(document).on('click', '.btn-hapus-dok', function() { $(this).closest('tr').remove(); });

        // ================= KEMASAN HANDLER =================
        const optJenisKemasan = `
            <option value="">-- Pilih --</option>
            @foreach($listJenisKemasan as $kKem => $vKem) <option value="{{ $kKem }}">{{ $kKem }} - {{ $vKem }}</option> @endforeach
        `;
        let kemasanIndex = {{ count($kemasans ?? []) }};
        $('#btn-add-kemasan').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm  input-decimal" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic ">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm " value="-"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kemasan').append(htmlTr);
            $(`select[name="kemasan[${kemasanIndex}][kodeJenisKemasan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kemasanIndex++;
        });
        $(document).on('click', '.btn-hapus-kemasan', function() { $(this).closest('tr').remove(); });

        // ================= KONTAINER HANDLER =================
        const optJenisKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listJenisKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optTipeKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listTipeKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        const optUkuranKontainer = `<option value="">-- Pilih --</option>` + `@foreach($listUkuranKontainer as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach`;
        let kontainerIndex = {{ count($kontainers ?? []) }};
        $('#btn-add-kontainer').on('click', function() {
            let htmlTr = `
                <tr>
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm  text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">${optTipeKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">${optUkuranKontainer}</select></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kontainer').append(htmlTr);
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() { $(this).closest('tr').remove(); });

        // ================= SARKUT HANDLER =================
        let sarkutIndex = {{ count($pengangkuts ?? []) }};
        $('#btn-add-sarkut').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${sarkutIndex + 1}" readonly></td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][namaPengangkut]" class="form-control form-control-sm "></td>
                <td><input type="text" name="pengangkut[${sarkutIndex}][nomorPengangkut]" class="form-control form-control-sm "></td>
                <td><select name="pengangkut[${sarkutIndex}][kodeCaraAngkut]" class="form-control form-control-sm select2bs4"><option value="">Pilih Cara Angkut</option>` + `@foreach($listCaraAngkut as $k => $v)<option value="{{ $k }}">{{ $k }} - {{ $v }}</option>@endforeach` + `</select></td>
                <td><div class="input-group input-group-sm"><input type="text" name="pengangkut[${sarkutIndex}][kodeBendera]" class="form-control form-control-sm  text-uppercase"><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-sarkut"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-sarkut').append(tr);
            sarkutIndex++;
        });
        $(document).on('click', '.btn-hapus-sarkut', function() { $(this).closest('tr').remove(); });

        // ================= BANK HANDLER =================
        const optBankHtml = `
            <option value="">Pilih Bank</option>
            @include('export-import.dokumen-pabean.options_bank')
        `;
        let bankIndex = {{ count($banks ?? []) }};
        $('#btn-add-bank').on('click', function() {
            let tr = `<tr>
                <td class="text-center align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${bankIndex + 1}" readonly></td>
                <td><select name="bankDevisa[${bankIndex}][kodeBank]" class="form-control form-control-sm select2bs4-dynamic select-bank">${optBankHtml}</select></td>
                <td><div class="input-group input-group-sm"><input type="text" name="bankDevisa[${bankIndex}][namaBank]" class="form-control form-control-sm input-nama-bank" placeholder="Nama Bank"><div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-bank"><i class="fas fa-trash-alt"></i></button></div></div></td>
            </tr>`;
            $('#tbody-bank').append(tr);
            $(`select[name="bankDevisa[${bankIndex}][kodeBank]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            bankIndex++;
        });
        $(document).on('click', '.btn-hapus-bank', function() { $(this).closest('tr').remove(); });
        $(document).on('change', '.select-bank', function() {
            let selectedOption = $(this).find('option:selected');
            let bankName = selectedOption.data('name') || '';
            if (bankName) {
                $(this).closest('tr').find('.input-nama-bank').val(bankName);
            }
        });

        // ================= JS PEMILIK BARANG =================
        let pemilikIndex = {{ count($pemiliks ?? []) }};
        $('#btn-add-pemilik').on('click', function(e) {
            e.preventDefault();
            $('#tbody-pemilik .no-data-row').remove();

            let tr = `<tr>
                <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${pemilikIndex + 1}" readonly></td>
                <td class="p-2">
                    <select name="pemilik[${pemilikIndex}][jenisId]" class="form-control form-control-sm mb-1 ">
                        <option value="6">NPWP 16 DIGIT</option>
                        <option value="5">NPWP 15 DIGIT</option>
                        <option value="2">Paspor</option>
                        <option value="3">KTP</option>
                    </select>
                    <input type="text" name="pemilik[${pemilikIndex}][noId]" class="form-control form-control-sm " placeholder="No. Identitas">
                </td>
                <td class="p-2"><textarea name="pemilik[${pemilikIndex}][alamat]" class="form-control form-control-sm " rows="2" placeholder="Alamat"></textarea></td>
                <td class="p-2 align-middle">
                    <div class="input-group input-group-sm">
                        <input type="text" name="pemilik[${pemilikIndex}][nama]" class="form-control form-control-sm " placeholder="Nama Pemilik">
                        <div class="input-group-append"><button type="button" class="btn btn-danger btn-hapus-pemilik"><i class="fas fa-trash-alt"></i></button></div>
                    </div>
                </td>
            </tr>`;
            $('#tbody-pemilik').append(tr);
            pemilikIndex++;
        });
        $(document).on('click', '.btn-hapus-pemilik', function() { $(this).closest('tr').remove(); });

        $('#btn-salin-penerima').on('click', function(e) {
            e.preventDefault();
            let nama = $('input[name="entitas[8][namaEntitas]"]').val();
            let alamat = $('textarea[name="entitas[8][alamatEntitas]"]').val();
            let negara = $('select[name="entitas[8][kodeNegara]"]').val();

            $('input[name="entitas[6][namaEntitas]"]').val(nama);
            $('textarea[name="entitas[6][alamatEntitas]"]').val(alamat);
            $('select[name="entitas[6][kodeNegara]"]').val(negara).trigger('change');

            Swal.fire({toast: true, position: 'top-end', icon: 'success', title: 'Data disalin', showConfirmButton: false, timer: 1500});
        });

        // ================= DOKUMEN FASILITAS & ENTITAS (DALAM BARANG) =================
        $(document).on('click', '.btn-add-dok-fasilitas', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-dok-fasilitas-${itemIdx}`);
            tbody.find('.no-data-row').remove();
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <td class="text-center p-2"><input type="text" class="form-control form-control-sm text-center bg-light" value="${rowIdx + 1}" readonly></td>
                <td class="p-2"><select name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeDokumen]" class="form-control form-control-sm select2bs4-dynamic ">${optDokumenHtml}</select></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][nomorDokumen]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="date" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][tanggalDokumen]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeFasilitas]" class="form-control form-control-sm "></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][dokFasilitas][${rowIdx}][seriIjin]" class="form-control form-control-sm"></td>
                <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-danger btn-hapus-dok-fas"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
            $(`select[name="barang[${itemIdx}][dokFasilitas][${rowIdx}][kodeDokumen]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        });
        $(document).on('click', '.btn-hapus-dok-fas', function() { $(this).closest('tr').remove(); });


        // ================= PPH EKSPOR KALKULASI =================
        function calculatePphEkspor() {
            let fob = parseFloat($('input[name="fob"]').val()) || 0;
            let ndpbm = parseFloat($('input[name="ndpbm"]').val()) || 0;
            let isChecked = $('#check-pph').is(':checked');
            let pph = isChecked ? (0.015 * fob * ndpbm) : 0;

            let formattedPph = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 2 }).format(pph);
            $('#text-nilai-pph').text(formattedPph);
            $('#val-nilai-pph').val(pph.toFixed(2));
        }

        $('input[name="fob"], input[name="ndpbm"]').on('input', calculatePphEkspor);
        $('#check-pph').on('change', calculatePphEkspor);
        calculatePphEkspor();

        // ================= ACCORDION & AJAX SUBMIT =================
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

        $('#form-edit-ceisa').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft akan diperbarui.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    this.submit();
                }
            });
        });

       $('.select2-pelabuhan').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Pelabuhan...',
            allowClear: true,
            language: {
                inputTooShort: function (args) {
                    var remain = args.minimum - args.input.length;
                    return "Masukkan " + remain + " karakter atau lebih";
                }
            },
            ajax: {
                url: '{{ route("ceisa.pelabuhan") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) { return { q: params.term }; },
                processResults: function (data) { return { results: data.results }; },
                cache: true
            },
            minimumInputLength: 2
        });

       $('.select2-tps').select2({
            theme: 'bootstrap4',
            placeholder: 'Cari Lokasi TPS...',
            allowClear: true
        });
        // ==========================================
        // ENTITAS BARANG (DALAM RINCIAN BARANG)
        // ==========================================
        $(document).on('click', '.btn-add-entitas-barang', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-entitas-barang-${itemIdx}`);
            tbody.find('.no-data-row').remove();
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <td class="text-center p-2 align-middle"><input type="text" class="form-control form-control-sm text-center bg-light" value="${rowIdx + 1}" readonly></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][nomorIdentitas]" class="form-control form-control-sm" placeholder="No. Identitas"></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][namaEntitas]" class="form-control form-control-sm" placeholder="Nama Entitas"></td>
                <td class="p-2"><input type="text" name="barang[${itemIdx}][entitasBarang][${rowIdx}][alamatEntitas]" class="form-control form-control-sm" placeholder="Alamat"></td>
                <td class="text-center p-2 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-entitas-brg"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
        });
        $(document).on('click', '.btn-hapus-entitas-brg', function() { $(this).closest('tr').remove(); });
    });
</script>
@endsection
