{{-- Gaya PDF SO / BOM DB baru (dompdf), mengikuti PDF BOM lama. DejaVu Sans supaya karakter seperti "–" tercetak. --}}
<style>
    @page { margin: 20px 28px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 8px; color: #333; }
    table { border-collapse: collapse; }
    .head { width: 100%; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #000; }
    .head h5 { margin: 0; font-size: 16px; }
    .info { width: 48%; float: left; margin-bottom: 10px; }
    .info.right { float: right; }
    .info th { width: 38%; padding: 3px 5px; border: 1px solid #ccc; background: #f2f2f2; text-align: left; }
    .info td { padding: 3px 5px; border: 1px solid #ccc; }
    .clear { clear: both; }
    .sec { margin: 12px 0 5px; padding: 4px 6px; background: #284a63; color: #fff; font-size: 10px; font-weight: bold; }
    .sub { margin: 8px 0 3px; font-size: 9px; font-weight: bold; }
    .grid { width: 100%; margin-bottom: 8px; }
    .grid th { padding: 3px; border: 1px solid #bbb; background: #f2f2f2; font-size: 7px; text-align: center; }
    .grid td { padding: 3px; border: 1px solid #ccc; font-size: 7.5px; vertical-align: top; }
    .grid tr.tot td { background: #e9f0fb; font-weight: bold; }
    .grid tr.subrow td { background: #fbfcfe; }
    .r { text-align: right; white-space: nowrap; }
    .c { text-align: center; }
    .b { font-weight: bold; }
    .dim { color: #888; font-style: italic; }
    .warn { margin: 0 0 8px; padding: 5px 7px; border: 1px solid #f0c36d; background: #fff8e6; color: #7a5300; }
    .printed { margin-top: 10px; color: #888; font-size: 7px; text-align: right; }
    .page-break { page-break-after: always; }
</style>
