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

    $listSatuanBarang = ['6' => 'small spray', '8' => 'heat lot', '10' => 'group', '13' => 'ration', '14' => 'shot', '15' => 'stick, military', '16' => 'hundred fifteen kg drum', '17' => 'hundred lb drum', '18' => 'fiftyfive gallon (US) drum', '19' => 'tank truck', '20' => 'twenty foot container', '21' => 'forty foot container', '22' => 'decilitre per gram', '24' => 'theoretical pound', '26' => 'actual ton', '28' => 'kilogram per square metre', '29' => 'pound per thousand square foot', '30' => 'horse power day per air dry metric ton', '31' => 'catch weight', '32' => 'kilogram per air dry metric ton', '33' => 'kilopascal square metre per gram', '34' => 'kilopascal per millimetre', '35' => 'millilitre per square centimetre second', '36' => 'cubic foot per minute per square foot', '38' => 'ounce per square foot per 0,01inch', '40' => 'millilitre per second', '43' => 'super bulk bag', '44' => 'fivehundred kg bulk bag', '46' => 'fifty lb bulk bag', '47' => 'fifty lb bag', '48' => 'bulk car load', '53' => 'theoretical kilogram', '54' => 'theoretical tonne', '57' => 'mesh', '58' => 'net kilogram', '60' => 'percent weight', '61' => 'part per billion (US)', '62' => 'percent per 1000 hour', '63' => 'failure rate in time', '64' => 'pound per square inch, gauge', '66' => 'oersted', '71' => 'volt ampere per pound', '72' => 'watt per pound', '73' => 'ampere tum per centimetre', '78' => 'kilogauss', '84' => 'kilopound-force per square inch', '85' => 'foot pound-force', '89' => 'poise', '92' => 'calorie per cubic centimetre', '93' => 'calorie per gram', '94' => 'curl unit', '96' => 'ten thousand gallon (US) tankcar', '97' => 'ten kg drum', '98' => 'fifteen kg drum', '--' => '--', '1C' => 'locomotive count', '1F' => 'train mile', '1I' => 'fixed rate', '1L' => 'total car count', '1M' => 'total car mile', '1X' => 'quarter mile', '2B' => 'radian per second squared', '2C' => 'roentgen', '2H' => 'volt DC', '2I' => 'British thermal unit(international table) per hour', '2J' => 'cubic centimetre per second', '2K' => 'cubic foot per hour', '2L' => 'cubic foot per minute', '2M' => 'centimetre per second', '2P' => 'kilobyte', '2Q' => 'kilobecquerel', '2R' => 'kilocurie', '2U' => 'megagram', '2V' => 'megagram per hour', '2W' => 'bin', '2X' => 'metre per minute', '2Z' => 'millivolt', '3E' => 'pound per pound of product', '3G' => 'pound per piece of product', '4A' => 'bobbin', '4G' => 'microlitre', '4H' => 'micrometre (micron)', '4L' => 'megabyte', '4M' => 'milligram per hour', '4O' => 'microfarad', '4P' => 'newton per metre', '4R' => 'ounce foot', '4T' => 'picofarad', '4U' => 'pound per hour', '4W' => 'ton (US) per hour', '5B' => 'batch', '5C' => 'gallon(US) per thousand', '5E' => 'MMSCF/day', '5G' => 'pump', '5H' => 'stage', '5K' => 'count per minute', '5P' => 'seismic level', '5Q' => 'seismic line', 'A1' => '15 C calorie', 'A11' => 'angstrom', 'A12' => 'astronomical unit', 'A13' => 'attojoule', 'A15' => 'barn per electronvolt', 'A2' => 'ampere per centimetre', 'A20' => 'British thermal unit/second squarefoot d/Rankine', 'A21' => 'British thermal unit (IT) per pound degree Rankine', 'A23' => 'Britishthermalunit/hour square foot degree Rankine', 'A24' => 'candela per square metre', 'A26' => 'coulomb metre', 'A27' => 'coulomb metre squared per volt', 'A28' => 'coulomb per cubic centimetre', 'A3' => 'ampere per millimetre', 'A32' => 'coulomb per mole', 'A33' => 'coulomb per square centimetre', 'A36' => 'cubic centimetre per mole', 'A38' => 'cubic metre per coulomb', 'A4' => 'ampere per square centimetre', 'A41' => 'ampere per square metre', 'A43' => 'deadweight tonnage', 'A44' => 'decalitre', 'A45' => 'decametre', 'A47' => 'decitex', 'A49' => 'denier', 'A51' => 'dyne second per centimetre', 'A52' => 'dyne second per centimetre to the fifth power', 'A56' => 'electronvolt square metre per kilogram', 'A58' => 'erg per centimetre', 'A59' => '8-part cloud cover', 'A6' => 'ampere per square metre kelvin squared', 'A60' => 'erg per cubic centimetre', 'A62' => 'erg per gram second', 'A64' => 'erg per second square centimetre', 'A65' => 'erg per square centimetre second', 'A66' => 'erg square centimetre', 'A67' => 'erg square centimetre per gram', 'A69' => 'farad per metre', 'A7' => 'ampere per square millimetre', 'A70' => 'femtojoule', 'A71' => 'femtometre', 'A73' => 'foot per second squared', 'A74' => 'foot pound-force per second', 'A76' => 'gal', 'A77' => 'Gaussian CGS unit of displacement', 'A78' => 'Gaussian CGS unit of electric current', 'A79' => 'Gaussian CGS unit of electric charge', 'A81' => 'Gaussian CGS unit of electric polarization', 'A82' => 'Gaussian CGS unit of electric potential', 'A83' => 'Gaussian CGS unit of magnetization', 'A85' => 'gigaelectronvolt', 'A87' => 'gigaohm', 'A88' => 'gigaohm metre', 'A9' => 'rate', 'A93' => 'gram per cubic metre', 'A95' => 'gray', 'A96' => 'gray per second', 'AA' => 'ball', 'AB' => 'bulk pack', 'ACR' => 'Acre (4840 yd2)', 'ACT' => 'activity', 'AD' => 'byte', 'AED' => 'United Arab Emirates Dirham', 'AFN' => 'Afghanistan Afghani', 'AH' => 'additional minute', 'AJ' => 'cop', 'AK' => 'fathom', 'AL' => 'access line', 'AMH' => 'Ampere-hour (3,6 kC)', 'ANG' => 'Netherlands Antilles Guilder', 'APZ' => 'Ounce GB,US (31,10348 g)', 'AQ' => 'anti-hemophilic factor (AHF) unit', 'ARE' => 'Are (100m2)', 'AS' => 'assortment', 'ASM' => 'alcoholic strength by mass', 'ASU' => 'alcoholic strength by volume', 'ATT' => 'Technical atmosphere (98066,5 Pa)', 'AUD' => 'Australia Dollar', 'AV' => 'capsule', 'AW' => 'powder filled vial', 'AY' => 'assembly', 'AZN' => 'Azerbaijan Manat', 'B0' => 'Btu per cubic foot', 'B10' => 'bit per second', 'B12' => 'joule per metre', 'B14' => 'joule per metre to the fourth power', 'B15' => 'joule per mole', 'B17' => 'credit', 'B19' => 'digit', 'B20' => 'joule square metre per kilogram', 'B21' => 'kelvin per watt', 'B22' => 'kiloampere', 'B26' => 'kilocoulomb', 'B27' => 'kilocoulomb per cubic metre', 'B29' => 'kiloelectronvolt', 'B34' => 'kilogram per cubic decimetre', 'B35' => 'kilogram per litre', 'B38' => 'kilogram-force metre', 'B39' => 'kilogram-force metre per second', 'B4' => 'barrel, imperial', 'B41' => 'kilojoule per kelvin', 'B43' => 'kilojoule per kilogram kelvin', 'B45' => 'kilomole', 'B46' => 'kilomole per cubic metre', 'B49' => 'kiloohm', 'B5' => 'billet', 'B50' => 'kiloohm metre', 'B51' => 'kilopond', 'B54' => 'kilosiemens per metre', 'B55' => 'kilovolt per metre', 'B56' => 'kiloweber per metre', 'B59' => 'lumen hour', 'B6' => 'bun', 'B60' => 'lumen per square metre', 'B64' => 'lux second', 'B65' => 'maxwell', 'B66' => 'megaampere per square metre', 'B67' => 'megabecquerel per kilogram', 'B68' => 'gigabit', 'B71' => 'megaelectronvolt', 'B74' => 'meganewton metre', 'B78' => 'megavolt', 'B79' => 'megavolt per metre', 'B81' => 'reciprocal metre squared reciprocal second', 'B85' => 'microbar', 'B86' => 'microcoulomb', 'B87' => 'microcoulomb per cubic metre', 'B88' => 'microcoulomb per square metre', 'B89' => 'microfarad per metre', 'B9' => 'batt', 'B90' => 'microhenry', 'B91' => 'microhenry per metre', 'B93' => 'micronewton metre', 'B99' => 'microsiemens', 'BAM' => 'Bosnia and Herzegovina Convertible Marka', 'BAR' => 'Bar', 'BB' => 'base box', 'BBD' => 'Barbados Dollar', 'BDT' => 'Bangladesh Taka', 'BFT' => 'board foot', 'BHD' => 'Bahrain Dinar', 'BHP' => 'brake horse power', 'BIF' => 'Burundi Franc', 'BL' => 'bale', 'BLD' => 'Dry barrel (115,627 dm3)', 'BMD' => 'Bermuda Dollar', 'BND' => 'Brunei Darussalam Dollar', 'BO' => 'bottle', 'BOB' => 'Bolivia Bolviano', 'BP' => 'hundred board foot', 'BPM' => 'beats per minute', 'BQL' => 'Becquerel', 'BR' => 'bar [unit of packaging]', 'BRL' => 'Brazil Real', 'BSD' => 'Bahamas Dollar', 'BT' => 'bolt', 'BTU' => 'British thermal unit', 'BUA' => 'Bushel (35,2391 dm3)', 'BUI' => 'Bushel (36,36874 dm3)', 'BW' => 'base weight', 'BWP' => 'Botswana Pula', 'BX' => 'box', 'BYN' => 'Belarus Ruble', 'BZ' => 'million BTUs', 'BZD' => 'Belize Dollar', 'C0' => 'call', 'C12' => 'milligram per metre', 'C13' => 'milligray', 'C14' => 'millihenry', 'C15' => 'millijoule', 'C2' => 'carset', 'C22' => 'millinewton per metre', 'C23' => 'milliohm metre', 'C24' => 'millipascal second', 'C26' => 'millisecond', 'C30' => 'millivolt per metre', 'C34' => 'mole', 'C35' => 'mole per cubic decimetre', 'C36' => 'mole per cubic metre', 'C38' => 'mole per litre', 'C4' => 'carload', 'C41' => 'nanofarad', 'C42' => 'nanofarad per metre', 'C43' => 'nanohenry', 'C47' => 'nanosecond', 'C49' => 'nanowatt', 'C5' => 'cost', 'C53' => 'newton metre second', 'C54' => 'newton metre squared kilogram squared', 'C56' => 'newton per square millimetre', 'C60' => 'ohm centimetre', 'C65' => 'pascal second', 'C66' => 'pascal second per cubic metre', 'C67' => 'pascal second per metre', 'C68' => 'petajoule', 'C69' => 'phon', 'C7' => 'centipoise', 'C73' => 'picohenry', 'C75' => 'picowatt', 'C79' => 'kilovolt ampere hour', 'C8' => 'millicoulomb per kilogram', 'C82' => 'radian square metre per mole', 'C85' => 'reciprocal angstrom', 'C88' => 'reciprocal electron volt per cubic metre', 'C90' => 'reciprocal joule per cubic metre', 'C92' => 'reciprocal metre', 'C94' => 'reciprocal minute', 'C95' => 'reciprocal mole', 'C99' => 'reciprocal second per metre squared', 'CAD' => 'Canada Dollar', 'CCT' => 'Carrying capacity in metric tonnes', 'CDF' => 'Congo/Kinshasa Franc', 'CEL' => 'Degree celcius', 'CEN' => 'Hundred', 'CG' => 'card', 'CGM' => 'centigram', 'CHF' => 'Switzerland Franc', 'CJ' => 'cone', 'CKG' => 'Coulomb per kilogram', 'CMT' => 'Centimetre', 'CNP' => 'Hundred packs', 'CNY' => 'China Yuan Renminbi', 'COP' => 'Colombia Peso', 'CR' => 'crate', 'CS' => 'case', 'CT' => 'carton', 'CTG' => 'content gram', 'CTM' => 'Metric carat (200 mg = 2.10-4 kg)', 'CUR' => 'Curie', 'CVE' => 'Cape Verde Escudo', 'CWA' => 'Hundredweight, US (45,3592 kg)', 'CY' => 'cylinder', 'D04' => 'lot [unit of weight]', 'D10' => 'siemens per metre', 'D11' => 'mebibit', 'D12' => 'siemens square metre per mole', 'D13' => 'sievert', 'D16' => 'square centimetre per erg', 'D17' => 'square centimetre per steradian erg', 'D18' => 'metre kelvin', 'D19' => 'square metre kelvin per watt', 'D2' => 'reciprocal second per steradian metre squared', 'D20' => 'square metre per joule', 'D22' => 'square metre per mole', 'D23' => 'pen gram (protein)', 'D25' => 'square metre per steradian joule', 'D27' => 'steradian', 'D28' => 'syphon', 'D29' => 'terahertz', 'D31' => 'terawatt', 'D36' => 'megabit', 'D37' => 'calorie (thermochemical) per gram kelvin', 'D42' => 'tropical year', 'D43' => 'unified atomic mass unit', 'D45' => 'volt squared per kelvin squared', 'D46' => 'volt - ampere', 'D49' => 'millivolt per kelvin', 'D5' => 'kilogram per square centimetre', 'D50' => 'volt per metre', 'D52' => 'watt per kelvin', 'D54' => 'watt per square metre', 'D56' => 'watt per square metre kelvin to the fourth power', 'D57' => 'watt per steradian', 'D58' => 'watt per steradian square metre', 'D59' => 'watt per metre', 'D6' => 'roentgen per second', 'D61' => 'minute [unit of angle]', 'D64' => 'block', 'D65' => 'round', 'D66' => 'cassette', 'D67' => 'dollar per hour', 'D69' => 'inch to the fourth power', 'D7' => 'sandwich', 'D70' => 'International Table (IT) calorie', 'D71' => 'IT calorie per second centimetre kelvin', 'D72' => 'IT calorie per second square centimetre kelvin', 'D74' => 'kilogram per mole', 'D75' => 'calorie (international table) per gram', 'D76' => 'calorie (international table) per gram kelvin', 'D79' => 'beam', 'D8' => 'draize score', 'D82' => 'microvolt', 'D83' => 'millinewton metre', 'D85' => 'microwatt per square metre', 'D88' => 'millicoulomb per cubic metre', 'D89' => 'millicoulomb per square metre', 'D90' => 'cubic metre (net)', 'D91' => 'rem', 'D94' => 'second per cubic metre radian', 'D95' => 'joule per gram', 'D97' => 'pallet/unit load', 'D99' => 'sleeve', 'DAA' => 'Decare', 'DAD' => 'Ten day', 'DB' => 'dry pound', 'DBC' => 'Decade (ten years)', 'DC' => 'disk (disc)', 'DD' => 'degree [unit of angle]', 'DE' => 'deal', 'DEC' => 'decade', 'DJF' => 'Djibouti Franc', 'DKK' => 'Denmark Krone', 'DMA' => 'cubic decametre', 'DMK' => 'Square decimetre', 'DMO' => 'standard kilolitre', 'DMQ' => 'Cubic decimetre', 'DN' => 'decinewton metre', 'DOP' => 'Dominican Republic Peso', 'DPC' => 'dozen piece', 'DPR' => 'Dozen pairs', 'DPT' => 'Displecement tonnege', 'DS' => 'display', 'DTN' => 'Centner, metric (100 kg)', 'DU' => 'dyne', 'DX' => 'dyne per centimetre', 'DZD' => 'Algeria Dinar', 'DZN' => 'Dozen', 'E01' => 'newton per square centimetre', 'E07' => 'megawatt hour per hour', 'E08' => 'megawatt per hertz', 'E09' => 'milliampere hour', 'E10' => 'degree day', 'E11' => 'gigacalorie', 'E12' => 'mille', 'E14' => 'kilocalorie (international table)', 'E16' => 'million Btu(IT) per hour', 'E17' => 'cubic foot per second', 'E2' => 'belt', 'E20' => 'megabit per second', 'E21' => 'shares', 'E23' => 'tyre', 'E25' => 'active unit', 'E31' => 'square metre per litre', 'E33' => 'foot per thousand', 'E34' => 'gigabyte', 'E35' => 'terabyte', 'E36' => 'petabyte', 'E4' => 'gross kilogram', 'E40' => 'part per hundred thousand', 'E42' => 'kilogram-force per square centimetre', 'E43' => 'joule per square centimetre', 'E44' => 'kilogram-force metre per square centimetre', 'E45' => 'milliohm', 'E46' => 'kilowatt hour per cubic metre', 'E47' => 'kilowatt hour per kelvin', 'E50' => 'accounting unit', 'E53' => 'test', 'E54' => 'trip', 'E55' => 'use', 'E56' => 'well', 'E57' => 'zone', 'E58' => 'exabit per second', 'E61' => 'tebibyte', 'E63' => 'mebibyte', 'E64' => 'kibibyte', 'E65' => 'exbibit per metre', 'E69' => 'gibibit per metre', 'E70' => 'gibibit per square metre', 'E71' => 'gibibit per cubic metre', 'E72' => 'kibibit per metre', 'E73' => 'kibibit per square metre', 'E74' => 'kibibit per cubic metre', 'E75' => 'mebibit per metre', 'E76' => 'mebibit per square metre', 'E77' => 'mebibit per cubic metre', 'E79' => 'petabit per second', 'E82' => 'pebibit per cubic metre', 'E85' => 'tebibit per metre', 'E87' => 'tebibit per square metre', 'E88' => 'bit per metre', 'E89' => 'bit per square metre', 'E90' => 'reciprocal centimetre', 'E92' => 'cubic decimetre per hour', 'E93' => 'kilogram per hour', 'E94' => 'kilomole per second', 'E96' => 'degree per second', 'E97' => 'millimetre per degree Celcius metre', 'E98' => 'degree celsius per kelvin', 'E99' => 'percent per bar', 'EA' => 'each', 'EB' => 'electronic mail box', 'EP' => 'eleven pack', 'EUR' => 'Euro Member Countries', 'F01' => 'bit per cubic metre', 'F02' => 'kelvin per kelvin', 'F04' => 'millibar per bar', 'F05' => 'megapascal per bar', 'F07' => 'pascal per bar', 'F08' => 'milliampere per inch', 'F10' => 'kelvin per hour', 'F11' => 'kelvin per minute', 'F12' => 'kelvin per second', 'F13' => 'slug', 'F14' => 'gram per kelvin', 'F17' => 'pound-force per foot', 'F18' => 'kilogram square centimetre', 'F19' => 'kilogram square millimetre', 'F23' => 'gram per cubic decimetre', 'F26' => 'gram per day', 'F27' => 'gram per hour', 'F29' => 'gram per second', 'F31' => 'kilogram per minute', 'F33' => 'milligram per minute', 'F35' => 'gram per day kelvin', 'F36' => 'gram per hour kelvin', 'F38' => 'gram per second kelvin', 'F39' => 'kilogram per day kelvin', 'F40' => 'kilogram per hour kelvin', 'F41' => 'kilogram per minute kelvin', 'F42' => 'kilogram per second kelvin', 'F43' => 'milligram per day kelvin', 'F44' => 'milligram per hour kelvin', 'F46' => 'milligram per second kelvin', 'F48' => 'pound-force per inch', 'F50' => 'micrometre per kelvin', 'F53' => 'millimetre per kelvin', 'F54' => 'milliohm per metre', 'F55' => 'ohm per mile', 'F56' => 'ohm per kilometre', 'F59' => 'milliampere per bar', 'F61' => 'kelvin per bar', 'F62' => 'gram per day bar', 'F63' => 'gram per hour bar', 'F64' => 'gram per minute bar', 'F65' => 'gram per second bar', 'F66' => 'kilogram per day bar', 'F67' => 'kilogram per hour bar', 'F69' => 'kilogram per second bar', 'F70' => 'milligram per day bar', 'F71' => 'milligram per hour bar', 'F72' => 'milligram per minute bar', 'F74' => 'gram per bar', 'F75' => 'milligram per bar', 'F76' => 'milliampere per millimetre', 'F77' => 'pascal second per kelvin', 'F78' => 'inch of water', 'F79' => 'inch of mercury', 'F80' => 'water horse power', 'F82' => 'hektopascal per kelvin', 'F83' => 'kilopascal per kelvin', 'F84' => 'millibar per kelvin', 'F85' => 'megapascal per kelvin', 'F86' => 'poise per kelvin', 'F89' => 'newton metre per degree', 'F9' => 'fibre per cubic centimetre of air', 'F90' => 'newton metre per ampere', 'F91' => 'bar litre per second', 'F92' => 'bar cubic metre per second', 'F94' => 'hektopascal cubic metre per second', 'F95' => 'millibar litre per second', 'F97' => 'megapascal litre per second', 'F99' => 'pascal litre per second', 'FAH' => 'degree Fahrenheit', 'FAR' => 'farad', 'FB' => 'field', 'FBM' => 'fibre metre', 'FC' => 'thousand cubic foot', 'FD' => 'million particle per cubic foot', 'FE' => 'track foot', 'FF' => 'hundred cubic metre', 'FG' => 'transdermal patch', 'FH' => 'micromole', 'FJD' => 'Fiji Dollar', 'FKP' => 'Falkland Islands (Malvinas) Pound', 'FL' => 'flake ton', 'FM' => 'million cubic foot', 'FOT' => 'Foot (0.3048 m)', 'FR' => 'foot per minute', 'FTK' => 'Square foot', 'FTQ' => 'Cubic foot', 'G01' => 'pascal cubic metre per second', 'G05' => 'metre per bar', 'G06' => 'millimetre per bar', 'G08' => 'square inch per second', 'G09' => 'square metre per second kelvin', 'G10' => 'stokes per kelvin', 'G11' => 'gram per cubic centimetre bar', 'G12' => 'gram per cubic decimetre bar', 'G16' => 'kilogram per cubic centimetre bar', 'G17' => 'kilogram per litre bar', 'G18' => 'kilogram per cubic metre bar', 'G19' => 'newton metre per kilogram', 'G2' => 'US gallon per minute', 'G20' => 'pound-force foot per pound', 'G21' => 'cup [unit of volume]', 'G23' => 'peck', 'G24' => 'tablespoon (US)', 'G25' => 'teaspoon (US)', 'G26' => 'stere', 'G27' => 'cubic centimetre per kelvin', 'G28' => 'litre per kelvin', 'G3' => 'Imperial gallon per minute', 'G30' => 'pH (potential of Hydrogen)', 'G31' => 'kilogram per cubic centimetre', 'G32' => 'ounce (avoirdupois) per cubic yard', 'G33' => 'gram per cubic centimetre kelvin', 'G34' => 'gram per cubic decimetre kelvin', 'G35' => 'gram per litre kelvin', 'G37' => 'gram per millilitre kelvin', 'G38' => 'kilogram per cubic centimetre kelvin', 'G39' => 'kilogram per litre kelvin', 'G41' => 'square metre per second bar', 'G42' => 'microsiemens per centimetre', 'G43' => 'microsiemens per metre', 'G44' => 'nanosiemens per centimetre', 'G46' => 'stokes per bar', 'G47' => 'cubic centimetre per day', 'G48' => 'cubic centimetre per hour', 'G49' => 'cubic centimetre per minute', 'G51' => 'litre per second', 'G52' => 'cubic metre per day', 'G54' => 'millilitre per day', 'G55' => 'millilitre per hour', 'G56' => 'cubic inch per hour', 'G57' => 'cubic inch per minute', 'G58' => 'cubic inch per second', 'G59' => 'milliampere per litre minute', 'G60' => 'volt per bar', 'G61' => 'cubic centimetre per day kelvin', 'G62' => 'cubic centimetre per hour kelvin', 'G63' => 'cubic centimetre per minute kelvin', 'G64' => 'cubic centimetre per second kelvin', 'G65' => 'litre per day kelvin', 'G66' => 'litre per hour kelvin', 'G67' => 'litre per minute kelvin', 'G68' => 'litre per second kelvin', 'G69' => 'cubic metre per day kelvin', 'G7' => 'microfiche sheet', 'G70' => 'cubic metre per hour kelvin', 'G71' => 'cubic metre per minute kelvin', 'G72' => 'cubic metre per second kelvin', 'G73' => 'millilitre per day kelvin', 'G74' => 'millilitre per hour kelvin', 'G75' => 'millilitre per minute kelvin', 'G76' => 'millilitre per second kelvin', 'G77' => 'millimetre to the fourth power', 'G78' => 'cubic centimetre per day bar', 'G79' => 'cubic centimetre per hour bar', 'G80' => 'cubic centimetre per minute bar', 'G81' => 'cubic centimetre per second bar', 'G82' => 'litre per day bar', 'G83' => 'litre per hour bar', 'G84' => 'litre per minute bar', 'G85' => 'litre per second bar', 'G86' => 'cubic metre per day bar', 'G87' => 'cubic metre per hour bar', 'G88' => 'cubic metre per minute bar', 'G89' => 'cubic metre per second bar', 'G90' => 'millilitre per day bar', 'G91' => 'millilitre per hour bar', 'G92' => 'millilitre per minute bar', 'G93' => 'millilitre per second bar', 'G94' => 'cubic centimetre per bar', 'G95' => 'litre per bar', 'G96' => 'cubic metre per bar', 'G97' => 'millilitre per bar', 'G98' => 'microhenry per kiloohm', 'G99' => 'microhenry per ohm', 'GB' => 'gallon (US) per day', 'GBP' => 'United Kingdom Pound', 'GBQ' => 'Gigabecquerel', 'GC' => 'gram per 100 gram', 'GD' => 'gross barrel', 'GDW' => 'gram, dry weight', 'GE' => 'pound per gallon (US)', 'GEL' => 'Georgia Lari', 'GF' => 'gram per metre (gram per 100 centimetres)', 'GFI' => 'gram of fissile isotope', 'GGP' => 'Guernsey Pound', 'GGR' => 'Great gross (12 gross)', 'GH' => 'half gallon (US)', 'GHS' => 'Ghana Cedi', 'GIA' => 'Gill (11,8294 cm3)', 'GIC' => 'gram, including container', 'GII' => 'Gill (0,142065 dm3)', 'GK' => 'gram per kilogram', 'GLD' => 'Dry gallon (4,404884 dm3)', 'GLI' => 'Gallon (4,546092 dm3)', 'GM' => 'gram per square metre', 'GMD' => 'Gambia Dalasi', 'GNF' => 'Guinea Franc', 'GO' => 'milligram per square metre', 'GP' => 'milligram per cubic metre', 'GQ' => 'microgram per cubic metre', 'GRN' => 'Grain GB,US (64,798910 mg)', 'GRO' => 'Gross', 'GRT' => 'Gross (register) ton', 'GTQ' => 'Guatemala Quetzal', 'GV' => 'gigajoule', 'GW' => 'gallon per thousand cubic foot', 'GWH' => 'Gigawatt-hour (1 million KW/h)', 'GY' => 'gross yard', 'GYD' => 'Guyana Dollar', 'H03' => 'henry per kiloohm', 'H04' => 'henry per ohm', 'H05' => 'millihenry per kiloohm', 'H06' => 'millihenry per ohm', 'H08' => 'microbecquerel', 'H09' => 'reciprocal year', 'H1' => 'half page - electronic', 'H11' => 'reciprocal month', 'H12' => 'degree Celsius per hour', 'H14' => 'degree Celsius per second', 'H16' => 'square decametre', 'H18' => 'square hectometre', 'H19' => 'cubic hectometre', 'H2' => 'half litre', 'H21' => 'blank', 'H22' => 'volt square inch per pound-force', 'H23' => 'volt per inch', 'H24' => 'volt per microsecond', 'H26' => 'ohm per metre', 'H29' => 'microgram per litre', 'H30' => 'square micrometre', 'H31' => 'ampere per kilogram', 'H32' => 'ampere squared second', 'H34' => 'hertz metre', 'H35' => 'kelvin metre per watt', 'H36' => 'megaohm per kilometre', 'H37' => 'megaohm per metre', 'H40' => 'newton per ampere', 'H41' => 'newton metre watt to the power minus 0,5', 'H42' => 'pascal per metre', 'H43' => 'siemens per centimetre', 'H44' => 'teraohm', 'H45' => 'volt second per metre', 'H46' => 'volt per second', 'H47' => 'watt per cubic metre', 'H48' => 'attofarad', 'H49' => 'centimetre per hour', 'H50' => 'reciprocal cubic centimetre', 'H51' => 'decibel per kilometre', 'H52' => 'decibel per metre', 'H53' => 'kilogram per bar', 'H54' => 'kilogram per cubic decimetre kelvin', 'H56' => 'kilogram per square metre second', 'H57' => 'inch per two pi radiant', 'H58' => 'metre per volt second', 'H60' => 'cubic metre per cubic metre', 'H62' => 'millivolt per minute', 'H63' => 'milligram per square centimetre', 'H65' => 'millilitre per cubic metre', 'H68' => 'millimole per gram', 'H69' => 'picopascal per kilometre', 'H70' => 'picosecond', 'H71' => 'percent per month', 'H74' => 'watt per metre', 'H76' => 'gram per millimetre', 'H77' => 'module width', 'H78' => 'conventional centimetre of water', 'H79' => 'French gauge', 'H80' => 'rack unit', 'H81' => 'millimetre per minute', 'H83' => 'litre per kilogram', 'H84' => 'gram millimetre', 'H85' => 'reciprocal week', 'H87' => 'piece', 'H88' => 'megaohm kilometre', 'H90' => 'percent per degree', 'H92' => 'percent per one hundred thousand', 'H93' => 'percent per hundred', 'H94' => 'percent per thousand', 'H95' => 'percent per volt', 'H96' => 'percent per bar', 'H98' => 'percent per inch', 'H99' => 'percent per metre', 'HA' => 'hank', 'HAR' => 'Hectare', 'HBA' => 'Hectobar', 'HBX' => 'hundred boxes', 'HC' => 'hundred count', 'HD' => 'half dozen', 'HDW' => 'hundred kilogram, dry weight', 'HE' => 'hundredth of a carat', 'HEA' => 'head', 'HF' => 'hundred foot', 'HH' => 'hundred cubic foot', 'HI' => 'hundred sheet', 'HIU' => 'Hundred intenational units', 'HJ' => 'metric horse power', 'HK' => 'hundred kilogram', 'HKD' => 'Hong Kong Dollar', 'HKM' => 'hundred kilogram, net mass', 'HL' => 'hundred foot (linear)', 'HLT' => 'Hectolitre', 'HM' => 'mile per hour', 'HMT' => 'Hectometre', 'HN' => 'conventional millimetre of mercury', 'HNL' => 'Honduras Lempira', 'HO' => 'hundred troy ounce', 'HPA' => 'Hectolitre of pure alcohol', 'HRK' => 'Croatia Kuna', 'HS' => 'hundred square foot', 'HT' => 'half hour', 'HTG' => 'Haiti Gourde', 'HTZ' => 'Hertz', 'HUF' => 'Hungary Forint', 'HUR' => 'Hour', 'HY' => 'hundred yard', 'IC' => 'count per inch', 'IDR' => 'Indonesia Rupiah', 'IE' => 'person', 'II' => 'column inch', 'IL' => 'inch per minute', 'ILS' => 'Israel Shekel', 'IM' => 'impression', 'IMP' => 'Isle of Man Pound', 'INH' => 'Inch (2.54 mm)', 'INK' => 'Square inch', 'INQ' => 'Cubic inch', 'INR' => 'India Rupee', 'ISK' => 'Iceland Krona', 'IU' => 'inch per second', 'IUG' => 'international unit per gram', 'IV' => 'inch per second squared', 'J12' => 'per mille per psi', 'J13' => 'degree API', 'J14' => 'degree Baume (origin scale)', 'J15' => 'degree Baume (US heavy)', 'J16' => 'degree Baume (US light)', 'J18' => 'degree Brix', 'J19' => 'd/Fhrnhet hoursquarefoot/Brtshthermlunt/thrmochemc', 'J2' => 'joule per kilogram', 'J20' => 'degree Fahrenheit per kelvin', 'J21' => 'degree Fahrenheit per bar', 'J22' => 'British thermalunit/hour square foot d/Fahrenheit', 'J25' => 'degree Fahrenheit per second', 'J26' => 'reciprocal degree Fahrenheit', 'J28' => 'degree Rankine per hour', 'J29' => 'degree Rankine per minute', 'J30' => 'degree Rankine per second', 'J31' => 'degree Twaddell', 'J32' => 'micropoise', 'J33' => 'microgram per kilogram', 'J34' => 'microgram per cubic metre kelvin', 'J35' => 'microgram per cubic metre bar', 'J36' => 'microlitre per litre', 'J38' => 'baud', 'J39' => 'British thermal unit (mean)', 'J40' => 'Brtish thermalunit foot/hoursquarefoot d/Fhrnheit', 'J41' => 'Brtishthermalunit inch/hour squarefoot d/Fahrnheit', 'J42' => 'Brtishthermalunit inch/scond squarefoot d/Fahrenht', 'J43' => 'British thermal unit per pound degree Fahrenheit', 'J44' => 'British thermal unit (international table) /minute', 'J45' => 'British thermal unit (international table) /second', 'J46' => 'Brtishthermalunit foot/hour squarefoot d/Fhrenheit', 'J47' => 'British thermal unit (thermochemical) per hour', 'J48' => 'Brtishthermalunit inch/hour squarefoot d/Fhrenheit', 'J49' => 'Brtishthrmalunit inch/scondsquarefoot d/Fahrnheit', 'J51' => 'British thermal unit (thermochemical) per minute', 'J53' => 'coulomb square metre per kilogram', 'J54' => 'megabaud', 'J55' => 'watt second', 'J56' => 'bar per bar', 'J57' => 'barrel (UK petroleum)', 'J58' => 'barrel (UK petroleum) per minute', 'J60' => 'barrel (UK petroleum) per hour', 'J61' => 'barrel (UK petroleum) per second', 'J63' => 'barrel (US petroleum) per second', 'J64' => 'bushel (UK) per day', 'J65' => 'bushel (UK) per hour', 'J66' => 'bushel (UK) per minute', 'J67' => 'bushel (UK) per second', 'J69' => 'bushel (US dry) per hour', 'J71' => 'bushel (US dry) per second', 'J72' => 'centinewton metre', 'J73' => 'centipoise per kelvin', 'J74' => 'centipoise per bar', 'J75' => 'calorie (mean)', 'J76' => 'calorie (international table) /gram degree Celsius', 'J79' => 'calorie (thermochemical) per gram degree Celsius', 'J81' => 'calorie (thermochemical) per minute', 'J82' => 'calorie (thermochemical) per second', 'J83' => 'clo', 'J84' => 'centimetre per second kelvin', 'J85' => 'centimetre per second bar', 'J89' => 'centimetre of mercury', 'J90' => 'cubic decimetre per day', 'J92' => 'cubic decimetre per minute', 'J93' => 'cubic decimetre per second', 'J94' => 'dyne centimetre', 'J95' => 'ounce (UK fluid) per day', 'J98' => 'ounce (UK fluid) per second', 'JB' => 'jumbo', 'JEP' => 'Jersey Pound', 'JK' => 'megajoule per kilogram', 'JM' => 'megajoule per cubic metre', 'JNT' => 'pipeline joint', 'JO' => 'joint', 'JOD' => 'Jordan Dinar', 'JPS' => 'hundred metre', 'JR' => 'jar', 'JWL' => 'number of jewels', 'K1' => 'kilowatt demand', 'K10' => 'ounce (US fluid) per hour', 'K11' => 'ounce (US fluid) per minute', 'K13' => 'foot per degree Fahrenheit', 'K14' => 'foot per hour', 'K15' => 'foot pound-force per hour', 'K16' => 'foot pound-force per minute', 'K17' => 'foot per psi', 'K19' => 'foot per second psi', 'K2' => 'kilovolt ampere reactive demand', 'K20' => 'reciprocal cubic foot', 'K21' => 'cubic foot per degree Fahrenheit', 'K22' => 'cubic foot per day', 'K23' => 'cubic foot per psi', 'K25' => 'foot of mercury', 'K26' => 'gallon (UK) per day', 'K27' => 'gallon (UK) per hour', 'K28' => 'gallon (UK) per second', 'K3' => 'kilovolt ampere reactive hour', 'K31' => 'gram-force per square centimetre', 'K32' => 'gill (UK) per day', 'K33' => 'gill (UK) per hour', 'K35' => 'gill (UK) per second', 'K37' => 'gill (US) per hour', 'K38' => 'gill (US) per minute', 'K39' => 'gill (US) per second', 'K41' => 'grain per gallon (US)', 'K42' => 'horsepower (boiler)', 'K43' => 'horsepower (electric)', 'K46' => 'inch per psi', 'K48' => 'inch per second psi', 'K49' => 'reciprocal cubic inch', 'K5' => 'kilovolt ampere (reactive)', 'K50' => 'kilobaud', 'K51' => 'kilocalorie (mean)', 'K52' => 'kilocalorie (IT) per hour metre degree Celsius', 'K53' => 'kilocalorie (thermochemical)', 'K54' => 'kilocalorie (thermochemical) per minute', 'K55' => 'kilocalorie (thermochemical) per second', 'K58' => 'kilomole per hour', 'K59' => 'kilomole per cubic metre kelvin', 'K6' => 'kilolitre', 'K60' => 'kilomole per cubic metre bar', 'K61' => 'kilomole per minute', 'K62' => 'litre per litre', 'K63' => 'reciprocal litre', 'K64' => 'pound (avoirdupois) per degree Fahrenheit', 'K65' => 'pound (avoirdupois) square foot', 'K67' => 'pound per foot hour', 'K68' => 'pound per foot second', 'K70' => 'pound (avoirdupois) per cubic foot psi', 'K71' => 'pound (avoirdupois) per gallon (UK)', 'K73' => 'pound (avoirdupois) per hour degree Fahrenheit', 'K74' => 'pound (avoirdupois) per hour psi', 'K75' => 'pound/avoirdupois per cubic inch degree Fahrenheit', 'K76' => 'pound (avoirdupois) per cubic inch psi', 'K77' => 'pound (avoirdupois) per psi', 'K78' => 'pound (avoirdupois) per minute', 'K79' => 'pound (avoirdupois) per minute degree Fahrenheit', 'K80' => 'pound (avoirdupois) per minute psi', 'K81' => 'pound (avoirdupois) per second', 'K83' => 'pound (avoirdupois) per second psi', 'K84' => 'pound per cubic yard', 'K85' => 'pound-force per square foot', 'K86' => 'pound-force per square inch degree Fahrenheit', 'K87' => 'psi cubic inch per second', 'K88' => 'psi litre per second', 'K89' => 'psi cubic metre per second', 'K90' => 'psi cubic yard per second', 'K92' => 'pound-force second per square inch', 'K93' => 'reciprocal psi', 'K95' => 'quart (UK liquid) per hour', 'K97' => 'quart (UK liquid) per second', 'K98' => 'quart (US liquid) per day', 'KA' => 'cake', 'KAT' => 'katal', 'KB' => 'kilocharacter', 'KBA' => 'Kilobar', 'KCC' => 'kilogram of choline chloride', 'KD' => 'kilogram decimal', 'KDW' => 'kilogram drained net weight', 'KEL' => 'Kelvin', 'KES' => 'Kenya Shilling', 'KF' => 'kilopacket', 'KGM' => 'Kilogram', 'KGS' => 'Kilogram Per Second', 'KHR' => 'Cambodia Riel', 'KI' => 'kilogram per millimetre width', 'KIC' => 'kilogram, including container', 'KIP' => 'kilogram, including inner packaging', 'KJO' => 'Kilojoule', 'KL' => 'kilogram per metre', 'KLK' => 'lactic dry material percentage', 'KMA' => 'kilogram of methylamine', 'KMF' => 'Comorian Franc', 'KMK' => 'Square kilometre', 'KMQ' => 'Kilogram per cubic meter', 'KNI' => 'Kilogram of nitrogen', 'KNM' => 'kilonewton per square metre', 'KNS' => 'Kilogram of named substance', 'KNT' => 'Knot ( 1 n mile oer hour', 'KO' => 'milliequivalence causticpotash per gram of product', 'KPA' => 'kilopascal', 'KPH' => 'Kilogram of potassium hydroxide (caustic potasn)', 'KPP' => 'Kgm of phosphorus pentoxide(phosphoric anhydride', 'KPW' => 'Korea (North) Won', 'KR' => 'kiloroentgen', 'KRW' => 'Korea (South) Won', 'KS' => 'thousand pound per square inch', 'KSD' => 'Kilogram of substance 90 per cent dry', 'KSH' => 'Kilogram of sodium hydyoxide (caustic soda)', 'KTM' => 'kilometre', 'KTN' => 'Kilotonne', 'KUR' => 'Kilogram of uranium', 'KVA' => 'Kilovolt - ampere', 'KVT' => 'kilovolt', 'KWT' => 'Kilowatt', 'KX' => 'millilitre per kilogram', 'KYD' => 'Cayman Islands Dollar', 'KZT' => 'Kazakhstan Tenge', 'L13' => 'metre per second bar', 'L14' => 'square metre hour degree Celsius per kilocalorie', 'L15' => 'millipascal second per kelvin', 'L16' => 'millipascal second per bar', 'L17' => 'milligram per cubic metre kelvin', 'L18' => 'milligram per cubic metre bar', 'L19' => 'millilitre per litre', 'L2' => 'litre per minute', 'L23' => 'mole per hour', 'L25' => 'mole per kilogram bar', 'L26' => 'mole per litre kelvin', 'L27' => 'mole per litre bar', 'L31' => 'milliroentgen aequivalent men', 'L32' => 'nanogram per kilogram', 'L33' => 'ounce (avoirdupois) per day', 'L34' => 'ounce (avoirdupois) per hour', 'L35' => 'ounce (avoirdupois) per minute', 'L36' => 'ounce (avoirdupois) per second', 'L37' => 'ounce (avoirdupois) per gallon (UK)', 'L39' => 'ounce (avoirdupois) per cubic inch', 'L43' => 'peck (UK)', 'L45' => 'peck (UK) per hour', 'L46' => 'peck (UK) per minute', 'L47' => 'peck (UK) per second', 'L51' => 'peck (US dry) per second', 'L52' => 'psi per psi', 'L53' => 'pint (UK) per day', 'L54' => 'pint (UK) per hour', 'L55' => 'pint (UK) per minute', 'L58' => 'pint (US liquid) per hour', 'L60' => 'pint (US liquid) per second', 'L61' => 'pint (US dry)', 'L63' => 'slug per day', 'L64' => 'slug per foot second', 'L65' => 'slug per cubic foot', 'L66' => 'slug per hour', 'L69' => 'tonne per kelvin', 'L72' => 'tonne per day kelvin', 'L73' => 'tonne per day bar', 'L74' => 'tonne per hour kelvin', 'L78' => 'tonne per minute', 'L81' => 'tonne per second', 'L82' => 'tonne per second kelvin', 'L83' => 'tonne per second bar', 'L84' => 'ton (UK shipping)', 'L85' => 'ton long per day', 'L87' => 'ton short per degree Fahrenheit', 'L88' => 'ton short per day', 'L89' => 'ton short per hour degree Fahrenheit', 'L92' => 'ton (UK long) per cubic yard', 'L94' => 'ton-force (US short)', 'L95' => 'common year', 'L96' => 'sidereal year', 'L99' => 'yard per psi', 'LA' => 'pound per cubic inch', 'LAC' => 'lactose excess percentage', 'LAK' => 'Laos Kip', 'LBP' => 'Lebanon Pound', 'LBR' => 'Pound GB,US (0,45359237 kg)', 'LC' => 'linear centimetre', 'LD' => 'litre per day', 'LEF' => 'leaf', 'LF' => 'linear foot', 'LH' => 'labour hour', 'LJ' => 'large spray', 'LK' => 'link', 'LKR' => 'Sri Lanka Rupee', 'LM' => 'linear metre', 'LN' => 'length', 'LO' => 'lot [unit of procurement]', 'LPA' => 'Litre of pure alcohol', 'LS' => 'lump sum', 'LSL' => 'Lesotho Loti', 'LUB' => 'metric ton, lubricating oil', 'LUM' => 'Lumen', 'LX' => 'linear yard per pound', 'LY' => 'linear yard', 'LYD' => 'Libya Dinar', 'M0' => 'magnetic tape', 'M1' => 'milligram per litre', 'M11' => 'cubic yard per degree Fahrenheit', 'M13' => 'cubic yard per hour', 'M15' => 'cubic yard per minute', 'M16' => 'cubic yard per second', 'M17' => 'kilohertz metre', 'M19' => 'Beaufort', 'M21' => 'reciprocal kilovolt - ampere hour', 'M22' => 'millilitre per square centimetre minute', 'M23' => 'newton per centimetre', 'M25' => 'percent per degree Celsius', 'M26' => 'gigaohm per metre', 'M30' => 'reciprocal volt - ampere second', 'M32' => 'pascal second per litre', 'M33' => 'millimole per litre', 'M34' => 'newton metre per square metre', 'M35' => 'millivolt - ampere', 'M39' => 'centimetre per second squared', 'M4' => 'monetary value', 'M40' => 'yard per second squared', 'M44' => 'revolution', 'M46' => 'revolution per minute', 'M49' => 'chain (based on U.S. survey foot)', 'M5' => 'microcurie', 'M50' => 'furlong', 'M51' => 'foot (U.S. survey)', 'M52' => 'mile (based on U.S. survey foot)', 'M55' => 'metre per radiant', 'M57' => 'mile per minute', 'M58' => 'mile per second', 'M59' => 'metre per second pascal', 'M60' => 'metre per hour', 'M61' => 'inch per year', 'M62' => 'kilometre per second', 'M63' => 'inch per minute', 'M64' => 'yard per second', 'M65' => 'yard per minute', 'M66' => 'yard per hour', 'M7' => 'micro-inch', 'M70' => 'ton, register', 'M71' => 'cubic metre per pascal', 'M73' => 'kilogram per cubic metre pascal', 'M74' => 'kilogram per pascal', 'M76' => 'poundal', 'M78' => 'pond', 'M81' => 'square centimetre per second', 'M82' => 'square metre per second pascal', 'M83' => 'denier', 'M84' => 'pound per yard', 'M87' => 'kilogram per second pascal', 'M89' => 'tonne per year', 'M90' => 'kilopound per hour', 'M95' => 'poundal foot', 'M97' => 'dyne metre', 'MA' => 'machine per unit', 'MAD' => 'Morocco Dirham', 'MAH' => 'megavolt ampere reactive hour', 'MAL' => 'Megalitre', 'MAR' => 'megavolt ampere reactive', 'MAW' => 'Megawatt', 'MBE' => 'thousand standard brick equivalent', 'MBF' => 'thousand board foot', 'MDL' => 'Moldova Leu', 'MF' => 'milligram per square foot per side', 'MGA' => 'Madagascar Ariary', 'MGM' => 'Milligram', 'MID' => 'Thousand', 'MIL' => 'thousand', 'MIN' => 'Minute', 'MKD' => 'Macedonia Denar', 'MLD' => 'Billion US', 'MMK' => 'Square millimetre', 'MMT' => 'Millimetre', 'MNT' => 'Mongolia Tughrik', 'MOP' => 'Macau Pataca', 'MPA' => 'megapascal', 'MQ' => 'thousand metre', 'MQH' => 'cubic metre per hour', 'MQS' => 'cubic metre per second', 'MT' => 'mat', 'MTK' => 'Square metre', 'MUR' => 'Mauritius Rupee', 'MWK' => 'Malawi Kwacha', 'MXN' => 'Mexico Peso', 'MYR' => 'Malaysia Ringgit', 'N10' => 'pound foot per second', 'N11' => 'pound inch per second', 'N17' => 'inch of mercury (60 F)', 'N18' => 'inch of water (39.2 F)', 'N19' => 'inch of water (60 F)', 'N2' => 'number of lines', 'N20' => 'kip per square inch', 'N21' => 'poundal per square foot', 'N22' => 'ounce (avoirdupois) per square inch', 'N23' => 'conventional metre of water', 'N24' => 'gram per square millimetre', 'N25' => 'pound per square yard', 'N27' => 'foot to the fourth power', 'N28' => 'cubic decimetre per kilogram', 'N3' => 'print point', 'N30' => 'cubic inch per pound', 'N31' => 'kilonewton per metre', 'N32' => 'poundal per inch', 'N33' => 'pound-force per yard', 'N36' => 'newton second per square metre', 'N37' => 'kilogram per metre second', 'N39' => 'kilogram per metre day', 'N40' => 'kilogram per metre hour', 'N42' => 'poundal second per square inch', 'N44' => 'pound per foot day', 'N46' => 'foot poundal', 'N49' => 'watt per square inch', 'N66' => 'British thermal unit (39 F)', 'N68' => 'British thermal unit (60 F)', 'N69' => 'calorie (20 C)', 'N71' => 'therm (EC)', 'N73' => 'British thermal unit (thermochemical) per pound', 'N79' => 'kelvin per pascal', 'N81' => 'kilowatt per metre kelvin', 'N82' => 'kilowatt per metre degree Celsius', 'N83' => 'metre per degree Celcius metre', 'N90' => 'kilofarad', 'N92' => 'picosiemens', 'N97' => 'gilbert', 'N98' => 'volt per pascal', 'N99' => 'picovolt', 'NA' => 'milligram per kilogram', 'NAD' => 'Namibia Dollar', 'NAR' => 'Number of articles', 'NB' => 'barge', 'NBB' => 'Number bobbins', 'NCL' => 'number of cells', 'NE' => 'net litre', 'NEW' => 'Newton', 'NG' => 'net gallon (us)', 'NGN' => 'Nigeria Naira', 'NH' => 'message hour', 'NI' => 'net imperial gallon', 'NIL' => 'nil', 'NIO' => 'Nicaragua Cordoba', 'NJ' => 'number of screens', 'NMB' => 'Number', 'NMI' => 'Nautical mile (1852 m)', 'NMP' => 'Number of packs', 'NOK' => 'Norway Krone', 'NPL' => 'Number of parcels', 'NPR' => 'number of pairs', 'NPT' => 'Number of parts', 'NQ' => 'mho', 'NR' => 'micromho', 'NRL' => 'Number of rolls', 'NT' => 'net ton', 'NTT' => 'Net (regirter) ton', 'NU' => 'newton metre', 'NV' => 'vehicle', 'NX' => 'part per thousand', 'NY' => 'pound per air dry metric ton', 'NZD' => 'New Zealand Dollar', 'OA' => 'panel', 'ODE' => 'ozone depletion equivalent', 'ODG' => 'ODS Grams', 'ODK' => 'ODS Kilograms', 'ODM' => 'ODS Milligrams', 'OHM' => 'Ohm', 'ON' => 'ounce per square yard', 'ONZ' => 'Ounce GB,US (28,349523 g)', 'OP' => 'two pack', 'OPM' => 'oscillations per minute', 'OT' => 'overtime hour', 'OZ' => 'ounce av', 'OZA' => 'Fluid ounce (29,5735 cm3)', 'OZI' => 'Fluid ounce (29,5735 cm3)', 'P0' => 'page - electronic', 'P1' => 'percent', 'P10' => 'coulomb per metre', 'P11' => 'kiloweber', 'P13' => 'kilotesla', 'P15' => 'joule per minute', 'P16' => 'joule per hour', 'P17' => 'joule per day', 'P18' => 'kilojoule per second', 'P19' => 'kilojoule per minute', 'P2' => 'pound per foot', 'P20' => 'kilojoule per hour', 'P21' => 'kilojoule per day', 'P22' => 'nanoohm', 'P24' => 'kilohenry', 'P25' => 'lumen per square foot', 'P26' => 'phot', 'P27' => 'footcandle', 'P28' => 'candela per square inch', 'P29' => 'footlambert', 'P3' => 'three pack', 'P32' => 'candela per square foot', 'P33' => 'kilocandela', 'P34' => 'millicandela', 'P39' => 'calorie (thermochemical) per square centimetre', 'P4' => 'four pack', 'P40' => 'langley', 'P42' => 'pascal squared second', 'P43' => 'bel per metre', 'P44' => 'pound mole', 'P45' => 'pound mole per second', 'P46' => 'pound mole per minute', 'P49' => 'newton square metre per ampere', 'P50' => 'weber metre', 'P53' => 'unit pole', 'P54' => 'milligray per second', 'P55' => 'microgray per second', 'P56' => 'nanogray per second', 'P59' => 'microgray per minute', 'P6' => 'six pack', 'P60' => 'nanogray per minute', 'P61' => 'gray per hour', 'P62' => 'milligray per hour', 'P64' => 'nanogray per hour', 'P65' => 'sievert per second', 'P66' => 'millisievert per second', 'P67' => 'microsievert per second', 'P69' => 'rem per second', 'P7' => 'seven pack', 'P70' => 'sievert per hour', 'P71' => 'millisievert per hour', 'P72' => 'microsievert per hour', 'P75' => 'millisievert per minute', 'P78' => 'reciprocal square inch', 'P79' => 'pascal square metre per kilogram', 'P8' => 'eight pack', 'P81' => 'kilopascal per metre', 'P83' => 'standard atmosphere per metre', 'P85' => 'torr per metre', 'P86' => 'psi per inch', 'P87' => 'cubic metre per second square metre', 'P88' => 'rhe', 'P89' => 'pound-force foot per inch', 'P9' => 'nine pack', 'P90' => 'pound-force inch per inch', 'P91' => 'perm (0 C)', 'P93' => 'byte per second', 'P94' => 'kilobyte per second', 'P97' => 'reciprocal radian', 'P98' => 'pascal to the power sum of stoichiometric numbers', 'PA' => 'packet', 'PAL' => 'Pascal', 'PB' => 'pair inch', 'PCE' => 'Piece', 'PD' => 'pad', 'PE' => 'pound equivalent', 'PEN' => 'Peru Sol', 'PFL' => 'proof litre', 'PG' => 'plate', 'PGK' => 'Papua New Guinea Kina', 'PHP' => 'Philippines Piso', 'PI' => 'pitch', 'PK' => 'pack', 'PLA' => 'degree Plato', 'PM' => 'pound percentage', 'PO' => 'pound per inch of length', 'PS' => 'pound-force per square inch', 'PT' => 'pint (US)', 'PU' => 'tray / tray pack', 'PV' => 'half pint (US)', 'PY' => 'peck dry (US)', 'PYG' => 'Paraguay Guarani', 'Q10' => 'joule per tesla', 'Q12' => 'octet', 'Q13' => 'octet per second', 'Q16' => 'natural unit of information', 'Q17' => 'shannon per second', 'Q19' => 'natural unit of information per second', 'Q22' => 'second per radian cubic metre', 'Q23' => 'weber to the power minus one', 'Q27' => 'newton metre per metre', 'Q29' => 'microgram per hectogram', 'Q3' => 'meal', 'Q30' => 'pH (potential of Hydrogen)', 'Q31' => 'kilojoule per gram', 'Q35' => 'megawatts per minute', 'Q36' => 'square metre per cubic metre', 'Q37' => 'Standard cubic metre per day', 'Q38' => 'Standard cubic metre per hour', 'Q39' => 'Normalized cubic metre per day', 'Q40' => 'Normalized cubic metre per hour', 'Q42' => 'Joule per standard cubic metre', 'QAN' => 'Quarter (of a year)', 'QD' => 'quarter dozen', 'QH' => 'quarter hour', 'QK' => 'quarter kilogram', 'QR' => 'quire', 'QT' => 'quart (US)', 'QTD' => 'Dry quart (1,101221 dm3)', 'QTI' => 'Quart (1,136523 dm3)', 'QTL' => 'Liquid quart (0,946353 dm3)', 'R1' => 'pica', 'R4' => 'calorie', 'R9' => 'thousand cubic metre', 'RG' => 'ring', 'RH' => 'running or operating hour', 'RK' => 'roll metric measure', 'RL' => 'reel', 'RM' => 'ream', 'RO' => 'roll', 'ROM' => 'room', 'RON' => 'Romania Leu', 'RP' => 'pound per ream', 'RPM' => 'Revolution per minute', 'RPS' => 'Revolution per second', 'RS' => 'reset', 'RT' => 'revenue ton mile', 'RUB' => 'Russia Ruble', 'RWF' => 'Rwanda Franc', 'S3' => 'square foot per second', 'S4' => 'square metre per second', 'S6' => 'session', 'S7' => 'storage unit', 'S8' => 'standard advertising unit', 'SA' => 'sack', 'SAN' => 'Half year (six Months)', 'SBD' => 'Solomon Islands Dollar', 'SCO' => 'Score', 'SCR' => 'Scruple GP,US (1,295982 g)', 'SD' => 'solid pound', 'SDG' => 'Sudan Pound', 'SE' => 'section', 'SEC' => 'Second', 'SEK' => 'Sweden Krona', 'SET' => 'Set', 'SG' => 'segment', 'SGD' => 'Singapore Dollar', 'SHP' => 'Saint Helena Pound', 'SHT' => 'Shipping ton', 'SIE' => 'Siemens', 'SK' => 'split tank truck', 'SL' => 'slipsheet', 'SLL' => 'Sierra Leone Leone', 'SM3' => 'Standard cubic metre', 'SMI' => 'Statute mile (1609.344 m)', 'SN' => 'square rod', 'SOS' => 'Somalia Shilling', 'SPL' => 'Seborga Luigino', 'SQ' => 'square', 'SQR' => 'square, roofing', 'SRD' => 'Suriname Dollar', 'SS' => 'sheet metric measure', 'ST' => 'sheet', 'STC' => 'stick', 'STI' => 'Stone GB (6,350293 kg)', 'STK' => 'stick, cigarette', 'STL' => 'standard litre', 'STN' => 'Short ton GB, US 2/ (0,90718474 t)', 'STW' => 'straw', 'SW' => 'skein', 'SX' => 'shipment', 'SZL' => 'Swaziland Lilangeni', 'T0' => 'telecommunication line in service', 'T1' => 'thousand pound gross', 'T3' => 'thousand piece', 'T5' => 'thousand casing', 'T6' => 'thousand gallon (US)', 'T7' => 'thousand impression', 'T8' => 'thousand linear inch', 'TA' => 'tenth cubic foot', 'TAH' => 'Thousand ampere-hour', 'TAN' => 'total acid number', 'TC' => 'truckload', 'TE' => 'tote', 'TF' => 'ten square yard', 'THB' => 'Thailand Baht', 'TI' => 'thousand square inch', 'TIP' => 'metric ton, including inner packaging', 'TJ' => 'thousand square centimetre', 'TK' => 'tank, rectangular', 'TL' => 'thousand foot (linear)', 'TMS' => 'kilogram of imported meat, less offal', 'TMT' => 'Turkmenistan Manat', 'TN' => 'tin', 'TND' => 'Tunisia Dinar', 'TOP' => 'Tonga Pa\'anga', 'TPI' => 'teeth per inch', 'TPR' => 'Ten pairs', 'TQ' => 'thousand foot', 'TQD' => 'thousand cubic metres per day', 'TR' => 'ten square foot', 'TRL' => 'Trillion Eur', 'TRY' => 'Turkey Lira', 'TS' => 'thousand square foot', 'TSH' => 'Ton of steam per hour', 'TST' => 'ten set', 'TT' => 'thousand linear metre', 'TTD' => 'Trinidad and Tobago Dollar', 'TTS' => 'ten thousand sticks', 'TU' => 'tube', 'TV' => 'thousand kilogram', 'TVD' => 'Tuvalu Dollar', 'TWD' => 'Taiwan New Dollar', 'TY' => 'tank, cylindrical', 'TZS' => 'Tanzania Shilling', 'U1' => 'treatment', 'U2' => 'tablet', 'UA' => 'torr', 'UAH' => 'Ukraine Hryvnia', 'UB' => 'telecommunication line in service average', 'UC' => 'telecommunication port', 'UD' => 'tenth minute', 'UE' => 'tenth hour', 'UF' => 'usage per telecommunication line average', 'UGX' => 'Uganda Shilling', 'UH' => 'ten thousand yard', 'UM' => 'million unit', 'USD' => 'United States Dollar', 'UYU' => 'Uruguay Peso', 'UZS' => 'Uzbekistan Som', 'VA' => 'volt - ampere per kilogram', 'VEF' => 'Venezuela Bolvar', 'VI' => 'vial', 'VK' => 'Vanpack', 'VL' => 'Bulk, liquid', 'VN' => 'Vehicle', 'VO' => 'Bulk, solid, large particles ("nodules")', 'VP' => 'Vacuumpacked', 'VQ' => 'Bulk,liquefied gas (at abnorml temprture/pressure)', 'VR' => 'Bulk, solid, granular particles ("grains")', 'VS' => 'Bulk, scrap metal', 'VY' => 'Bulk, solid, fine particles ("powders")', 'WA' => 'Intermediate bulk container', 'WB' => 'Wickerbottle', 'WC' => 'Intermediate bulk container, steel', 'WD' => 'Intermediate bulk container, aluminium', 'WF' => 'Intermediate bulk container, metal', 'WG' => 'Intermediate bulk cont,steel,pressurised >10 kpa', 'WH' => 'Intermedt bulk cont,aluminium,pressurised >10 kpa', 'WJ' => 'Intermediate bulk container,metal, pressure 10 kpa', 'WK' => 'Intermediate bulk container, steel, liquid', 'WL' => 'Intermediate bulk container, aluminium, liquid', 'WM' => 'Intermediate bulk container, metal, liquid', 'WN' => 'Intermd bulk cont,woven plastic,without coat/liner', 'WP' => 'Intermediate bulk container, woven plastic, coated', 'WQ' => 'Intermd bulk cont,woven plastic,with liner', 'WR' => 'Intermedt bulk cont,woven plastic,coated and liner', 'WS' => 'Intermediate bulk container, plastic film', 'WT' => 'Intermd bulk cont,textile with out coat/liner', 'WU' => 'Intermdte bulk cont,natural wood,with inner liner', 'WV' => 'Intermediate bulk container, textile, coated', 'WW' => 'Intermediate bulk container, textile, with liner', 'WX' => 'Intermediate bulk cont,textile,coated and liner', 'WY' => 'Intermd bulk cont,plywood,with inner liner', 'WZ' => 'Intermd bulk cont,reconstttd wood,with inner liner', 'XA' => 'Bag, woven plastic, without inner coat/liner', 'XB' => 'Bag, woven plastic, sift proof', 'XC' => 'Bag, woven plastic, water resistant', 'XD' => 'Bag, plastics film', 'XF' => 'Bag, textile, without inner coat/liner', 'XG' => 'Bag, textile, sift proof', 'XH' => 'Bag, textile, water resistant', 'XJ' => 'Bag, paper, multi-wall', 'XK' => 'Bag, paper, multi-wall, water resistant', 'XN' => 'test', 'YA' => 'Compsite packging,plastic receptacle in steel drum', 'YB' => 'Compste packgng,plastc recptcle in steel crate box', 'YC' => 'Compste packgng,plastic recptcle in aluminium drum', 'YD' => 'Compste packgng,plastic recptcle in alumnium crate', 'YF' => 'Compsite packging,plastic receptacle in wooden box', 'YG' => 'Compste packgng,plastic receptacle in plywood drum', 'YH' => 'Compste packging,plastic receptacle in plywood box', 'YJ' => 'Compsite packging,plastic receptacle in fibre drum', 'YK' => 'Compste packgng,plastic recptcle in fibreboard box', 'YL' => 'Compste packgng,plastic receptacle in plastic drum', 'YM' => 'Compsite packgng,plstc recptcle in solid plstc box', 'YN' => 'Composite packaging,glass receptacle in steel drum', 'YP' => 'Compste packgng,glass recptacle in steel crate box', 'YQ' => 'Compste packgng,glass receptacle in aluminium drum', 'YR' => 'Compste packgng,glass recptacle in aluminium crate', 'YS' => 'Composite packaging,glass receptacle in wooden box', 'YT' => 'Compsite packging,glass receptacle in plywood drum', 'YV' => 'Compste packgng,glass recptcle in wickrwork hamper', 'YW' => 'Composite packaging,glass receptacle in fibre drum', 'YX' => 'Compste packgng,glass receptacle in fibreboard box', 'YY' => 'Compste pckgng,glss recptcl in expndbl plastc pack', 'YZ' => 'Compsite packgng,glass recptcle in solid plstc pck', 'ZA' => 'Intermediate bulk container, paper, multi-wall', 'ZB' => 'Bag, large', 'ZC' => 'Intermd bulk cont,paper,multi-wall,water resistant', 'ZD' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,solid', 'ZF' => 'Intermd bulk cont,rgid plstc,freestandng,solds', 'ZG' => 'Intermdbulk cnt,rgd plstc,w/strctrl equipm,pressrd', 'ZH' => 'Intermd bulk cont,rgd plstc,freestnd,pressurised', 'ZJ' => 'Intermd bulk cont,rgd plstc,w/strctrl equipm,lquid', 'ZK' => 'Intermd bulk cont,rigid plstc,freestanding,liquids', 'ZL' => 'Intermd bulk cont,composite,rigid plastic,solids', 'ZM' => 'Intermd bulk cont,compste,flexbl plastic, solids', 'ZN' => 'Intermd bulk cont,compsit,rgid plstc,pressurised', 'ZP' => 'Intermd bulk cont,compsit,flexbl plstc,pressurised', 'ZQ' => 'Intermd bulk cont,composite,rigid plastic,liquids', 'ZR' => 'Intermd bulk cont,compsite,flexible plastc,liquids', 'ZS' => 'Intermediate bulk container, composite', 'ZT' => 'Intermediate bulk container, fibreboard', 'ZU' => 'Intermediate bulk container, flexible', 'ZV' => 'Intermediate bulk container,metal,other than steel', 'ZW' => 'Intermediate bulk container, natural wood', 'ZX' => 'Intermediate bulk container, plywood', 'ZY' => 'Intermediate bulk container, reconstituted wood', 'ZZ' => 'Mutually defined'];
@endphp

<div class="container-fluid">
<div class="card card-sb">
    <div class="card-header">
        <h5 class="card-title fw-bold mb-0">
            <i class="fas fa-edit"></i> BC 2.6.2 - PEMBERITAHUAN PEMASUKAN KEMBALI BARANG YANG DIKELUARKAN DARI TEMPAT PENIMBUNAN BERIKAT DENGAN JAMINAN
        </h5>
    </div>

    <form action="{{ route('dokumen-pabean-update_draft_bc262', $header->bppbno ?? $header->trx_no_par) }}" method="POST" id="form-edit-ceisa" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="alert alert-info py-2 mb-4">
                <strong>No. Transaksi:</strong> {{ $header->trx_no_par }} |
                <strong>Supplier / Vendor:</strong> {{ $header->supplier ?? '-' }}
                <input type="hidden" name="kodeDokumen" value="262">
                <input type="hidden" name="asalData" value="S">
            </div>

            <ul class="nav nav-tabs" id="ceisaTab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="header-tab" data-toggle="tab" href="#tab-header" role="tab">Header</a></li>
                <li class="nav-item"><a class="nav-link" id="entitas-tab" data-toggle="tab" href="#tab-entitas" role="tab">Entitas</a></li>
                <li class="nav-item"><a class="nav-link" id="dokumen-tab" data-toggle="tab" href="#tab-dokumen" role="tab">Dokumen</a></li>
                <li class="nav-item"><a class="nav-link" id="pengangkut-tab" data-toggle="tab" href="#tab-pengangkut" role="tab">Pengangkut</a></li>
                <li class="nav-item"><a class="nav-link" id="kemasan-tab" data-toggle="tab" href="#tab-kemasan" role="tab">Kemasan & Peti Kemas</a></li>
                <li class="nav-item"><a class="nav-link" id="transaksi-tab" data-toggle="tab" href="#tab-transaksi" role="tab">Transaksi</a></li>
                <li class="nav-item"><a class="nav-link" id="barang-tab" data-toggle="tab" href="#tab-barang" role="tab">Barang</a></li>
                <li class="nav-item"><a class="nav-link" id="jaminan-tab" data-toggle="tab" href="#tab-jaminan" role="tab">Jaminan</a></li>
                <li class="nav-item"><a class="nav-link" id="pungutan-tab" data-toggle="tab" href="#tab-pungutan" role="tab">Pungutan</a></li>
                <li class="nav-item"><a class="nav-link" id="pernyataan-tab" data-toggle="tab" href="#tab-pernyataan" role="tab">Pernyataan</a></li>
            </ul>

            <div class="tab-content mt-3" id="ceisaTabContent">


                <div class="tab-pane fade show active" id="tab-header" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengajuan</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Nomor Aju</label>
                                        <input type="text" name="nomorAju" class="form-control form-control-sm fw-bold" value="{{ $nomorAju }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Kantor Pabean</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Kantor Pabean Pengawasan</label>
                                        <select name="kantorPabean" class="form-control form-control-sm select2bs4">
                                            <option value="">Pilih Kantor Pabean</option>
                                            @foreach($kantorList as $ktr)
                                                <option value="{{ $ktr['kode'] }}" {{ ($dataDetail['kantorPabean'] ?? '') == $ktr['kode'] ? 'selected' : '' }}>{{ $ktr['nama'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Keterangan Lain</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label class="text-sm">Tujuan Pengiriman</label>
                                        <select name="tujuanPengiriman" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Tujuan Pengiriman --</option>
                                            @foreach($listTujuanPengiriman as $k => $v)
                                                <option value="{{ $k }}" {{ ($dataDetail['tujuanPengiriman'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-entitas" role="tabpanel">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pengusaha TPB (Asal)</div>
                                <div class="card-body">
                                    @php $entTpb = $dataDetail['entitas']['tpb'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP)</label>
                                        <input type="text" name="entitas[tpb][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIdentitas'] ?? '0745406926444000000000' }}" placeholder="NPWP 15/16 Digit">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[tpb][nitku]" class="form-control form-control-sm" value="{{ $entTpb['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pengusaha TPB</label>
                                        <input type="text" name="entitas[tpb][namaEntitas]" class="form-control form-control-sm" value="{{ $entTpb['namaEntitas'] ?? 'NIRWANA ALABARE GARMENT' }}" placeholder="Nama Perusahaan">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat</label>
                                        <textarea name="entitas[tpb][alamatEntitas]" class="form-control form-control-sm" rows="2" placeholder="Alamat Perusahaan">{{ $entTpb['alamatEntitas'] ?? 'JL. RAYA RANCAEKEK MAJALAYA NO. 289 RT. 001 RW. 007' }}</textarea>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Ijin TPB</label>
                                        <input type="text" name="entitas[tpb][nomorIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nomorIjinEntitas'] ?? '16/MK/WBC.09/2026' }}" placeholder="Nomor Ijin">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Tanggal Ijin TPB</label>
                                        <input type="date" name="entitas[tpb][tanggalIjinEntitas]" class="form-control form-control-sm" value="{{ $entTpb['tanggalIjinEntitas'] ?? '2026-01-20' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="small mb-0">NIB</label>
                                        <input type="text" name="entitas[tpb][nibEntitas]" class="form-control form-control-sm" value="{{ $entTpb['nibEntitas'] ?? '0220103231143' }}" placeholder="NIB">
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Penerima Barang / Pembeli</div>
                                <div class="card-body">
                                    @php $entPenerima = $dataDetail['entitas']['penerima'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[penerima][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPenerima['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[penerima][nitku]" class="form-control form-control-sm" value="{{ $entPenerima['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Penerima</label>
                                        <input type="text" name="entitas[penerima][namaEntitas]" class="form-control form-control-sm" value="{{ $entPenerima['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Penerima">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Penerima</label>
                                        <textarea name="entitas[penerima][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Penerima">{{ $entPenerima['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-4">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold px-3 py-2 bg-light text-dark" style="font-size:13px;">Pemilik Barang</div>
                                <div class="card-body">
                                    @php $entPemilik = $dataDetail['entitas']['pemilik'] ?? []; @endphp
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nomor Identitas (NPWP/KTP)</label>
                                        <input type="text" name="entitas[pemilik][nomorIdentitas]" class="form-control form-control-sm" value="{{ $entPemilik['nomorIdentitas'] ?? ($header->npwp_supplier ?? '') }}" placeholder="NPWP / KTP">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">NITKU</label>
                                        <input type="text" name="entitas[pemilik][nitku]" class="form-control form-control-sm" value="{{ $entPemilik['nitku'] ?? '' }}" placeholder="NITKU">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Nama Pemilik</label>
                                        <input type="text" name="entitas[pemilik][namaEntitas]" class="form-control form-control-sm" value="{{ $entPemilik['namaEntitas'] ?? ($header->supplier ?? '') }}" placeholder="Nama Pemilik">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label class="small mb-0">Alamat Pemilik</label>
                                        <textarea name="entitas[pemilik][alamatEntitas]" class="form-control form-control-sm" rows="3" placeholder="Alamat Pemilik">{{ $entPemilik['alamatEntitas'] ?? ($header->alamat_supplier ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-dokumen" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold d-flex align-items-center px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <span>Dokumen Lampiran</span>
                            <button type="button" class="btn btn-sm btn-light py-0 px-2" style="margin-left: auto !important;" id="btn-add-dok" title="Tambah Dokumen"><i class="fas fa-plus text-primary"></i> Tambah Dokumen</button>
                        </div>
                        <div class="card-body p-0" style="overflow-x: auto;">
                            <table class="table table-sm table-bordered mb-0" id="table-dokumen" style="min-width: 800px;">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="25%">Jenis Dokumen</th>
                                        <th width="22%">Nomor</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="12%">Fasilitas</th>
                                        <th width="13%">Kode Izin</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-dokumen">
                                    @foreach($dokumens as $index => $dok)
                                        <tr>
                                            <td class="text-center align-middle">
                                                {{ $index + 1 }}
                                                <input type="hidden" name="dok[{{ $index }}][seriDokumen]" value="{{ $index + 1 }}">
                                            </td>
                                            <td>
                                                <select name="dok[{{ $index }}][kode]" class="form-control form-control-sm select2bs4">
                                                    <option value="">-- Pilih Kode --</option>
                                                    @foreach($referensiDokumen as $val => $text)
                                                        <option value="{{ $val }}" {{ ($dok['kodeDokumen'] ?? $dok['kode'] ?? '') == $val ? 'selected' : '' }}>
                                                            {{ $val }} - {{ $text }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="dok[{{ $index }}][nomor]" class="form-control form-control-sm" value="{{ $dok['nomorDokumen'] ?? $dok['nomor'] ?? '' }}">
                                            </td>
                                            <td>
                                                <input type="date" name="dok[{{ $index }}][tgl]" class="form-control form-control-sm" value="{{ $dok['tanggalDokumen'] ?? $dok['tgl'] ?? '' }}">
                                            </td>
                                            <td><input type="text" name="dok[{{ $index }}][fasilitas]" class="form-control form-control-sm" value="{{ $dok['fasilitas'] ?? '' }}" placeholder="Kode Fasilitas"></td>
                                            <td><input type="text" name="dok[{{ $index }}][izin]" class="form-control form-control-sm" value="{{ $dok['izin'] ?? '' }}" placeholder="Kode Izin"></td>
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
                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Pengangkutan</div>
                        <div class="card-body">
                            <div class="form-group mb-0">
                                <label class="small mb-1">Cara Pengangkutan</label>
                                @php $sarkutFirst = $pengangkuts[0] ?? []; @endphp
                                <input type="hidden" name="pengangkut[0][seriPengangkut]" value="1">
                                <input type="hidden" name="pengangkut[0][kodeBendera]" value="ID">
                                <select name="pengangkut[0][kodeCaraAngkut]" class="form-control form-control-sm select2bs4">
                                    <option value="">Pilih Cara Angkut</option>
                                    @foreach($listCaraAngkut as $k => $v)
                                        <option value="{{ $k }}" {{ ($pengangkuts[0]['kodeCaraAngkut'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ strtoupper($v) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-0 mt-3">
                                <label class="small mb-1">Keterangan Sarana Angkut Lainnya</label>
                                <input type="text" name="pengangkut[0][namaPengangkut]" class="form-control form-control-sm" value="{{ $sarkutFirst['namaPengangkut'] ?? '' }}" placeholder="Wajib diisi jika Cara Pengangkutan = LAINNYA">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-kemasan" role="tabpanel">

                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Kemasan</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="margin-left: auto !important;" id="btn-add-kemasan" title="Tambah Kemasan"><i class="fas fa-plus"></i> Tambah Kemasan</button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0" id="table-kemasan">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="5%">Seri</th>
                                        <th width="18%">Jumlah</th>
                                        <th width="38%">Jenis Kemasan</th>
                                        <th width="29%">Merek</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kemasan">
                                    @foreach($kemasans as $kIndex => $kemasan)
                                    <tr>
                                        <td class="text-center align-middle">
                                            {{ $kIndex + 1 }}
                                            <input type="hidden" name="kemasan[{{ $kIndex }}][seriKemasan]" value="{{ $kIndex + 1 }}">
                                        </td>
                                        <td><input type="number" step="any" name="kemasan[{{ $kIndex }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $kemasan['jumlahKemasan'] ?? 0 }}"></td>
                                        <td>
                                            <select name="kemasan[{{ $kIndex }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis Kemasan --</option>
                                                @foreach($listJenisKemasan as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kemasan['kodeJenisKemasan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kemasan[{{ $kIndex }}][merkKemasan]" class="form-control form-control-sm" value="{{ $kemasan['merkKemasan'] ?? '-' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kemasan"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Data Petikemas (Kontainer)</span>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="margin-left: auto !important;" id="btn-add-kontainer" title="Tambah Kontainer"><i class="fas fa-plus"></i> Tambah Petikemas</button>
                        </div>
                        <div class="card-body p-0">
                            @php
                                $listJenisKontainer = ['4' => 'Empty', '7' => 'LCL', '8' => 'FCL'];
                                $listTipeKontainer = [
                                    '1' => 'General/Dry Cargo', '2' => 'Tunnel Type', '3' => 'Open Top Steel',
                                    '4' => 'Flat Rack', '5' => 'Reefer/Refrigerated', '6' => 'Barge Container',
                                    '7' => 'Bulk Container', '8' => 'Isotank', '99' => 'Lain-lain'
                                ];
                                $listUkuranKontainer = ['20' => '20 Feet', '40' => '40 Feet', '45' => '45 Feet', '60' => '60 Feet'];
                            @endphp
                            <table class="table table-sm table-bordered mb-0" id="table-kontainer">
                                <thead class="bg-light text-center">
                                    <tr>
                                        <th width="25%">Nomor Kontainer</th>
                                        <th width="15%">Ukuran</th>
                                        <th width="20%">Jenis</th>
                                        <th width="20%">Tipe</th>
                                        <th width="12%">Jenis Muatan</th>
                                        <th width="8%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-kontainer">
                                    @foreach($kontainers as $cIndex => $kont)
                                    <tr>
                                        <input type="hidden" name="kontainer[{{ $cIndex }}][seriKontainer]" value="{{ $cIndex + 1 }}">
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][nomorKontainer]" class="form-control form-control-sm text-uppercase" value="{{ $kont['nomorKontainer'] ?? '' }}"></td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Ukuran --</option>
                                                @foreach($listUkuranKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeUkuranKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Jenis --</option>
                                                @foreach($listJenisKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeJenisKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select name="kontainer[{{ $cIndex }}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4">
                                                <option value="">-- Pilih Tipe --</option>
                                                @foreach($listTipeKontainer as $k => $v)
                                                    <option value="{{ $k }}" {{ ($kont['kodeTipeKontainer'] ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="text" name="kontainer[{{ $cIndex }}][jenisMuatan]" class="form-control form-control-sm" value="{{ $kont['jenisMuatan'] ?? '' }}"></td>
                                        <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-transaksi" role="tabpanel">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Harga</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Jenis Valuta</label>
                                        <select name="valuta" class="form-control form-control-sm select2bs4">
                                            <option value="">-- Pilih Valuta --</option>
                                            @foreach($listValuta as $kVal => $nVal)
                                                <option value="{{ $kVal }}" {{ ($dataDetail['valuta'] ?? 'IDR') == $kVal ? 'selected' : '' }}>{{ $kVal }} - {{ $nVal }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>NDPBM</label>
                                        <input type="number" step="any" name="ndpbm" class="form-control form-control-sm" value="{{ $dataDetail['ndpbm'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Nilai CIF</label>
                                        <input type="number" step="any" name="nilaiCif" class="form-control form-control-sm" value="{{ $dataDetail['nilaiCif'] ?? '0.00' }}">
                                    </div>
                                    <div class="form-group mb-0">
                                        <label>Nilai Pabean</label>
                                        <input type="number" step="any" name="nilaiPabean" class="form-control form-control-sm" value="{{ $dataDetail['nilaiPabean'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm mb-3 border">
                                <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Berat</div>
                                <div class="card-body">
                                    <div class="form-group mb-2">
                                        <label>Berat Kotor (KGM)</label>
                                        <input type="number" step="any" name="bruto" class="form-control form-control-sm" value="{{ $dataDetail['bruto'] ?? '0.00' }}">
                                        <small class="text-muted" style="font-size: 10px;">Berat Kotor harus lebih besar dari 0</small>
                                    </div>
                                    <div class="form-group mb-2">
                                        <label>Berat Bersih (KGM)</label>
                                        <input type="number" step="any" name="netto" class="form-control form-control-sm" value="{{ $dataDetail['netto'] ?? '0.00' }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>


                <div class="tab-pane fade" id="tab-barang" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">
                            <i class="fas fa-boxes"></i> Rincian Barang ({{ count($items) }} Item)
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="accordionBarang">
                                @foreach($items as $index => $item)
                                @php
                                    $draftItem = $dataDetail['barang'][$index] ?? [];
                                @endphp

                                <div class="card mb-2 border">
                                    <div class="card-header py-2 btn-collapse-barang" data-target="#collapseBarang{{ $index }}" style="cursor: pointer; background-color: #001f3f;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-white" style="font-size: 13px;">
                                                {{ $item->goods_code ?? $item->id_item }} - {{ $item->itemdesc }}
                                            </div>
                                            <i class="fas fa-chevron-down text-white icon-collapse"></i>
                                        </div>
                                    </div>

                                    <div id="collapseBarang{{ $index }}" class="collapse {{ $index == 0 ? 'show' : '' }}" data-parent="#accordionBarang">
                                        <div class="card-body py-3 px-3 bg-white">


                                            <input type="hidden" name="barang[{{ $index }}][seriBarang]" value="{{ $index + 1 }}">

                                            <div class="row">

                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Jenis</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Seri</label>
                                                                <input type="text" class="form-control form-control-sm" value="{{ $index + 1 }}" readonly>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Pos Tarif/HS <i class="far fa-question-circle text-primary"></i></label>
                                                                <input type="text" name="barang[{{ $index }}][posTarif]" class="form-control form-control-sm" value="{{ $draftItem['posTarif'] ?? '' }}" placeholder="Pos Tarif/HS">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kode Barang</label>
                                                                <input type="text" name="barang[{{ $index }}][kodeBarang]" class="form-control form-control-sm" value="{{ $draftItem['kodeBarang'] ?? $item->goods_code ?? $item->id_item ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label class="d-flex justify-content-between align-items-center mb-1">
                                                                    Uraian Jenis Barang
                                                                    <button type="button" class="btn btn-primary btn-sm py-0" style="font-size: 10px;">Sesuai Hs</button>
                                                                </label>
                                                                <textarea name="barang[{{ $index }}][uraian]" class="form-control form-control-sm " rows="3">{{ $draftItem['uraian'] ?? $item->itemdesc ?? '' }}</textarea>
                                                                <small style="font-size: 10px;">Uraian kosong</small>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Merek</label>
                                                                <input type="text" name="barang[{{ $index }}][merk]" class="form-control form-control-sm" value="{{ $draftItem['merk'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Tipe</label>
                                                                <input type="text" name="barang[{{ $index }}][tipe]" class="form-control form-control-sm" value="{{ $draftItem['tipe'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Ukuran</label>
                                                                <input type="text" name="barang[{{ $index }}][ukuran]" class="form-control form-control-sm" value="{{ $draftItem['ukuran'] ?? '-' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Spesifikasi Lain</label>
                                                                <input type="text" name="barang[{{ $index }}][spesifikasiLain]" class="form-control form-control-sm" value="{{ $draftItem['spesifikasiLain'] ?? '-' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Jumlah & Berat</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Jumlah dan Satuan Barang</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" step="any" name="barang[{{ $index }}][jumlahSatuan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahSatuan'] ?? (float) $item->qty }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeSatuanBarang]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Satuan --</option>
                                                                            @foreach($listSatuanBarang as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeSatuanBarang'] ?? $item->unit ?? '') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-2">
                                                                <label>Kemasan</label>
                                                                <div class="row">
                                                                    <div class="col-sm-6 pr-1">
                                                                        <input type="number" name="barang[{{ $index }}][jumlahKemasan]" class="form-control form-control-sm" value="{{ $draftItem['jumlahKemasan'] ?? 0 }}">
                                                                    </div>
                                                                    <div class="col-sm-6 pl-1">
                                                                        <select name="barang[{{ $index }}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4">
                                                                            <option value="">-- Kemasan --</option>
                                                                            @foreach($listJenisKemasan as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($draftItem['kodeJenisKemasan'] ?? 'CT') == $k ? 'selected' : '' }}>{{ $k }} - {{ $v }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Berat Bersih (Kg)</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][netto]" class="form-control form-control-sm" value="{{ $draftItem['netto'] ?? '0.0000' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <div class="col-md-4">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2" style="font-size:13px;">Harga</div>
                                                        <div class="card-body">
                                                            <div class="form-group mb-2">
                                                                <label>Nilai CIF</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][cif]" class="form-control form-control-sm mb-2" value="{{ $draftItem['cif'] ?? '0.00' }}">
                                                            </div>
                                                            <div class="form-group mb-0">
                                                                <label>Harga Penyerahan</label>
                                                                <input type="number" step="any" name="barang[{{ $index }}][hargaPenyerahan]" class="form-control form-control-sm" value="{{ $draftItem['hargaPenyerahan'] ?? (float)($item->qty * $item->price) }}">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="card shadow-sm mb-3 border d-none">
                                                        <div class="card-header fw-bold bg-light text-dark px-3 py-2 d-flex justify-content-between align-items-center" style="font-size:13px;">
                                                            <span>Dokumen Fasilitas/Lartas</span>
                                                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="font-size:11px;"><i class="fas fa-plus"></i> Tambah</button>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-sm mb-0">
                                                                    <tbody id="tbody-fasilitas-{{ $index }}">
                                                                        <tr>
                                                                            <td class="text-center text-muted py-5">
                                                                                <i class="fas fa-inbox fa-3x mb-2" style="color: #f1f1f1;"></i><br>
                                                                                <small style="color: #ccc;">No Data</small>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="row mt-3 d-none">
                                                <div class="col-md-12">
                                                    <div class="card shadow-sm mb-3 border">
                                                        <div class="card-header fw-bold d-flex justify-content-between align-items-center bg-light text-dark px-3 py-2" style="font-size:13px;">
                                                            <span>Bahan Baku</span>
                                                            <div>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0">Urutkan</button>
                                                                <button type="button" class="btn btn-sm btn-primary py-0 btn-add-bahan-baku" data-itemidx="{{ $index }}">Aksi</button>
                                                            </div>
                                                        </div>
                                                        <div class="card-body p-0">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered mb-0">
                                                                    <thead class="bg-light text-center" style="font-size: 12px;">
                                                                        <tr>
                                                                            <th>Seri</th>
                                                                            <th>HS</th>
                                                                            <th>Uraian</th>
                                                                            <th>Nilai Barang</th>
                                                                            <th>Kode Satuan</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tbody-bahan-baku-{{ $index }}">
                                                                        @php $bahanBaku = $draftItem['bahanBaku'] ?? []; @endphp
                                                                        @if(count($bahanBaku) == 0)
                                                                        <tr>
                                                                            <td colspan="6" class="text-center text-muted py-4">
                                                                                <i class="fas fa-inbox fa-2x mb-2 text-light"></i><br>
                                                                                <small>No Data</small>
                                                                            </td>
                                                                        </tr>
                                                                        @else
                                                                            @foreach($bahanBaku as $bbIndex => $bb)
                                                                            <tr>
                                                                                <td class="p-1 text-center align-middle">
                                                                                    <input type="hidden" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][seriBahanBaku]" value="{{ $bbIndex + 1 }}">
                                                                                    {{ $bbIndex + 1 }}
                                                                                </td>
                                                                                <td class="p-1"><input type="text" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][hs]" class="form-control form-control-sm" value="{{ $bb['hs'] ?? '' }}" placeholder="HS"></td>
                                                                                <td class="p-1"><input type="text" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][uraian]" class="form-control form-control-sm" value="{{ $bb['uraian'] ?? '' }}" placeholder="Uraian"></td>
                                                                                <td class="p-1"><input type="number" step="any" name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][nilaiBarang]" class="form-control form-control-sm" value="{{ $bb['nilaiBarang'] ?? 0 }}"></td>
                                                                                <td class="p-1">
                                                                                    <select name="barang[{{ $index }}][bahanBaku][{{ $bbIndex }}][kodeSatuan]" class="form-control form-control-sm select2bs4">
                                                                                        <option value="">Satuan</option>
                                                                                        @foreach($listSatuanBarang as $k => $v)
                                                                                            <option value="{{ $k }}" {{ ($bb['kodeSatuan'] ?? '') == $k ? 'selected' : '' }}>{{ $k }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
                                                                            </tr>
                                                                            @endforeach
                                                                        @endif
                                                                    </tbody>
                                                                </table>
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
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-jaminan" role="tabpanel">
                    <div class="card mb-3">
                        <div class="card-header fw-bold d-flex align-items-center px-3 py-2 bg-light text-dark" style="font-size:13px;">
                            <span>Jaminan</span>
                            <button type="button" class="btn btn-sm btn-primary py-0 px-2" style="margin-left: auto !important;" onclick="addJaminanRow()">
                                <i class="fas fa-plus"></i> Tambah Jaminan
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm text-center" id="table-jaminan">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Jenis Jaminan</th>
                                            <th>Nomor Jaminan</th>
                                            <th>Tgl Jaminan</th>
                                            <th>Nilai Jaminan</th>
                                            <th>Jatuh Tempo</th>
                                            <th>Penjamin</th>
                                            <th>Nomor BPJ</th>
                                            <th>Tgl BPJ</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody-jaminan">
                                        @foreach($jaminans as $idx => $j)
                                        <tr>
                                            <td>
                                                <select class="form-control form-control-sm select2bs4" name="jaminan[{{ $idx }}][kodeJenisJaminan]">
                                                    <option value="">Pilih</option>
                                                    @foreach($listJenisJaminan as $kode => $nama)
                                                        <option value="{{ $kode }}" {{ ($j['kodeJenisJaminan'] ?? '') == $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nomorJaminan]" value="{{ $j['nomorJaminan'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalJaminan]" value="{{ $j['tanggalJaminan'] ?? '' }}"></td>
                                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nilaiJaminan]" value="{{ $j['nilaiJaminan'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalJatuhTempo]" value="{{ $j['tanggalJatuhTempo'] ?? '' }}"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][penjamin]" value="{{ $j['penjamin'] ?? '' }}"></td>
                                            <td><input type="text" class="form-control form-control-sm" name="jaminan[{{ $idx }}][nomorBpj]" value="{{ $j['nomorBpj'] ?? '' }}"></td>
                                            <td><input type="date" class="form-control form-control-sm" name="jaminan[{{ $idx }}][tanggalBpj]" value="{{ $j['tanggalBpj'] ?? '' }}"></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="tab-pungutan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-end mb-3">
                                <button type="button" class="btn btn-sm btn-primary py-1 px-3">Detail Barang</button>
                            </div>

                            <div class="row">

                                <div class="col-md-4 mb-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th colspan="2" class="text-dark fw-bold" style="font-size:12px;">BM & BMT</th>
                                            </tr>
                                        </thead>
                                        @php
                                            $pungs = collect($dataDetail['pungutan'] ?? []);
                                            $valPungutan = function($kode) use ($pungs) {
                                                $p = $pungs->firstWhere('kodeJenisPungutan', $kode);
                                                return $p ? $p['nilaiPungutan'] : '0.00';
                                            };
                                        @endphp
                                        <tbody>
                                            <tr>
                                                <td class="align-middle" width="30%" style="font-size:12px;">BM</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[0][kodeJenisPungutan]" value="BM">
                                                    <input type="number" step="any" name="pungutan[0][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('BM') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">BMAD</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[1][kodeJenisPungutan]" value="BMAD">
                                                    <input type="number" step="any" name="pungutan[1][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('BMAD') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">BMI</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[2][kodeJenisPungutan]" value="BMI">
                                                    <input type="number" step="any" name="pungutan[2][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('BMI') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">BMTP</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[3][kodeJenisPungutan]" value="BMTP">
                                                    <input type="number" step="any" name="pungutan[3][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('BMTP') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">BMP</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[4][kodeJenisPungutan]" value="BMP">
                                                    <input type="number" step="any" name="pungutan[4][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('BMP') }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <table class="table table-bordered table-sm mb-3">
                                        <thead class="bg-light">
                                            <tr>
                                                <th colspan="2" class="text-dark fw-bold" style="font-size:12px;">CUKAI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle" width="35%" style="font-size:12px;">CUKAI EA</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[5][kodeJenisPungutan]" value="CEA">
                                                    <input type="number" step="any" name="pungutan[5][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('CEA') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">CUKAI MMEA</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[6][kodeJenisPungutan]" value="CMEA">
                                                    <input type="number" step="any" name="pungutan[6][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('CMEA') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">CUKAI CTEM</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[7][kodeJenisPungutan]" value="CTEM">
                                                    <input type="number" step="any" name="pungutan[7][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('CTEM') }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th colspan="2" class="text-dark fw-bold" style="font-size:12px;">TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle" width="35%" style="font-size:12px;">Total</td>
                                                <td><input type="number" step="any" class="form-control form-control-sm border-0 bg-transparent text-end" value="0.00"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>


                                <div class="col-md-4 mb-3">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th colspan="2" class="text-dark fw-bold" style="font-size:12px;">PDRI</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="align-middle" width="30%" style="font-size:12px;">PPN</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[8][kodeJenisPungutan]" value="PPN">
                                                    <input type="number" step="any" name="pungutan[8][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('PPN') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">PPNBM</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[9][kodeJenisPungutan]" value="PPNBM">
                                                    <input type="number" step="any" name="pungutan[9][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('PPNBM') }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="align-middle" style="font-size:12px;">PPH</td>
                                                <td>
                                                    <input type="hidden" name="pungutan[10][kodeJenisPungutan]" value="PPH">
                                                    <input type="number" step="any" name="pungutan[10][nilaiPungutan]" class="form-control form-control-sm border-0 bg-transparent text-end" value="{{ $valPungutan('PPH') }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="tab-pane fade" id="tab-pernyataan" role="tabpanel">
                    <div class="card shadow-sm mb-3 border">
                        <div class="card-header text-white fw-bold px-3 py-2" style="font-size:13px; background-color: #001f3f;">Pernyataan & Penandatangan</div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 form-group"><label>Nama TTD</label><input type="text" name="namaTtd" class="form-control form-control-sm" value="{{ $dataDetail['namaTtd'] ?? '' }}" placeholder="Nama Lengkap"></div>
                                <div class="col-md-3 form-group"><label>Jabatan</label><input type="text" name="jabatanTtd" class="form-control form-control-sm" value="{{ $dataDetail['jabatanTtd'] ?? '' }}" placeholder="Jabatan"></div>
                                <div class="col-md-3 form-group"><label>Tempat / Kota TTD</label><input type="text" name="tempatTtd" class="form-control form-control-sm" value="{{ $dataDetail['tempatTtd'] ?? '' }}" placeholder="Kota"></div>
                                <div class="col-md-3 form-group"><label>Tanggal TTD</label><input type="date" name="tanggalTtd" class="form-control form-control-sm" value="{{ $dataDetail['tanggalTtd'] ?? date('Y-m-d') }}"></div>
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
</div>
@endsection

@section('custom-script')
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <td class="text-center align-middle">${dokIndex + 1}<input type="hidden" name="dok[${dokIndex}][seriDokumen]" value="${dokIndex + 1}"></td>
                    <td><select name="dok[${dokIndex}][kode]" class="form-control form-control-sm select2bs4-dynamic">${optDokumenHtml}</select></td>
                    <td><input type="text" name="dok[${dokIndex}][nomor]" class="form-control form-control-sm"></td>
                    <td><input type="date" name="dok[${dokIndex}][tgl]" class="form-control form-control-sm"></td>
                    <td><input type="text" name="dok[${dokIndex}][fasilitas]" class="form-control form-control-sm" placeholder="Kode Fasilitas"></td>
                    <td><input type="text" name="dok[${dokIndex}][izin]" class="form-control form-control-sm" placeholder="Kode Izin"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-dok"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-dokumen').append(htmlTr);
            $(`select[name="dok[${dokIndex}][kode]"]`).select2({ theme: 'bootstrap4', width: '100%' });
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
                    <td class="text-center align-middle">${kemasanIndex + 1}<input type="hidden" name="kemasan[${kemasanIndex}][seriKemasan]" value="${kemasanIndex + 1}"></td>
                    <td><input type="number" step="any" name="kemasan[${kemasanIndex}][jumlahKemasan]" class="form-control form-control-sm" value="0"></td>
                    <td><select name="kemasan[${kemasanIndex}][kodeJenisKemasan]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKemasan}</select></td>
                    <td><input type="text" name="kemasan[${kemasanIndex}][merkKemasan]" class="form-control form-control-sm" value="-"></td>
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
                    <input type="hidden" name="kontainer[${kontainerIndex}][seriKontainer]" value="${kontainerIndex + 1}">
                    <td><input type="text" name="kontainer[${kontainerIndex}][nomorKontainer]" class="form-control form-control-sm text-uppercase"></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeUkuranKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optUkuranKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeJenisKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optJenisKontainer}</select></td>
                    <td><select name="kontainer[${kontainerIndex}][kodeTipeKontainer]" class="form-control form-control-sm select2bs4-dynamic">${optTipeKontainer}</select></td>
                    <td><input type="text" name="kontainer[${kontainerIndex}][jenisMuatan]" class="form-control form-control-sm"></td>
                    <td class="text-center align-middle"><button type="button" class="btn btn-sm btn-danger py-0 px-2 btn-hapus-kontainer"><i class="fas fa-trash-alt"></i></button></td>
                </tr>`;
            $('#tbody-kontainer').append(htmlTr);
            $(`select[name="kontainer[${kontainerIndex}][kodeUkuranKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            $(`select[name="kontainer[${kontainerIndex}][kodeJenisKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            $(`select[name="kontainer[${kontainerIndex}][kodeTipeKontainer]"]`).select2({ theme: 'bootstrap4', width: '100%' });
            kontainerIndex++;
        });
        $(document).on('click', '.btn-hapus-kontainer', function() { $(this).closest('tr').remove(); });

        // ================= BAHAN BAKU LOKAL HANDLER =================
        const optSatuanHtml = `
            <option value="">Satuan</option>
            @foreach($listSatuanBarang as $k => $v) <option value="{{ $k }}">{{ $k }}</option> @endforeach
        `;
        $(document).on('click', '.btn-add-bahan-baku', function() {
            let itemIdx = $(this).data('itemidx');
            let tbody = $(`#tbody-bahan-baku-${itemIdx}`);
            let rowIdx = tbody.find('tr').length;

            let tr = `<tr>
                <input type="hidden" name="barang[${itemIdx}][bahanBaku][${rowIdx}][seriBahanBaku]" value="${rowIdx + 1}">
                <td class="p-1"><input type="text" name="barang[${itemIdx}][bahanBaku][${rowIdx}][hs]" class="form-control form-control-sm" placeholder="HS"></td>
                <td class="p-1">
                    <input type="text" name="barang[${itemIdx}][bahanBaku][${rowIdx}][uraian]" class="form-control form-control-sm mb-1" placeholder="Uraian">
                    <select name="barang[${itemIdx}][bahanBaku][${rowIdx}][kodeSatuan]" class="form-control form-control-sm select2bs4-dynamic">${optSatuanHtml}</select>
                </td>
                <td class="p-1"><input type="number" step="any" name="barang[${itemIdx}][bahanBaku][${rowIdx}][nilaiBarang]" class="form-control form-control-sm" value="0"></td>
                <td class="text-center p-1 align-middle"><button type="button" class="btn btn-sm btn-outline-danger btn-hapus-bb"><i class="fas fa-trash-alt"></i></button></td>
            </tr>`;
            tbody.append(tr);
            $(`select[name="barang[${itemIdx}][bahanBaku][${rowIdx}][kodeSatuan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        });
        $(document).on('click', '.btn-hapus-bb', function() { $(this).closest('tr').remove(); });

        // ================= PUNGUTAN HANDLER =================
        let pungutanIndex = {{ count($dataDetail['pungutan'] ?? []) }};
        // ================= SYNC NETTO =================
        $('#btn-sync-netto').on('click', function() {
            let totalNetto = 0;
            $('[name$="[netto]"]').each(function() {
                let val = parseFloat($(this).val()) || 0;
                totalNetto += val;
            });
            $('[name="netto"]').val(totalNetto.toFixed(4));
        });

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
            let form = $(this);
            Swal.fire({
                title: 'Simpan Perubahan?',
                text: "Data draft BC 2.6.2 akan diperbarui.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                confirmButtonText: 'Ya, Simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); }});
                    $.ajax({
                        url: form.attr('action'),
                        type: form.attr('method') || 'POST',
                        data: form.serialize(),
                        success: function(response) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message || 'Draft BC 2.6.2 berhasil disimpan.',
                                icon: 'success'
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        // Add Jaminan Row
        window.addJaminanRow = function() {
            let idx = $('#tbody-jaminan tr').length;
            let options = '<option value="">Pilih</option>';
            @foreach($listJenisJaminan as $kode => $nama)
                options += '<option value="{{ $kode }}">{{ $kode }} - {{ $nama }}</option>';
            @endforeach
            let html = `
                <tr>
                    <td><select class="form-control form-control-sm select2bs4-dynamic" name="jaminan[${idx}][kodeJenisJaminan]">${options}</select></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][nomorJaminan]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalJaminan]" value=""></td>
                    <td><input type="number" step="0.01" class="form-control form-control-sm" name="jaminan[${idx}][nilaiJaminan]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalJatuhTempo]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][penjamin]" value=""></td>
                    <td><input type="text" class="form-control form-control-sm" name="jaminan[${idx}][nomorBpj]" value=""></td>
                    <td><input type="date" class="form-control form-control-sm" name="jaminan[${idx}][tanggalBpj]" value=""></td>
                    <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)"><i class="fas fa-trash"></i></button></td>
                </tr>
            `;
            $('#tbody-jaminan').append(html);
            $(`select[name="jaminan[${idx}][kodeJenisJaminan]"]`).select2({ theme: 'bootstrap4', width: '100%' });
        }

        window.removeRow = function(btn) {
            $(btn).closest('tr').remove();
        }
    });
</script>
@endsection
