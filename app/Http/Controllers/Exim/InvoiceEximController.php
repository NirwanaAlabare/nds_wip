<?php

namespace App\Http\Controllers\Exim;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceEximController extends Controller
{
    /**
     * Koneksi ke database aplikasi AR. Koneksi mysql_sb di nds_wip sudah
     * menunjuk ke database yang sama dengan yang dipakai AR, jadi tidak perlu
     * koneksi baru. Kalau database AR dipindah (mis. ganti periode), cukup ubah
     * DB_DATABASE_SB di .env.
     */
    const KONEKSI_AR = 'mysql_sb';

    /** Invoice Local selalu Local - kode L di nomor invoice menentukan ini. */
    const SHIPP_LOCAL = 'Local';

    /** Invoice Export selalu Export - kode E di nomor invoice menentukan ini. */
    const SHIPP_EXPORT = 'Export';

    /** Tabel khusus Invoice Export (lihat migrations/20260917_invoice_exim_export.sql). */
    const TABEL_EXP_H    = 'tbl_book_invoice_exim_export_h';
    const TABEL_EXP_SHIP = 'tbl_book_invoice_exim_export_ship';
    const TABEL_EXP_DET  = 'tbl_book_invoice_exim_export_det';

    /**
     * Tipe pengeluaran garment yang boleh ditagihkan (awalan bppb.bppbno_int).
     *
     * Cuma FG/OUT yang punya SO - harganya ikut so_det.price. Sisanya keluar
     * tanpa SO dan di bppb harganya diisi 0, jadi harganya diketik user di
     * layar Detail SJ dan TIDAK boleh diambil dari database.
     */
    const TIPE_SJ_GARMENT = array('FG', 'GK', 'GEN', 'WIP', 'GACC', 'SCR', 'SPCK');

    /** Tipe SJ yang harganya sudah pasti dari SO - harga manual ditolak. */
    const TIPE_SJ_BERHARGA = array('FG', 'OFC');

    /**
     * Mata uang yang boleh dipilih untuk baris SJ tanpa SO.
     *
     * Sengaja tidak memakai daftar dari tabel so: isinya belasan mata uang lama
     * yang tidak pernah dipakai untuk penjualan seperti ini. Daftar ini juga
     * yang dipakai server untuk memeriksa kiriman layar.
     */
    const MATA_UANG_SJ = array('IDR', 'USD');

    /** Dokumen pabean untuk invoice Local. */
    const DOC_TYPE_LOCAL = array('BC 4.1', 'BC 25', 'BC 27');

    /** Dokumen pabean untuk invoice Export. */
    const DOC_TYPE_EXPORT = array('PEB', 'BC 2.7');

    /**
     * Shipper invoice export selalu perusahaan sendiri, tidak dipilih.
     * Datanya tetap dibaca dari mastersupplier supaya alamatnya satu sumber
     * dengan master, bukan diketik ulang di kode.
     */
    const ID_SHIPPER_NAG = '799';

    public function local()
    {
        return view('export-import.invoice.local.index', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "invoice-export-import",
            "subPage"        => "invoice-local",
            "containerFluid" => true,
            "customer"       => $this->daftarCustomer(),
        ]);
    }

    public function export()
    {
        return view('export-import.invoice.export.index', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "invoice-export-import",
            "subPage"        => "invoice-export",
            "containerFluid" => true,
            "customer"       => $this->daftarCustomer(),
        ]);
    }

    public function dataLocal(Request $request)
    {
        return response()->json(['data' => $this->ambilData($request, 'local')]);
    }

    public function dataExport(Request $request)
    {
        return response()->json(['data' => $this->ambilData($request, 'export')]);
    }

    public function createLocal()
    {
        return view('export-import.invoice.local.form', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "invoice-export-import",
            "subPage"        => "invoice-local",
            "containerFluid" => true,
            "customer"       => $this->daftarCustomer(),
            "profitCenter"   => $this->daftarProfitCenter(),
            "tipe"           => DB::connection(self::KONEKSI_AR)->select("SELECT id, type FROM tbl_type ORDER BY id"),
            "buyer"          => $this->daftarBuyer(),
            "docType"        => self::DOC_TYPE_LOCAL,
            "shipp"          => self::SHIPP_LOCAL,
            // Dipakai kolom Curr di Detail SJ: SJ tanpa SO tidak punya mata uang
            // di database, jadi dipilih sendiri di layar.
            "mataUangSj"     => self::MATA_UANG_SJ,
            "noInvoice"      => $this->nomorInvoiceBerikutnya('NAG'),
        ]);
    }

    /**
     * Halaman Create Invoice Export.
     *
     * Bentuk dokumennya beda jauh dari Invoice Local - tiga lapis, bukan dua:
     * pihak-pihak di atas, blok pengiriman (bisa lebih dari satu), lalu Invoice
     * Summary yang isinya sudah diringkas per warna. Baris FG/OUT-nya tetap
     * dipilih seperti Local, cuma tidak ikut tercetak: dia jadi asal angka Qty
     * Invoiced, dan nanti dibaca Create Invoice di AR untuk menandai bppb.
     */
    public function createExport()
    {
        return view('export-import.invoice.export.form', $this->dataFormExport() + array(
            'noInvoice' => $this->nomorInvoiceBerikutnya('NAG', 'E'),
        ));
    }

    /**
     * Mata uang yang dipakai di tabel so.
     *
     * Disaring tiga huruf saja: kolom curr sempat terisi kosongan dan satu
     * nilai sampah ('90'), yang kalau ikut masuk malah jadi pilihan di layar.
     */
    private function daftarCurrency()
    {
        $hasil = array();
        foreach ($this->koneksiAr()->select(
            "SELECT DISTINCT UPPER(TRIM(curr)) AS curr FROM so
              WHERE curr IS NOT NULL AND TRIM(curr) <> ''
                AND UPPER(TRIM(curr)) REGEXP '^[A-Z]{3}$'
              ORDER BY curr"
        ) as $r) {
            $hasil[] = $r->curr;
        }
        // USD dipastikan ada walau tabelnya kelak kosong - itu bawaan layarnya.
        if (!in_array('USD', $hasil, true)) {
            array_unshift($hasil, 'USD');
        }
        return $hasil;
    }

    /** Data shipper tetap untuk invoice export - perusahaan sendiri. */
    private function shipperNag()
    {
        $r = $this->koneksiAr()->select(
            "SELECT id_supplier, supplier, alamat FROM mastersupplier WHERE id_supplier = ? LIMIT 1",
            array(self::ID_SHIPPER_NAG)
        );
        return $r ? (array) $r[0] : array('id_supplier' => self::ID_SHIPPER_NAG, 'supplier' => '', 'alamat' => '');
    }

    /**
     * Daftar kode negara untuk kolom Final Destination.
     *
     * Dibaca dari master_negara_kode kalau tabelnya sudah ada. Kalau migrasinya
     * belum dijalankan, layar tetap terbuka dengan daftar kosong - kolomnya
     * memang boleh diketik manual, jadi tidak ada yang mandek.
     */
    private function daftarNegara()
    {
        if (!$this->tabelAda('master_negara_kode')) {
            return array();
        }
        return $this->koneksiAr()->select(
            "SELECT nama_negara, kode FROM master_negara_kode ORDER BY nama_negara"
        );
    }

    /**
     * Kode negara untuk satu nama negara yang diketik bebas.
     *
     * Dipakai layar Create Export: begitu Receiver-nya diisi, kolom Final
     * Destination diisikan kodenya - tapi tetap boleh ditimpa, karena isi
     * mastersupplier.country memang tidak seragam.
     */
    public function kodeNegara(Request $request)
    {
        $nama = strtoupper(trim((string) $request->query('nama')));
        if ($nama === '' || !$this->tabelAda('master_negara_kode')) {
            return response()->json(array('kode' => ''));
        }
        $r = $this->koneksiAr()->select(
            "SELECT kode FROM master_negara_kode WHERE UPPER(nama_negara) = ? LIMIT 1",
            array($nama)
        );
        return response()->json(array('kode' => $r ? (string) $r[0]->kode : ''));
    }

    /**
     * Nomor invoice berikutnya, dibangkitkan (bukan diambil dari Booking
     * Invoice). Formatnya sama dengan yang dipakai AR:
     *
     *     0210/L/NAG/0926   =  urut / L=Local / profit center / bulan-tahun
     *
     * Nomor urut mengikuti Model_nag::get_kode_book_invoice(): 4 digit,
     * direset tiap bulan, diambil dari nomor terbesar bulan berjalan.
     *
     * Ini baru nomor PRATINJAU - nomor final harus diambil ulang saat simpan,
     * supaya dua user yang membuka form bersamaan tidak memakai nomor sama.
     */
    public function nomorInvoiceBerikutnya($profitCenter, $kodeJenis = 'L')
    {
        $baris = DB::connection(self::KONEKSI_AR)->select(
            "SELECT MAX(LEFT(no_invoice, 4)) AS kd_max
               FROM tbl_book_invoice
              WHERE YEAR(tgl_book_inv) = YEAR(CURRENT_DATE())
                AND MONTH(tgl_book_inv) = MONTH(CURRENT_DATE())"
        );

        $urut = 1;
        if ($baris && $baris[0]->kd_max !== null) {
            $urut = ((int) $baris[0]->kd_max) + 1;
        }

        return sprintf('%04s', $urut) . '/' . $kodeJenis . '/' . $profitCenter . '/' . date('my');
    }

    /** Nomor berikutnya saat profit center diganti di form. */
    public function nomorInvoice(Request $request)
    {
        $pc = preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('profit_center', 'NAG'));

        return response()->json([
            'no_invoice' => $this->nomorInvoiceBerikutnya($pc ?: 'NAG'),
        ]);
    }

    /** Koneksi database knitting (PostgreSQL), sumber SJ untuk profit center NAK. */
    const KONEKSI_NAK = 'pgsql_nak';

    /**
     * Daftar SJ untuk modal Add SJ.
     *
     * Beda dengan AR: di sana SJ baru muncul setelah sebuah SO dipilih
     * (Model_nag::cari_sj memakai id_so). Di sini SJ langsung dicari dari
     * rentang tanggal + buyer, tanpa tahap SO. Isi kolom & syarat barisnya
     * tetap sama dengan AR supaya hasilnya cocok.
     *
     * Dua sumber digabung: garment (MySQL) dan knitting (PostgreSQL).
     */
    /**
     * Daftar WS (SO) untuk modal "Add WS" di Invoice Export.
     *
     * Kenapa ada: SJ-nya sering belum terbit waktu invoice harus dibuat -
     * barangnya belum keluar, tapi nomor invoice sudah diminta buyer. Jadi
     * invoice boleh dimulai dari WS/SO dulu, SJ-nya dilengkapi belakangan.
     *
     * Satu baris = satu baris SO (WS + warna + size), bentuknya sengaja
     * disamakan dengan baris SJ (daftarSj) supaya Invoice Summary bisa
     * memperlakukan keduanya dengan rumus yang sama.
     *
     * Rentang tanggalnya memakai tanggal SO, bukan tanggal SJ - SJ-nya memang
     * belum ada.
     */
    public function daftarWs(Request $request)
    {
        $tglAwal  = $this->tanggal($request->query('tgl_awal'));
        $tglAkhir = $this->tanggal($request->query('tgl_akhir'));
        $buyer    = trim((string) $request->query('buyer', ''));

        // Dipanggil dengan daftar WS: dipakai layar untuk melengkapi Detail SO
        // sendiri sesudah SJ dipilih. Rentang tanggal tidak berlaku di sini -
        // WS-nya sudah ditunjuk, tanggal SO-nya tidak relevan lagi.
        $ws = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $request->query('ws', ''))
        ), function ($v) { return $v !== ''; }));

        if (!$ws && ($tglAwal === null || $tglAkhir === null)) {
            return response()->json(['data' => [], 'pesan' => 'Invalid date range.'], 422);
        }

        $pc = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('profit_center', 'NAG')));

        // Knitting belum ikut: SO knitting bentuknya berbeda (sales_orders /
        // detail_so di PostgreSQL) dan belum pernah dipakai untuk invoice
        // export. Dikosongkan dengan keterangan, bukan diam-diam kosong.
        if ($pc !== 'NAG') {
            return response()->json([
                'data'  => array(),
                'pesan' => 'WS list is only available for NAG (garment) for now - pick the SJ instead.',
            ]);
        }

        return response()->json(array(
            'data'  => $this->wsGarment($tglAwal, $tglAkhir, $buyer, array(), $ws),
            'pesan' => null,
        ));
    }

    /**
     * Baris SO garment, diringkas per WS + warna. Kolomnya sengaja senama
     * dengan baris SJ:
     *
     *   no_so, ws, styleno, product_group, product_item, color, size,
     *   curr, uom, qty, unit_price, total_price
     *
     * Bedanya: sj / bppbdate / shipping_number kosong (SJ-nya memang belum
     * ada), asal = 'WS', dan id_baris memakai id so_det.
     *
     * Baris yang qty-nya sudah habis terkirim TIDAK disaring di sini: yang
     * dipakai invoice adalah qty SO, dan tim exim yang memutuskan berapa yang
     * ditagih. Yang dibuang cuma baris SO yang dibatalkan.
     */
    private function wsGarment($tglAwal, $tglAkhir, $buyer, array $idBaris = array(), array $ws = array())
    {
        // Satu baris = satu WS + satu WARNA. Size sengaja digabung: yang ditagih
        // per warna, dan qty-nya pun diketik ulang (pengiriman bisa sebagian),
        // jadi rincian per size tidak menambah apa-apa selain baris.
        //
        // Harga satuannya dihitung dari nilai / qty - kalau size besar lebih
        // mahal, totalnya tetap persis jumlah nilai SO-nya.
        // Biaya jasa costing dihitung PER BARIS di dalam tabel turunan, baru
        // baris-barisnya diringkas. MySQL menolak agregat yang membungkus
        // subquery berkorelasi (error 1111 "Invalid use of group function"),
        // jadi MAX(...) tidak boleh dipasang langsung di sekeliling SELECT itu.
        $dalam = "SELECT a.so_no AS no_so, d.kpno AS ws, d.styleno, d.brand,
                         e.product_group, e.product_item, b.color,
                         d.curr, b.unit AS uom, b.qty, ROUND(b.price, 4) AS price,
                         b.id_so, b.id AS id_so_det, a.so_date,
                         (SELECT IF(ao.curr = 'IDR', ao.val_idr, ao.val_usd)
                            FROM act_others ao
                           WHERE ao.cost_no = d.cost_no
                             AND ao.mattype = 'SERVICE CHARGE'
                           LIMIT 1) AS service_charge
                    FROM so AS a
              INNER JOIN so_det AS b ON b.id_so = a.id
              INNER JOIN act_costing AS d ON d.id = a.id_cost
               LEFT JOIN masterproduct AS e ON e.id = d.id_product
               LEFT JOIN mastersupplier AS f ON f.Id_Supplier = d.id_buyer
                   WHERE (b.cancel IS NULL OR b.cancel <> 'Y')";
        $bind = array();

        // Daftar WS atau daftar so_det mengalahkan rentang tanggal: barisnya
        // sudah ditunjuk satu per satu, jadi tanggal SO-nya tidak relevan -
        // dan waktu dibaca ulang saat Save tanggalnya memang tidak dikirim.
        if ($ws) {
            $dalam .= " AND d.kpno IN (" . implode(',', array_fill(0, count($ws), '?')) . ")";
            $bind = array_merge($bind, array_values($ws));
        } elseif (!$idBaris) {
            $dalam .= " AND a.so_date BETWEEN ? AND ?";
            $bind[] = $tglAwal;
            $bind[] = $tglAkhir;
        }
        if ($buyer !== '' && !$ws) {
            $dalam .= " AND d.id_buyer = ?";
            $bind[] = $buyer;
        }
        // Dipakai waktu simpan: baris tertentu dibaca ulang dari sumbernya.
        if ($idBaris) {
            $dalam .= " AND b.id IN (" . implode(',', array_fill(0, count($idBaris), '?')) . ")";
            $bind = array_merge($bind, array_values($idBaris));
        }

        // Satu baris = satu WS + satu WARNA. Size sengaja digabung: yang ditagih
        // per warna, dan qty-nya pun diketik ulang (pengiriman bisa sebagian),
        // jadi rincian per size tidak menambah apa-apa selain baris.
        //
        // Harga satuannya nilai / qty - kalau size besar lebih mahal, totalnya
        // tetap persis jumlah nilai SO-nya.
        $sql = "SELECT MAX(x.no_so) AS no_so, '' AS sj, NULL AS bppbdate, '' AS shipping_number,
                       x.ws, MAX(x.styleno) AS styleno, MAX(x.brand) AS brand,
                       MAX(x.product_group) AS product_group,
                       MAX(x.product_item) AS product_item,
                       x.color, '' AS size,
                       MAX(x.curr) AS curr, MAX(x.uom) AS uom,
                       SUM(x.qty) AS qty_so,
                       SUM(x.qty) AS qty,
                       ROUND(IF(SUM(x.qty) > 0,
                                SUM(x.qty * x.price) / SUM(x.qty),
                                MAX(x.price)), 4) AS unit_price,
                       ROUND(SUM(x.qty * x.price), 4) AS total_price,
                       MAX(x.id_so) AS id_so, NULL AS id_bppb,
                       MIN(x.id_so_det) AS id_baris,
                       -- Semua baris SO yang diringkas jadi baris ini. Dipakai
                       -- waktu simpan untuk membaca ulang angkanya dari sumbernya.
                       GROUP_CONCAT(x.id_so_det ORDER BY x.id_so_det) AS id_so_det,
                       'GRADE A' AS grade, 'A' AS grade_kode,
                       'WS' AS tipe_sj, 0 AS harga_manual,
                       MAX(x.so_date) AS so_date,
                       MAX(x.service_charge) AS service_charge
                  FROM ($dalam) AS x
              GROUP BY x.ws, x.color
              ORDER BY x.ws, x.color";

        return $this->baris($this->koneksiAr()->select($sql, $bind), 'WS');
    }

    public function daftarSj(Request $request)
    {
        $tglAwal  = $this->tanggal($request->query('tgl_awal'));
        $tglAkhir = $this->tanggal($request->query('tgl_akhir'));
        $buyer    = trim((string) $request->query('buyer', ''));

        if ($tglAwal === null || $tglAkhir === null) {
            return response()->json(['data' => [], 'pesan' => 'Invalid date range.'], 422);
        }

        // Sumber SJ mengikuti profit center, sama seperti Model_nag::cari_sj di AR:
        // NAG dari garment (MySQL), selain itu dari knitting (PostgreSQL).
        // Sengaja TIDAK digabung - kalau user memilih NAK, SJ garment tidak boleh
        // muncul walaupun customer yang dipilih punya SJ di garment.
        $pc = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->query('profit_center', 'NAG')));
        $pesan = null;

        // Kalau layar Edit yang memanggil, baris milik invoice itu sendiri tetap
        // boleh muncul - kalau tidak, baris yang sudah dia pakai malah hilang.
        $abaikan = $request->query('abaikan');

        if ($pc === 'NAG') {
            $baris = $this->sjGarment($tglAwal, $tglAkhir, $buyer);
        } else {
            try {
                $baris = $this->sjKnitting($tglAwal, $tglAkhir, $buyer);
            } catch (\Throwable $e) {
                Log::warning('Invoice EXIM: SJ knitting gagal diambil - ' . $e->getMessage());
                $baris = array();
                $pesan = 'Knitting SJ could not be loaded (database not configured or unreachable).';
            }
        }

        $baris = $this->saringTerpakai($baris, $pc, $abaikan);

        usort($baris, function ($a, $b) {
            return strcmp((string) $a['sj'], (string) $b['sj']);
        });

        return response()->json(['data' => $baris, 'pesan' => $pesan]);
    }

    /**
     * Data satu invoice untuk dicetak.
     *
     * Dipakai bersama oleh PDF dan Excel supaya isi keduanya tidak pernah
     * berbeda. Formatnya mengikuti Model_nag::report_invoice_detail di AR.
     *
     * Invoice berstatus CANCEL ditolak di sini (404): yang dibatalkan tidak
     * boleh punya cetakan yang beredar. Rinciannya tetap bisa dilihat lewat
     * detail(), yang memang tidak menyaring status.
     */
    protected function dataCetakLocal($id)
    {
        $id = (int) $id;
        if ($id < 1 || !$this->tabelAda(self::TABEL_DET)) {
            abort(404);
        }

        $db = $this->koneksiAr();

        $h = $db->select(
            "SELECT b.id, b.no_invoice, b.shipp, b.status, b.doc_type, b.doc_number,
                    b.id_customer_ship, b.id_customer,
                    b.profit_center, b.curr, b.value, b.booking_by, b.booking_date,
                    DATE(b.tgl_book_inv) AS tgl_book_inv, b.tgl_inv, b.sj_date,
                    UPPER(cs.Supplier) AS customer, cs.alamat AS alamat, cs.Phone AS phone,
                    UPPER(cb.Supplier) AS bill_customer, cb.alamat AS bill_alamat,
                    t.type
               FROM tbl_book_invoice b
               LEFT JOIN mastersupplier cs ON cs.Id_Supplier = b.id_customer_ship
               LEFT JOIN mastersupplier cb ON cb.Id_Supplier = b.id_customer
               LEFT JOIN tbl_type t        ON t.id           = b.id_type
              WHERE b.id = ?
                AND b.shipp = ?
                AND UPPER(b.status) <> 'CANCEL'
                -- Invoice yang dibuat dari WS saja belum punya baris SJ.
                AND (EXISTS (SELECT 1 FROM " . self::TABEL_DET . " d WHERE d.id_book_invoice = b.id)"
             . ($this->tabelAda(self::TABEL_SO)
                 ? " OR EXISTS (SELECT 1 FROM " . self::TABEL_SO . " s WHERE s.id_book_invoice = b.id)"
                 : '')
             . ")
              LIMIT 1",
            array($id, self::SHIPP_LOCAL)
        );
        if (!$h) {
            abort(404);
        }

        $inv = (array) $h[0];
        $inv['tgl_inv'] = $inv['tgl_inv'] ?: $inv['tgl_book_inv'];
        $inv['type'] = strtoupper((string) $inv['type']);

        // PO konsumen cuma ada setelah migrasi 20260920 dijalankan; sebelum itu
        // kolomnya diisi tanda "-" supaya cetakan knitting tetap bisa dibuka.
        $po = $this->kolomAda(self::TABEL_DET, 'po_konsumen') ? 'po_konsumen' : "'-'";

        $det = array();
        foreach ($db->select(
            // Format angkanya dibuat sama persis dengan Model_nag::report_invoice_detail
            // di AR (3 desimal untuk unit price, 2 untuk total), termasuk urutannya.
            "SELECT styleno, product_group, product_item, color, size, qty,
                    FORMAT(ROUND(unit_price, 3), 3) AS unit_price, disc,
                    FORMAT(total_price, 2) AS total_price, uom, curr, id_bppb,
                    $po AS po_konsumen
               FROM " . self::TABEL_DET . " WHERE id_book_invoice = ? ORDER BY id_bppb ASC",
            array($id)
        ) as $r) {
            $det[] = (array) $r;
        }

        // Invoice boleh dibuat sebelum SJ-nya terbit. Warna yang belum punya
        // baris SJ dicetak dari baris SO-nya - nama barang, warna & harganya
        // memang sudah ada di SO. Warna yang SJ-nya sudah ada TIDAK diambil
        // dari sini, supaya tidak tercetak dua kali.
        $det = array_merge($det, $this->barisCetakSo($db, $id, $det));

        $adaTgl = $this->kolomAda(self::TABEL_POT, 'tgl_invoice');
        $p = $db->select(
            "SELECT total, discount, dp, dp_cbd, retur, twot, vat, grand_total"
            . ($adaTgl ? ", tgl_invoice" : "") . "
               FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1",
            array($id)
        );
        // Tanggal cetakan: Invoice Date yang diisi di form, baru jatuh ke
        // tanggal milik AR / tanggal booking kalau memang belum ada.
        if ($p && !empty($p[0]->tgl_invoice)) {
            $inv['tgl_inv'] = $p[0]->tgl_invoice;
        }
        $pot = $p ? (array) $p[0] : array('total' => 0, 'discount' => 0, 'dp' => 0, 'dp_cbd' => 0,
            'retur' => 0, 'twot' => 0, 'vat' => 0, 'grand_total' => 0);
        unset($pot['tgl_invoice']);
        foreach ($pot as $k => $v) {
            $pot[$k] = number_format((float) $v, 2, '.', ',');
        }

        return array(
            'data_invoice'        => $inv,
            'data_invoice_detail' => $det,
            'data_invoice_pot'    => $pot,
            // Template mengakses baris ini sebagai array, bukan objek.
            // Baris BPPB# diisi nomor FG/OUT, bukan nomor SJ.
            'group_bppb_number'   => $this->kolomArray($db, self::TABEL_DET, 'shipp_number', $id),
            'group_so_number'     => $this->kolomArray($db, self::TABEL_DET, 'so_number', $id),
            // mPDF membaca logo dari berkas lokal, jadi yang dikirim path penuh.
            'logo'                => str_replace('\\', '/', public_path('img/nag_logo3.jpg')),
        );
    }

    /**
     * Cetak PDF invoice EXIM.
     *
     * Templatenya berangkat dari reportinvoice3.php di AR dan mesinnya juga
     * mPDF, jadi bentuknya sama. Bedanya sesuai permintaan EXIM: Terms Of
     * Payment, blok Bank dan blok NOTE dibuang, BPPB# diisi nomor FG/OUT
     * (bukan nomor SJ), dan tanda tangan cuma satu kolom di kanan.
     * Sumber datanya dari tabel booking EXIM, bukan tbl_invoice_detail.
     */
    public function pdfLocal(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));

        // &gaya=lama membuka tampilan sebelum gaya CARING - cadangan pembanding.
        if ($this->gayaLama($request)) {
            $mpdf = new \Mpdf\Mpdf(array('tempDir' => storage_path('app/mpdf')));
            $mpdf->setFooter('{PAGENO} / {nbpg}');
            $mpdf->WriteHTML(view('export-import.invoice.local.pdf-lama', $data)->render());
        } else {
            $mpdf = $this->mpdfCaring();
            $mpdf->SetTitle($data['data_invoice']['no_invoice']);
            $mpdf->WriteHTML(view('export-import.invoice.local.pdf', $data + $this->asetCaring())->render());
        }

        return response($mpdf->Output('', 'S'), 200, array(
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->namaBerkas($data) . '.pdf"',
        ));
    }

    /**
     * Baris cetakan yang berasal dari SO (WS), untuk warna yang belum punya SJ.
     *
     * Bentuk barisnya disamakan dengan baris SJ - termasuk format angkanya -
     * supaya template cetakannya tidak perlu tahu baris itu datang dari mana.
     * Kolom yang cuma ada di SJ (nomor SJ, size) dikosongkan.
     *
     * @param array $sudah baris SJ yang sudah terkumpul; warnanya tidak diulang
     */
    private function barisCetakSo($db, $id, array $sudah)
    {
        if (!$this->tabelAda(self::TABEL_SO)) {
            return array();
        }

        $adaWarna = array();
        foreach ($sudah as $r) {
            $adaWarna[$this->kunciWarna($r)] = true;
        }

        $out = array();
        foreach ($db->select(
            "SELECT styleno, product_group, product_item, color, '' AS size, qty,
                    FORMAT(ROUND(unit_price, 3), 3) AS unit_price, disc,
                    FORMAT(total_price, 2) AS total_price, uom, curr,
                    NULL AS id_bppb, NULL AS po_konsumen
               FROM " . self::TABEL_SO . " WHERE id_book_invoice = ? ORDER BY id ASC",
            array($id)
        ) as $r) {
            $r = (array) $r;
            if (isset($adaWarna[$this->kunciWarna($r)])) { continue; }
            $out[] = $r;
        }
        return $out;
    }

    /** Minta tampilan lama (sebelum gaya CARING)? - ?gaya=lama */
    private function gayaLama(Request $request)
    {
        return strtolower(trim((string) $request->query('gaya'))) === 'lama';
    }

    /**
     * mPDF untuk PDF bergaya CARING - pengaturannya disamakan dengan PDF Debit
     * Note di AR (Arnag::_dn_mpdf_baru): margin bawah dilebihkan untuk pita
     * merah di kaki halaman, dan font Dancing Script untuk tulisan "Caring -".
     */
    private function mpdfCaring($marginBawah = 30)
    {
        $bawaan = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $font   = (new \Mpdf\Config\FontVariables())->getDefaults();

        return new \Mpdf\Mpdf(array(
            'tempDir'       => storage_path('app/mpdf'),
            'format'        => 'A4',
            'margin_top'    => 7,
            // Kaki halaman tumbuh ke atas dari margin_footer; pitanya setinggi
            // 16mm, jadi margin bawah harus >= 4 + tinggi pita + baris tulisan.
            'margin_bottom' => $marginBawah,
            'margin_footer' => 4,
            'margin_left'   => 8,
            'margin_right'  => 8,
            'fontDir'       => array_merge($bawaan['fontDir'], array(resource_path('fonts/dancing-script'))),
            'fontdata'      => $font['fontdata'] + array(
                'dancingscript' => array('R' => 'DancingScript.ttf'),
            ),
        ));
    }

    /** Gambar gaya CARING (path penuh - mPDF membaca dari berkas lokal). */
    private function asetCaring()
    {
        return array(
            'gbr_caring' => str_replace('\\', '/', public_path('img/caring/caring.svg')),
            'gbr_pita'   => str_replace('\\', '/', public_path('img/caring/pita-kaki.svg')),
        );
    }

    /**
     * Cetak PDF invoice Local - bentuk RINGKAS.
     *
     * Bentuk kedua di samping PDF rinci di atas, mengikuti berkas contoh yang
     * dipakai NAK: barisnya digabung per produk (tidak dipecah per style,
     * warna dan size), potongan yang nol tidak ikut dicetak, dan yang tampil
     * cuma Total - PPN - TOTAL. Datanya diambil dari sumber yang sama dengan
     * PDF rinci, jadi angkanya tidak mungkin berbeda.
     */
    public function pdfLocalRingkas(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));
        $data = array_merge($data, $this->ringkasCetakLocal($data));

        // &gaya=lama membuka tampilan sebelum gaya CARING - cadangan pembanding.
        if ($this->gayaLama($request)) {
            $mpdf = new \Mpdf\Mpdf(array(
                'tempDir'       => storage_path('app/mpdf'),
                'format'        => 'A4',
                'margin_left'   => 12,
                'margin_right'  => 12,
                'margin_top'    => 10,
                'margin_bottom' => 14,
            ));
            $mpdf->setFooter('{PAGENO} / {nbpg}');
            $tampilan = 'export-import.invoice.local.pdf-ringkas-lama';
        } else {
            $mpdf = $this->mpdfCaring();
            $data += $this->asetCaring();
            $tampilan = 'export-import.invoice.local.pdf-ringkas';
        }
        $mpdf->SetTitle($data['data_invoice']['no_invoice']);
        $mpdf->WriteHTML(view($tampilan, $data)->render());

        return response($mpdf->Output('', 'S'), 200, array(
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->namaBerkas($data) . '_ringkas.pdf"',
        ));
    }

    /**
     * Cetak PDF invoice Local - bentuk KNITTING.
     *
     * Bentuk ketiga, khusus invoice knitting (NAK): tanpa kop surat, dibuka
     * blok CONSIGNOR / CONSIGNEE / BILL TO, lalu satu tabel berbingkai
     * (Invoice Date, Product Item, Style/Color, PO, Qty, Price, Total) dan
     * keterangan pengapalan & bank di bawahnya. Susunannya mengikuti berkas
     * contoh yang dipakai tim knitting.
     */
    public function pdfLocalKnitting(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));
        $data = array_merge($data, $this->knittingCetakLocal($data));

        // &gaya=lama = PDF Classic (tanpa CARING, pakai kop surat lama).
        if ($this->gayaLama($request)) {
            $mpdf = new \Mpdf\Mpdf(array(
                'tempDir'       => storage_path('app/mpdf'),
                'format'        => 'A4',
                'margin_left'   => 12,
                'margin_right'  => 12,
                'margin_top'    => 12,
                'margin_bottom' => 14,
            ));
            $mpdf->setFooter('{PAGENO} / {nbpg}');
            $tampilan = 'export-import.invoice.local.pdf-knitting-lama';
        } else {
            $mpdf = $this->mpdfCaring();
            $data += $this->asetCaring();
            $tampilan = 'export-import.invoice.local.pdf-knitting';
        }
        $mpdf->SetTitle($data['data_invoice']['no_invoice']);
        $mpdf->WriteHTML(view($tampilan, $data)->render());

        return response($mpdf->Output('', 'S'), 200, array(
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->namaBerkas($data) . '_knitting.pdf"',
        ));
    }

    /**
     * Bahan tambahan untuk PDF knitting.
     *
     * Barisnya digabung per produk + warna + PO + harga satuan, angkanya
     * ditulis gaya Indonesia, dan blok bank diambil dari acuan yang menempel
     * di booking (id_top / id_bank milik tbl_book_invoice). Kalau acuannya
     * belum diisi, bloknya tidak dicetak - lebih baik kosong daripada salah
     * nomor rekening.
     */
    private function knittingCetakLocal(array $data)
    {
        $inv = $data['data_invoice'];
        $angka = function ($teks) { return (float) str_replace(',', '', (string) $teks); };
        $tulis = function ($n) { return number_format((float) $n, 2, ',', '.'); };

        $grup = array();
        foreach ($data['data_invoice_detail'] as $r) {
            $po = trim((string) $r['po_konsumen']);
            $kunci = $r['product_item'] . '|' . $r['color'] . '|' . $po . '|' . $r['unit_price'];
            if (!isset($grup[$kunci])) {
                $grup[$kunci] = array(
                    'produk' => trim((string) $r['product_item']),
                    'warna'  => trim((string) $r['color']),
                    'po'     => $po === '' ? '-' : $po,
                    'qty'    => 0,
                    'total'  => 0,
                );
            }
            $grup[$kunci]['qty']   += (float) $r['qty'];
            $tp = $angka($r['total_price']);
            $grup[$kunci]['total'] += $tp;
        }

        $baris = array();
        foreach ($grup as $g) {
            $satuan = $g['qty'] > 0 ? $g['total'] / $g['qty'] : 0;
            $baris[] = array(
                'produk'     => $g['produk'],
                'warna'      => $g['warna'],
                'po'         => $g['po'],
                'qty'        => $tulis($g['qty']),
                'unit_price' => $tulis($satuan),
                'total'      => $tulis($g['total']),
                // Angka mentah untuk Excel.
                'qty_n'      => $g['qty'],
                'unit_n'     => $satuan,
                'total_n'    => $g['total'],
            );
        }

        // Satuan & mata uang dipakai di judul kolom, jadi diambil yang pertama.
        $uom = $curr = '';
        foreach ($data['data_invoice_detail'] as $r) {
            if ($uom === '')  { $uom  = trim((string) $r['uom']); }
            if ($curr === '') { $curr = trim((string) $r['curr']); }
        }

        // Alamat dari master ditulis huruf besar semua; di cetakan knitting
        // ditulis Kapital Di Awal Kata supaya lebih enak dibaca.
        $alamat = function ($teks) {
            return $this->alamatKapitalAwal($teks);
        };

        return array(
            'baris_knit'   => $baris,
            'kol_uom'      => $uom !== '' ? $uom : '-',
            'kol_curr'     => $curr !== '' ? $curr : (string) $inv['curr'],
            'tgl_knit'     => $this->tanggalKnitting($inv['tgl_inv']),
            'kirim_atas'   => trim((string) $inv['bill_customer']) !== ''
                ? $inv['bill_customer'] : $inv['customer'],
            'consignee'    => array('nama' => (string) $inv['customer'],
                                    'baris' => $alamat($inv['alamat'])),
            'bill_to'      => array('nama' => (string) $inv['bill_customer'],
                                    'baris' => $alamat($inv['bill_alamat'])),
            'bayar'        => $this->acuanBayarLocal($inv),
        );
    }

    /**
     * Cara bayar & rekening bank yang dicetak di PDF knitting.
     *
     * Rekeningnya diambil dari master_supplier_bank (master yang diisi lewat
     * aplikasi AP) milik pihak BILL TO - di invoice knitting pembayarannya
     * lewat rekening pihak itu (contoh: Teejay Mauritius -> HSBC Mauritius).
     * Yang diambil cuma yang berstatus Active, dan kalau ada lebih dari satu,
     * yang mata uangnya sama dengan invoice didahulukan.
     *
     * Yang belum ada di master dicetak "-" - tinggal dilengkapi di masternya,
     * cetakan langsung ikut tanpa mengubah kode. Tabel itu belum punya kolom
     * SWIFT; kalau nanti ditambah dengan nama swift_code, langsung terbaca.
     *
     * Payment Terms bukan data bank: diambil dari tbl_master_top (id_top
     * booking) kalau ada, selain itu "Direct LC" seperti berkas contoh.
     */
    private function acuanBayarLocal(array $inv)
    {
        $hasil = array('top' => 'Direct LC', 'bank' => '-', 'no_rek' => '-', 'swift' => '-', 'curr' => '-');
        $db = $this->koneksiAr();

        // ---- Payment Terms ----
        try {
            $t = $db->select(
                "SELECT tp.type AS tipe_top, tp.top AS lama_top
                   FROM tbl_book_invoice b
                   JOIN tbl_master_top tp ON tp.id = b.id_top
                  WHERE b.id = ? LIMIT 1",
                array($inv['id'])
            );
            if ($t && trim((string) $t[0]->tipe_top) !== '') {
                // Tenor berupa angka hari ditulis "Within 30 days of invoice
                // date"; selain itu apa adanya (mis. "Direct LC").
                $hasil['top'] = (int) $t[0]->lama_top > 0
                    ? 'Within ' . (int) $t[0]->lama_top . ' days of invoice date'
                    : trim((string) $t[0]->tipe_top);
            }
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM: payment terms gagal dibaca - ' . $e->getMessage());
        }

        // ---- Rekening bank pihak BILL TO ----
        $idCustomer = trim((string) $inv['id_customer']);
        if ($idCustomer === '' || !$this->tabelAda('master_supplier_bank')) {
            return $hasil;
        }
        $kolSwift = $this->kolomAda('master_supplier_bank', 'swift_code') ? 'swift_code' : "''";
        try {
            $r = $db->select(
                "SELECT bank_name, bank_account, bank_currency, $kolSwift AS swift
                   FROM master_supplier_bank
                  WHERE id_supplier = ?
                    AND tipe_sup <> 'S'
                    AND status = 'Active'
                  ORDER BY (UPPER(TRIM(bank_currency)) = ?) DESC, id DESC
                  LIMIT 1",
                array($idCustomer, strtoupper(trim((string) $inv['curr'])))
            );
            if ($r) {
                $isi = function ($v) {
                    $v = trim((string) $v);
                    return $v === '' ? '-' : $v;
                };
                $hasil['bank']   = $isi($r[0]->bank_name);
                $hasil['no_rek'] = $isi($r[0]->bank_account);
                $hasil['swift']  = $isi($r[0]->swift);
                $hasil['curr']   = $isi($r[0]->bank_currency);
            }
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM: rekening pelanggan gagal dibaca - ' . $e->getMessage());
        }
        return $hasil;
    }

    /**
     * Alamat untuk cetakan: dipecah per baris, spasinya dibersihkan, lalu
     * ditulis Kapital Di Awal Kata. Singkatan yang memang huruf besar (RT, RW,
     * PT, CV, KM, DKI, PO) dikembalikan jadi huruf besar.
     *
     * Spasi tak-putus (U+00A0) ikut diganti spasi biasa - termasuk yang
     * tersimpan dobel-encoding sehingga tampil sebagai "Â" di PDF.
     */
    private function alamatKapitalAwal($teks)
    {
        $teks = str_replace(array("\xC3\x82\xC2\xA0", "\xC3\x82 ", "\xC2\xA0"), ' ', (string) $teks);
        $hasil = array();
        foreach (preg_split('/\r\n|\r|\n/', $teks) as $b) {
            $b = trim(preg_replace('/\s+/u', ' ', $b));
            if ($b === '') {
                continue;
            }
            $b = mb_convert_case(mb_strtolower($b, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            $b = preg_replace_callback('/\b(Rt|Rw|Pt|Cv|Km|Dki|Po)\b/u', function ($m) {
                return strtoupper($m[1]);
            }, $b);
            // Bilangan urutan tetap huruf kecil: "3Rd" -> "3rd".
            $b = preg_replace_callback('/(\d)(St|Nd|Rd|Th)\b/u', function ($m) {
                return $m[1] . strtolower($m[2]);
            }, $b);
            $hasil[] = $b;
        }
        return $hasil;
    }

    /** Tanggal bentuk "17/Sep/2026" seperti di berkas contoh knitting. */
    private function tanggalKnitting($nilai)
    {
        $iso = $this->tanggal($nilai);
        return $iso === null ? (string) $nilai : date('d/M/Y', strtotime($iso));
    }

    /**
     * Bahan tambahan untuk PDF Local ringkas.
     *
     * Yang disiapkan di sini: baris per produk, total qty, rekap yang hanya
     * berisi potongan terpakai, NPWP tujuan, tanggal panjang dan simbol mata
     * uangnya.
     */
    private function ringkasCetakLocal(array $data)
    {
        $inv = $data['data_invoice'];
        $pot = $data['data_invoice_pot'];

        // Angka dari database sudah diformat pakai koma ribuan; dibalikkan lagi
        // supaya bisa dijumlah.
        $angka = function ($teks) { return (float) str_replace(',', '', (string) $teks); };
        $tulis = function ($n) { return number_format((float) $n, 2, '.', ','); };

        // ---- Baris digabung per produk + harga satuan ----
        // GARMENT: yang menentukan cuma nama item & harganya - warna tidak ikut,
        // jadi "POLO SHIRT MOCCA" & "POLO SHIRT BROWN" yang harganya sama jadi
        // SATU baris "POLO SHIRT". Knitting tetap memakai warna, karena di sana
        // warnanya memang bagian dari barangnya.
        $garment = strtoupper(trim((string) (isset($inv['profit_center']) ? $inv['profit_center'] : ''))) !== 'NAK';
        $grup = array();
        $totalQty = 0;
        foreach ($data['data_invoice_detail'] as $r) {
            $nama = $garment
                ? trim((string) $r['product_item'])
                : trim(trim((string) $r['product_item']) . ' ' . trim((string) $r['color']));
            if ($nama === '') {
                $nama = trim((string) $r['styleno']);
            }
            $kunci = $nama . '|' . $r['unit_price'];
            if (!isset($grup[$kunci])) {
                $grup[$kunci] = array('nama' => $nama, 'qty' => 0, 'total' => 0);
            }
            $grup[$kunci]['qty']   += (float) $r['qty'];
            $grup[$kunci]['total'] += $angka($r['total_price']);
            $totalQty              += (float) $r['qty'];
        }

        $baris = array();
        foreach ($grup as $g) {
            // Harga satuan dihitung ulang dari nilainya, jadi sisa pembulatan
            // per baris tidak ikut tercetak.
            $satuan = $g['qty'] > 0 ? $g['total'] / $g['qty'] : 0;
            $baris[] = array(
                'nama'       => $g['nama'],
                'qty'        => $tulis($g['qty']),
                'unit_price' => $tulis($satuan),
                'total'      => $tulis($g['total']),
                // Angka mentah untuk Excel (selnya tetap bisa dihitung).
                'qty_n'      => $g['qty'],
                'unit_n'     => $satuan,
                'total_n'    => $g['total'],
            );
        }

        // ---- Rekap: Total, potongan yang terpakai, lalu PPN ----
        $rekap = array(
            // "Subtotal": ini jumlah baris sebelum potongan & PPN - yang
            // bernama TOTAL cuma yang paling bawah.
            array('label' => 'Subtotal', 'nilai' => $tulis($angka($pot['total'])), 'angka' => $angka($pot['total']),
                  'tebal' => true, 'garis' => true),
        );
        foreach (array('discount' => 'Discount', 'dp' => 'Down Payment',
                       'dp_cbd' => 'DP/CBD from Invoice', 'retur' => 'Return') as $kunci => $label) {
            if (isset($pot[$kunci]) && $angka($pot[$kunci]) != 0) {
                $rekap[] = array('label' => $label, 'nilai' => $tulis($angka($pot[$kunci])),
                                 'angka' => $angka($pot[$kunci]), 'tebal' => false, 'garis' => false);
            }
        }
        // Invoice tanpa PPN (mis. non commercial) tidak perlu barisnya sama
        // sekali - cukup satu baris Total.
        $adaVat = $angka($pot['vat']) != 0;
        if ($adaVat) {
            $rekap[] = array(
                'label' => 'PPN ' . $this->persenVatLocal($inv['id']) . '%',
                'nilai' => $tulis($angka($pot['vat'])), 'angka' => $angka($pot['vat']),
                'tebal' => false, 'garis' => false,
            );
        }

        // Alamat dipecah per baris supaya panjangnya tidak menabrak kolom kanan.
        $alamat = preg_split('/\r\n|\r|\n/', (string) $inv['alamat']);
        $alamat = array_values(array_filter(array_map('trim', $alamat), function ($b) {
            return $b !== '';
        }));

        $curr = strtoupper(trim((string) $inv['curr']));

        return array(
            'baris_produk'     => $baris,
            'total_qty'        => $tulis($totalQty),
            'baris_rekap'      => $rekap,
            // Baris TOTAL paling bawah cuma perlu kalau ada yang menambah atau
            // mengurangi - kalau tidak, angkanya sama dengan baris Total.
            'pakai_grand'      => count($rekap) > 1,
            'grand_total_teks' => $tulis($angka($pot['grand_total'])),
            'total_qty_n'      => $totalQty,
            'grand_n'          => $angka($pot['grand_total']),
            'tgl_cetak'        => $this->tanggalPanjang($inv['tgl_inv']),
            'tujuan_npwp'      => $this->npwpPelanggan($inv),
            'alamat_baris'     => $alamat,
            'simbol'           => ($curr === 'IDR' || $curr === 'RP' || $curr === '') ? 'Rp' : $curr,
        );
    }

    /** Tarif PPN yang tersimpan untuk invoice ini; 11 kalau belum tercatat. */
    private function persenVatLocal($idBook)
    {
        if (!$this->kolomAda(self::TABEL_POT, 'vat_persen')) {
            return '11';
        }
        $r = $this->koneksiAr()->select(
            "SELECT vat_persen FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1",
            array($idBook)
        );
        $p = $r ? (float) $r[0]->vat_persen : 0;
        return $p > 0 ? rtrim(rtrim(number_format($p, 2, '.', ''), '0'), '.') : '11';
    }

    /**
     * NPWP pelanggan untuk baris "To" di PDF ringkas.
     *
     * Kalau kolomnya tidak ada atau masih kosong, yang dicetak namanya - lebih
     * baik daripada barisnya kosong melompong.
     */
    private function npwpPelanggan(array $inv)
    {
        $id = isset($inv['id_customer_ship']) ? $inv['id_customer_ship'] : null;
        if ($id && $this->kolomAda('mastersupplier', 'npwp')) {
            try {
                $r = $this->koneksiAr()->select(
                    "SELECT npwp FROM mastersupplier WHERE Id_Supplier = ? LIMIT 1",
                    array($id)
                );
                if ($r && trim((string) $r[0]->npwp) !== '') {
                    return trim((string) $r[0]->npwp);
                }
            } catch (\Throwable $e) {
                Log::warning('Invoice EXIM: NPWP pelanggan gagal dibaca - ' . $e->getMessage());
            }
        }
        return (string) $inv['customer'];
    }

    /** Tanggal bentuk panjang, mis. "09 September 2026". */
    private function tanggalPanjang($nilai)
    {
        $iso = $this->tanggal($nilai);
        return $iso === null ? (string) $nilai : date('d F Y', strtotime($iso));
    }

    /**
     * Cetak Excel invoice EXIM.
     *
     * Susunannya mengikuti PDF-nya baris per baris: kop surat, judul, blok
     * keterangan, tabel detail, rekap potongan, lalu tanda tangan di kanan.
     * Angkanya ditulis sebagai angka sungguhan (bukan teks) dengan format
     * tampilan yang sama, jadi terlihat sama tapi masih bisa dihitung.
     */
    public function excelLocal(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));
        $inv  = $data['data_invoice'];
        $det  = $data['data_invoice_detail'];
        $pot  = $data['data_invoice_pot'];
        $curr = (string) $inv['curr'];

        // Angka dari database sudah diformat pakai koma ribuan, dibalikkan lagi
        // jadi angka supaya selnya tetap bisa dipakai berhitung di Excel.
        $angka = function ($teks) { return (float) str_replace(',', '', (string) $teks); };

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle('Invoice');
        // Garis kotak bawaan Excel dimatikan - yang terlihat hanya bingkai tabelnya.
        $s->setShowGridlines(false);

        foreach (array('A' => 16, 'B' => 12, 'C' => 26, 'D' => 9,
                       'E' => 7, 'F' => 18, 'G' => 6, 'H' => 16) as $kol => $lebar) {
            $s->getColumnDimension($kol)->setWidth($lebar);
        }

        // ---------------- Kop surat ----------------
        $this->kopExcelLocal($s);

        foreach (array(7 => trim($inv['type'] . ' INVOICE'),
                       8 => (string) $inv['no_invoice']) as $baris => $isi) {
            $s->mergeCells('A' . $baris . ':H' . $baris);
            $s->setCellValue('A' . $baris, $isi);
            $s->getStyle('A' . $baris)->getFont()->setBold(true)->setSize(11);
            $s->getStyle('A' . $baris)->getAlignment()->setHorizontal('center');
        }

        // ---------------- Blok keterangan ----------------
        $gabung = function ($daftar, $kolom) {
            $out = array();
            foreach ($daftar as $r) {
                $out[] = $r[$kolom];
            }
            return implode(' , ', $out);
        };

        $ket = array(
            'Date'         => (string) $inv['tgl_inv'],
            'To'           => (string) $inv['customer'],
            'Address'      => (string) $inv['alamat'],
            'Telp.'        => (string) $inv['phone'],
            'BPPB#'        => $gabung($data['group_bppb_number'], 'shipp_number'),
            'SALES ORDER#' => $gabung($data['group_so_number'], 'so_number'),
        );

        $b = 10;
        foreach ($ket as $label => $nilai) {
            $s->mergeCells('A' . $b . ':B' . $b);
            $s->setCellValue('A' . $b, $label);
            $s->mergeCells('C' . $b . ':H' . $b);
            $s->setCellValueExplicit(
                'C' . $b,
                ': ' . $nilai,
                \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING
            );
            $s->getStyle('A' . $b . ':H' . $b)->getFont()->setSize(10);
            $s->getStyle('A' . $b . ':H' . $b)->getAlignment()->setVertical('top');
            $b++;
        }

        // ---------------- Tabel detail ----------------
        $bKepala = $b + 1;
        foreach (array('A' => 'Style', 'B' => 'Color', 'C' => 'Product Item',
                       'D' => 'Quantity', 'F' => 'Unit Price', 'G' => 'Total') as $kol => $judul) {
            $s->setCellValue($kol . $bKepala, $judul);
        }
        $s->mergeCells('D' . $bKepala . ':E' . $bKepala);
        $s->mergeCells('G' . $bKepala . ':H' . $bKepala);
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getFont()->setBold(true);
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getAlignment()->setHorizontal('center');
        // Latar abu tipis: kepala tabel kebaca sebagai judul kolom, bukan baris data.
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFF1F5F9');

        $b = $bKepala + 1;
        $bAwalIsi = $b;
        $totQty = 0;
        foreach ($det as $r) {
            $totQty += (float) $r['qty'];
            $s->setCellValue('A' . $b, (string) $r['styleno']);
            $s->setCellValue('B' . $b, (string) $r['color']);
            $s->setCellValue('C' . $b, trim($r['product_item'] . ' (' . $r['size'] . ')'));
            $s->setCellValue('D' . $b, (float) $r['qty']);
            $s->setCellValue('E' . $b, (string) $r['uom']);
            $s->setCellValue('F' . $b, $angka($r['unit_price']));
            $s->setCellValue('G' . $b, (string) $r['curr']);
            $s->setCellValue('H' . $b, $angka($r['total_price']));
            $b++;
        }
        $bAkhirIsi = $b - 1;

        if ($bAkhirIsi >= $bAwalIsi) {
            $s->getStyle('D' . $bAwalIsi . ':D' . $bAkhirIsi)->getNumberFormat()->setFormatCode('#,##0');
            $s->getStyle('E' . $bAwalIsi . ':E' . $bAkhirIsi)->getAlignment()->setHorizontal('center');
            // Di PDF unit price ditulis "IDR 24,684.685" dalam satu sel.
            $s->getStyle('F' . $bAwalIsi . ':F' . $bAkhirIsi)->getNumberFormat()
                ->setFormatCode('"' . $curr . '" #,##0.000');
            $s->getStyle('G' . $bAwalIsi . ':G' . $bAkhirIsi)->getAlignment()->setHorizontal('center');
            $s->getStyle('H' . $bAwalIsi . ':H' . $bAkhirIsi)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // Di PDF ada satu baris yang isinya cuma jumlah qty.
        $s->setCellValue('D' . $b, $totQty);
        $s->getStyle('D' . $b)->getNumberFormat()->setFormatCode('#,##0');
        $b++;

        // ---------------- Rekap potongan ----------------
        $rekap = array(
            'Total'                        => $pot['total'],
            'Discount'                     => $pot['discount'],
            'Down Payment'                 => $pot['dp'],
            'Return'                       => $pot['retur'],
            'Total Before Value Added Tax' => $pot['twot'],
            'Value Added Tax'              => $pot['vat'],
            'Grand Total'                  => $pot['grand_total'],
        );
        // Label rekap rata kanan supaya menempel ke angkanya; baris yang
        // menentukan (Total & Grand Total) ditebalkan dan diberi latar.
        $tegas = array('Total', 'Grand Total');
        foreach ($rekap as $label => $nilai) {
            $s->mergeCells('A' . $b . ':F' . $b);
            $s->setCellValue('A' . $b, $label);
            $s->getStyle('A' . $b)->getAlignment()->setHorizontal('right');
            $s->setCellValue('G' . $b, $curr);
            $s->getStyle('G' . $b)->getAlignment()->setHorizontal('center');
            $s->setCellValue('H' . $b, $angka($nilai));
            $s->getStyle('H' . $b)->getNumberFormat()->setFormatCode('#,##0.00');
            $s->getStyle('H' . $b)->getAlignment()->setHorizontal('right');
            if (in_array($label, $tegas, true)) {
                $s->getStyle('A' . $b . ':H' . $b)->getFont()->setBold(true);
                $s->getStyle('A' . $b . ':H' . $b)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF1F5F9');
            }
            $b++;
        }
        $bAkhirTabel = $b - 1;

        $s->getStyle('A' . $bKepala . ':H' . $bAkhirTabel)->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $s->getStyle('A' . $bKepala . ':H' . $bAkhirTabel)->getFont()->setSize(10);

        // ---------------- Tanda tangan: kanan, pakai garis ----------------
        $b += 2;
        $s->mergeCells('G' . $b . ':H' . $b);
        $s->setCellValue('G' . $b, 'Approved By');
        $s->getStyle('G' . $b)->getAlignment()->setHorizontal('center');
        $s->getStyle('G' . $b)->getFont()->setSize(10);

        $bTtd = $b + 4;   // ruang kosong untuk tanda tangan basah
        $s->mergeCells('G' . $bTtd . ':H' . $bTtd);
        $s->setCellValue('G' . $bTtd, 'Yus Yulius');
        $s->getStyle('G' . $bTtd)->getAlignment()->setHorizontal('center');
        $s->getStyle('G' . $bTtd)->getFont()->setSize(10);
        $s->getStyle('G' . $bTtd . ':H' . $bTtd)->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $s->mergeCells('G' . ($bTtd + 1) . ':H' . ($bTtd + 1));
        $s->setCellValue('G' . ($bTtd + 1), 'Exim Manager');
        $s->getStyle('G' . ($bTtd + 1))->getAlignment()->setHorizontal('center');
        $s->getStyle('G' . ($bTtd + 1))->getFont()->setSize(10);

        // Siap cetak tanpa diatur ulang, sama dengan Excel Invoice Export.
        $this->aturCetakExcelLocal($s, 'A1:H' . ($bTtd + 2));

        return $this->kirimExcel($ss, $this->namaBerkas($data) . '.xlsx');
    }
    /**
     * Kop surat Excel Invoice Local: logo + nama perusahaan + alamat + garis
     * tebal - sama dengan kop PDF Local. Dipakai Excel Detail & Summary.
     */
    private function kopExcelLocal(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s)
    {
        $logo = public_path('img/nag_logo3.jpg');
        if (is_file($logo)) {
            $gbr = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $gbr->setPath($logo);
            $gbr->setHeight(58);
            $gbr->setCoordinates('A1');
            $gbr->setOffsetX(5);
            $gbr->setOffsetY(5);
            $gbr->setWorksheet($s);
        }

        $kop = array(
            1 => array('PT.NIRWANA ALABARE GARMENT', 18),
            2 => array('Jl. Raya Rancaekek - Majalaya No. 289 Desa Solokan Jeruk Kecamatan Solokan Jeruk,', 10),
            3 => array('Kabupaten Bandung 40382', 10),
            4 => array('Telp. 022-85962081', 10),
        );
        foreach ($kop as $baris => $isi) {
            $s->mergeCells('B' . $baris . ':H' . $baris);
            $s->setCellValue('B' . $baris, $isi[0]);
            $s->getStyle('B' . $baris)->getFont()->setBold(true)->setSize($isi[1]);
            $s->getStyle('B' . $baris)->getAlignment()->setHorizontal('center');
        }

        // Garis tebal pemisah kop, sama seperti di PDF.
        $s->getStyle('A5:H5')->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK);
    }

    /** Pengaturan cetak Excel Local: A4 tegak, muat selebar kertas, siap print. */
    private function aturCetakExcelLocal(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s, $area)
    {
        $atur = $s->getPageSetup();
        $atur->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $atur->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $atur->setFitToWidth(1);
        $atur->setFitToHeight(0);
        $atur->setHorizontalCentered(true);
        $s->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $atur->setPrintArea($area);
        $s->getSheetView()->setZoomScale(90);
    }

    /** Kirim spreadsheet sebagai unduhan .xlsx. */
    private function kirimExcel(\PhpOffice\PhpSpreadsheet\Spreadsheet $ss, $nama)
    {
        $penulis = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
        return response()->streamDownload(function () use ($penulis) {
            $penulis->save('php://output');
        }, $nama, array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ));
    }

    /**
     * Excel invoice Local - bentuk RINGKAS.
     *
     * Susunannya mengikuti PDF Summary (local/pdf-ringkas.blade.php) baris per
     * baris: kop yang sama, judul, Date, To/Address, tabel PRODUCT - QUANTITY -
     * UNIT PRICE - TOTAL (Rp cuma di kolom TOTAL), total qty, rekap Subtotal /
     * PPN / TOTAL di kanan, lalu Approved By. Datanya dari ringkasCetakLocal(),
     * sumber yang sama dengan PDF-nya. Angka ditulis sebagai angka sungguhan.
     */
    public function excelLocalRingkas(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));
        $data = array_merge($data, $this->ringkasCetakLocal($data));
        $inv  = $data['data_invoice'];
        $TIPIS  = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $SEDANG = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM;
        $UANG   = '#,##0.00';

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle('Invoice');
        $s->setShowGridlines(false);
        // A-C = PRODUCT (B juga dipakai tanda ":" di blok To/Address),
        // D = QUANTITY, E-F = UNIT PRICE, G = Rp, H = TOTAL.
        foreach (array('A' => 16, 'B' => 3, 'C' => 30, 'D' => 13,
                       'E' => 8, 'F' => 12, 'G' => 6, 'H' => 18) as $kol => $lebar) {
            $s->getColumnDimension($kol)->setWidth($lebar);
        }
        $ss->getDefaultStyle()->getFont()->setSize(10);

        $this->kopExcelLocal($s);

        // ---------------- Judul ----------------
        foreach (array(7 => trim($inv['type'] . ' INVOICE'), 8 => (string) $inv['no_invoice']) as $b => $isi) {
            $s->mergeCells('A' . $b . ':H' . $b);
            $s->setCellValue('A' . $b, $isi);
            $s->getStyle('A' . $b)->getFont()->setBold(true)->setSize(11);
            $s->getStyle('A' . $b)->getAlignment()->setHorizontal('center');
        }

        // ---------------- Date (kanan) ----------------
        $s->mergeCells('E10:F10');
        $s->setCellValue('E10', 'Date');
        $s->getStyle('E10')->getFont()->setBold(true);
        $s->getStyle('E10')->getAlignment()->setHorizontal('right');
        $s->setCellValue('G10', ':');
        $s->getStyle('G10')->getAlignment()->setHorizontal('center');
        $s->setCellValue('H10', (string) $data['tgl_cetak']);
        $s->getStyle('H10')->getAlignment()->setHorizontal('right');

        // ---------------- To / Address ----------------
        $b = 12;
        $kiri = function ($b, $label, $isi) use ($s) {
            if ($label !== '') {
                $s->setCellValue('A' . $b, $label);
                $s->setCellValue('B' . $b, ':');
            }
            $s->mergeCells('C' . $b . ':H' . $b);
            $s->setCellValueExplicit('C' . $b, (string) $isi, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        };
        $kiri($b, 'To', $data['tujuan_npwp']);
        $b++;
        $kiri($b, 'Address', $inv['customer']);
        foreach ($data['alamat_baris'] as $baris) {
            $b++;
            $kiri($b, '', $baris);
        }

        // ---------------- Tabel produk ----------------
        $bKepala = $b + 2;
        $s->mergeCells('A' . $bKepala . ':C' . $bKepala);
        $s->setCellValue('A' . $bKepala, 'PRODUCT');
        $s->setCellValue('D' . $bKepala, 'QUANTITY');
        $s->mergeCells('E' . $bKepala . ':F' . $bKepala);
        $s->setCellValue('E' . $bKepala, 'UNIT PRICE');
        $s->mergeCells('G' . $bKepala . ':H' . $bKepala);
        $s->setCellValue('G' . $bKepala, 'TOTAL');
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getFont()->setBold(true);
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getAlignment()->setHorizontal('center');
        // Seperti PDF: cuma garis mendatar di atas & bawah kepala tabel.
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getBorders()->getTop()->setBorderStyle($SEDANG);
        $s->getStyle('A' . $bKepala . ':H' . $bKepala)->getBorders()->getBottom()->setBorderStyle($SEDANG);

        $b = $bKepala;
        foreach ($data['baris_produk'] as $r) {
            $b++;
            $s->mergeCells('A' . $b . ':C' . $b);
            $s->setCellValue('A' . $b, (string) $r['nama']);
            $s->setCellValue('D' . $b, (float) $r['qty_n']);
            $s->mergeCells('E' . $b . ':F' . $b);
            $s->setCellValue('E' . $b, round((float) $r['unit_n'], 2));
            $s->setCellValue('G' . $b, (string) $data['simbol']);
            $s->setCellValue('H' . $b, round((float) $r['total_n'], 2));
            $s->getStyle('D' . $b . ':H' . $b)->getNumberFormat()->setFormatCode($UANG);
            $s->getStyle('D' . $b . ':F' . $b)->getAlignment()->setHorizontal('right');
            $s->getStyle('H' . $b)->getAlignment()->setHorizontal('right');
        }

        // Total qty persis di bawah kolom QUANTITY, bergaris atas.
        $b++;
        $s->setCellValue('D' . $b, (float) $data['total_qty_n']);
        $s->getStyle('D' . $b)->getNumberFormat()->setFormatCode($UANG);
        $s->getStyle('D' . $b)->getBorders()->getTop()->setBorderStyle($TIPIS);

        // ---------------- Rekap: kanan, selebar blok tanda tangan (E-H) ----------------
        $b++;
        $garis = function ($b) use ($s, $TIPIS) {
            $s->getStyle('E' . $b . ':H' . $b)->getBorders()->getTop()->setBorderStyle($TIPIS);
        };
        foreach ($data['baris_rekap'] as $r) {
            $b++;
            $s->mergeCells('E' . $b . ':G' . $b);
            $s->setCellValue('E' . $b, (string) $r['label']);
            $s->getStyle('E' . $b)->getAlignment()->setHorizontal('right');
            $s->setCellValue('H' . $b, round((float) $r['angka'], 2));
            $s->getStyle('H' . $b)->getNumberFormat()->setFormatCode($UANG);
            if (!empty($r['tebal'])) {
                $s->getStyle('E' . $b)->getFont()->setBold(true);
            }
            if (!empty($r['garis'])) {
                $garis($b);
            }
        }
        if (!empty($data['pakai_grand'])) {
            $b++;
            $s->mergeCells('E' . $b . ':G' . $b);
            $s->setCellValue('E' . $b, 'TOTAL');
            $s->getStyle('E' . $b)->getAlignment()->setHorizontal('right');
            $s->setCellValue('H' . $b, round((float) $data['grand_n'], 2));
            $s->getStyle('H' . $b)->getNumberFormat()->setFormatCode($UANG);
            $s->getStyle('E' . $b . ':H' . $b)->getFont()->setBold(true);
            $garis($b);
        }

        // ---------------- Tanda tangan: selebar rekap, pakai garis ----------------
        $b += 3;
        $s->mergeCells('E' . $b . ':H' . $b);
        $s->setCellValue('E' . $b, 'Approved By');
        $s->getStyle('E' . $b)->getAlignment()->setHorizontal('center');

        $bTtd = $b + 5;   // ruang kosong untuk tanda tangan basah
        $s->mergeCells('E' . $bTtd . ':H' . $bTtd);
        $s->setCellValue('E' . $bTtd, self::TTD_EXPORT['nama']);
        $s->getStyle('E' . $bTtd)->getAlignment()->setHorizontal('center');
        $s->getStyle('E' . $bTtd . ':H' . $bTtd)->getBorders()->getBottom()->setBorderStyle($TIPIS);
        $s->mergeCells('E' . ($bTtd + 1) . ':H' . ($bTtd + 1));
        $s->setCellValue('E' . ($bTtd + 1), self::TTD_EXPORT['jabatan']);
        $s->getStyle('E' . ($bTtd + 1))->getAlignment()->setHorizontal('center');

        $this->aturCetakExcelLocal($s, 'A1:H' . ($bTtd + 2));

        return $this->kirimExcel($ss, $this->namaBerkas($data) . '_ringkas.xlsx');
    }

    /**
     * Excel invoice Local - bentuk KNITTING.
     *
     * Mengikuti PDF Knitting (local/pdf-knitting.blade.php): tanpa kop surat,
     * blok CONSIGNOR di kiri dan CONSIGNEE / BILL TO di kanan, nomor invoice,
     * tabel berbingkai tujuh kolom, lalu keterangan pengapalan & bank. Datanya
     * dari knittingCetakLocal(), sumber yang sama dengan PDF-nya.
     */
    public function excelLocalKnitting(Request $request)
    {
        $data = $this->dataCetakLocal($request->query('id'));
        $data = array_merge($data, $this->knittingCetakLocal($data));
        $inv  = $data['data_invoice'];
        $TIPIS = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $ANGKA = '#,##0.00';

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle('Invoice');
        $s->setShowGridlines(false);
        foreach (array('A' => 13, 'B' => 32, 'C' => 18, 'D' => 14,
                       'E' => 12, 'F' => 12, 'G' => 14) as $kol => $lebar) {
            $s->getColumnDimension($kol)->setWidth($lebar);
        }
        $ss->getDefaultStyle()->getFont()->setSize(10);

        // ---------------- Pengirim (kiri) & penerima (kanan) ----------------
        $kiri = array('PT Nirwana Alabare Garment', 'Jl. Raya Rancaekek - Majalaya No. 289',
                      'Solokan Jeruk, Majalaya', 'Kab. Bandung, West Java, Indonesia',
                      'Zip code : 40376', 'Phone No. : +62 22 85962076 / +62 22 85962081');
        $kanan = array_merge(array($data['consignee']['nama']), $data['consignee']['baris']);
        if (trim((string) $data['bill_to']['nama']) !== '') {
            $kanan[] = '';
            $kanan[] = '#BILL TO:';   // tanda judul, ditebalkan di bawah
            $kanan = array_merge($kanan, array($data['bill_to']['nama']), $data['bill_to']['baris']);
        }

        $s->setCellValue('A1', 'CONSIGNOR');
        $s->setCellValue('D1', 'CONSIGNEE');
        $s->getStyle('A1')->getFont()->setBold(true);
        $s->getStyle('D1')->getFont()->setBold(true);
        foreach ($kiri as $i => $isi) {
            $b = $i + 2;
            $s->mergeCells('A' . $b . ':C' . $b);
            $s->setCellValue('A' . $b, $isi);
        }
        foreach ($kanan as $i => $isi) {
            $b = $i + 2;
            $s->mergeCells('D' . $b . ':G' . $b);
            if (strpos((string) $isi, '#') === 0) {
                $s->setCellValue('D' . $b, substr($isi, 1));
                $s->getStyle('D' . $b)->getFont()->setBold(true);
            } else {
                $s->setCellValueExplicit('D' . $b, (string) $isi, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        $b = max(count($kiri), count($kanan)) + 3;

        // ---------------- Nomor invoice ----------------
        $s->mergeCells('A' . $b . ':G' . $b);
        $s->setCellValue('A' . $b, 'Invoice ' . $inv['no_invoice']);
        $s->getStyle('A' . $b)->getFont()->setBold(true);
        $s->getStyle('A' . $b)->getAlignment()->setHorizontal('center');

        // ---------------- Tabel berbingkai ----------------
        $bKepala = $b + 1;
        $judul = array('Invoice Date', 'Product Item', 'Style / Color', 'PO',
                       'Qty (' . $data['kol_uom'] . ')', 'Price (' . $data['kol_curr'] . ')',
                       'Total (' . $data['kol_curr'] . ')');
        foreach ($judul as $i => $isi) {
            $s->setCellValue(chr(65 + $i) . $bKepala, $isi);
        }
        $s->getStyle('A' . $bKepala . ':G' . $bKepala)->getFont()->setBold(true);
        $s->getStyle('A' . $bKepala . ':G' . $bKepala)->getAlignment()->setHorizontal('center');

        $b = $bKepala;
        foreach ($data['baris_knit'] as $r) {
            $b++;
            $s->setCellValue('A' . $b, (string) $data['tgl_knit']);
            $s->setCellValue('B' . $b, (string) $r['produk']);
            $s->setCellValueExplicit('C' . $b, (string) $r['warna'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $s->setCellValueExplicit('D' . $b, (string) $r['po'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $s->setCellValue('E' . $b, (float) $r['qty_n']);
            $s->setCellValue('F' . $b, round((float) $r['unit_n'], 2));
            $s->setCellValue('G' . $b, round((float) $r['total_n'], 2));
            $s->getStyle('E' . $b . ':G' . $b)->getNumberFormat()->setFormatCode($ANGKA);
            $s->getStyle('B' . $b . ':C' . $b)->getAlignment()->setWrapText(true);
            // Nama kain panjang dibungkus: tinggi baris dikira dari panjang teksnya.
            $s->getRowDimension($b)->setRowHeight(15 * max(1, (int) ceil(mb_strlen((string) $r['produk']) / 34)));
        }
        $s->getStyle('A' . $bKepala . ':G' . $b)->getAlignment()->setVertical('top');
        $s->getStyle('A' . $bKepala . ':G' . $bKepala)->getAlignment()->setVertical('center');
        $s->getStyle('A' . $bKepala . ':G' . $b)->getBorders()->getAllBorders()->setBorderStyle($TIPIS);

        // ---------------- Pengapalan & bank ----------------
        $b += 2;
        $bawah = array(array('Shipping on behalf of', false), array($data['kirim_atas'], true));
        if ($data['bayar']['top'] !== '') {
            $bawah[] = array('Payment Terms :', false);
            $bawah[] = array($data['bayar']['top'], false);
        }
        foreach (array('bank' => 'Name of the bank : ', 'no_rek' => 'Bank Account Number : ',
                       'swift' => 'SWIFT Code : ', 'curr' => 'Bank account currency. : ') as $k => $label) {
            if ($data['bayar'][$k] !== '') {
                $bawah[] = array($label . $data['bayar'][$k], false);
            }
        }
        foreach ($bawah as $isi) {
            $s->mergeCells('A' . $b . ':D' . $b);
            $s->setCellValueExplicit('A' . $b, (string) $isi[0], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            if ($isi[1]) {
                $s->getStyle('A' . $b)->getFont()->setBold(true);
            }
            $b++;
        }

        $atur = $s->getPageSetup();
        $atur->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $atur->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $atur->setFitToWidth(1);
        $atur->setFitToHeight(0);
        $atur->setHorizontalCentered(true);
        $s->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $atur->setPrintArea('A1:G' . $b);
        $s->getSheetView()->setZoomScale(90);

        return $this->kirimExcel($ss, $this->namaBerkas($data) . '_knitting.xlsx');
    }


    /** Nomor invoice dijadikan nama berkas - garis miringnya diganti. */
    private function namaBerkas($data)
    {
        return str_replace('/', '_', (string) $data['data_invoice']['no_invoice']);
    }

    /** Nilai unik satu kolom, dalam bentuk array asosiatif seperti yang
     *  diharapkan template AR. */
    private function kolomArray($db, $tabel, $kolom, $idBook)
    {
        $hasil = array();
        foreach ($db->select("SELECT DISTINCT `$kolom` FROM $tabel WHERE id_book_invoice = ? ORDER BY `$kolom`", array($idBook)) as $r) {
            $hasil[] = (array) $r;
        }
        return $hasil;
    }

    /** Rincian satu Invoice Local, untuk modal di halaman daftar. */
    public function detailLocal(Request $request)
    {
        $id = (int) $request->query('id');
        if ($id < 1) {
            return response()->json(array('status' => false, 'pesan' => 'Invalid invoice id.'), 422);
        }
        if (!$this->tabelAda(self::TABEL_DET)) {
            return response()->json(array('status' => false, 'pesan' => 'Invoice tables are not ready yet.'), 500);
        }

        $db = $this->koneksiAr();

        $header = $db->select(
            "SELECT b.id, b.no_invoice, b.shipp, DATE(b.tgl_book_inv) AS tanggal, b.status,
                    b.doc_type, b.doc_number, b.profit_center, b.curr, b.value,
                    b.booking_by, b.booking_date,
                    UPPER(c.Supplier)  AS customer,  c.alamat  AS alamat,
                    UPPER(cs.Supplier) AS customer_ship, cs.alamat AS alamat_ship,
                    t.type
               FROM tbl_book_invoice b
               LEFT JOIN mastersupplier c  ON c.Id_Supplier  = b.id_customer
               LEFT JOIN mastersupplier cs ON cs.Id_Supplier = b.id_customer_ship
               LEFT JOIN tbl_type t        ON t.id           = b.id_type
              WHERE b.id = ?
                AND b.shipp = ?
                AND EXISTS (SELECT 1 FROM " . self::TABEL_DET . " d WHERE d.id_book_invoice = b.id)
              LIMIT 1",
            array($id, self::SHIPP_LOCAL)
        );

        if (!$header) {
            return response()->json(array('status' => false, 'pesan' => 'Invoice not found.'), 404);
        }

        $baris = $db->select(
            "SELECT asal, so_number, bppb_number, sj_date, shipp_number, ws, styleno,
                    product_group, product_item, color, size, curr, uom,
                    qty, unit_price, disc, total_price
               FROM " . self::TABEL_DET . "
              WHERE id_book_invoice = ?
              ORDER BY id",
            array($id)
        );

        $pot = $db->select(
            "SELECT total, discount, dp, dp_cbd, retur, twot, vat_persen, vat, grand_total"
            . ($this->kolomAda(self::TABEL_POT, 'tgl_invoice') ? ", tgl_invoice" : "") . "
               FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1",
            array($id)
        );

        return response()->json(array(
            'status' => true,
            'header' => (array) $header[0],
            'baris'  => array_map(function ($r) { return (array) $r; }, $baris),
            'pot'    => $pot ? (array) $pot[0] : null,
        ));
    }

    /** Tabel booking EXIM - dibuat lewat migrations/20260916_invoice_exim_booking.sql */
    const TABEL_DET = 'tbl_book_invoice_exim_det';
    const TABEL_POT = 'tbl_book_invoice_exim_pot';
    /** Baris SO (WS) Invoice Export - lihat migrations/20260928_invoice_exim_so.sql. */
    const TABEL_SO  = 'tbl_book_invoice_exim_so';

    /**
     * Simpan Invoice Local EXIM.
     *
     * Alurnya masuk sebagai booking: header ke tbl_book_invoice status DRAFT, baris SJ
     * ke tbl_book_invoice_exim_det, angka potongan ke tbl_book_invoice_exim_pot.
     * Status di bppb / official_out_h SENGAJA tidak disentuh - itu dilakukan
     * nanti saat Create Invoice di AR.
     *
     * Nilai uang TIDAK dipercaya dari kiriman browser: baris SJ-nya dibaca ulang
     * dari sumbernya, lalu semua angka dihitung di sini.
     */
    /**
     * Pemeriksaan & perhitungan yang dipakai bersama oleh Save (invoice baru)
     * dan Update (invoice yang sudah ada).
     *
     * Kiriman browser cuma dipakai untuk menentukan baris MANA dan berapa persen
     * diskonnya. Semua nilai uang dibaca ulang dari sumbernya, jadi angka yang
     * dikirim dari layar tidak bisa dipakai untuk menggeser total.
     *
     * Kalau ada yang tidak beres, yang dikembalikan adalah array berisi kunci
     * 'gagal' (JsonResponse siap dikirim). Kalau beres, isinya hasil hitungan.
     *
     * @param string|null $pcPaksa Profit center yang dipaksakan (dipakai saat
     *                             Update: nilainya diambil dari data tersimpan,
     *                             bukan dari kiriman browser, karena profit
     *                             center sudah terlanjur menyatu dengan nomor
     *                             invoice).
     */
    private function siapkanSimpan(Request $request, $pcPaksa = null, $abaikanInvoice = null)
    {
        $salah = function ($pesan, $kode) {
            return array('gagal' => response()->json(array('status' => false, 'pesan' => $pesan), $kode));
        };

        foreach (array(self::TABEL_DET, self::TABEL_POT) as $t) {
            if (!$this->tabelAda($t)) {
                return $salah('Table ' . $t . ' does not exist yet. Run migrations/20260916_invoice_exim_booking.sql first.', 500);
            }
        }

        $pc = $pcPaksa !== null
            ? strtoupper((string) $pcPaksa)
            : strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('profit_center')));
        if ($pc !== 'NAG' && $pc !== 'NAK') {
            return $salah('Invalid profit center.', 422);
        }

        // Wajib diisi - dijaga juga di sini, bukan cuma di layar.
        $idCustomer = trim((string) $request->input('id_customer'));
        $idCustomerShip = trim((string) $request->input('id_customer_ship'));
        $docType = trim((string) $request->input('doc_type'));
        $tglInvoice = $this->tanggal(trim((string) $request->input('tgl_invoice')));
        $kurang = array();
        if ($idCustomer === '')     { $kurang[] = 'Billed To'; }
        if ($idCustomerShip === '') { $kurang[] = 'Shipped To'; }
        if ($docType === '')        { $kurang[] = 'Document Type'; }
        if ($tglInvoice === null)   { $kurang[] = 'Invoice Date'; }
        if ($kurang) {
            return $salah(implode(', ', $kurang) . ' is required.', 422);
        }

        $barisWs = $request->input('baris_ws');
        $baris = $request->input('baris');
        $baris = is_array($baris) ? $baris : array();

        // Harus ada ISINYA - dari SJ atau dari SO, tidak harus dua-duanya.
        // Keduanya sah: invoice yang terbit sebelum barangnya keluar baru punya
        // baris SO, sedangkan yang barangnya sudah keluar bisa saja langsung
        // punya SJ tanpa SO-nya pernah dipesan lebih dulu. Sama dengan Export.
        $adaWs = is_array($barisWs) && $barisWs;
        if (!$adaWs && !$baris) {
            return $salah('Pick at least one SJ or WS row.', 422);
        }

        // Tanpa baris WS tidak ada yang perlu dibaca ulang. Pembacaannya
        // dilewati sekalian, karena di dalamnya ada pemeriksaan "WS cuma untuk
        // NAG" - dan itu tidak boleh ikut menolak invoice knitting yang memang
        // isinya SJ saja.
        $ws = array();
        if ($adaWs) {
            $hasilWs = $this->barisWsUlang($pc, $barisWs);
            if (isset($hasilWs['pesan'])) {
                return $salah($hasilWs['pesan'], 422);
            }
            $ws = $hasilWs['baris'];
        }

        $diskon = array();
        foreach ($baris as $b) {
            if (!is_array($b) || !isset($b['id_baris']) || !is_scalar($b['id_baris'])) {
                return $salah('Invalid detail row.', 422);
            }
            $d = isset($b['disc']) ? (float) $b['disc'] : 0;
            $diskon[(string) $b['id_baris']] = max(0, min(100, $d));
        }
        $hargaKetik = $this->ketikanHarga($baris);

        try {
            // Tanpa SJ sama sekali: daftar kosong bukan berarti "ambil semua".
            $asli = $diskon ? $this->sjUlang($pc, array_keys($diskon), $abaikanInvoice) : array();
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM: gagal baca ulang SJ - ' . $e->getMessage());
            return $salah('Could not re-read the SJ rows. Please search again.', 500);
        }

        if (count($asli) !== count($diskon)) {
            return $salah('Some SJ rows are no longer available - they may have just been'
                . ' taken by another invoice. Please search again.', 409);
        }

        // Harga & mata uang baris SJ tanpa SO diambil dari layar - lihat
        // terapkanHargaManual().
        $kurangIsi = $this->terapkanHargaManual($asli, $hargaKetik);
        if ($kurangIsi['harga'] || $kurangIsi['curr']) {
            return $salah($this->pesanTanpaHarga($kurangIsi), 422);
        }
        $currCampur = $this->pesanCurrCampur($asli);
        if ($currCampur !== null) {
            return $salah($currCampur, 422);
        }

        // ---- semua angka dihitung di sini ----
        // Warna yang punya baris SO dihitung dari baris SO; baris SJ cuma
        // dipakai untuk warna yang tidak punya SO. Aturannya sama persis
        // dengan barisDitagih() di layar - kalau dijumlahkan begitu saja,
        // warna yang punya keduanya terhitung dua kali.
        $adaSo = array();
        foreach ($ws as $r) { $adaSo[$this->kunciWarna($r)] = true; }

        $total = 0;
        $discount = 0;
        foreach ($ws as $r) {
            $harga = (float) $r['total_price'];
            $total += $harga;
            $discount += ((float) $r['disc']) / 100 * $harga;
        }
        foreach ($asli as $r) {
            if (isset($adaSo[$this->kunciWarna($r)])) { continue; }
            $harga = (float) $r['total_price'];
            $total += $harga;
            $discount += $diskon[(string) $r['id_baris']] / 100 * $harga;
        }
        $dp    = max(0, (float) $request->input('dp', 0));
        $dpCbd = max(0, (float) $request->input('dp_cbd', 0));
        $retur = max(0, (float) $request->input('retur', 0));
        $twot  = $total - $discount - $dp - $dpCbd - $retur;

        $vatPersen = (float) $request->input('vat_persen', 0);
        if (!in_array($vatPersen, array(0.0, 11.0, 12.0), true)) {
            $vatPersen = 0.0;
        }
        $vat   = $twot * $vatPersen / 100;
        $grand = $twot + $vat;

        return array(
            'pc'               => $pc,
            'id_customer'      => $idCustomer,
            'id_customer_ship' => $idCustomerShip,
            'doc_type'         => $docType,
            'tgl_invoice'      => $tglInvoice,
            'asli'             => $asli,
            'ws'               => $ws,
            'diskon'           => $diskon,
            'total'            => $total,
            'discount'         => $discount,
            'dp'               => $dp,
            'dp_cbd'           => $dpCbd,
            'retur'            => $retur,
            'twot'             => $twot,
            'vat_persen'       => $vatPersen,
            'vat'              => $vat,
            'grand'            => $grand,
        );
    }

    /** Baris detail siap insert, dari hasil siapkanSimpan(). */
    /**
     * Baris SO (WS) dibaca ulang dari sumbernya, persis seperti baris SJ.
     *
     * Dari layar yang dipercaya cuma DUA hal: baris SO mana yang dipilih
     * (daftar so_det-nya) dan berapa qty yang ditagih. Nama, harga, dan qty
     * pesanan dibaca ulang di sini - kiriman browser tidak dipakai.
     *
     * Qty yang ditagih boleh lebih kecil dari qty pesanan (pengiriman sering
     * bertahap), tapi tidak boleh nol dan tidak boleh melebihi pesanannya.
     *
     * @return array array('baris' => [...]) atau array('pesan' => '...')
     */
    private function barisWsUlang($pc, $kirim)
    {
        if (strtoupper((string) $pc) !== 'NAG') {
            return array('pesan' => 'WS rows are only available for NAG (garment) for now.');
        }

        $qtyKirim = array();   // daftar so_det (apa adanya) => qty yang ditagih
        $discKirim = array();  // daftar so_det => diskon persen (Invoice Local)
        $semuaId  = array();
        foreach ((array) $kirim as $b) {
            if (!is_array($b) || !isset($b['id_so_det']) || !is_scalar($b['id_so_det'])) {
                return array('pesan' => 'Invalid SO row.');
            }
            $daftar = array_values(array_filter(array_map(
                'trim',
                explode(',', (string) $b['id_so_det'])
            ), function ($v) { return $v !== '' && ctype_digit($v); }));
            if (!$daftar) {
                return array('pesan' => 'Invalid SO row.');
            }
            $kunci = implode(',', $daftar);
            if (isset($qtyKirim[$kunci])) {
                return array('pesan' => 'The same SO row was sent twice. Please reload the page.');
            }
            $qtyKirim[$kunci] = isset($b['qty']) ? $this->angkaIsian($b['qty']) : null;
            // Invoice Local mengetik diskon per baris SO; Export menaruhnya
            // di baris Invoice Summary, jadi di sana kolom ini tidak dikirim.
            $discKirim[$kunci] = isset($b['disc']) ? max(0, min(100, (float) $b['disc'])) : 0;
            $semuaId = array_merge($semuaId, $daftar);
        }
        if (!$qtyKirim) {
            return array('baris' => array());
        }

        try {
            $sumber = $this->wsGarment(null, null, '', array_unique($semuaId));
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM Export: gagal baca ulang WS - ' . $e->getMessage());
            return array('pesan' => 'Could not re-read the SO rows. Please search again.');
        }

        $perKunci = array();
        foreach ($sumber as $r) {
            $perKunci[(string) $r['id_so_det']] = $r;
        }

        $baris = array();
        foreach ($qtyKirim as $kunci => $qty) {
            if (!isset($perKunci[$kunci])) {
                return array('pesan' => 'Some SO rows are no longer available. Please search again.');
            }
            $r = $perKunci[$kunci];
            $qtySo = (float) $r['qty_so'];

            if ($qty === null || $qty <= 0) {
                return array('pesan' => 'Qty is required on SO row ' . $r['ws'] . ' / ' . $r['color'] . '.');
            }
            // Dibulatkan dulu: angka di layar juga 2 desimal, jadi selisih
            // pembulatan tidak boleh dianggap "melebihi pesanan".
            if (round($qty, 2) > round($qtySo, 2)) {
                return array('pesan' => 'Qty on SO row ' . $r['ws'] . ' / ' . $r['color']
                    . ' is more than the ordered qty (' . rtrim(rtrim(number_format($qtySo, 2, '.', ''), '0'), '.') . ').');
            }

            $r['qty'] = $qty;
            $r['disc'] = isset($discKirim[$kunci]) ? $discKirim[$kunci] : 0;
            $r['total_price'] = round($qty * (float) $r['unit_price'], 4);
            $baris[] = $r;
        }

        return array('baris' => $baris);
    }

    /** Baris SO yang ditulis ke tbl_book_invoice_exim_so. */
    private function barisSo($idBook, $noInvoice, array $s, $user, $now)
    {
        $out = array();
        foreach ($s['ws'] as $r) {
            $id = (string) $r['id_so_det'];
            $out[] = array(
                'id_book_invoice' => $idBook,
                'no_invoice'      => $noInvoice,
                'ws'              => $r['ws'],
                'so_number'       => $r['no_so'],
                'so_date'         => $r['so_date'],
                'styleno'         => $r['styleno'],
                'product_group'   => $r['product_group'],
                'product_item'    => $r['product_item'],
                'color'           => $r['color'],
                'curr'            => $r['curr'],
                'uom'             => $r['uom'],
                'qty_so'          => (float) $r['qty_so'],
                'qty'             => (float) $r['qty'],
                'unit_price'      => (float) $r['unit_price'],
                // Export menaruh diskon di baris Invoice Summary; Local
                // mengetiknya per baris SO.
                'disc'            => isset($s['diskon_ws'][$id])
                    ? (float) $s['diskon_ws'][$id]
                    : (float) (isset($r['disc']) ? $r['disc'] : 0),
                'total_price'     => (float) $r['total_price'],
                'id_so'           => $r['id_so'],
                'id_so_det'       => $id,
                'urutan_summary'  => isset($s['urutan_ws'][$id]) ? $s['urutan_ws'][$id] : null,
                'created_by'      => $user,
                'created_at'      => $now,
            );
        }
        return $out;
    }

    private function barisDetail($idBook, $noInvoice, array $s, $user, $now)
    {
        // Kolom po_konsumen baru ada setelah migrasi 20260920 dijalankan, dan
        // service_charge setelah 20260923 - selama belum, kolomnya dilewati
        // supaya penyimpanan tetap jalan.
        $adaPo = $this->kolomAda(self::TABEL_DET, 'po_konsumen');
        $adaJasa = $this->kolomAda(self::TABEL_DET, 'service_charge');

        $det = array();
        foreach ($s['asli'] as $r) {
            $baris = array();
            if ($adaPo) {
                $baris['po_konsumen'] = isset($r['po_konsumen']) ? $r['po_konsumen'] : null;
            }
            // Cuma FG/OUT yang punya costing, jadi baris lain memang NULL.
            if ($adaJasa) {
                $baris['service_charge'] = isset($r['service_charge']) && $r['service_charge'] !== null
                    ? (float) $r['service_charge'] : null;
            }
            $det[] = $baris + array(
                'id_book_invoice' => $idBook,
                'no_invoice'      => $noInvoice,
                'asal'            => $s['pc'],
                'id_bppb'         => $r['id_bppb'],
                'id_baris'        => $r['id_baris'],
                'id_so'           => $r['id_so'],
                'so_number'       => $r['no_so'],
                'bppb_number'     => $r['sj'],
                'sj_date'         => $r['bppbdate'],
                'shipp_number'    => $r['shipping_number'],
                'ws'              => $r['ws'],
                'styleno'         => $r['styleno'],
                'product_group'   => $r['product_group'],
                'product_item'    => $r['product_item'],
                'color'           => $r['color'],
                'size'            => $r['size'],
                'curr'            => $r['curr'],
                'uom'             => $r['uom'],
                'qty'             => $r['qty'],
                'unit_price'      => $r['unit_price'],
                'disc'            => $s['diskon'][(string) $r['id_baris']],
                'total_price'     => $r['total_price'],
                'created_by'      => $user,
                'created_at'      => $now,
            );
        }
        return $det;
    }

    /** Isi tabel potongan, dari hasil siapkanSimpan(). */
    private function barisPotongan($idBook, $noInvoice, array $s, $user, $now)
    {
        $baris = array(
            'id_book_invoice' => $idBook,
            'no_invoice'      => $noInvoice,
            'total'           => round($s['total'], 4),
            'discount'        => round($s['discount'], 4),
            'dp'              => round($s['dp'], 4),
            'dp_cbd'          => round($s['dp_cbd'], 4),
            'retur'           => round($s['retur'], 4),
            'twot'            => round($s['twot'], 4),
            'vat_persen'      => $s['vat_persen'],
            'vat'             => round($s['vat'], 4),
            'grand_total'     => round($s['grand'], 4),
            'created_by'      => $user,
            'created_at'      => $now,
        );

        // Invoice Date: satu tanggal untuk daftar, modal & cetakan. Kolomnya
        // dari migrations/20260919 - kalau belum dijalankan, penyimpanan tetap
        // jalan, tanggalnya saja yang belum ikut tersimpan.
        if (isset($s['tgl_invoice']) && $this->kolomAda(self::TABEL_POT, 'tgl_invoice')) {
            $baris['tgl_invoice'] = $s['tgl_invoice'];
        }
        return $baris;
    }

    public function simpanLocal(Request $request)
    {
        $db = $this->koneksiAr();

        $s = $this->siapkanSimpan($request);
        if (isset($s['gagal'])) {
            return $s['gagal'];
        }

        $user = (string) (auth()->user()->username ?? '');
        $now  = now()->format('Y-m-d H:i:s');

        try {
            $hasil = $db->transaction(function () use ($db, $s, $request, $user, $now) {
                // Nomor dibuat ULANG di sini (bukan yang tampil di layar) sambil
                // memegang kunci - dua user yang menyimpan bersamaan tidak bisa
                // dapat nomor yang sama.
                $lock = $db->select("SELECT GET_LOCK('gen_no_inv_exim', 10) AS ok");
                if (!$lock || (int) $lock[0]->ok !== 1) {
                    throw new \RuntimeException('sibuk');
                }
                try {
                    $noInvoice = $this->nomorInvoiceBerikutnya($s['pc']);
                    if ($db->table('tbl_book_invoice')->where('no_invoice', $noInvoice)->exists()) {
                        throw new \RuntimeException('nomor_dipakai');
                    }

                    $idBook = $db->table('tbl_book_invoice')->insertGetId(array(
                        'no_invoice'       => $noInvoice,
                        'id_customer'      => $s['id_customer'],
                        'id_customer_ship' => $s['id_customer_ship'],
                        'shipp'            => self::SHIPP_LOCAL,
                        'id_type'          => (int) $request->input('id_type'),
                        'tgl_book_inv'     => $now,
                        'status'           => 'DRAFT',
                        'value'            => round($s['grand'], 2),
                        'curr'             => $this->currInvoice($s),
                        'doc_type'         => $s['doc_type'],
                        'doc_number'       => (string) $request->input('doc_number'),
                        'profit_center'    => $s['pc'],
                        'booking_by'       => $user,
                        'booking_date'     => $now,
                    ));

                    // Baris SJ boleh tidak ada - invoice yang dibuat dari WS saja.
                    $det = $this->barisDetail($idBook, $noInvoice, $s, $user, $now);
                    if ($det) { $db->table(self::TABEL_DET)->insert($det); }
                    $so = $this->barisSo($idBook, $noInvoice, $s, $user, $now);
                    if ($so) { $db->table(self::TABEL_SO)->insert($so); }
                    $db->table(self::TABEL_POT)->insert($this->barisPotongan($idBook, $noInvoice, $s, $user, $now));

                    // Nomor invoicenya ikut ditulis ke SJ-nya di bppb.
                    $this->selaraskanInvno($db, $idBook, $noInvoice);
                    $this->catatRiwayat($db, $idBook, $noInvoice, self::SHIPP_LOCAL, 'CREATE', $user, $now);

                    return array('id' => $idBook, 'no_invoice' => $noInvoice);
                } finally {
                    $db->select("SELECT RELEASE_LOCK('gen_no_inv_exim')");
                }
            });
        } catch (\RuntimeException $e) {
            $pesan = $e->getMessage() === 'nomor_dipakai'
                ? 'Invoice number was just taken by someone else. Please try again.'
                : 'The system is busy, please try again.';
            return response()->json(array('status' => false, 'pesan' => $pesan), 409);
        } catch (\Throwable $e) {
            Log::error('Invoice EXIM simpan gagal - ' . $e->getMessage());
            return response()->json(array('status' => false, 'pesan' => 'Save failed, please try again.'), 500);
        }

        // SJ knitting ada di database lain, jadi ditandai di luar transaksi -
        // sesudah invoicenya benar-benar tersimpan.
        $this->tandaiInvnoNak($hasil['no_invoice'], $this->idSjNak($hasil['id']));

        return response()->json(array(
            'status'     => true,
            'no_invoice' => $hasil['no_invoice'],
            'pesan'      => 'Invoice saved as DRAFT.',
        ));
    }

    // ======================================================================
    //  Invoice Export - simpan
    // ======================================================================

    /**
     * Kolom teks blok pengiriman: label di layar & panjang maksimalnya, sama
     * dengan tabel tbl_book_invoice_exim_export_ship. null = TEXT.
     */
    const KOLOM_KIRIM = array(
        'dest_purchase'       => array('Dest Purchase', 255),
        'style_no'            => array('Style NO', 255),
        'brand'               => array('Brand', 255),
        'chanel_description'  => array('Chanel Description', 255),
        'currency'            => array('Currency', 25),
        'payment_term'        => array('Payment Term', 255),
        'final_destination'   => array('Final Destination', 10),
        'country_origin'      => array('Country of Origin', 10),
        'ship_mode'           => array('Ship Mode', 20),
        'term_of_sale'        => array('Term of Sale', 255),
        'transfer_point'      => array('Transfer Point', 100),
        'port_of_loading'     => array('Port of Loading', 100),
        'product_description' => array('Product Description', null),
    );

    /** Kolom angka blok pengiriman. */
    const ANGKA_KIRIM = array(
        'total_gross_weight'   => 'Total Gross Weight',
        'total_net_weight'     => 'Total Net Weight',
        'total_net_net_weight' => 'Total Net Net Weight',
        'total_carton'         => 'Total Carton',
    );

    /**
     * Simpan Invoice Export sebagai booking DRAFT.
     *
     * Yang ditulis, semuanya dalam satu transaksi:
     *   tbl_book_invoice      header dasar - Seller jadi Billed To & Shipped To,
     *                         value = Grand Total CM (yang ditagih tim AR)
     *   _exim_export_h        pihak-pihak, Invoice Date, notes & reference
     *   _exim_export_ship     baris Shipment Details
     *   _exim_export_det      Invoice Summary, satu baris per warna
     *   _exim_det             baris SJ asalnya + urutan baris summary-nya
     *   _exim_pot             rekap uang CM + total & grand total FOB
     */
    public function simpanExport(Request $request)
    {
        $s = $this->siapkanSimpanExport($request);
        if (isset($s['gagal'])) {
            return $s['gagal'];
        }

        $db   = $this->koneksiAr();
        $user = (string) (auth()->user()->username ?? '');
        $now  = now()->format('Y-m-d H:i:s');

        try {
            $hasil = $db->transaction(function () use ($db, $s, $request, $user, $now) {
                // Kunci & nomornya sama dengan Local - nomor urutnya memang satu
                // deret untuk L dan E.
                $lock = $db->select("SELECT GET_LOCK('gen_no_inv_exim', 10) AS ok");
                if (!$lock || (int) $lock[0]->ok !== 1) {
                    throw new \RuntimeException('sibuk');
                }
                try {
                    $noInvoice = $this->nomorInvoiceBerikutnya($s['pc'], 'E');
                    if ($db->table('tbl_book_invoice')->where('no_invoice', $noInvoice)->exists()) {
                        throw new \RuntimeException('nomor_dipakai');
                    }

                    $idBook = $db->table('tbl_book_invoice')->insertGetId(array(
                        'no_invoice'    => $noInvoice,
                        'shipp'         => self::SHIPP_EXPORT,
                        'tgl_book_inv'  => $now,
                        'status'        => 'DRAFT',
                        'profit_center' => $s['pc'],
                        'booking_by'    => $user,
                        'booking_date'  => $now,
                    ) + $this->kolomBookExport($s, $request));

                    $db->table(self::TABEL_EXP_H)->insert(
                        array('id_book_invoice' => $idBook, 'no_invoice' => $noInvoice)
                        + $s['header'] + array('created_by' => $user, 'created_at' => $now)
                    );
                    $this->tulisIsiExport($db, $idBook, $noInvoice, $s, $user, $now);
                    // Nomor invoicenya ikut ditulis ke SJ-nya di bppb.
                    $this->selaraskanInvno($db, $idBook, $noInvoice);
                    $this->catatRiwayat($db, $idBook, $noInvoice, self::SHIPP_EXPORT, 'CREATE', $user, $now);

                    return array('id' => $idBook, 'no_invoice' => $noInvoice);
                } finally {
                    $db->select("SELECT RELEASE_LOCK('gen_no_inv_exim')");
                }
            });
        } catch (\RuntimeException $e) {
            $pesan = $e->getMessage() === 'nomor_dipakai'
                ? 'Invoice number was just taken by someone else. Please try again.'
                : 'The system is busy, please try again.';
            return response()->json(array('status' => false, 'pesan' => $pesan), 409);
        } catch (\Throwable $e) {
            Log::error('Invoice EXIM Export simpan gagal - ' . $e->getMessage());
            return response()->json(array('status' => false, 'pesan' => 'Save failed, please try again.'), 500);
        }

        // SJ knitting ada di database lain, jadi ditandai di luar transaksi -
        // sesudah invoicenya benar-benar tersimpan.
        $this->tandaiInvnoNak($hasil['no_invoice'], $this->idSjNak($hasil['id']));

        return response()->json(array(
            'status'     => true,
            'no_invoice' => $hasil['no_invoice'],
            'pesan'      => 'Invoice saved as DRAFT.',
        ));
    }

    /**
     * Pemeriksaan & perhitungan Save Invoice Export.
     *
     * Prinsipnya sama dengan siapkanSimpan() milik Local: kiriman browser hanya
     * menentukan baris SJ MANA yang dipakai, ditambah isian yang memang diketik
     * user (Color Code, Total Pieces, Unit Cost FOB, Disc, isi Shipment Details,
     * pihak-pihak). Qty dan harga CM dibaca ulang dari sumbernya, dan semua total
     * dihitung di sini - angka uang dari layar tidak dipakai.
     *
     * Unit Cost (CM) per warna = jumlah total_price SJ warna itu dibagi qty-nya.
     * Kalau satu warna punya harga berbeda (mis. ukuran besar lebih mahal),
     * total CM-nya tetap persis sama dengan jumlah nilai SJ.
     */
    private function siapkanSimpanExport(Request $request, $pcPaksa = null, $abaikanInvoice = null)
    {
        $salah = function ($pesan, $kode) {
            return array('gagal' => response()->json(array('status' => false, 'pesan' => $pesan), $kode));
        };

        $belum = $this->pesanBelumSiapExport();
        if ($belum !== null) {
            return $salah($belum, 500);
        }

        // Saat Update, profit center diambil dari data tersimpan - sudah menyatu
        // dengan nomor invoice, jadi kiriman layar tidak dipakai.
        $pc = $pcPaksa !== null
            ? strtoupper((string) $pcPaksa)
            : strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $request->input('profit_center')));
        if ($pc !== 'NAG' && $pc !== 'NAK') {
            return $salah('Invalid profit center.', 422);
        }

        $teks = function ($kunci) use ($request) {
            return trim((string) $request->input($kunci));
        };

        // ---- wajib diisi - dijaga juga di sini, bukan cuma di layar ----
        $idSeller = $teks('id_seller');
        $tgl      = $this->tanggal($teks('tgl_invoice'));
        $docType  = $teks('doc_type');
        $kirim    = $request->input('kirim');
        $baris    = $request->input('baris');
        $barisWs  = $request->input('baris_ws');

        $kurang = array();
        if ($idSeller === '')                    { $kurang[] = 'Seller'; }
        if ($teks('purchaser_nama') === '')      { $kurang[] = 'Purchaser'; }
        if ($teks('receiver_nama') === '')       { $kurang[] = 'Receiver / Ship To'; }
        if ($tgl === null)                       { $kurang[] = 'Invoice Date'; }
        if (!in_array($docType, self::DOC_TYPE_EXPORT, true)) { $kurang[] = 'Document Type'; }
        if (!is_array($kirim) || !$kirim)        { $kurang[] = 'Shipment Details (at least one row)'; }
        // Harus ada ISINYA - dari SJ atau dari SO, tidak harus dua-duanya.
        // Keduanya sah: invoice yang terbit sebelum barangnya keluar baru punya
        // baris SO, sedangkan yang barangnya sudah keluar bisa saja langsung
        // punya SJ tanpa SO-nya pernah dipesan lebih dulu.
        $adaWs = is_array($barisWs) && $barisWs;
        $adaSj = is_array($baris) && $baris;
        if (!$adaWs && !$adaSj) {
            $kurang[] = 'Detail SJ or Detail SO (at least one row)';
        }
        if ($kurang) {
            return $salah(implode(', ', $kurang) . (count($kurang) > 1 ? ' are' : ' is') . ' required.', 422);
        }

        foreach (array(
            'no_invoice_2'   => array('Invoice Number #2', 255),
            'purchaser_nama' => array('Purchaser', 255),
            'receiver_nama'  => array('Receiver / Ship To', 255),
            'reference'      => array('Refference', 255),
        ) as $kunci => $atur) {
            if (mb_strlen($teks($kunci)) > $atur[1]) {
                return $salah($atur[0] . ' is too long (max ' . $atur[1] . ' characters).', 422);
            }
        }

        // Nama Seller & Shipper diambil dari master, bukan dari kiriman layar.
        $seller = $this->koneksiAr()->select(
            "SELECT Id_Supplier, Supplier FROM mastersupplier WHERE Id_Supplier = ? AND tipe_sup = 'C' LIMIT 1",
            array($idSeller)
        );
        if (!$seller) {
            return $salah('The selected seller was not found. Please pick it again.', 422);
        }
        $shipper = $this->shipperNag();
        $alamatShipper = $teks('shipper_alamat');

        $header = array(
            'no_invoice_2'        => $teks('no_invoice_2') !== '' ? $teks('no_invoice_2') : null,
            'tgl_invoice'         => $tgl,
            'id_shipper'          => self::ID_SHIPPER_NAG,
            'shipper_nama'        => (string) $shipper['supplier'],
            'shipper_alamat'      => $alamatShipper,
            'id_seller'           => (string) $seller[0]->Id_Supplier,
            'seller_nama'         => (string) $seller[0]->Supplier,
            'seller_alamat'       => $teks('seller_alamat'),
            'purchaser_nama'      => $teks('purchaser_nama'),
            'purchaser_alamat'    => $teks('purchaser_alamat'),
            'receiver_nama'       => $teks('receiver_nama'),
            'receiver_alamat'     => $teks('receiver_alamat'),
            'invoice_notes'       => $teks('invoice_notes'),
            // Manufacturer selalu perusahaan sendiri, sama dengan Shipper.
            'manufacturer_nama'   => (string) $shipper['supplier'],
            'manufacturer_alamat' => $alamatShipper,
            'reference'           => $teks('reference'),
        );

        // ---- Shipment Details ----
        $barisKirim = array();
        foreach (array_values($kirim) as $i => $k) {
            if (!is_array($k)) {
                return $salah('Invalid shipment row.', 422);
            }
            $no = $i + 1;
            $r = array('urutan' => $no);
            foreach (self::KOLOM_KIRIM as $kolom => $atur) {
                $nilai = isset($k[$kolom]) && is_scalar($k[$kolom]) ? trim((string) $k[$kolom]) : '';
                if ($atur[1] !== null && mb_strlen($nilai) > $atur[1]) {
                    return $salah('Shipment row ' . $no . ': ' . $atur[0] . ' is too long (max '
                        . $atur[1] . ' characters).', 422);
                }
                $r[$kolom] = $nilai;
            }
            foreach (self::ANGKA_KIRIM as $kolom => $label) {
                $nilai = $this->angkaIsian(isset($k[$kolom]) ? $k[$kolom] : '');
                if ($nilai === null) {
                    return $salah('Shipment row ' . $no . ': ' . $label . ' must be a number of 0 or more.', 422);
                }
                $r[$kolom] = $nilai;
            }
            $barisKirim[] = $r;
        }

        // ---- baris SO (WS): dibaca ulang dari sumbernya ----
        // Tanpa baris WS tidak ada yang perlu dibaca ulang. Pembacaannya
        // dilewati sekalian, karena di dalamnya ada pemeriksaan "WS cuma untuk
        // NAG" - dan itu tidak boleh ikut menolak invoice knitting yang memang
        // isinya SJ saja. Pemeriksaan itu tetap berlaku untuk kiriman yang
        // benar-benar berisi baris WS.
        $ws = array();
        if (is_array($barisWs) && $barisWs) {
            $hasilWs = $this->barisWsUlang($pc, $barisWs);
            if (isset($hasilWs['pesan'])) {
                return $salah($hasilWs['pesan'], 422);
            }
            $ws = $hasilWs['baris'];
        }

        // ---- baris SJ: dibaca ulang dari sumbernya (boleh tidak ada) ----
        $baris = is_array($baris) ? $baris : array();
        $idBaris = array();
        foreach ($baris as $b) {
            $id = is_array($b) ? (isset($b['id_baris']) ? $b['id_baris'] : null) : $b;
            if (!is_scalar($id) || (string) $id === '') {
                return $salah('Invalid detail row.', 422);
            }
            $idBaris[] = (string) $id;
        }
        $hargaKetik = $this->ketikanHarga($baris);
        if (count(array_unique($idBaris)) !== count($idBaris)) {
            return $salah('The same SJ row was sent twice. Please reload the page.', 422);
        }

        try {
            // Tanpa SJ sama sekali: jangan panggil sjUlang dengan daftar kosong,
            // itu bukan "ambil semua" melainkan memang tidak ada yang diminta.
            $sumber = $idBaris ? $this->sjUlang($pc, $idBaris, $abaikanInvoice) : array();
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM Export: gagal baca ulang SJ - ' . $e->getMessage());
            return $salah('Could not re-read the SJ rows. Please search again.', 500);
        }
        // Harga & mata uang baris SJ tanpa SO diambil dari layar - lihat
        // terapkanHargaManual().
        $kurangIsi = $this->terapkanHargaManual($sumber, $hargaKetik);
        if ($kurangIsi['harga'] || $kurangIsi['curr']) {
            return $salah($this->pesanTanpaHarga($kurangIsi), 422);
        }
        $currCampur = $this->pesanCurrCampur($sumber);
        if ($currCampur !== null) {
            return $salah($currCampur, 422);
        }

        $perId = array();
        foreach ($sumber as $r) {
            $perId[(string) $r['id_baris']] = $r;
        }
        if (count($perId) !== count($idBaris) || array_diff($idBaris, array_map('strval', array_keys($perId)))) {
            return $salah('Some SJ rows are no longer available - they may have just been'
                . ' taken by another invoice. Please search again.', 409);
        }

        // Urutan baris & warna mengikuti layar, supaya nomor baris summary yang
        // tersimpan sama dengan yang dilihat user.
        $asli  = array();
        $warna = array();

        // Baris SO dulu: itu yang ditagih. Memilih SJ ikut mengisi Detail SO,
        // jadi kalau keduanya dijumlahkan warna itu terhitung dua kali -
        // aturannya sama persis dengan semuaBaris() di layar.
        foreach ($ws as $r) {
            $warna['w' . $this->kunciWarna($r)][] = $r;
        }
        foreach ($idBaris as $id) {
            $r = $perId[$id];
            $asli[] = $r;
            $kunci = 'w' . $this->kunciWarna($r);
            // Warna yang tidak punya baris SO (SJ tanpa SO, mis. GK/GEN/WIP)
            // tetap menurunkan baris Invoice Summary sendiri.
            $adaSo = false;
            foreach ((array) (isset($warna[$kunci]) ? $warna[$kunci] : array()) as $x) {
                if (isset($x['asal']) && $x['asal'] === 'WS') { $adaSo = true; break; }
            }
            if (!$adaSo) { $warna[$kunci][] = $r; }
        }

        $isian = array();
        foreach ((array) $request->input('ringkas', array()) as $x) {
            if (is_array($x) && isset($x['warna']) && is_scalar($x['warna'])) {
                $isian['w' . trim((string) $x['warna'])] = $x;
            }
        }

        // ---- Invoice Summary: satu baris per warna ----
        $summary   = array();
        $diskon    = array();   // id_baris => disc warnanya (ikut dicatat di baris SJ)
        $urutanSj  = array();   // id_baris => urutan baris summary
        $diskonWs  = array();   // id_so_det => disc warnanya (baris SO)
        $urutanWs  = array();   // id_so_det => urutan baris summary
        $urutanWarna = array(); // kunci warna => urutan baris summary
        $diskonWarna = array(); // kunci warna => disc baris summary
        $tanpaKode = array();
        $tanpaSet  = array();   // baris SET yang Total Pieces-nya belum diisi
        $total = $discount = $totalFob = $discFob = 0;
        $no = 0;
        foreach ($warna as $kunci => $milik) {
            $no++;
            $w = substr($kunci, 1);
            if (!isset($isian[$kunci])) {
                return $salah('Invoice Summary row for colour ' . $w . ' is missing. Please reload the page.', 422);
            }
            $x = $isian[$kunci];

            $kode = isset($x['color_code']) && is_scalar($x['color_code']) ? trim((string) $x['color_code']) : '';
            if ($kode === '') {
                $tanpaKode[] = $no . ' (' . $w . ')';
            } elseif (mb_strlen($kode) > 255) {
                return $salah('Invoice Summary row ' . $no . ': Color Code is too long (max 255 characters).', 422);
            }

            // Satuan Total Pieces: satuan asli SJ-nya (YRD, KG, PCS, ... - ikut
            // Qty Invoiced) atau SET (diketik user). Satuan aslinya dibaca dari
            // baris SJ, bukan dari layar: kalau barangnya keluar dalam YRD,
            // "PCS" tidak ada artinya.
            $uomAsli = '';
            foreach ($milik as $r) {
                $u = strtoupper(trim((string) $r['uom']));
                if ($uomAsli === '' && $u !== '' && $u !== '-') { $uomAsli = $u; }
            }
            if ($uomAsli === '') { $uomAsli = 'PCS'; }

            $satuan = isset($x['total_pieces_unit']) && is_scalar($x['total_pieces_unit'])
                ? strtoupper(trim((string) $x['total_pieces_unit'])) : $uomAsli;
            if ($satuan !== 'SET' && $satuan !== $uomAsli) {
                return $salah('Invoice Summary row ' . $no . ' (' . $w . '): the unit of Total Pieces'
                    . ' must be ' . $uomAsli . ' or SET.', 422);
            }

            $pieces = $this->angkaIsian(isset($x['total_pieces']) ? $x['total_pieces'] : '');
            $fob    = $this->angkaIsian(isset($x['unit_cost_fob']) ? $x['unit_cost_fob'] : '');
            $disc   = $this->angkaIsian(isset($x['disc']) ? $x['disc'] : '');
            if ($pieces === null || $fob === null || $disc === null || $disc > 100) {
                return $salah('Invoice Summary row ' . $no . ' (' . $w . '): Total Pieces, Unit Cost (FOB)'
                    . ' and Disc must be numbers of 0 or more, and Disc cannot be more than 100.', 422);
            }
            $fob = round($fob, 4);

            $qty = 0;
            $kotorCm = 0;
            foreach ($milik as $r) {
                $qty     += (float) $r['qty'];
                $kotorCm += (float) $r['total_price'];
                if (isset($r['asal']) && $r['asal'] === 'WS') {
                    $diskonWs[(string) $r['id_so_det']] = $disc;
                    $urutanWs[(string) $r['id_so_det']] = $no;
                } else {
                    $diskon[(string) $r['id_baris']]   = $disc;
                    $urutanSj[(string) $r['id_baris']] = $no;
                }
            }

            // Satuan asli: Total Pieces selalu sama dengan Qty Invoiced - angka
            // dari layar tidak dipercaya. SET: wajib diisi user.
            if ($satuan !== 'SET') {
                $pieces = $qty;
            } elseif ($pieces <= 0) {
                $tanpaSet[] = $no . ' (' . $w . ')';
            }
            // Total FOB dihitung dari Total Pieces (satuan custom), bukan Qty Invoiced.
            $kotorFob = $pieces * $fob;
            $sisa = 1 - $disc / 100;

            $summary[] = array(
                'urutan'        => $no,
                'color_code'    => $kode,
                'color_name'    => $w,
                'total_pieces'  => $pieces,
                'total_pieces_unit' => $satuan,
                'qty_invoiced'  => $qty,
                'unit_cost_cm'  => $qty > 0 ? round($kotorCm / $qty, 4) : 0,
                'unit_cost_fob' => $fob,
                'disc'          => $disc,
                'total_cm'      => round($kotorCm * $sisa, 4),
                'total_fob'     => round($kotorFob * $sisa, 4),
            );

            $urutanWarna[$kunci] = $no;
            $diskonWarna[$kunci] = $disc;

            $total    += $kotorCm;
            $discount += $kotorCm * $disc / 100;
            $totalFob += $kotorFob;
            $discFob  += $kotorFob * $disc / 100;
        }

        // Baris SJ yang warnanya diwakili baris SO tidak ikut menurunkan baris
        // summary sendiri - tapi tetap harus tahu dia masuk baris summary yang
        // mana, karena nomor itu ikut disimpan di tiap baris SJ.
        foreach ($asli as $r) {
            $id = (string) $r['id_baris'];
            if (isset($urutanSj[$id])) { continue; }
            $kunci = 'w' . $this->kunciWarna($r);
            $urutanSj[$id] = isset($urutanWarna[$kunci]) ? $urutanWarna[$kunci] : null;
            $diskon[$id]   = isset($diskonWarna[$kunci]) ? $diskonWarna[$kunci] : 0;
        }
        if ($tanpaKode) {
            return $salah('Color Code is required on Invoice Summary row ' . implode(', ', $tanpaKode)
                . '. Type - if there is none.', 422);
        }
        if ($tanpaSet) {
            return $salah('Total Pieces is required on Invoice Summary row ' . implode(', ', $tanpaSet)
                . ' because the unit is SET.', 422);
        }

        // ---- rekap: rumusnya sama dengan layar & Invoice Local ----
        $dp    = max(0, (float) $request->input('dp', 0));
        $dpCbd = max(0, (float) $request->input('dp_cbd', 0));
        $retur = max(0, (float) $request->input('retur', 0));

        $vatPersen = (float) $request->input('vat_persen', 0);
        if (!in_array($vatPersen, array(0.0, 11.0, 12.0), true)) {
            $vatPersen = 0.0;
        }

        $twot    = $total - $discount - $dp - $dpCbd - $retur;
        $vat     = $twot * $vatPersen / 100;
        $twotFob = $totalFob - $discFob - $dp - $dpCbd - $retur;

        return array(
            'pc'         => $pc,
            'doc_type'   => $docType,
            'tgl_invoice' => $tgl,
            'header'     => $header,
            'kirim'      => $barisKirim,
            'summary'    => $summary,
            'asli'       => $asli,
            'ws'         => $ws,
            'diskon_ws'  => $diskonWs,
            'urutan_ws'  => $urutanWs,
            'diskon'     => $diskon,
            'urutan_sj'  => $urutanSj,
            'total'      => $total,
            'discount'   => $discount,
            'dp'         => $dp,
            'dp_cbd'     => $dpCbd,
            'retur'      => $retur,
            'twot'       => $twot,
            'vat_persen' => $vatPersen,
            'vat'        => $vat,
            'grand'      => $twot + $vat,
            'total_fob'  => $totalFob,
            'grand_fob'  => $twotFob + $twotFob * $vatPersen / 100,
        );
    }

    // ======================================================================
    //  Invoice Export - edit, cancel, rincian & cetak
    // ======================================================================

    /** Data layar Create/Edit Invoice Export yang selalu sama. */
    private function dataFormExport()
    {
        return array(
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "invoice-export-import",
            "subPage"        => "invoice-export",
            "containerFluid" => true,
            "customer"       => $this->daftarCustomer(),
            "profitCenter"   => $this->daftarProfitCenter(),
            "tipe"           => DB::connection(self::KONEKSI_AR)->select("SELECT id, type FROM tbl_type ORDER BY id"),
            "buyer"          => $this->daftarBuyer(),
            "shipp"          => self::SHIPP_EXPORT,
            "docType"        => self::DOC_TYPE_EXPORT,
            // mataUang: pilihan Currency di blok Shipment Details (dari tabel so).
            // mataUangSj: pilihan Curr di Detail SJ untuk baris tanpa SO.
            "mataUang"       => $this->daftarCurrency(),
            "mataUangSj"     => self::MATA_UANG_SJ,
            "shipper"        => $this->shipperNag(),
            "negara"         => $this->daftarNegara(),
        );
    }

    /**
     * Pesan kalau tabel/kolom Invoice Export dari berkas migrasi belum ada,
     * null kalau sudah siap semua.
     */
    private function pesanBelumSiapExport()
    {
        $butuh = array(
            array('20260916_invoice_exim_booking.sql', self::TABEL_DET, null),
            array('20260916_invoice_exim_booking.sql', self::TABEL_POT, null),
            array('20260917_invoice_exim_export.sql', self::TABEL_EXP_H, null),
            array('20260917_invoice_exim_export.sql', self::TABEL_EXP_SHIP, null),
            array('20260917_invoice_exim_export.sql', self::TABEL_EXP_DET, null),
            array('20260917_invoice_exim_export.sql', self::TABEL_DET, 'urutan_summary'),
            array('20260917_invoice_exim_export.sql', self::TABEL_POT, 'grand_total_fob'),
            array('20260918_invoice_exim_export_tgl.sql', self::TABEL_EXP_H, 'tgl_invoice'),
            array('20260921_invoice_exim_export_satuan.sql', self::TABEL_EXP_DET, 'total_pieces_unit'),
            array('20260928_invoice_exim_so.sql', self::TABEL_SO, null),
        );
        foreach ($butuh as $b) {
            $ada = $b[2] === null ? $this->tabelAda($b[1]) : $this->kolomAda($b[1], $b[2]);
            if (!$ada) {
                return 'The database is not ready for Invoice Export yet. Run migrations/' . $b[0] . ' first.';
            }
        }
        return null;
    }

    /**
     * Mata uang invoice. Diambil dari baris SO dulu, baru baris SJ - invoice
     * boleh dibuat tanpa SJ sama sekali, jadi baris SJ tidak bisa dijadikan
     * satu-satunya sumber.
     */
    private function currInvoice(array $s)
    {
        foreach (array('ws', 'asli') as $kunci) {
            foreach ((array) (isset($s[$kunci]) ? $s[$kunci] : array()) as $r) {
                $c = trim((string) (isset($r['curr']) ? $r['curr'] : ''));
                if ($c !== '' && $c !== '-') { return $c; }
            }
        }
        return '';
    }

    /** Kolom tbl_book_invoice yang ditulis sama oleh Save & Update Export. */
    private function kolomBookExport(array $s, Request $request)
    {
        return array(
            'id_customer'      => $s['header']['id_seller'],
            'id_customer_ship' => $s['header']['id_seller'],
            'id_type'          => (int) $request->input('id_type'),
            'value'            => round($s['grand'], 2),
            'curr'             => $this->currInvoice($s),
            'doc_type'         => $s['doc_type'],
            'doc_number'       => (string) $request->input('doc_number'),
        );
    }

    /**
     * Tulis isi Invoice Export di bawah header: shipment, summary, baris SJ
     * & rekap. Dipakai Save dan Update, jadi keduanya tidak mungkin beda cara.
     */
    private function tulisIsiExport($db, $idBook, $noInvoice, array $s, $user, $now)
    {
        $tanda = array('id_book_invoice' => $idBook, 'no_invoice' => $noInvoice);
        $jejak = array('created_by' => $user, 'created_at' => $now);

        $kirim = array();
        foreach ($s['kirim'] as $k) {
            $kirim[] = $tanda + $k + $jejak;
        }
        $db->table(self::TABEL_EXP_SHIP)->insert($kirim);

        $ringkas = array();
        foreach ($s['summary'] as $r) {
            $ringkas[] = $tanda + $r + $jejak;
        }
        $db->table(self::TABEL_EXP_DET)->insert($ringkas);

        // Baris SJ boleh tidak ada - invoice yang dibuat dari WS saja.
        $det = $this->barisDetail($idBook, $noInvoice, $s, $user, $now);
        foreach ($det as $i => $d) {
            // Pertahanan berlapis: baris tanpa nomor summary lebih baik tersimpan
            // dengan kolom kosong daripada menggagalkan seluruh penyimpanan.
            $id = (string) $d['id_baris'];
            $det[$i]['urutan_summary'] = isset($s['urutan_sj'][$id]) ? $s['urutan_sj'][$id] : null;
        }
        if ($det) {
            $db->table(self::TABEL_DET)->insert($det);
        }

        $so = $this->barisSo($idBook, $noInvoice, $s, $user, $now);
        if ($so) {
            $db->table(self::TABEL_SO)->insert($so);
        }

        $pot = $this->barisPotongan($idBook, $noInvoice, $s, $user, $now);
        $pot['total_fob']       = round($s['total_fob'], 4);
        $pot['grand_total_fob'] = round($s['grand_fob'], 4);
        $db->table(self::TABEL_POT)->insert($pot);
    }

    /**
     * Halaman Edit Invoice Export.
     *
     * Layarnya sama dengan Create, cuma isinya sudah terisi. Yang dibaca apa
     * yang tersimpan (bukan sumber SJ-nya lagi) supaya tampilannya persis
     * seperti saat disimpan.
     */
    public function editExport($id)
    {
        if ($this->pesanBelumSiapExport() !== null) {
            abort(404);
        }
        $inv = $this->headerDraft($id, self::SHIPP_EXPORT);
        if (!$inv) {
            abort(404);
        }

        $db = $this->koneksiAr();
        $idBook = (int) $inv['id'];

        $h = $db->select("SELECT * FROM " . self::TABEL_EXP_H . " WHERE id_book_invoice = ? LIMIT 1", array($idBook));
        if (!$h) {
            abort(404);
        }

        $kirim = array();
        foreach ($db->select(
            "SELECT dest_purchase, style_no, brand, chanel_description, currency, payment_term,
                    final_destination, country_origin, ship_mode, term_of_sale, transfer_point,
                    port_of_loading, total_gross_weight, total_net_weight, total_net_net_weight,
                    total_carton, product_description
               FROM " . self::TABEL_EXP_SHIP . " WHERE id_book_invoice = ? ORDER BY urutan, id",
            array($idBook)
        ) as $r) {
            $r = (array) $r;
            foreach (array_keys(self::ANGKA_KIRIM) as $k) {
                $r[$k] = $this->angkaIsi($r[$k]);
            }
            $kirim[] = $r;
        }

        $ringkas = array();
        foreach ($db->select(
            "SELECT color_name, color_code, total_pieces, total_pieces_unit, qty_invoiced, unit_cost_fob, disc
               FROM " . self::TABEL_EXP_DET . " WHERE id_book_invoice = ? ORDER BY urutan, id",
            array($idBook)
        ) as $r) {
            // Baris lama belum punya satuan: kalau Total Pieces-nya diketik
            // berbeda dari Qty Invoiced, itu dianggap SET supaya isiannya tidak
            // tertimpa; selain itu ikut satuan asli SJ-nya (dipastikan di layar).
            $satuan = strtoupper(trim((string) $r->total_pieces_unit));
            if ($satuan === '') {
                $satuan = ((float) $r->total_pieces > 0
                    && abs((float) $r->total_pieces - (float) $r->qty_invoiced) > 0.0001) ? 'SET' : 'PCS';
            }
            $ringkas[] = array(
                'warna'         => trim((string) $r->color_name),
                'color_code'    => (string) $r->color_code,
                'total_pieces'  => $this->angkaIsi($r->total_pieces),
                'total_pieces_unit' => $satuan,
                'unit_cost_fob' => $this->angkaIsi($r->unit_cost_fob),
                'disc'          => $this->angkaIsi($r->disc),
            );
        }

        $p = $db->select(
            "SELECT dp, dp_cbd, retur, vat_persen FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1",
            array($idBook)
        );

        return view('export-import.invoice.export.form', $this->dataFormExport() + array(
            'noInvoice'   => $inv['no_invoice'],
            // Penanda mode edit - dipakai layar & JavaScript-nya.
            'ubah'        => $inv,
            'ubahHeader'  => (array) $h[0],
            'ubahKirim'   => $kirim,
            'ubahRingkas' => $ringkas,
            'ubahBaris'   => $this->barisSjTersimpan($idBook),
            'ubahBarisSo' => $this->barisSoTersimpan($idBook),
            'ubahPot'     => $p ? (array) $p[0] : array('dp' => 0, 'dp_cbd' => 0, 'retur' => 0, 'vat_persen' => 0),
        ));
    }

    /**
     * Simpan hasil Edit Invoice Export.
     *
     * Nomor invoice & profit center tidak ikut berubah (sama dengan Local).
     * Isi di bawah header - shipment, summary, baris SJ, rekap - dihapus lalu
     * ditulis ulang dalam satu transaksi. Header export diperbarui di tempat
     * supaya jejak pembuatnya (created_by/at) tidak hilang.
     */
    public function perbaruiExport(Request $request)
    {
        $inv = $this->headerDraft($request->input('id'), self::SHIPP_EXPORT);
        if (!$inv) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice can no longer be edited. It may have been processed or removed.'), 409);
        }

        $s = $this->siapkanSimpanExport($request, $inv['profit_center'], $inv['id']);
        if (isset($s['gagal'])) {
            return $s['gagal'];
        }

        $db        = $this->koneksiAr();
        $user      = (string) (auth()->user()->username ?? '');
        $now       = now()->format('Y-m-d H:i:s');
        $idBook    = (int) $inv['id'];
        $noInvoice = (string) $inv['no_invoice'];

        try {
            $db->transaction(function () use ($db, $s, $request, $user, $now, $idBook, $noInvoice) {
                // Status dikunci & diperiksa lagi di dalam transaksi supaya tidak
                // menimpa invoice yang baru saja diproses orang lain.
                $kunci = $db->select("SELECT status FROM tbl_book_invoice WHERE id = ? FOR UPDATE", array($idBook));
                if (!$kunci || strtoupper((string) $kunci[0]->status) !== 'DRAFT') {
                    throw new \RuntimeException('bukan_draft');
                }

                $db->table('tbl_book_invoice')->where('id', $idBook)->update($this->kolomBookExport($s, $request));

                $header = $s['header'] + array('updated_by' => $user, 'updated_at' => $now);
                if ($db->table(self::TABEL_EXP_H)->where('id_book_invoice', $idBook)->exists()) {
                    $db->table(self::TABEL_EXP_H)->where('id_book_invoice', $idBook)->update($header);
                } else {
                    $db->table(self::TABEL_EXP_H)->insert(array('id_book_invoice' => $idBook, 'no_invoice' => $noInvoice)
                        + $header + array('created_by' => $user, 'created_at' => $now));
                }

                $tabel = array(self::TABEL_EXP_SHIP, self::TABEL_EXP_DET, self::TABEL_DET, self::TABEL_POT);
                if ($this->tabelAda(self::TABEL_SO)) { $tabel[] = self::TABEL_SO; }
                foreach ($tabel as $t) {
                    $db->table($t)->where('id_book_invoice', $idBook)->delete();
                }
                $this->tulisIsiExport($db, $idBook, $noInvoice, $s, $user, $now);
                // Nomor invoicenya ikut ditulis ke SJ-nya di bppb.
                $this->selaraskanInvno($db, $idBook, $noInvoice);
                $this->catatRiwayat($db, $idBook, $noInvoice, self::SHIPP_EXPORT, 'UPDATE', $user, $now);
            });
        } catch (\RuntimeException $e) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice is no longer a draft, so it cannot be changed.'), 409);
        } catch (\Throwable $e) {
            Log::error('Invoice EXIM Export perbarui gagal - ' . $e->getMessage());
            return response()->json(array('status' => false, 'pesan' => 'Update failed, please try again.'), 500);
        }

        // SJ knitting ada di database lain, jadi ditandai di luar transaksi -
        // sesudah invoicenya benar-benar tersimpan.
        $this->tandaiInvnoNak($noInvoice, $this->idSjNak($idBook));

        return response()->json(array(
            'status'     => true,
            'no_invoice' => $noInvoice,
            'pesan'      => 'Invoice updated.',
        ));
    }

    public function batalExport(Request $request)
    {
        return $this->batal($request->input('id'), self::SHIPP_EXPORT);
    }

    /** Rincian satu Invoice Export, untuk modal di halaman daftar. */
    public function detailExport(Request $request)
    {
        $id = (int) $request->query('id');
        if ($id < 1) {
            return response()->json(array('status' => false, 'pesan' => 'Invalid invoice id.'), 422);
        }
        $belum = $this->pesanBelumSiapExport();
        if ($belum !== null) {
            return response()->json(array('status' => false, 'pesan' => $belum), 500);
        }

        $isi = $this->isiExport($id, false);
        if (!$isi) {
            return response()->json(array('status' => false, 'pesan' => 'Invoice not found.'), 404);
        }

        return response()->json(array('status' => true) + $isi);
    }

    /**
     * Seluruh isi satu Invoice Export. Dipakai modal rincian, PDF & Excel,
     * jadi ketiganya selalu membaca angka yang sama.
     *
     * @param bool $tolakCancel true untuk cetakan - invoice CANCEL dianggap
     *                          tidak ada, karena yang dibatalkan tidak boleh
     *                          punya cetakan yang beredar.
     */
    private function isiExport($id, $tolakCancel)
    {
        $db = $this->koneksiAr();

        $sql = "SELECT b.id, b.no_invoice, b.status, b.doc_type, b.doc_number, b.profit_center,
                       b.curr, b.value, b.booking_by, b.booking_date, DATE(b.tgl_book_inv) AS tanggal,
                       t.type,
                       h.no_invoice_2, h.tgl_invoice, h.shipper_nama, h.shipper_alamat,
                       h.seller_nama, h.seller_alamat, h.purchaser_nama, h.purchaser_alamat,
                       h.receiver_nama, h.receiver_alamat, h.invoice_notes,
                       h.manufacturer_nama, h.manufacturer_alamat, h.reference
                  FROM tbl_book_invoice b
                 INNER JOIN " . self::TABEL_EXP_H . " h ON h.id_book_invoice = b.id
                  LEFT JOIN tbl_type t ON t.id = b.id_type
                 WHERE b.id = ? AND b.shipp = ?";
        if ($tolakCancel) {
            $sql .= " AND UPPER(b.status) <> 'CANCEL'";
        }
        $h = $db->select($sql . " LIMIT 1", array((int) $id, self::SHIPP_EXPORT));
        if (!$h) {
            return null;
        }
        $header = (array) $h[0];

        $kirim = array();
        foreach ($db->select(
            "SELECT urutan, dest_purchase, style_no, brand, chanel_description, currency, payment_term,
                    final_destination, country_origin, ship_mode, term_of_sale, transfer_point,
                    port_of_loading, total_gross_weight, total_net_weight, total_net_net_weight,
                    total_carton, product_description
               FROM " . self::TABEL_EXP_SHIP . " WHERE id_book_invoice = ? ORDER BY urutan, id",
            array((int) $id)
        ) as $r) {
            $kirim[] = (array) $r;
        }

        // Nilai kotor CM per baris summary diambil langsung dari baris SJ-nya -
        // sama dengan cara Save menghitung, jadi tidak ada selisih pembulatan
        // dari unit cost rata-rata.
        $kotorCm = array();
        foreach ($db->select(
            "SELECT urutan_summary, SUM(total_price) AS kotor FROM " . self::TABEL_DET . "
              WHERE id_book_invoice = ? GROUP BY urutan_summary",
            array((int) $id)
        ) as $r) {
            $kotorCm[(int) $r->urutan_summary] = (float) $r->kotor;
        }

        $kolSatuan = $this->kolomAda(self::TABEL_EXP_DET, 'total_pieces_unit') ? 'total_pieces_unit' : 'NULL';
        $summary = array();
        foreach ($db->select(
            "SELECT urutan, color_code, color_name, total_pieces, $kolSatuan AS total_pieces_unit,
                    qty_invoiced, unit_cost_cm, unit_cost_fob, disc, total_cm, total_fob
               FROM " . self::TABEL_EXP_DET . " WHERE id_book_invoice = ? ORDER BY urutan, id",
            array((int) $id)
        ) as $r) {
            $r = (array) $r;
            $qty = (float) $r['qty_invoiced'];
            $r['kotor_cm']  = isset($kotorCm[(int) $r['urutan']]) ? $kotorCm[(int) $r['urutan']] : $qty * (float) $r['unit_cost_cm'];
            // FOB memakai Total Pieces (satuan custom). Baris lama yang belum
            // punya satuan tetap memakai Qty Invoiced - cetakannya tidak berubah.
            $satuan = strtoupper(trim((string) $r['total_pieces_unit']));
            // SET: angka ketikan user. Satuan asli (YRD, KG, PCS, ...): sama
            // dengan Qty Invoiced, dan itu yang tersimpan di total_pieces.
            $qtyFob = $satuan === 'SET' ? (float) $r['total_pieces'] : $qty;
            $r['kotor_fob'] = $qtyFob * (float) $r['unit_cost_fob'];
            $summary[] = $r;
        }

        $baris = array();
        foreach ($db->select(
            "SELECT asal, so_number, bppb_number, sj_date, shipp_number, ws, styleno, product_group,
                    product_item, color, size, curr, uom, qty, unit_price, disc, total_price, urutan_summary
               FROM " . self::TABEL_DET . " WHERE id_book_invoice = ? ORDER BY urutan_summary, id",
            array((int) $id)
        ) as $r) {
            $baris[] = (array) $r;
        }

        $p = $db->select(
            "SELECT total, discount, dp, dp_cbd, retur, twot, vat_persen, vat, grand_total, total_fob, grand_total_fob
               FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1",
            array((int) $id)
        );
        $pot = $p ? (array) $p[0] : array();
        $f = function ($k) use ($pot) { return isset($pot[$k]) ? (float) $pot[$k] : 0.0; };

        // Versi CM dibaca dari tabel rekap apa adanya. Versi FOB disusun dari
        // baris summary karena yang tersimpan cuma total & grand total-nya.
        // DP, DP/CBD, Return & tarif VAT-nya satu, dipakai untuk keduanya -
        // rumusnya sama dengan layar.
        $totalFob = 0;
        $discFob = 0;
        foreach ($summary as $r) {
            $totalFob += $r['kotor_fob'];
            $discFob  += $r['kotor_fob'] * (float) $r['disc'] / 100;
        }
        $vatPersen = $f('vat_persen');
        $twotFob = $totalFob - $discFob - $f('dp') - $f('dp_cbd') - $f('retur');

        return array(
            'header'     => $header,
            'kirim'      => $kirim,
            'summary'    => $summary,
            'baris'      => $baris,
            'vat_persen' => $vatPersen,
            'rekap'      => array(
                'cm'  => array(
                    'total' => $f('total'), 'discount' => $f('discount'), 'dp' => $f('dp'),
                    'dp_cbd' => $f('dp_cbd'), 'retur' => $f('retur'), 'twot' => $f('twot'),
                    'vat' => $f('vat'), 'grand' => $f('grand_total'),
                ),
                'fob' => array(
                    'total' => $totalFob, 'discount' => $discFob, 'dp' => $f('dp'),
                    'dp_cbd' => $f('dp_cbd'), 'retur' => $f('retur'), 'twot' => $twotFob,
                    'vat' => $twotFob * $vatPersen / 100, 'grand' => $f('grand_total_fob'),
                ),
            ),
        );
    }

    /**
     * Data cetak Invoice Export untuk satu versi harga.
     *
     *   cm  - harga CM, dipakai penagihan tim AR
     *   fob - harga FOB, dipakai perizinan barang keluar BC
     *
     * Isinya sama persis selain harga & rekapnya - datanya satu. Judulnya pun
     * sama ("COMMERCIAL INVOICE"): versinya sengaja tidak ditulis di dokumen.
     */
    private function dataCetakExport($id, $versi)
    {
        $versi = strtolower(trim((string) $versi));
        $id = (int) $id;
        if (!in_array($versi, array('cm', 'fob'), true) || $id < 1 || $this->pesanBelumSiapExport() !== null) {
            abort(404);
        }
        $isi = $this->isiExport($id, true);
        if (!$isi) {
            abort(404);
        }

        $h = $isi['header'];
        $summary = array();
        $subQty = 0;
        $subTotal = 0;
        $adaSet = false;
        foreach ($isi['summary'] as $r) {
            $ext = $versi === 'cm' ? $r['kotor_cm'] : $r['kotor_fob'];
            $subQty   += (float) $r['qty_invoiced'];
            $subTotal += $ext;

            // Total Pieces cuma dicetak untuk baris SET - untuk satuan asli
            // (YRD, KG, PCS, ...) angkanya sama persis dengan Quantity Invoiced,
            // jadi tidak perlu diulang. Baris lama yang belum punya satuan
            // dianggap SET kalau isiannya memang beda dari qty (sama dengan
            // tebakan di layar Edit).
            $satuan = strtoupper(trim((string) (isset($r['total_pieces_unit']) ? $r['total_pieces_unit'] : '')));
            if ($satuan === '') {
                $satuan = ((float) $r['total_pieces'] > 0
                    && abs((float) $r['total_pieces'] - (float) $r['qty_invoiced']) > 0.0001) ? 'SET' : 'PCS';
            }
            if ($satuan === 'SET') {
                $adaSet = true;
            }

            $summary[] = array(
                'color_code'   => (string) $r['color_code'],
                'color_name'   => (string) $r['color_name'],
                'satuan'       => $satuan,
                'total_pieces' => $satuan === 'SET' ? (float) $r['total_pieces'] : null,
                // Angka apa adanya untuk Excel - Excel belum ikut aturan PDF di atas.
                'total_pieces_semua' => (float) $r['total_pieces'],
                'qty'          => (float) $r['qty_invoiced'],
                'unit_cost'    => (float) ($versi === 'cm' ? $r['unit_cost_cm'] : $r['unit_cost_fob']),
                'extended'     => $ext,
            );
        }

        $tipe = strtoupper(trim((string) $h['type']));
        $curr = strtoupper(trim((string) $h['curr']));

        return array(
            'versi'     => $versi,
            // Versinya ikut dicetak di samping DATE - satu invoice bisa dicetak
            // dua kali dengan harga berbeda, jadi pembacanya harus tahu yang
            // dipegangnya yang mana.
            'labelVersi' => $this->labelVersiExport($versi),
            'judul'     => ($tipe !== '' ? $tipe : 'COMMERCIAL') . ' INVOICE',
            // Cetakan cuma menampilkan SATU nomor: Invoice Number #2 kalau diisi
            // (dipakai kalau buyer minta penomoran sendiri), selain itu nomor sistem.
            'noCetak'   => trim((string) $h['no_invoice_2']) !== '' ? trim((string) $h['no_invoice_2']) : (string) $h['no_invoice'],
            'inv'       => $h,
            'tanggal'   => $h['tgl_invoice'] ? date('d-M-y', strtotime($h['tgl_invoice'])) : '',
            'kirim'     => $isi['kirim'],
            'summary'   => $summary,
            // Kolom Total Pieces cuma dicetak kalau ada baris SET.
            'adaSet'    => $adaSet,
            'subQty'    => $subQty,
            'subTotal'  => $subTotal,
            'rekap'     => $isi['rekap'][$versi],
            'vatPersen' => $isi['vat_persen'],
            'curr'      => $curr,
            'simbol'    => $curr === 'USD' ? '$' : $curr,
            'baris'     => array(
                'shipper'   => $this->barisAlamat($h['shipper_nama'], $h['shipper_alamat']),
                'seller'    => $this->barisAlamat($h['seller_nama'], $h['seller_alamat']),
                'purchaser' => $this->barisAlamat($h['purchaser_nama'], $h['purchaser_alamat']),
                'receiver'  => $this->barisAlamat($h['receiver_nama'], $h['receiver_alamat']),
            ),
            'kop'       => self::KOP_EXPORT,
            'penanda'   => self::TTD_EXPORT,
            // mPDF membaca logo dari berkas lokal, jadi yang dikirim path penuh.
            'logo'      => str_replace('\\', '/', public_path('img/nag_logo3.jpg')),
        );
    }

    /** Kop surat Invoice Export - mengikuti contoh cetakan dari tim EXIM. */
    const KOP_EXPORT = array(
        'nama'   => 'PT. NIRWANA ALABARE GARMENT',
        'alamat' => array(
            'Jl. Raya Rancaekek - Majalaya No. 289 Desa Solokan Jeruk, Kecamatan Solokan Jeruk,',
            'Kabupaten Bandung 40382 Jawa Barat - Indonesia',
        ),
        'telp'   => 'Phone : +62 22 8596 2076 / +62 22 8596 2081',
    );

    /** Penanda tangan Invoice Export - sama dengan Invoice Local. */
    const TTD_EXPORT = array('nama' => 'Yus Yulius', 'jabatan' => 'Exim Manager');

    /** Nama + alamat jadi daftar baris cetak; baris kosong dibuang. */
    private function barisAlamat($nama, $alamat)
    {
        $out = array();
        if (trim((string) $nama) !== '') {
            $out[] = trim((string) $nama);
        }
        foreach (preg_split('/\r\n|\r|\n/', (string) $alamat) as $b) {
            if (trim($b) !== '') {
                $out[] = trim($b);
            }
        }
        return $out;
    }

    /** Nama berkas cetak Export: nomor invoice + versi, mis. 0197_E_NAG_0926_CM. */
    private function namaBerkasExport(array $data)
    {
        return str_replace('/', '_', (string) $data['inv']['no_invoice']) . '_' . strtoupper($data['versi']);
    }

    /**
     * Label versi cetakan Export: FOB atau CMT.
     *
     * Dipakai PDF (CARING & Classic) dan Excel - satu tempat, supaya ketiganya
     * tidak bisa menyebut hal yang berbeda untuk invoice yang sama.
     */
    private function labelVersiExport($versi)
    {
        return strtolower(trim((string) $versi)) === 'fob' ? 'FOB' : 'CMT';
    }

    /** PDF Invoice Export - ?id=..&versi=cm|fob */
    public function pdfExport(Request $request)
    {
        $data = $this->dataCetakExport($request->query('id'), $request->query('versi'));

        // &gaya=lama = PDF Classic (tanpa CARING) - tampilan sebelum gaya CARING.
        if ($this->gayaLama($request)) {
            $mpdf = new \Mpdf\Mpdf(array(
                'tempDir'       => storage_path('app/mpdf'),
                'format'        => 'A4',
                'margin_left'   => 10,
                'margin_right'  => 10,
                'margin_top'    => 10,
                'margin_bottom' => 14,
            ));
            $mpdf->setFooter('{PAGENO} / {nbpg}');
            $tampilan = 'export-import.invoice.export.pdf-lama';
        } else {
            // Pita kaki halaman Export 12mm, jadi margin bawahnya cukup 26mm.
            $mpdf = $this->mpdfCaring(26);
            $data += $this->asetCaring();
            $tampilan = 'export-import.invoice.export.pdf';
        }
        $mpdf->SetTitle($data['inv']['no_invoice'] . ' ' . strtoupper($data['versi']));
        $mpdf->WriteHTML(view($tampilan, $data)->render());

        return response($mpdf->Output('', 'S'), 200, array(
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $this->namaBerkasExport($data) . '.pdf"',
        ));
    }

    /**
     * Excel Invoice Export - ?id=..&versi=cm|fob
     *
     * Susunannya mengikuti PDF baris per baris di atas delapan kolom yang sama
     * (A-H). Angka ditulis sebagai angka sungguhan dengan format tampilan yang
     * sama, jadi terlihat sama tapi masih bisa dihitung.
     */
    public function excelExport(Request $request)
    {
        $data = $this->dataCetakExport($request->query('id'), $request->query('versi'));
        $inv  = $data['inv'];

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle('Invoice ' . strtoupper($data['versi']));
        $ss->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);
        // Garis kotak bawaan Excel dimatikan: yang boleh terlihat hanya bingkai
        // dokumennya sendiri, seperti invoice yang dicetak.
        $s->setShowGridlines(false);

        foreach (array('A' => 15, 'B' => 21, 'C' => 11, 'D' => 13, 'E' => 12, 'F' => 12, 'G' => 12, 'H' => 21) as $kol => $lebar) {
            $s->getColumnDimension($kol)->setWidth($lebar);
        }

        $TIPIS = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $teks  = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;

        // Sel/rentang: tulis teks, gabung, rata, tebal, bingkai.
        $tulis = function ($rentang, $nilai, array $gaya = array()) use ($s, $TIPIS, $teks) {
            $awal = explode(':', $rentang)[0];
            if (strpos($rentang, ':') !== false) {
                $s->mergeCells($rentang);
            }
            if (is_string($nilai)) {
                $s->setCellValueExplicit($awal, $nilai, $teks);
            } elseif ($nilai !== null) {
                $s->setCellValue($awal, $nilai);
            }
            $st = $s->getStyle($rentang);
            if (!empty($gaya['tebal']))   { $st->getFont()->setBold(true); }
            if (!empty($gaya['miring']))  { $st->getFont()->setItalic(true); }
            if (!empty($gaya['ukuran']))  { $st->getFont()->setSize($gaya['ukuran']); }
            $st->getAlignment()->setHorizontal(isset($gaya['rata']) ? $gaya['rata'] : 'left')
                ->setVertical('center')->setWrapText(!empty($gaya['lipat']));
            if (!empty($gaya['format'])) { $st->getNumberFormat()->setFormatCode($gaya['format']); }
            if (!empty($gaya['bingkai'])) { $st->getBorders()->getAllBorders()->setBorderStyle($TIPIS); }
            // Latar abu tipis untuk baris judul tabel - membedakan kepala dari isi
            // tanpa perlu garis tebal.
            if (!empty($gaya['latar'])) {
                $st->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($gaya['latar']);
            }
        };
        $ABU = 'FFF1F5F9';
        $garisBawah = function ($baris) use ($s, $TIPIS) {
            $s->getStyle('A' . $baris . ':H' . $baris)->getBorders()->getBottom()->setBorderStyle($TIPIS);
        };

        $angka3 = '#,##0.000';
        $qtyFmt = '#,##0.##';
        $uang   = '"' . $data['simbol'] . '"* #,##0.00';
        $unit   = '#,##0.00##';

        // ---------------- Kop surat ----------------
        $logo = public_path('img/nag_logo3.jpg');
        if (is_file($logo)) {
            $gbr = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $gbr->setPath($logo);
            $gbr->setHeight(56);
            $gbr->setCoordinates('A1');
            $gbr->setOffsetX(6);
            $gbr->setOffsetY(4);
            $gbr->setWorksheet($s);
        }
        $tulis('B1:H1', $data['kop']['nama'], array('tebal' => true, 'ukuran' => 16, 'rata' => 'center'));
        $tulis('B2:H2', $data['kop']['alamat'][0], array('miring' => true, 'ukuran' => 8, 'rata' => 'center'));
        $tulis('B3:H3', $data['kop']['alamat'][1], array('miring' => true, 'ukuran' => 8, 'rata' => 'center'));
        $tulis('B4:H4', $data['kop']['telp'], array('miring' => true, 'tebal' => true, 'ukuran' => 8, 'rata' => 'center'));
        $s->getRowDimension(1)->setRowHeight(24);
        $s->getStyle('A4:H4')->getBorders()->getBottom()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DOUBLE);

        // ---------------- Judul & nomor ----------------
        $b = 6;
        $tulis('A' . $b . ':H' . $b, $data['judul'], array('tebal' => true, 'ukuran' => 11, 'rata' => 'center'));
        $b += 2;
        $tulis('A' . $b, 'INVOICE NO :');
        $tulis('B' . $b . ':D' . $b, $data['noCetak']);
        // DATE sejajar label pihak di kolom E, tanggalnya sejajar isinya (F:H).
        $tulis('E' . $b, 'DATE :');
        $tulis('F' . $b . ':H' . $b, $data['tanggal'] . '   |   ' . $data['labelVersi']);

        // ---------------- Pihak-pihak ----------------
        $pasangan = array(
            // Labelnya saja yang berbeda; isinya tetap dari kolom yang sama.
            array('SHIP FROM', $data['baris']['shipper'],
                  'PURCHASER / INVOICE TO', $data['baris']['seller']),
            array('ULTIMATE CONSIGNEE', $data['baris']['purchaser'],
                  'SHIP TO :', $data['baris']['receiver']),
        );
        foreach ($pasangan as $p) {
            $b += 2;
            $tulis('A' . $b . ':D' . $b, $p[0], array('tebal' => true));
            $tulis('E' . $b, $p[2], array('tebal' => true));
            // Seperti contoh cetakan: pihak kiri mulai di bawah labelnya, pihak
            // kanan mulai sejajar labelnya.
            foreach ($p[1] as $i => $isi) {
                $tulis('A' . ($b + 1 + $i) . ':D' . ($b + 1 + $i), $isi);
            }
            foreach ($p[3] as $i => $isi) {
                $tulis('F' . ($b + $i) . ':H' . ($b + $i), $isi);
            }
            $b += max(count($p[1]), count($p[3]) - 1);
        }
        $b += 1;
        $garisBawah($b);

        // ---------------- Shipment Details ----------------
        foreach ($data['kirim'] as $k) {
            $b++;
            foreach (array('A' => 'Dest Purchase', 'B' => 'Style NO', 'C' => 'Brand', 'D' => 'Chanel Description',
                           'E' => 'Currency', 'F' => 'Payment Term', 'G' => 'Final Destination',
                           'H' => 'Country of origin') as $kol => $judul) {
                $tulis($kol . $b, $judul, array('tebal' => true, 'rata' => 'center', 'lipat' => true, 'bingkai' => true, 'latar' => $ABU));
            }
            $s->getRowDimension($b)->setRowHeight(26);
            $b++;
            foreach (array('A' => 'dest_purchase', 'B' => 'style_no', 'C' => 'brand', 'D' => 'chanel_description',
                           'E' => 'currency', 'F' => 'payment_term', 'G' => 'final_destination',
                           'H' => 'country_origin') as $kol => $kunci) {
                $tulis($kol . $b, (string) $k[$kunci], array('rata' => 'center', 'lipat' => true, 'bingkai' => true));
            }
            $b++;
            foreach (array('A' => 'Ship Mode', 'B' => 'Term of Sale', 'C' => 'Transfer Point', 'D' => 'Port Of Loading',
                           'E' => 'Total Gross Weight(KGS)', 'F' => 'Total Net Weight(KGS)',
                           'G' => 'Total Net Net Weight(KGS)', 'H' => 'Total Carton') as $kol => $judul) {
                $tulis($kol . $b, $judul, array('tebal' => true, 'rata' => 'center', 'lipat' => true, 'bingkai' => true, 'latar' => $ABU));
            }
            $s->getRowDimension($b)->setRowHeight(26);
            $b++;
            foreach (array('A' => 'ship_mode', 'B' => 'term_of_sale', 'C' => 'transfer_point', 'D' => 'port_of_loading') as $kol => $kunci) {
                $tulis($kol . $b, (string) $k[$kunci], array('rata' => 'center', 'lipat' => true, 'bingkai' => true));
            }
            foreach (array('E' => 'total_gross_weight', 'F' => 'total_net_weight', 'G' => 'total_net_net_weight') as $kol => $kunci) {
                $tulis($kol . $b, (float) $k[$kunci], array('rata' => 'center', 'format' => $angka3, 'bingkai' => true));
            }
            $tulis('H' . $b, (float) $k['total_carton'], array('rata' => 'center', 'format' => $qtyFmt, 'bingkai' => true));
            $b++;
            $tulis('A' . $b . ':H' . $b, 'Product Description', array('rata' => 'center', 'bingkai' => true));
            $b++;
            $tulis('A' . $b . ':H' . $b, (string) $k['product_description'], array('rata' => 'center', 'lipat' => true, 'bingkai' => true));
            $s->getRowDimension($b)->setRowHeight(max(15, 13 * (int) ceil(max(1, mb_strlen((string) $k['product_description'])) / 110)));
        }

        // ---------------- Invoice Summary ----------------
        $b += 2;
        $tulis('A' . $b . ':H' . $b, 'Invoice Summary', array('tebal' => true));
        $b++;
        $tulis('A' . $b, 'Color Code', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'lipat' => true, 'latar' => $ABU));
        $tulis('B' . $b, 'Color Name', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'lipat' => true, 'latar' => $ABU));
        $tulis('C' . $b . ':D' . $b, 'Total Pieces (Custom Units)', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'lipat' => true, 'latar' => $ABU));
        $tulis('E' . $b . ':F' . $b, 'Quantity Invoiced (Each)', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'lipat' => true, 'latar' => $ABU));
        $tulis('G' . $b, 'Unit Cost', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'latar' => $ABU));
        $tulis('H' . $b, 'Extended Line Total', array('tebal' => true, 'rata' => 'center', 'bingkai' => true, 'lipat' => true, 'latar' => $ABU));
        $s->getRowDimension($b)->setRowHeight(26);

        foreach ($data['summary'] as $r) {
            $b++;
            $tulis('A' . $b, $r['color_code'], array('bingkai' => true, 'lipat' => true));
            $tulis('B' . $b, $r['color_name'], array('rata' => 'center', 'bingkai' => true, 'lipat' => true));
            $tulis('C' . $b . ':D' . $b, $r['total_pieces_semua'] ? $r['total_pieces_semua'] : null,
                array('rata' => 'center', 'format' => $qtyFmt, 'bingkai' => true));
            $tulis('E' . $b . ':F' . $b, $r['qty'], array('rata' => 'center', 'format' => $qtyFmt, 'bingkai' => true));
            $tulis('G' . $b, $r['unit_cost'], array('rata' => 'center', 'format' => $unit, 'bingkai' => true));
            $tulis('H' . $b, round($r['extended'], 2), array('rata' => 'right', 'format' => $uang, 'bingkai' => true));
        }

        // Baris rekap: label di A, qty di E:F, nilai di H. Baris yang di contoh
        // cetakan memang kosong (Total Dozens, Hard Tag Cost) ikut dicetak kosong.
        $rekap = $data['rekap'];
        $baris = array(
            array('Total Dozens', null, null, false),
            array('Sub Total', $data['subQty'], $data['subTotal'], true),
            array('Hard Tag Cost', null, null, false),
            array('Gross Invoice Sub total', null, $data['subTotal'], true),
            array('Discount', null, $rekap['discount'] ? -$rekap['discount'] : null, false),
        );
        foreach (array('dp' => 'Down Payment', 'dp_cbd' => 'DP/CBD from Invoice', 'retur' => 'Return') as $k => $label) {
            if (abs($rekap[$k]) >= 0.005) {
                $baris[] = array($label, null, -$rekap[$k], false);
            }
        }
        if (abs($rekap['vat']) >= 0.005) {
            $baris[] = array('VAT (' . (0 + $data['vatPersen']) . '%)', null, $rekap['vat'], false);
        }
        $baris[] = array('Net Invoice Total', null, $rekap['grand'], true);

        foreach ($baris as $r) {
            $b++;
            // Label memakai dua kolom (A:B) - kolom Color Code terlalu sempit.
            $tulis('A' . $b . ':B' . $b, $r[0], array('bingkai' => true));
            $tulis('C' . $b . ':D' . $b, null, array('bingkai' => true));
            $tulis('E' . $b . ':F' . $b, $r[1], array('rata' => 'center', 'format' => $qtyFmt, 'bingkai' => true));
            $tulis('G' . $b, null, array('bingkai' => true));
            $tulis('H' . $b, $r[2] === null ? null : round($r[2], 2),
                array('rata' => 'right', 'format' => $uang, 'bingkai' => true, 'tebal' => $r[3]));
            // Baris bertanda (Sub Total, Gross, Net) diberi latar abu supaya angka
            // yang menentukan langsung ketemu waktu digulir.
            if ($r[3]) {
                $s->getStyle('A' . $b . ':H' . $b)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB($ABU);
                $s->getStyle('A' . $b . ':B' . $b)->getFont()->setBold(true);
            }
        }

        // ---------------- Penutup ----------------
        $b += 2;
        $tulis('A' . $b . ':H' . $b, 'Invoice Notes :');
        $b++;
        $tulis('A' . $b . ':H' . $b, (string) $inv['invoice_notes'], array('lipat' => true));
        $garisBawah($b);

        $b += 2;
        $tulis('A' . $b . ':H' . $b, 'Manufacturer Information');
        $b++;
        $tulis('A' . $b . ':D' . $b, (string) $inv['manufacturer_nama'], array('rata' => 'center', 'lipat' => true));
        $tulis('E' . $b . ':H' . $b, preg_replace('/\s*(\r\n|\r|\n)\s*/', ' , ', trim((string) $inv['manufacturer_alamat'])),
            array('lipat' => true));
        $s->getStyle('A' . $b . ':H' . $b)->getBorders()->getTop()->setBorderStyle($TIPIS);
        $s->getStyle('A' . $b . ':H' . $b)->getBorders()->getBottom()->setBorderStyle($TIPIS);
        $s->getStyle('E' . $b)->getBorders()->getLeft()->setBorderStyle($TIPIS);
        $s->getRowDimension($b)->setRowHeight(28);

        $b += 2;
        $tulis('A' . $b . ':H' . $b, 'I hereby certify that all information provided is true and correct');
        $b++;
        $tulis('A' . $b . ':D' . $b, "Prepare's Name");
        $b += 2;
        $tulis('F' . $b, 'REFF');
        $tulis('G' . $b . ':H' . $b, (string) $inv['reference']);
        $b += 3;
        $tulis('B' . $b . ':C' . $b, $data['penanda']['nama'], array('rata' => 'center'));
        $b++;
        $tulis('B' . $b . ':C' . $b, $data['penanda']['jabatan'], array('rata' => 'center'));

        $s->getStyle('A1:H' . ($b + 1))->getBorders()->getOutline()->setBorderStyle($TIPIS);

        $atur = $s->getPageSetup();
        $atur->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
        $atur->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $atur->setFitToWidth(1);
        $atur->setFitToHeight(0);
        // Siap cetak tanpa diatur ulang: marjin tipis, isi ditengahkan, dan
        // seluruh dokumen ditandai sebagai area cetak.
        $atur->setHorizontalCentered(true);
        $s->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);
        $atur->setPrintArea('A1:H' . ($b + 1));
        $s->getSheetView()->setZoomScale(90);

        $penulis = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
        return response()->streamDownload(function () use ($penulis) {
            $penulis->save('php://output');
        }, $this->namaBerkasExport($data) . '.xlsx', array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ));
    }

    /** Angka tersimpan untuk kotak isian: 0 jadi kosong, tanpa nol di belakang koma. */
    private function angkaIsi($nilai)
    {
        $n = (float) $nilai;
        if (abs($n) < 0.0000001) {
            return '';
        }
        return rtrim(rtrim(number_format($n, 4, '.', ''), '0'), '.');
    }

    /**
     * Baris SJ yang tersimpan di tabel detail EXIM, dalam bentuk yang sama
     * dengan keluaran daftarSj() - dipakai layar Edit Local & Export, jadi
     * JavaScript-nya tidak perlu dibedakan.
     */
    /**
     * Baris SO (WS) yang tersimpan - dipakai layar Edit.
     *
     * Bentuknya disamakan dengan hasil wsGarment() supaya layar tidak perlu
     * membedakan baris yang baru dipilih dengan baris yang dibaca dari
     * database.
     */
    private function barisSoTersimpan($idBook)
    {
        if (!$this->tabelAda(self::TABEL_SO)) {
            return array();
        }

        $baris = array();
        foreach ($this->koneksiAr()->select(
            "SELECT ws, so_number, so_date, styleno, product_group, product_item, color,
                    curr, uom, qty_so, qty, unit_price, disc, total_price, id_so, id_so_det
               FROM " . self::TABEL_SO . "
              WHERE id_book_invoice = ?
           ORDER BY urutan_summary, id",
            array($idBook)
        ) as $r) {
            $r = (array) $r;
            $baris[] = array(
                'asal'          => 'WS',
                'ws'            => $r['ws'],
                'no_so'         => $r['so_number'],
                'so_date'       => $r['so_date'],
                'styleno'       => $r['styleno'],
                'product_group' => $r['product_group'],
                'product_item'  => $r['product_item'],
                'color'         => $r['color'],
                'size'          => '',
                'curr'          => $r['curr'],
                'uom'           => $r['uom'],
                'qty_so'        => (float) $r['qty_so'],
                'qty'           => (float) $r['qty'],
                'unit_price'    => (float) $r['unit_price'],
                // Invoice Local mengetik diskon per baris SO; Export menaruhnya
                // di baris Invoice Summary, jadi di sana nilainya 0.
                'disc'          => (float) $r['disc'],
                'total_price'   => (float) $r['total_price'],
                'id_so'         => $r['id_so'],
                'id_so_det'     => (string) $r['id_so_det'],
                // Kolom yang cuma ada di baris SJ - dikosongkan, bukan dikarang.
                'sj'            => '',
                'bppbdate'      => null,
                'shipping_number' => '',
                'id_bppb'       => null,
                'id_baris'      => (string) $r['id_so_det'],
                'tipe_sj'       => 'WS',
                'harga_manual'  => 0,
            );
        }
        return $baris;
    }

    private function barisSjTersimpan($idBook)
    {
        $baris = array();
        foreach ($this->koneksiAr()->select(
            "SELECT asal, id_bppb, id_baris, id_so, so_number, bppb_number, sj_date,
                    shipp_number, ws, styleno, product_group, product_item, color, size,
                    curr, uom, qty, unit_price, disc, total_price
               FROM " . self::TABEL_DET . "
              WHERE id_book_invoice = ?
              ORDER BY id",
            array((int) $idBook)
        ) as $r) {
            $r = (array) $r;
            $baris[] = array(
                'asal'            => $r['asal'],
                'id_bppb'         => $r['id_bppb'],
                'id_baris'        => $r['id_baris'],
                'id_so'           => $r['id_so'],
                'no_so'           => $r['so_number'],
                'sj'              => $r['bppb_number'],
                'bppbdate'        => $r['sj_date'],
                'shipping_number' => $r['shipp_number'],
                'ws'              => $r['ws'],
                'styleno'         => $r['styleno'],
                'product_group'   => $r['product_group'],
                'product_item'    => $r['product_item'],
                'color'           => $r['color'],
                'size'            => $r['size'],
                'curr'            => $r['curr'],
                'uom'             => $r['uom'],
                'qty'             => $r['qty'],
                'unit_price'      => $r['unit_price'],
                'total_price'     => $r['total_price'],
                'disc'            => (float) $r['disc'],
                // Tipe SJ dibaca lagi dari nomor internalnya, supaya layar Edit
                // tahu baris mana yang harganya masih boleh diubah.
                'tipe_sj'         => $this->tipeSj($r['shipp_number']),
                'harga_manual'    => $this->hargaManual($r['shipp_number']) ? 1 : 0,
            );
        }
        return $baris;
    }

    /** Isian angka dari layar: kosong = 0, bukan angka / minus = null. */
    private function angkaIsian($nilai)
    {
        if (!is_scalar($nilai)) {
            return null;
        }
        $nilai = trim((string) $nilai);
        if ($nilai === '') {
            return 0.0;
        }
        if (!is_numeric($nilai) || (float) $nilai < 0) {
            return null;
        }
        return (float) $nilai;
    }
    /**
     * Header invoice EXIM yang boleh diubah.
     *
     * Yang boleh diedit hanya yang statusnya masih DRAFT dan yang memang dibuat
     * lewat menu ini (ada barisnya di tabel detail EXIM). Invoice yang sudah
     * diproses di AR sengaja ditolak di sini, bukan cuma disembunyikan
     * tombolnya di layar.
     */
    private function headerDraft($id, $shipp = null)
    {
        $id = (int) $id;
        if ($id < 1 || !$this->tabelAda(self::TABEL_DET)) {
            return null;
        }

        // $shipp diisi oleh Edit & Update Local: invoice Export punya tabel
        // tambahan (shipment, summary) yang tidak dikenal form Local, jadi kalau
        // sampai dibuka & disimpan lewat form Local datanya jadi tidak utuh.
        // Invoice Export boleh dibuat dari WS saja - SJ-nya menyusul. Jadi
        // yang dicari "punya baris SJ ATAU baris SO"; kalau cuma baris SJ,
        // invoice yang belum ada SJ-nya tidak bisa dibuka sama sekali (404).
        $sql = "SELECT b.id, b.no_invoice, b.id_customer, b.id_customer_ship, b.shipp,
                       b.id_type, b.status, b.doc_type, b.doc_number, b.profit_center, b.curr
                  FROM tbl_book_invoice b
                 WHERE b.id = ?
                   AND UPPER(b.status) = 'DRAFT'
                   AND (EXISTS (SELECT 1 FROM " . self::TABEL_DET . " d WHERE d.id_book_invoice = b.id)"
             . ($this->tabelAda(self::TABEL_SO)
                 ? " OR EXISTS (SELECT 1 FROM " . self::TABEL_SO . " s WHERE s.id_book_invoice = b.id)"
                 : '')
             . ")";
        $bind = array($id);
        if ($shipp !== null) {
            $sql .= " AND b.shipp = ?";
            $bind[] = $shipp;
        }
        $h = $this->koneksiAr()->select($sql . " LIMIT 1", $bind);

        return $h ? (array) $h[0] : null;
    }

    /**
     * Halaman Edit Invoice Local.
     *
     * Memakai layar yang sama dengan Create, cuma isinya sudah terisi. Baris
     * SJ-nya dibaca dari tabel detail EXIM (bukan dari sumbernya lagi) supaya
     * yang tampil persis seperti yang tersimpan; bentuk arraynya disamakan
     * dengan keluaran daftarSj() agar JavaScript-nya tidak perlu dibedakan.
     */
    public function editLocal($id)
    {
        $inv = $this->headerDraft($id, self::SHIPP_LOCAL);
        if (!$inv) {
            abort(404);
        }

        $baris = $this->barisSjTersimpan($id);

        $p = $this->koneksiAr()->select(
            "SELECT dp, dp_cbd, retur, vat_persen"
            . ($this->kolomAda(self::TABEL_POT, 'tgl_invoice') ? ", tgl_invoice" : "") . "
               FROM " . self::TABEL_POT . "
              WHERE id_book_invoice = ? LIMIT 1",
            array((int) $id)
        );
        $pot = $p ? (array) $p[0] : array('dp' => 0, 'dp_cbd' => 0, 'retur' => 0, 'vat_persen' => 0);

        return view('export-import.invoice.local.form', [
            "page"           => "dashboard-export-import",
            "subPageGroup"   => "invoice-export-import",
            "subPage"        => "invoice-local",
            "containerFluid" => true,
            "customer"       => $this->daftarCustomer(),
            "profitCenter"   => $this->daftarProfitCenter(),
            "tipe"           => DB::connection(self::KONEKSI_AR)->select("SELECT id, type FROM tbl_type ORDER BY id"),
            "buyer"          => $this->daftarBuyer(),
            "docType"        => self::DOC_TYPE_LOCAL,
            "shipp"          => self::SHIPP_LOCAL,
            // Dipakai kolom Curr di Detail SJ: SJ tanpa SO tidak punya mata uang
            // di database, jadi dipilih sendiri di layar.
            "mataUangSj"     => self::MATA_UANG_SJ,
            "noInvoice"      => $inv['no_invoice'],
            // Penanda mode edit - dipakai layar & JavaScript-nya.
            "ubah"           => $inv,
            "ubahBaris"      => $baris,
            "ubahBarisSo"    => $this->barisSoTersimpan($id),
            "ubahPot"        => $pot,
        ]);
    }

    /**
     * Simpan hasil Edit.
     *
     * Nomor invoice dan profit center TIDAK ikut berubah: keduanya sudah
     * menyatu (0211/L/NAG/0926), jadi mengubah profit center berarti nomornya
     * harus ikut diganti. Baris SJ-nya boleh diganti seluruhnya - yang lama
     * dihapus lalu yang baru ditulis, semuanya di dalam satu transaksi.
     */
    public function perbaruiLocal(Request $request)
    {
        $inv = $this->headerDraft($request->input('id'), self::SHIPP_LOCAL);
        if (!$inv) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice can no longer be edited. It may have been processed or removed.'), 409);
        }

        $db = $this->koneksiAr();

        $s = $this->siapkanSimpan($request, $inv['profit_center'], $inv['id']);
        if (isset($s['gagal'])) {
            return $s['gagal'];
        }

        $user = (string) (auth()->user()->username ?? '');
        $now  = now()->format('Y-m-d H:i:s');
        $idBook = (int) $inv['id'];
        $noInvoice = (string) $inv['no_invoice'];

        try {
            $db->transaction(function () use ($db, $s, $request, $user, $now, $idBook, $noInvoice) {
                // Statusnya dikunci & diperiksa lagi di dalam transaksi supaya
                // tidak menimpa invoice yang baru saja diproses orang lain.
                $kunci = $db->select(
                    "SELECT status FROM tbl_book_invoice WHERE id = ? FOR UPDATE",
                    array($idBook)
                );
                if (!$kunci || strtoupper((string) $kunci[0]->status) !== 'DRAFT') {
                    throw new \RuntimeException('bukan_draft');
                }

                $db->table('tbl_book_invoice')->where('id', $idBook)->update(array(
                    'id_customer'      => $s['id_customer'],
                    'id_customer_ship' => $s['id_customer_ship'],
                    'id_type'          => (int) $request->input('id_type'),
                    'value'            => round($s['grand'], 2),
                    'curr'             => $this->currInvoice($s),
                    'doc_type'         => $s['doc_type'],
                    'doc_number'       => (string) $request->input('doc_number'),
                ));

                // Baris lama dibuang seluruhnya lalu ditulis ulang - lebih aman
                // daripada mencocokkan satu per satu, dan SJ boleh berubah total.
                $db->table(self::TABEL_DET)->where('id_book_invoice', $idBook)->delete();
                $db->table(self::TABEL_POT)->where('id_book_invoice', $idBook)->delete();
                if ($this->tabelAda(self::TABEL_SO)) {
                    $db->table(self::TABEL_SO)->where('id_book_invoice', $idBook)->delete();
                }

                // Baris SJ boleh tidak ada - invoice yang dibuat dari WS saja.
                $det = $this->barisDetail($idBook, $noInvoice, $s, $user, $now);
                if ($det) { $db->table(self::TABEL_DET)->insert($det); }
                $so = $this->barisSo($idBook, $noInvoice, $s, $user, $now);
                if ($so) { $db->table(self::TABEL_SO)->insert($so); }
                $db->table(self::TABEL_POT)->insert($this->barisPotongan($idBook, $noInvoice, $s, $user, $now));

                // Barisnya baru saja ditulis ulang, jadi keadaan sebelumnya hanya
                // tersimpan di riwayat - potretnya diambil sesudah semuanya masuk.
                // Nomor invoicenya ikut ditulis ke SJ-nya di bppb.
                $this->selaraskanInvno($db, $idBook, $noInvoice);
                $this->catatRiwayat($db, $idBook, $noInvoice, self::SHIPP_LOCAL, 'UPDATE', $user, $now);
            });
        } catch (\RuntimeException $e) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice is no longer a draft, so it cannot be changed.'), 409);
        } catch (\Throwable $e) {
            Log::error('Invoice EXIM perbarui gagal - ' . $e->getMessage());
            return response()->json(array('status' => false, 'pesan' => 'Update failed, please try again.'), 500);
        }

        // SJ knitting ada di database lain, jadi ditandai di luar transaksi -
        // sesudah invoicenya benar-benar tersimpan.
        $this->tandaiInvnoNak($noInvoice, $this->idSjNak($idBook));

        return response()->json(array(
            'status'     => true,
            'no_invoice' => $noInvoice,
            'pesan'      => 'Invoice updated.',
        ));
    }

    /**
     * Batalkan invoice EXIM.
     *
     * Statusnya diubah jadi CANCEL, barisnya sengaja TIDAK dihapus supaya
     * riwayatnya tetap terbaca. Karena penyaringan SJ mengabaikan booking
     * berstatus CANCEL, semua FG/OUT-nya otomatis bisa dipakai lagi.
     */
    public function batalLocal(Request $request)
    {
        return $this->batal($request->input('id'), self::SHIPP_LOCAL);
    }

    /** Dipakai batalLocal() & batalExport() - hanya jenis yang cocok yang bisa dibatalkan. */
    private function batal($id, $shipp)
    {
        $inv = $this->headerDraft($id, $shipp);
        if (!$inv) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice can no longer be cancelled. It may have been processed or already cancelled.'), 409);
        }

        $db = $this->koneksiAr();
        $idBook    = (int) $inv['id'];
        $noInvoice = (string) $inv['no_invoice'];
        $user      = (string) (auth()->user()->username ?? '');
        $now       = now()->format('Y-m-d H:i:s');

        try {
            $db->transaction(function () use ($db, $idBook, $noInvoice, $shipp, $user, $now) {
                $kunci = $db->select(
                    "SELECT status FROM tbl_book_invoice WHERE id = ? FOR UPDATE",
                    array($idBook)
                );
                if (!$kunci || strtoupper((string) $kunci[0]->status) !== 'DRAFT') {
                    throw new \RuntimeException('bukan_draft');
                }
                $db->table('tbl_book_invoice')->where('id', $idBook)->update(array('status' => 'CANCEL'));
                // Invoicenya batal - tandanya di SJ ikut dilepas supaya SJ-nya
                // tidak terbaca masih masuk invoice ini.
                $this->lepasInvno($db, $noInvoice);
                $this->catatRiwayat($db, $idBook, $noInvoice, $shipp, 'CANCEL', $user, $now);
            });
        } catch (\RuntimeException $e) {
            return response()->json(array('status' => false,
                'pesan' => 'This invoice is no longer a draft, so it cannot be cancelled.'), 409);
        } catch (\Throwable $e) {
            Log::error('Invoice EXIM batal gagal - ' . $e->getMessage());
            return response()->json(array('status' => false, 'pesan' => 'Cancel failed, please try again.'), 500);
        }

        // Invoicenya batal - tandanya di SJ knitting ikut dilepas.
        $this->tandaiInvnoNak((string) $inv['no_invoice'], array());

        return response()->json(array(
            'status'     => true,
            'no_invoice' => (string) $inv['no_invoice'],
            'pesan'      => 'Invoice cancelled. Its SJ rows are available again.',
        ));
    }

    // ======================================================================
    //  Riwayat - potret invoice tiap kali disimpan
    // ======================================================================

    /**
     * Nama kolom nomor invoice di SJ knitting (official_out_h).
     *
     * Di form knitting isiannya bernama "No Invoice". Kalau di database nama
     * kolomnya ternyata lain, cukup ganti di sini - penandaannya dilewati
     * (dengan catatan di log) selama kolomnya tidak ketemu, jadi salah nama
     * tidak akan menggagalkan penyimpanan invoice.
     */
    const KOLOM_INVNO_NAK = 'no_invoice';

    /**
     * Tandai SJ knitting dengan nomor invoicenya.
     *
     * Dipanggil SESUDAH invoicenya tersimpan, bukan di dalam transaksi: SJ
     * knitting ada di database lain, jadi tidak bisa ikut dibatalkan bersama.
     * Karena itu kegagalannya cuma dicatat - invoicenya sendiri sudah sah.
     *
     * Barisnya dibersihkan dulu lalu dipasang lagi, sama seperti garment: waktu
     * invoice di-update SJ-nya bisa berganti, dan yang dilepas tidak boleh tetap
     * membawa nomor invoice ini.
     *
     * @param array $idSj id official_out_h; kosong = cuma membersihkan
     */
    private function tandaiInvnoNak($noInvoice, array $idSj)
    {
        $no = trim((string) $noInvoice);
        if ($no === '') {
            return;
        }

        try {
            $nak = DB::connection(self::KONEKSI_NAK);
            $kolom = self::KOLOM_INVNO_NAK;
            $ada = $nak->select(
                "SELECT 1 FROM information_schema.columns
                  WHERE table_name = 'official_out_h' AND column_name = ? LIMIT 1",
                array($kolom)
            );
            if (!$ada) {
                Log::warning('Invoice EXIM: kolom official_out_h.' . $kolom . ' tidak ada,'
                    . ' nomor invoice tidak ditandai di SJ knitting.');
                return;
            }

            $nak->update("UPDATE official_out_h SET $kolom = NULL WHERE $kolom = ?", array($no));
            if ($idSj) {
                $isi = implode(',', array_map('intval', $idSj));
                $nak->update("UPDATE official_out_h SET $kolom = ? WHERE id IN ($isi)", array($no));
            }
        } catch (\Throwable $e) {
            // Invoicenya sudah tersimpan - gagal menandai tidak boleh membatalkannya.
            Log::warning('Invoice EXIM: tanda nomor invoice di SJ knitting gagal ('
                . $no . ') - ' . $e->getMessage());
        }
    }

    /** id SJ knitting milik satu invoice - dibaca dari baris yang tersimpan. */
    private function idSjNak($idBook)
    {
        if (!$this->tabelAda(self::TABEL_DET)) {
            return array();
        }
        $out = array();
        foreach ($this->koneksiAr()->select(
            "SELECT DISTINCT id_bppb FROM " . self::TABEL_DET . "
              WHERE id_book_invoice = ? AND UPPER(IFNULL(asal, '')) = 'NAK'",
            array((int) $idBook)
        ) as $r) {
            $id = trim((string) $r->id_bppb);
            if ($id !== '' && ctype_digit($id)) { $out[] = $id; }
        }
        return $out;
    }
    /**
     * Tandai SJ garment dengan nomor invoicenya di tabel asalnya (bppb.invno).
     *
     * Supaya di aplikasi sebelah langsung terlihat SJ itu masuk invoice mana,
     * tanpa harus menelusuri balik lewat tbl_book_invoice_exim_det.
     *
     * Baris yang tadinya bertanda invoice ini dibersihkan DULU, baru baris yang
     * sekarang ditandai: waktu invoice di-update SJ-nya bisa berganti, dan SJ
     * yang dilepas tidak boleh tetap membawa nomor invoice ini.
     *
     * Garment dicocokkan lewat id (bppb.id = id_bppb). SJ knitting juga punya
     * barisnya di bppb, tapi id_bppb untuk baris NAK itu official_out_h.id -
     * bukan bppb.id - jadi yang dicocokkan NOMOR SJ-nya. Nomornya bisa tersimpan
     * di bppbno atau bppbno_int, jadi dua-duanya dicoba; nomor SJ cukup khas
     * sehingga tidak mungkin mengenai baris lain.
     *
     * SJ knitting ditandai di dua tempat: di sini (bppb) dan di official_out_h
     * database knitting - lihat tandaiInvnoNak().
     */
    private function selaraskanInvno($db, $idBook, $noInvoice)
    {
        if (!$this->kolomAda('bppb', 'invno')) {
            return;
        }
        $this->lepasInvno($db, $noInvoice);
        $db->update(
            "UPDATE bppb SET invno = ?
              WHERE id IN (SELECT id_bppb FROM " . self::TABEL_DET . "
                            WHERE id_book_invoice = ? AND UPPER(IFNULL(asal, '')) = 'NAG')",
            array((string) $noInvoice, (int) $idBook)
        );
        $db->update(
            "UPDATE bppb SET invno = ?
              WHERE bppbno IN (SELECT bppb_number FROM " . self::TABEL_DET . "
                                WHERE id_book_invoice = ? AND UPPER(IFNULL(asal, '')) = 'NAK'
                                  AND IFNULL(bppb_number, '') <> '')
                 OR bppbno_int IN (SELECT bppb_number FROM " . self::TABEL_DET . "
                                    WHERE id_book_invoice = ? AND UPPER(IFNULL(asal, '')) = 'NAK'
                                      AND IFNULL(bppb_number, '') <> '')",
            array((string) $noInvoice, (int) $idBook, (int) $idBook)
        );
    }

    /** Lepaskan tanda invoice ini dari SJ-nya - dipakai waktu update & batal. */
    private function lepasInvno($db, $noInvoice)
    {
        if (!$this->kolomAda('bppb', 'invno')) {
            return;
        }
        $db->update(
            "UPDATE bppb SET invno = ? WHERE invno = ?",
            array($this->kosongInvno($db), (string) $noInvoice)
        );
    }

    /**
     * Nilai "tidak ada invoice" untuk bppb.invno.
     *
     * NULL kalau kolomnya memang boleh kosong; kalau tidak, string kosong -
     * memaksa NULL ke kolom NOT NULL akan menggagalkan seluruh penyimpanan.
     */
    private function kosongInvno($db)
    {
        $r = $db->select(
            "SELECT IS_NULLABLE FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'bppb'
                AND COLUMN_NAME = 'invno' LIMIT 1"
        );
        return ($r && strtoupper((string) $r[0]->IS_NULLABLE) === 'YES') ? null : '';
    }

    /** Tabel riwayat - dibuat lewat migrations/20260929_invoice_exim_riwayat.sql */
    const TABEL_LOG = 'tbl_book_invoice_exim_log';

    /** Rekap uang yang ikut dipotret, beserta labelnya. */
    const UANG_RIWAYAT = array(
        'total'           => 'Total',
        'discount'        => 'Discount',
        'dp'              => 'DP',
        'dp_cbd'          => 'DP CBD',
        'retur'           => 'Retur',
        'twot'            => 'TWOT',
        'vat_persen'      => 'VAT (%)',
        'vat'             => 'VAT',
        'grand_total'     => 'Grand Total',
        'total_fob'       => 'Total FOB',
        'grand_total_fob' => 'Grand Total FOB',
    );

    /**
     * Catat potret invoice sesudah barisnya ditulis.
     *
     * Dipanggil dari dalam transaksi simpan/update/batal, sesudah semua baris
     * masuk. Yang dipotret isi tabelnya - bukan kiriman browser - supaya yang
     * tercatat memang keadaan yang benar-benar tersimpan.
     *
     * Gagal mencatat riwayat TIDAK boleh menggagalkan invoice-nya: ini catatan,
     * bukan bagian dari dokumennya. Jadi kesalahannya ditulis ke log lalu
     * dilewati - termasuk kalau migrasinya belum dijalankan.
     */
    private function catatRiwayat($db, $idBook, $noInvoice, $shipp, $aksi, $user, $now)
    {
        if (!$this->tabelAda(self::TABEL_LOG)) {
            return;
        }
        try {
            $isi = $this->potretInvoice($db, $idBook, $shipp);
            // Nomor revisinya diambil di sini, di dalam transaksi yang sudah
            // memegang kunci baris invoice-nya - jadi urutannya tidak berebut.
            $urut = $db->select(
                "SELECT COALESCE(MAX(revisi) + 1, 0) AS n FROM " . self::TABEL_LOG
                . " WHERE id_book_invoice = ?",
                array((int) $idBook)
            );
            $db->table(self::TABEL_LOG)->insert(array(
                'id_book_invoice' => (int) $idBook,
                'no_invoice'      => (string) $noInvoice,
                'shipp'           => (string) $shipp,
                'revisi'          => $urut ? (int) $urut[0]->n : 0,
                'aksi'            => $aksi,
                'ringkas'         => $this->ringkasRiwayat($isi),
                'isi'             => json_encode($isi),
                'created_by'      => (string) $user,
                'created_at'      => $now,
            ));
        } catch (\Throwable $e) {
            Log::warning('Invoice EXIM riwayat tidak tercatat (id ' . $idBook . ') - ' . $e->getMessage());
        }
    }

    /**
     * Potret invoice yang sudah tersimpan, dalam bentuk yang siap dibaca orang.
     *
     * Bentuknya sengaja label + nilai, bukan salinan mentah tabelnya: potret
     * lama tetap terbaca apa adanya walau nanti tabelnya bertambah kolom, dan
     * membandingkan dua revisi jadi urusan mencocokkan label - bukan menebak
     * kolom mana yang sepadan.
     *
     * Tiap baris punya `kunci` supaya baris yang sama bisa dicocokkan antar
     * revisi; tanpa itu yang bisa dilaporkan cuma "jumlah barisnya berubah".
     */
    private function potretInvoice($db, $idBook, $shipp)
    {
        $idBook = (int) $idBook;
        $export = $shipp === self::SHIPP_EXPORT;

        $b = $this->satuBaris($db,
            "SELECT b.status, b.curr, b.doc_type, b.doc_number, b.profit_center,
                    UPPER(c.Supplier)  AS customer,
                    UPPER(cs.Supplier) AS customer_ship,
                    t.type AS tipe
               FROM tbl_book_invoice b
               LEFT JOIN mastersupplier c  ON c.Id_Supplier  = b.id_customer
               LEFT JOIN mastersupplier cs ON cs.Id_Supplier = b.id_customer_ship
               LEFT JOIN tbl_type t        ON t.id           = b.id_type
              WHERE b.id = ? LIMIT 1",
            array($idBook)
        );
        $pot = $this->satuBaris($db,
            "SELECT * FROM " . self::TABEL_POT . " WHERE id_book_invoice = ? LIMIT 1", array($idBook));

        $header = array(
            array('Status', $this->teksRiwayat($b, 'status')),
            array('Profit Center', $this->teksRiwayat($b, 'profit_center')),
        );
        $grup = array();

        if ($export) {
            $h = $this->satuBaris($db,
                "SELECT * FROM " . self::TABEL_EXP_H . " WHERE id_book_invoice = ? LIMIT 1", array($idBook));
            $header[] = array('Invoice Date', $this->tanggalTampil($this->teksRiwayat($h, 'tgl_invoice')));
            $header[] = array('Invoice No 2', $this->teksRiwayat($h, 'no_invoice_2'));
            $header[] = array('Shipper', $this->teksRiwayat($h, 'shipper_nama'));
            $header[] = array('Seller', $this->teksRiwayat($h, 'seller_nama'));
            $header[] = array('Purchaser', $this->teksRiwayat($h, 'purchaser_nama'));
            $header[] = array('Receiver', $this->teksRiwayat($h, 'receiver_nama'));
            $header[] = array('Invoice Notes', $this->teksRiwayat($h, 'invoice_notes'));
        } else {
            $header[] = array('Invoice Date', $this->tanggalTampil($this->teksRiwayat($pot, 'tgl_invoice')));
            $header[] = array('Billed To', $this->teksRiwayat($b, 'customer'));
            $header[] = array('Shipped To', $this->teksRiwayat($b, 'customer_ship'));
            $header[] = array('Type', $this->teksRiwayat($b, 'tipe'));
            $header[] = array('Doc Type', $this->teksRiwayat($b, 'doc_type'));
            $header[] = array('Doc Number', $this->teksRiwayat($b, 'doc_number'));
        }
        $header[] = array('Currency', $this->teksRiwayat($b, 'curr'));

        if ($export) {
            $grup[] = $this->grupRiwayat('Shipment Details',
                $db->select("SELECT * FROM " . self::TABEL_EXP_SHIP
                    . " WHERE id_book_invoice = ? ORDER BY urutan, id", array($idBook)),
                function ($r) { return 'kirim|' . $r['urutan']; },
                function ($r) { return 'Row ' . $r['urutan']; },
                function ($r) {
                    $isi = array();
                    foreach (self::KOLOM_KIRIM as $k => $def) {
                        $isi[] = array($def[0], $this->teksRiwayat($r, $k));
                    }
                    foreach (self::ANGKA_KIRIM as $k => $label) {
                        $isi[] = array($label, $this->angkaRiwayat($r, $k, 2));
                    }
                    return $isi;
                });

            $grup[] = $this->grupRiwayat('Invoice Summary',
                $db->select("SELECT * FROM " . self::TABEL_EXP_DET
                    . " WHERE id_book_invoice = ? ORDER BY urutan, id", array($idBook)),
                function ($r) { return 'sum|' . $r['urutan'] . '|' . $r['color_name']; },
                function ($r) {
                    return $this->labelRiwayat(array($r['color_code'], $r['color_name']));
                },
                function ($r) {
                    return array(
                        array('Total Pieces', $this->angkaRiwayat($r, 'total_pieces', 0)),
                        array('Qty Invoiced', $this->angkaRiwayat($r, 'qty_invoiced', 0)),
                        array('Unit Cost CM', $this->angkaRiwayat($r, 'unit_cost_cm', 4)),
                        array('Unit Cost FOB', $this->angkaRiwayat($r, 'unit_cost_fob', 4)),
                        array('Disc (%)', $this->angkaRiwayat($r, 'disc', 2)),
                        array('Total CM', $this->angkaRiwayat($r, 'total_cm', 2)),
                        array('Total FOB', $this->angkaRiwayat($r, 'total_fob', 2)),
                    );
                });
        }

        $grup[] = $this->grupRiwayat('Detail SJ',
            $db->select("SELECT * FROM " . self::TABEL_DET
                . " WHERE id_book_invoice = ? ORDER BY id", array($idBook)),
            function ($r) { return 'sj|' . $r['id_baris']; },
            function ($r) {
                return $this->labelRiwayat(array($r['bppb_number'], $r['color'], $r['size']));
            },
            function ($r) {
                return array(
                    array('SJ Date', $this->tanggalTampil($this->teksRiwayat($r, 'sj_date'))),
                    array('WS#', $this->teksRiwayat($r, 'ws')),
                    array('SO Number', $this->teksRiwayat($r, 'so_number')),
                    array('Product Item', $this->teksRiwayat($r, 'product_item')),
                    array('Qty', $this->angkaRiwayat($r, 'qty', 2)),
                    array('Unit Price', $this->angkaRiwayat($r, 'unit_price', 4)),
                    array('Disc (%)', $this->angkaRiwayat($r, 'disc', 2)),
                    array('Total Price', $this->angkaRiwayat($r, 'total_price', 2)),
                );
            });

        if ($this->tabelAda(self::TABEL_SO)) {
            $grup[] = $this->grupRiwayat('Detail SO',
                $db->select("SELECT * FROM " . self::TABEL_SO
                    . " WHERE id_book_invoice = ? ORDER BY id", array($idBook)),
                function ($r) { return 'so|' . $r['id_so_det']; },
                function ($r) {
                    return $this->labelRiwayat(array($r['ws'], $r['color']));
                },
                function ($r) {
                    return array(
                        array('SO Number', $this->teksRiwayat($r, 'so_number')),
                        array('Product Item', $this->teksRiwayat($r, 'product_item')),
                        array('Qty SO', $this->angkaRiwayat($r, 'qty_so', 2)),
                        array('Qty', $this->angkaRiwayat($r, 'qty', 2)),
                        array('Unit Price', $this->angkaRiwayat($r, 'unit_price', 4)),
                        array('Disc (%)', $this->angkaRiwayat($r, 'disc', 2)),
                        array('Total Price', $this->angkaRiwayat($r, 'total_price', 2)),
                    );
                });
        }

        $uang = array();
        foreach (self::UANG_RIWAYAT as $kolom => $label) {
            if (!array_key_exists($kolom, $pot)) { continue; }
            $uang[] = array($label, $this->angkaRiwayat($pot, $kolom, $kolom === 'vat_persen' ? 2 : 2));
        }

        return array('header' => $header, 'grup' => $grup, 'uang' => $uang);
    }

    /** Pemisah antar bagian label baris - satu tempat supaya kunci & label sejalan. */
    const TITIK = '·';

    /** "SJ-001 · BLACK · M" - bagian yang kosong dilewati, bukan jadi titik kosong. */
    private function labelRiwayat(array $bagian)
    {
        $isi = array();
        foreach ($bagian as $v) {
            $v = trim((string) $v);
            if ($v !== '' && $v !== '-') { $isi[] = $v; }
        }
        return implode(' ' . self::TITIK . ' ', $isi);
    }

    /** Satu kelompok baris dalam potret. */
    private function grupRiwayat($nama, $rows, $kunci, $label, $nilai)
    {
        $baris = array();
        foreach ($rows as $r) {
            $r = (array) $r;
            $baris[] = array(
                'kunci' => (string) $kunci($r),
                'label' => (string) $label($r),
                'nilai' => $nilai($r),
            );
        }
        return array('nama' => $nama, 'baris' => $baris);
    }

    /** Satu baris hasil query sebagai array - array() kalau tidak ada. */
    private function satuBaris($db, $sql, array $ikat)
    {
        $r = $db->select($sql, $ikat);
        return $r ? (array) $r[0] : array();
    }

    /** Teks apa adanya, kolom yang tidak ada dianggap kosong. */
    private function teksRiwayat($r, $kolom)
    {
        return isset($r[$kolom]) && $r[$kolom] !== null ? trim((string) $r[$kolom]) : '';
    }

    /**
     * Angka untuk potret, jumlah desimalnya dipatok.
     *
     * Kalau tidak dipatok, dua revisi yang angkanya sama bisa terbaca "berubah"
     * cuma karena yang satu tersimpan 100 dan yang lain 100.0000.
     */
    private function angkaRiwayat($r, $kolom, $desimal = 2)
    {
        if (!isset($r[$kolom]) || $r[$kolom] === null || $r[$kolom] === '') { return ''; }
        return number_format((float) $r[$kolom], $desimal, '.', ',');
    }

    /** Sebaris ringkasan untuk daftar riwayat: jumlah baris tiap bagian & grand total. */
    private function ringkasRiwayat(array $isi)
    {
        $bagian = array();
        foreach ($isi['grup'] as $g) {
            if (!$g['baris']) { continue; }
            $bagian[] = $g['nama'] . ' ' . count($g['baris']);
        }
        foreach ($isi['uang'] as $u) {
            if ($u[0] === 'Grand Total') { $bagian[] = 'Grand Total ' . $u[1]; }
        }
        return mb_substr(implode('  |  ', $bagian), 0, 255);
    }

    /**
     * Riwayat satu invoice, urut dari yang paling awal.
     *
     * Tiap revisi dikirim bersama daftar bedanya dari revisi sebelumnya, jadi
     * layarnya tidak perlu tahu bentuk potretnya - cukup menampilkan.
     */
    public function riwayat(Request $request)
    {
        $id = (int) $request->query('id');
        if ($id < 1) {
            return response()->json(array('status' => false, 'pesan' => 'Invalid invoice id.'), 422);
        }
        if (!$this->tabelAda(self::TABEL_LOG)) {
            return response()->json(array('status' => false,
                'pesan' => 'History is not available yet. Run migrations/20260929_invoice_exim_riwayat.sql first.'), 500);
        }

        $db = $this->koneksiAr();
        $rows = $db->select(
            "SELECT revisi, aksi, ringkas, isi, created_by, created_at
               FROM " . self::TABEL_LOG . "
              WHERE id_book_invoice = ?
              ORDER BY revisi ASC, id ASC",
            array($id)
        );

        $out = array();
        $sebelum = null;
        foreach ($rows as $r) {
            $isi = json_decode((string) $r->isi, true);
            if (!is_array($isi)) {
                $isi = array('header' => array(), 'grup' => array(), 'uang' => array());
            }
            $out[] = array(
                'revisi'  => (int) $r->revisi,
                'aksi'    => (string) $r->aksi,
                'judul'   => $this->judulRevisi((int) $r->revisi, (string) $r->aksi),
                'oleh'    => (string) $r->created_by,
                'waktu'   => $r->created_at ? date('d-M-Y H:i', strtotime($r->created_at)) : '',
                'ringkas' => (string) $r->ringkas,
                'isi'     => $isi,
                'beda'    => $sebelum === null ? array() : $this->bedaRiwayat($sebelum, $isi),
            );
            $sebelum = $isi;
        }

        return response()->json(array('status' => true, 'baris' => $out));
    }

    /** "Created", "Edit #1", "Cancelled". */
    private function judulRevisi($revisi, $aksi)
    {
        if ($aksi === 'CREATE') { return 'Created'; }
        if ($aksi === 'CANCEL') { return 'Cancelled'; }
        // Invoice yang sudah ada sebelum riwayat dinyalakan: edit pertamanya
        // yang tercatat di revisi 0, dan potret saat dibuatnya memang tidak ada.
        return $revisi === 0 ? 'Edited (earlier history not recorded)' : 'Edit #' . $revisi;
    }

    /**
     * Beda dua potret, sebagai daftar kalimat pendek.
     *
     * Header & rekap uang dibandingkan per label; baris dibandingkan per kunci,
     * jadi yang dilaporkan baris mana yang berubah - bukan cuma jumlahnya.
     * Label yang cuma ada di salah satu potret dilewati: itu tandanya bentuk
     * potretnya yang berbeda (mis. kolomnya belum ada waktu itu), bukan isinya
     * yang berubah.
     */
    private function bedaRiwayat(array $lama, array $baru)
    {
        $beda = array();

        foreach (array('header' => 'Header', 'uang' => 'Amounts') as $bagian => $judul) {
            $p1 = $this->petaLabel(isset($lama[$bagian]) ? $lama[$bagian] : array());
            $p2 = $this->petaLabel(isset($baru[$bagian]) ? $baru[$bagian] : array());
            foreach ($p2 as $label => $nilai) {
                if (!array_key_exists($label, $p1) || $p1[$label] === $nilai) { continue; }
                $beda[] = array('bagian' => $judul, 'jenis' => 'ubah', 'apa' => $label,
                    'dari' => $p1[$label], 'ke' => $nilai);
            }
        }

        $g1 = $this->petaGrup($lama);
        $g2 = $this->petaGrup($baru);
        foreach (array_keys($g1 + $g2) as $nama) {
            $b1 = isset($g1[$nama]) ? $g1[$nama] : array();
            $b2 = isset($g2[$nama]) ? $g2[$nama] : array();
            foreach ($b2 as $kunci => $r) {
                if (!isset($b1[$kunci])) {
                    $beda[] = array('bagian' => $nama, 'jenis' => 'tambah', 'apa' => $r['label'],
                        'dari' => '', 'ke' => $this->ringkasBaris($r));
                    continue;
                }
                $n1 = $this->petaLabel($b1[$kunci]['nilai']);
                $n2 = $this->petaLabel($r['nilai']);
                foreach ($n2 as $label => $nilai) {
                    if (!array_key_exists($label, $n1) || $n1[$label] === $nilai) { continue; }
                    $beda[] = array('bagian' => $nama, 'jenis' => 'ubah',
                        'apa' => $r['label'] . ' - ' . $label,
                        'dari' => $n1[$label], 'ke' => $nilai);
                }
            }
            foreach ($b1 as $kunci => $r) {
                if (isset($b2[$kunci])) { continue; }
                $beda[] = array('bagian' => $nama, 'jenis' => 'buang', 'apa' => $r['label'],
                    'dari' => $this->ringkasBaris($r), 'ke' => '');
            }
        }

        return $beda;
    }

    /**
     * Angka penting satu baris potret, sebagai satu kalimat pendek.
     *
     * Dipakai untuk baris yang ditambah atau dibuang: labelnya saja tidak cukup
     * - yang ingin diketahui qty & nilainya berapa, tanpa harus membuka potret
     * lengkapnya.
     */
    private function ringkasBaris(array $r)
    {
        $penting = array('Qty', 'Total Pieces', 'Total Price', 'Total CM');
        $peta = $this->petaLabel(isset($r['nilai']) ? $r['nilai'] : array());
        $isi = array();
        foreach ($penting as $label) {
            if (isset($peta[$label]) && $peta[$label] !== '') {
                $isi[] = $label . ' ' . $peta[$label];
            }
        }
        return implode(', ', $isi);
    }
    /** Daftar [label, nilai] jadi peta label => nilai. */
    private function petaLabel($pasangan)
    {
        $out = array();
        foreach ((array) $pasangan as $p) {
            if (!is_array($p) || !array_key_exists(0, $p)) { continue; }
            $out[(string) $p[0]] = array_key_exists(1, $p) ? (string) $p[1] : '';
        }
        return $out;
    }

    /** Kelompok baris potret jadi peta nama grup => (kunci => baris). */
    private function petaGrup(array $isi)
    {
        $out = array();
        foreach (isset($isi['grup']) ? (array) $isi['grup'] : array() as $g) {
            if (!isset($g['nama'])) { continue; }
            $baris = array();
            foreach (isset($g['baris']) ? (array) $g['baris'] : array() as $r) {
                if (!isset($r['kunci'])) { continue; }
                $baris[(string) $r['kunci']] = $r;
            }
            $out[(string) $g['nama']] = $baris;
        }
        return $out;
    }

    /** Baris SJ dibaca ulang dari sumbernya berdasarkan id_baris. */
    private function sjUlang($pc, array $idBaris, $abaikanInvoice = null)
    {
        $idBaris = array_values(array_unique(array_map('strval', $idBaris)));
        if (!$idBaris) {
            return array();
        }

        $semua = ($pc === 'NAG')
            ? $this->sjGarment('1900-01-01', '2999-12-31', '', $idBaris)
            : $this->sjKnitting('1900-01-01', '2999-12-31', '', $idBaris);

        $hasil = array();
        foreach ($semua as $r) {
            if (in_array((string) $r['id_baris'], $idBaris, true)) {
                $hasil[] = $r;
            }
        }

        // Disaring juga di sini, bukan cuma waktu mencari. Kalau user lain
        // sempat memakai baris yang sama sejak layar ini dibuka, jumlahnya jadi
        // berkurang dan penyimpanan ditolak - bukan diam-diam dipakai berdua.
        return $this->saringTerpakai($hasil, $pc, $abaikanInvoice);
    }

    /**
     * Buang baris SJ yang sudah dipakai booking EXIM lain.
     *
     * Status di tabel bppb / official_out_h SENGAJA belum disentuh - itu baru
     * dilakukan saat Create Invoice di AR. Jadi selama masih berupa booking,
     * penanda "sudah dipakai" hanya ada di tabel EXIM ini, dan di sinilah
     * dibacanya. Booking yang statusnya CANCEL tidak menahan apa-apa, jadi
     * SJ-nya otomatis bisa dipilih lagi.
     *
     * Yang ditanyakan ke database cuma id baris yang sedang dipegang, bukan
     * seluruh riwayat - jadi bebannya ikut besar hasil pencarian, bukan ikut
     * umur data.
     *
     * @param string|int|null $abaikanInvoice Booking yang sedang diedit; baris
     *                                        miliknya sendiri tidak dianggap terpakai.
     */
    private function saringTerpakai(array $rows, $asal, $abaikanInvoice = null)
    {
        if (!$rows || !$this->tabelAda(self::TABEL_DET)) {
            return $rows;
        }

        $id = array();
        foreach ($rows as $r) {
            $id[(string) $r['id_baris']] = true;
        }
        $id = array_keys($id);

        $abaikan = (int) $abaikanInvoice;
        $terpakai = array();

        foreach (array_chunk($id, 1000) as $bagian) {
            $sql = "SELECT DISTINCT x.id_baris
                      FROM " . self::TABEL_DET . " x
                      INNER JOIN tbl_book_invoice bi ON bi.id = x.id_book_invoice
                     WHERE x.asal = ?
                       AND UPPER(bi.status) <> 'CANCEL'
                       AND x.id_baris IN (" . implode(',', array_fill(0, count($bagian), '?')) . ")";
            $bind = array_merge(array($asal), $bagian);
            if ($abaikan > 0) {
                $sql .= " AND x.id_book_invoice <> ?";
                $bind[] = $abaikan;
            }
            foreach ($this->koneksiAr()->select($sql, $bind) as $r) {
                $terpakai[(string) $r->id_baris] = true;
            }
        }

        if (!$terpakai) {
            return $rows;
        }

        $sisa = array();
        foreach ($rows as $r) {
            if (!isset($terpakai[(string) $r['id_baris']])) {
                $sisa[] = $r;
            }
        }
        return $sisa;
    }

    private function kolomAda($tabel, $kolom)
    {
        try {
            return count($this->koneksiAr()->select(
                "SELECT 1 FROM information_schema.columns
                  WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1",
                array($tabel, $kolom)
            )) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function tabelAda($nama)
    {
        try {
            return count($this->koneksiAr()->select(
                "SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1",
                array($nama)
            )) > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }


    /**
     * SJ garment - semua tipe pengeluaran yang boleh ditagihkan, bukan FG/OUT saja.
     *
     * Bedanya dengan AR (yang masih FG/OUT saja): di sini tabel utamanya `bppb`,
     * bukan `so`. Tipe selain FG/OUT tidak punya id_so_det, jadi kalau dimulai
     * dari so lalu INNER JOIN seperti dulu, barisnya hilang diam-diam.
     *
     * Dua rangkaian master dipakai berdampingan:
     *   FG/OUT      so_det -> so -> act_costing -> masterproduct  (punya SO & harga)
     *   tipe lain   masteritem lewat bppb.id_item                 (tanpa SO, harga 0)
     *
     * Harga tipe selain FG/OUT sengaja dikeluarkan sebagai 0: di bppb kolom
     * price-nya memang diisi '0' waktu barangnya dikeluarkan. Angka aslinya
     * diketik user di layar Detail SJ (lihat terapkanHargaManual()).
     */
    private function sjGarment($tglAwal, $tglAkhir, $buyer, array $idBaris = array())
    {
        // Dipakai berkali-kali di dalam query - isinya tetap, bukan dari user.
        $fg = "c.bppbno_int LIKE 'FG/OUT/%'";
        $pola = '^(' . implode('|', self::TIPE_SJ_GARMENT) . ')/OUT/';

        // "Penjualan", "Penjualan Lokal", dan "Penjualan Ekspor" dicakup satu
        // pola supaya ejaan di mastertransaksi tidak perlu ditebak persis.
        $jenis = "($fg AND c.bppbdate < '2026-08-01')
                  OR c.jenis_trans LIKE 'Penjualan%'
                  OR c.jenis_trans LIKE 'Pengiriman ke Subkontraktor CMT%'
                  OR c.jenis_trans LIKE 'Pengiriman Sample%'";

        $sql = "SELECT a.so_no AS no_so, c.bppbno AS sj, c.bppbdate, c.bppbno_int AS shipping_number,
                       d.kpno AS ws, d.styleno,
                       IF($fg, e.product_group, mi.matclass) AS product_group,
                       IF($fg, e.product_item, mi.itemdesc) AS product_item,
                       IF($fg, b.color, mi.color) AS color,
                       IF($fg, b.size, mi.size) AS size,
                       c.curr, c.unit AS uom, c.qty,
                       IF($fg, ROUND(c.price, 4), 0) AS unit_price,
                       IF($fg, ROUND(c.qty * ROUND(c.price, 4), 4), 0) AS total_price,
                       b.id_so, c.id AS id_bppb, c.id AS id_baris, c.grade,
                       -- Brand per kontrak/style, dipakai mengisi Shipment Details.
                       d.brand AS brand,
                       IF(c.grade = 'GRADE A', 'A', 'B') AS grade_kode,
                       UPPER(SUBSTRING_INDEX(c.bppbno_int, '/', 1)) AS tipe_sj,
                       IF($fg, 0, 1) AS harga_manual,
                       -- Biaya jasa costing baris ini. Tidak ditampilkan di layar
                       -- dan tidak ikut hitungan invoice - cuma ikut tersimpan.
                       -- Subquery, bukan JOIN: satu costing bisa punya lebih dari
                       -- satu baris act_others, dan itu akan menggandakan baris SJ.
                       (SELECT IF(ao.curr = 'IDR', ao.val_idr, ao.val_usd)
                          FROM act_others ao
                         WHERE ao.cost_no = d.cost_no
                           AND ao.mattype = 'SERVICE CHARGE'
                         LIMIT 1) AS service_charge
                  FROM bppb AS c
                  LEFT JOIN so_det AS b ON b.id = c.id_so_det
                  LEFT JOIN so AS a ON a.id = b.id_so
                  LEFT JOIN act_costing AS d ON d.id = a.id_cost
                  LEFT JOIN masterproduct AS e ON e.id = d.id_product
                  LEFT JOIN masteritem AS mi ON mi.id_item = c.id_item
                  INNER JOIN mastersupplier AS f ON f.Id_Supplier = c.id_supplier
                 WHERE c.bppbdate BETWEEN ? AND ?
                   AND c.bppbno_int REGEXP '$pola'
                   AND ($jenis)
                   AND c.id_supplier != '1038'
                   AND (ISNULL(c.stat_inv) OR c.stat_inv = '' OR c.stat_inv = '0')
                   AND c.confirm = 'Y'
                   AND (c.cancel IS NULL OR c.cancel <> 'Y')
                   -- Pengiriman ke subkontraktor CMT penerimanya memang bukan
                   -- customer, jadi syarat tipe_sup cuma berlaku untuk FG/OUT.
                   AND (NOT ($fg) OR f.tipe_sup = 'C')";
        $bind = array($tglAwal, $tglAkhir);
        if ($buyer !== '') {
            $sql .= " AND f.Id_Supplier = ?";
            $bind[] = $buyer;
        }
        // Dipakai waktu simpan: baris tertentu dibaca ulang dari sumbernya.
        if ($idBaris) {
            $sql .= " AND c.id IN (" . implode(',', array_fill(0, count($idBaris), '?')) . ")";
            $bind = array_merge($bind, array_values($idBaris));
        }
        $sql .= " ORDER BY c.bppbno";

        return $this->baris($this->koneksiAr()->select($sql, $bind), 'NAG');
    }

    /**
     * SJ knitting - query & syarat barisnya sama dengan cabang selain NAG di AR.
     * Buyer dipetakan lewat mastersupplier.knitting_code, sama seperti cari_so().
     */
    private function sjKnitting($tglAwal, $tglAkhir, $buyer, array $idBaris = array())
    {
        $kodeKnitting = '';
        if ($buyer !== '') {
            $s = $this->koneksiAr()->select(
                "SELECT knitting_code FROM mastersupplier WHERE tipe_sup = 'C' AND Id_Supplier = ?",
                array($buyer)
            );
            $kodeKnitting = $s ? (string) $s[0]->knitting_code : '';
            // Buyer dipilih tapi tidak punya padanan di knitting: tidak ada SJ knitting.
            if ($kodeKnitting === '') {
                return array();
            }
        }

        // po_konsumen ikut dibawa: dipakai kolom PO di PDF khusus knitting.
        $sql = "SELECT f.po_konsumen AS po_konsumen,
                       f.kode_so AS no_so, a.kode_out AS sj, a.tgl_pengeluaran AS bppbdate,
                       a.kode_out AS shipping_number, '-' AS ws, d.lab_dip AS styleno,
                       '-' AS product_group, c.nama_kain AS product_item, d.warna AS color,
                       '-' AS size, f.currency AS curr,
                       -- SATUAN, QTY & HARGA ikut satuan SO - yang sama dengan
                       -- satuan SJ-nya, bukan satuan tagih. Satu SO bisa ditagih
                       -- dalam Kilogram padahal SO & SJ-nya Yard; cetakan EXIM
                       -- harus menyebut Yard, karena itu yang benar-benar dikirim.
                       --
                       -- Create Invoice di AR TIDAK ikut: di sana yang ditarik
                       -- memang satuan tagihnya (Model_nag::cari_sj_knitting -
                       -- kolom uom_so/qty_so/harga, dari id_unit_sales_order).
                       --
                       -- Harga kirim yang kosong jatuh ke harga tagih, bukan ke
                       -- nol: invoice tanpa harga lebih berbahaya daripada harga
                       -- yang satuannya perlu dicek user.
                       g.nama_unit AS uom,
                       CASE
                           WHEN g.nama_unit = 'Meter' THEN b.meter
                           WHEN g.nama_unit = 'Yard'  THEN b.yard
                           ELSE b.qty_netto
                       END AS qty,
                       ROUND(COALESCE(e.harga_shipment, e.harga, 0), 4) AS unit_price,
                       ROUND((CASE
                                  WHEN g.nama_unit = 'Meter' THEN b.meter
                                  WHEN g.nama_unit = 'Yard'  THEN b.yard
                                  ELSE b.qty_netto
                              END) * ROUND(COALESCE(e.harga_shipment, e.harga, 0), 4), 4) AS total_price,
                       a.no_so AS id_so, a.id AS id_bppb, b.id AS id_baris,
                       'GRADE A' AS grade, 'A' AS grade_kode,
                       -- Knitting cuma punya OFC/OUT, dan harganya selalu ada
                       -- di detail_so - tidak pernah diketik manual.
                       UPPER(SPLIT_PART(a.kode_out, '/', 1)) AS tipe_sj,
                       0 AS harga_manual
                  FROM official_out_h a
                  INNER JOIN official_out_barcode b ON b.id_official = a.id
                  INNER JOIN master_kain c ON c.id = b.kain_id
                  LEFT JOIN master_kain_detail d ON d.id = b.detail_kain_id
                  INNER JOIN sales_orders f ON f.id = a.no_so
                  -- Satuan & harga diambil dari SO YANG DIKIRIM (SO di header SJ),
                  -- bukan dari SO asal stoknya. Rollnya sering diambil dari SO
                  -- lain, dan satuan SO itu belum tentu sama - menyambung lewat
                  -- roll bikin satuannya ikut SO yang salah. Create Invoice di AR
                  -- memang sudah menyambung lewat jalur ini.
                  --
                  -- ea = baris SO asal rollnya, dipakai kalau SO yang dikirim
                  -- tidak punya baris untuk kain ini - supaya SJ-nya tidak hilang
                  -- dari daftar, dan penyaringan barisnya persis seperti dulu.
                  INNER JOIN detail_so ea ON ea.id = b.detail_so_id
                  LEFT JOIN LATERAL (
                      SELECT ds.harga, ds.harga_shipment, ds.id_unit_sales_order_shipment
                        FROM detail_so ds
                       WHERE ds.sales_order_id = f.id
                         AND ds.master_kain_id = b.kain_id
                       ORDER BY ds.id
                       LIMIT 1
                  ) ek ON TRUE
                  -- Satu baris saja yang dipakai: harga & satuannya harus datang
                  -- dari SO yang sama, kalau tidak totalnya dihitung dengan harga
                  -- per satuan yang berbeda.
                  LEFT JOIN LATERAL (
                      SELECT COALESCE(ek.harga, ea.harga) AS harga,
                             COALESCE(ek.harga_shipment, ea.harga_shipment) AS harga_shipment,
                             COALESCE(ek.id_unit_sales_order_shipment,
                                      ea.id_unit_sales_order_shipment)
                                 AS id_unit_sales_order_shipment
                  ) e ON TRUE
                  -- g = satuan kirim SO itu. Satuan tagih (id_unit_sales_order)
                  -- tidak dibaca di sini; yang memakainya Create Invoice AR.
                  LEFT JOIN master_unit g ON g.id = e.id_unit_sales_order_shipment
                  LEFT JOIN master_konsumen k ON k.id = f.konsumen_id
                 WHERE a.status_inv IS NULL
                   AND a.tipe_pengeluaran IN ('Penjualan','Sample')
                   AND a.tgl_pengeluaran BETWEEN ? AND ?";
        $bind = array($tglAwal, $tglAkhir);
        if ($kodeKnitting !== '') {
            $sql .= " AND k.kode_konsumen = ?";
            $bind[] = $kodeKnitting;
        }
        if ($idBaris) {
            $sql .= " AND b.id IN (" . implode(',', array_fill(0, count($idBaris), '?')) . ")";
            $bind = array_merge($bind, array_values($idBaris));
        }
        $sql .= " ORDER BY a.kode_out ASC";

        return $this->baris(DB::connection(self::KONEKSI_NAK)->select($sql, $bind), 'NAK');
    }

    /** Hasil query dijadikan bentuk yang sama untuk kedua sumber. */
    private function baris($rows, $asal)
    {
        $hasil = array();
        foreach ($rows as $r) {
            $a = (array) $r;
            $a['asal'] = $asal;
            // Dua kolom ini yang menentukan boleh-tidaknya harga diketik user,
            // jadi bentuknya disamakan di satu tempat saja.
            $a['tipe_sj'] = $this->tipeSj(isset($a['tipe_sj']) ? $a['tipe_sj'] : $a['shipping_number']);
            $a['harga_manual'] = $this->hargaManual($a['tipe_sj']) ? 1 : 0;
            $hasil[] = $a;
        }
        return $hasil;
    }

    /**
     * Harga & mata uang baris SJ yang tidak punya SO diambil dari ketikan user.
     *
     * Baris FG/OUT dan knitting tidak pernah disentuh di sini: harga dan mata
     * uangnya sudah dibaca ulang dari sumbernya, dan itu yang dipakai - kiriman
     * layar untuk baris seperti itu diabaikan, bukan dipercaya.
     *
     * @param array $asli  hasil sjUlang(), diubah di tempat
     * @param array $ketik id_baris => array('harga' => .., 'curr' => ..)
     * @return array       array('harga' => nomor SJ tanpa harga,
     *                           'curr'  => nomor SJ tanpa mata uang)
     */
    private function terapkanHargaManual(array &$asli, array $ketik)
    {
        $kurangHarga = array();
        $kurangCurr  = array();
        foreach ($asli as $i => $r) {
            if (empty($r['harga_manual'])) {
                continue;
            }
            $kunci = (string) $r['id_baris'];
            $isi   = isset($ketik[$kunci]) ? $ketik[$kunci] : array();
            $nomor = trim((string) $r['shipping_number']);
            if ($nomor === '') { $nomor = (string) $r['sj']; }

            $harga = isset($isi['harga']) ? round((float) $isi['harga'], 4) : 0.0;
            if ($harga <= 0) {
                $kurangHarga[] = $nomor;
            } else {
                $asli[$i]['unit_price']  = $harga;
                $asli[$i]['total_price'] = round((float) $r['qty'] * $harga, 4);
            }

            // Mata uang: hanya yang ada di daftar pilihan layar yang diterima.
            // Selain itu dianggap belum diisi - bukan disimpan apa adanya.
            $curr = isset($isi['curr']) ? strtoupper(trim((string) $isi['curr'])) : '';
            if (!in_array($curr, self::MATA_UANG_SJ, true)) {
                $kurangCurr[] = $nomor;
                continue;
            }
            $asli[$i]['curr'] = $curr;
        }
        return array(
            'harga' => array_values(array_unique($kurangHarga)),
            'curr'  => array_values(array_unique($kurangCurr)),
        );
    }

    /** Isian harga & mata uang dari payload, dipisah per id baris. */
    private function ketikanHarga($baris)
    {
        $ketik = array();
        foreach ((array) $baris as $b) {
            if (!is_array($b) || !isset($b['id_baris']) || !is_scalar($b['id_baris'])) {
                continue;
            }
            $kunci = (string) $b['id_baris'];
            $ketik[$kunci] = array(
                'harga' => isset($b['unit_price']) && is_scalar($b['unit_price']) ? $b['unit_price'] : null,
                'curr'  => isset($b['curr']) && is_scalar($b['curr']) ? $b['curr'] : '',
            );
        }
        return $ketik;
    }

    /** Pesan untuk baris SJ tanpa SO yang harga / mata uangnya masih kosong. */
    private function pesanTanpaHarga(array $kurang)
    {
        $sebut = function (array $nomor) {
            $daftar = array_slice($nomor, 0, 5);
            $sisa = count($nomor) - count($daftar);
            return implode(', ', $daftar) . ($sisa > 0 ? ' and ' . $sisa . ' more SJ' : '');
        };
        if (!empty($kurang['harga'])) {
            return 'Unit Price is required on ' . $sebut($kurang['harga'])
                . ': these SJ have no SO, so their price must be typed in Detail SJ.';
        }
        return 'Currency is required on ' . $sebut($kurang['curr'])
            . ': these SJ have no SO, so their currency must be picked in Detail SJ ('
            . implode(' or ', self::MATA_UANG_SJ) . ').';
    }

    /**
     * Satu invoice satu mata uang.
     *
     * Rekapnya menjumlahkan seluruh baris jadi satu angka dan header
     * tbl_book_invoice cuma punya satu kolom curr - campuran mata uang berarti
     * angka yang tersimpan tidak berarti apa-apa.
     *
     * @return string|null pesan kesalahan, atau null kalau sudah seragam
     */
    private function pesanCurrCampur(array $asli)
    {
        $ada = array();
        foreach ($asli as $r) {
            $c = strtoupper(trim((string) $r['curr']));
            if ($c !== '' && !in_array($c, $ada, true)) {
                $ada[] = $c;
            }
        }
        if (count($ada) < 2) {
            return null;
        }
        return 'An invoice can only use one currency, but the SJ rows mix '
            . implode(', ', $ada) . '.';
    }

    /**
     * Penentu baris Invoice Summary di Invoice Export.
     *
     * Normalnya warna. SJ tanpa SO (GK/OUT dan kawan-kawan) sering tidak punya
     * warna di master barang, jadi kalau warnanya kosong dipakai nama barangnya
     * - kalau tidak, semua barang tanpa warna akan menumpuk jadi satu baris.
     * Rumus yang sama dipakai di layar (fungsi kunciWarna di form Export).
     */
    private function kunciWarna(array $r)
    {
        $warna = trim((string) (isset($r['color']) ? $r['color'] : ''));
        if ($warna !== '' && $warna !== '-') {
            return $warna;
        }
        $nama = trim((string) (isset($r['product_item']) ? $r['product_item'] : ''));
        return $nama !== '' ? $nama : '-';
    }

    /** Awalan nomor internal SJ: FG, GK, GEN, WIP, GACC, SCR, SPCK, OFC. */
    private function tipeSj($nilai)
    {
        $teks = strtoupper(trim((string) $nilai));
        $potong = explode('/', $teks);
        return $potong[0] !== '' ? $potong[0] : '-';
    }

    /** True kalau harga baris ini harus diketik user (SJ tanpa SO). */
    private function hargaManual($tipe)
    {
        return !in_array($this->tipeSj($tipe), self::TIPE_SJ_BERHARGA, true);
    }

    private function koneksiAr()
    {
        return DB::connection(self::KONEKSI_AR);
    }

    /** Tanggal Y-m-d yang benar-benar valid, atau null. */
    private function tanggal($nilai)
    {
        $nilai = trim((string) $nilai);
        $d = \DateTime::createFromFormat('Y-m-d', $nilai);
        return ($d && $d->format('Y-m-d') === $nilai) ? $nilai : null;
    }

    /**
     * Daftar buyer untuk modal Add SO - query-nya sama persis dengan
     * Model_nag::cari_buyer() di aplikasi AR.
     */
    private function daftarBuyer()
    {
        return DB::connection(self::KONEKSI_AR)->select(
            "SELECT DISTINCT Id_Supplier, UPPER(Supplier) AS Supplier
               FROM mastersupplier WHERE tipe_sup = 'C' ORDER BY supplier ASC"
        );
    }

    private function daftarProfitCenter()
    {
        return DB::connection(self::KONEKSI_AR)->select(
            "SELECT kode_pc, nama_pc FROM master_pc GROUP BY id_pc"
        );
    }
    /**
     * Daftar customer untuk filter - query-nya sama persis dengan
     * Model_nag::cari_customer() di aplikasi AR.
     */
    private function daftarCustomer()
    {
        return DB::connection(self::KONEKSI_AR)->select(
            "SELECT DISTINCT alamat, Id_Supplier, UPPER(Supplier) AS Supplier
               FROM mastersupplier
              WHERE tipe_sup = 'C' AND id_supplier NOT IN ('1006','2048')
              ORDER BY supplier ASC"
        );
    }

    /**
     * Sumber data daftar invoice. Masih kosong: header invoice EXIM rencananya
     * disimpan ke tbl_book_invoice milik AR dan detailnya ke tabel baru, tapi
     * bentuk tabel detailnya belum diputuskan. Begitu final, query-nya cukup
     * ditulis di sini - tampilan daftarnya sudah siap menerima kolom di bawah.
     *
     * Kolom yang dipakai tampilan (sama dengan menu Booking Invoice di AR):
     * id, no_invoice, customer, customer_ship, shipp, tanggal, type, status,
     * doc_type, doc_number, value
     */
    /**
     * Isi daftar Invoice EXIM.
     *
     * Hanya invoice yang dibuat dari menu ini yang ditampilkan - dikenali dari
     * adanya baris di tbl_book_invoice_exim_det. Booking yang dibuat lewat menu
     * Booking Invoice di AR sengaja TIDAK ikut, supaya daftarnya tidak campur.
     *
     * $jenis menentukan Local / Export lewat kolom shipp.
     */
    /** Excel daftar Invoice Local - tombol Export di layar daftar. */
    public function excelDaftarLocal(Request $request)
    {
        return $this->excelDaftar($request, 'local');
    }

    /** Excel daftar Invoice Export - tombol Export di layar daftar. */
    public function excelDaftarExport(Request $request)
    {
        return $this->excelDaftar($request, 'export');
    }

    /**
     * Daftar invoice yang sedang tersaring, dalam bentuk Excel.
     * Bentuknya mengikuti List Invoice di AR: judul, periode, lalu tabelnya.
     */
    private function excelDaftar(Request $request, $jenis)
    {
        $baris  = $this->ambilData($request, $jenis);
        $export = $jenis === 'export';

        // Kolomnya sama dengan yang tampil di layar - yang isinya selalu sama
        // (Shipp) tidak ikut, seperti di daftar.
        $kolom = array(
            array('Inv Number',   'no_invoice', 22, 'teks'),
            array('Invoice Date', 'tanggal',    14, 'tanggal'),
        );
        if ($export) {
            $kolom[] = array('Seller', 'customer', 34, 'teks');
        } else {
            $kolom[] = array('Billed To',  'customer',      30, 'teks');
            $kolom[] = array('Shipped To', 'customer_ship', 30, 'teks');
        }
        $kolom[] = array('Type',       'type',       16, 'teks');
        $kolom[] = array('Doc Type',   'doc_type',   12, 'teks');
        $kolom[] = array('Doc Number', 'doc_number', 16, 'teks');
        $kolom[] = array($export ? 'Value (CM)' : 'Value', 'value', 18, 'angka');
        $kolom[] = array('Status', 'status', 18, 'teks');

        $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $s  = $ss->getActiveSheet();
        $s->setTitle($export ? 'Invoice Export' : 'Invoice Local');
        $s->setShowGridlines(false);
        $ss->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);

        $TIPIS = \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN;
        $TEKS  = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
        $ABU   = 'FFF1F5F9';
        $huruf = function ($i) { return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i); };
        $akhir = $huruf(count($kolom));

        // ---- Judul & periode, sama seperti List Invoice di AR ----
        $s->setCellValue('A1', 'LIST INVOICE ' . ($export ? 'EXPORT' : 'LOCAL'));
        $s->mergeCells('A1:' . $akhir . '1');
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $s->setCellValueExplicit('A2', 'Period : ' . $this->tanggalTampil($request->query('tgl_awal'))
            . ' To ' . $this->tanggalTampil($request->query('tgl_akhir')), $TEKS);
        $s->mergeCells('A2:' . $akhir . '2');

        // ---- Kepala tabel ----
        $bKepala = 4;
        foreach ($kolom as $i => $k) {
            $kol = $huruf($i + 1);
            $s->setCellValueExplicit($kol . $bKepala, $k[0], $TEKS);
            $s->getColumnDimension($kol)->setWidth($k[2]);
        }
        $rentangKepala = 'A' . $bKepala . ':' . $akhir . $bKepala;
        $s->getStyle($rentangKepala)->getFont()->setBold(true);
        $s->getStyle($rentangKepala)->getAlignment()->setHorizontal('center')->setVertical('center');
        $s->getStyle($rentangKepala)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($ABU);
        $s->getRowDimension($bKepala)->setRowHeight(20);

        // ---- Isi ----
        $b = $bKepala;
        $jumlah = 0;
        foreach ($baris as $r) {
            $b++;
            foreach ($kolom as $i => $k) {
                $kol = $huruf($i + 1);
                $nilai = isset($r[$k[1]]) ? $r[$k[1]] : '';
                if ($k[3] === 'angka') {
                    $s->setCellValue($kol . $b, (float) $nilai);
                    $s->getStyle($kol . $b)->getNumberFormat()->setFormatCode('#,##0.00');
                    $jumlah += (float) $nilai;
                } elseif ($k[3] === 'tanggal') {
                    $s->setCellValueExplicit($kol . $b, $this->tanggalTampil($nilai), $TEKS);
                    $s->getStyle($kol . $b)->getAlignment()->setHorizontal('center');
                } else {
                    $s->setCellValueExplicit($kol . $b, (string) $nilai, $TEKS);
                }
            }
        }
        if ($b === $bKepala) {
            $b++;
            $s->setCellValueExplicit('A' . $b, 'No invoice in this period.', $TEKS);
            $s->mergeCells('A' . $b . ':' . $akhir . $b);
            $s->getStyle('A' . $b)->getFont()->setItalic(true);
            $s->getStyle('A' . $b)->getAlignment()->setHorizontal('center');
        } else {
            // Baris jumlah: yang paling sering dicari sesudah daftarnya dibuka.
            $b++;
            $kolNilai = $huruf(count($kolom) - 1);
            $s->setCellValueExplicit('A' . $b, 'Total', $TEKS);
            $s->mergeCells('A' . $b . ':' . $huruf(count($kolom) - 2) . $b);
            $s->getStyle('A' . $b)->getAlignment()->setHorizontal('right');
            $s->setCellValue($kolNilai . $b, $jumlah);
            $s->getStyle($kolNilai . $b)->getNumberFormat()->setFormatCode('#,##0.00');
            $s->getStyle('A' . $b . ':' . $akhir . $b)->getFont()->setBold(true);
            $s->getStyle('A' . $b . ':' . $akhir . $b)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB($ABU);
        }

        $s->getStyle('A' . $bKepala . ':' . $akhir . $b)->getBorders()->getAllBorders()->setBorderStyle($TIPIS);
        // Kepala tabel tetap terlihat waktu digulir & bisa disaring di Excel.
        $s->freezePane('A' . ($bKepala + 1));
        $s->setAutoFilter($rentangKepala);

        $atur = $s->getPageSetup();
        $atur->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $atur->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $atur->setFitToWidth(1);
        $atur->setFitToHeight(0);
        $atur->setRowsToRepeatAtTopByStartAndEnd($bKepala, $bKepala);
        $s->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.4)->setRight(0.4);

        $nama = 'Invoice ' . ($export ? 'Export' : 'Local') . ' '
            . $this->tanggalTampil($request->query('tgl_awal')) . ' - '
            . $this->tanggalTampil($request->query('tgl_akhir'));
        $penulis = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss);
        return response()->streamDownload(function () use ($penulis) {
            $penulis->save('php://output');
        }, str_replace('/', '-', $nama) . '.xlsx', array(
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ));
    }

    /** yyyy-mm-dd -> '18 Sep 2026'; yang bukan tanggal dikembalikan apa adanya. */
    private function tanggalTampil($nilai)
    {
        $iso = $this->tanggal($nilai);
        return $iso === null ? (string) $nilai : date('j M Y', strtotime($iso));
    }

    private function ambilData(Request $request, $jenis)
    {
        if (!$this->tabelAda(self::TABEL_DET)) {
            return array();
        }

        // Daftar Export memakai Invoice Date - itu tanggal yang diisi & diedit
        // di form, dan yang tercetak. tgl_book_inv cuma catatan kapan booking
        // dibuat, jadi tidak ikut berubah saat Invoice Date diedit.
        $pakaiExport = $jenis === 'export' && $this->kolomAda(self::TABEL_EXP_H, 'tgl_invoice');
        // Invoice Local maupun Export boleh dibuat dari WS saja - SJ-nya
        // menyusul. Jadi yang dicari "punya baris SJ ATAU baris SO"; kalau
        // cuma baris SJ, invoice yang belum ada SJ-nya hilang dari daftar.
        $adaTabelSo = $this->tabelAda(self::TABEL_SO);
        $pakaiPot = !$pakaiExport && $this->kolomAda(self::TABEL_POT, 'tgl_invoice');
        $kolomTgl = "DATE(b.tgl_book_inv)";
        if ($pakaiExport) { $kolomTgl = "COALESCE(h.tgl_invoice, DATE(b.tgl_book_inv))"; }
        if ($pakaiPot)    { $kolomTgl = "COALESCE(p.tgl_invoice, DATE(b.tgl_book_inv))"; }

        $sql = "SELECT b.id,
                       b.no_invoice,
                       UPPER(c.Supplier)  AS customer,
                       UPPER(cs.Supplier) AS customer_ship,
                       b.shipp,
                       $kolomTgl AS tanggal,
                       t.type,
                       b.status,
                       b.doc_type,
                       b.doc_number,
                       -- Dipakai daftar untuk memutuskan cetakan mana yang
                       -- ditawarkan: bentuk knitting cuma untuk NAK.
                       b.profit_center,
                       b.value
                  FROM tbl_book_invoice b
                  LEFT JOIN mastersupplier c  ON c.Id_Supplier  = b.id_customer
                  LEFT JOIN mastersupplier cs ON cs.Id_Supplier = b.id_customer_ship
                  LEFT JOIN tbl_type t        ON t.id           = b.id_type"
             . ($pakaiExport ? " LEFT JOIN " . self::TABEL_EXP_H . " h ON h.id_book_invoice = b.id" : '')
             . ($pakaiPot ? " LEFT JOIN " . self::TABEL_POT . " p ON p.id_book_invoice = b.id" : '')
             . " WHERE b.shipp = ?
                   AND (EXISTS (SELECT 1 FROM " . self::TABEL_DET . " d
                                 WHERE d.id_book_invoice = b.id)"
             . ($adaTabelSo
                 ? " OR EXISTS (SELECT 1 FROM " . self::TABEL_SO . " s
                                  WHERE s.id_book_invoice = b.id)"
                 : '')
             . ")";
        $bind = array($jenis === 'export' ? 'Export' : self::SHIPP_LOCAL);

        $tglAwal  = $this->tanggal($request->query('tgl_awal'));
        $tglAkhir = $this->tanggal($request->query('tgl_akhir'));
        if ($tglAwal !== null && $tglAkhir !== null) {
            $sql .= " AND $kolomTgl BETWEEN ? AND ?";
            $bind[] = $tglAwal;
            $bind[] = $tglAkhir;
        }

        // Filter di layar berlabel "Shipped To", jadi disaring ke id_customer_ship.
        $customer = trim((string) $request->query('customer'));
        if ($customer !== '') {
            $sql .= " AND b.id_customer_ship = ?";
            $bind[] = $customer;
        }

        $status = trim((string) $request->query('status'));
        if ($status !== '') {
            $sql .= " AND b.status = ?";
            $bind[] = $status;
        }

        $sql .= " ORDER BY b.id DESC";

        $hasil = array();
        foreach ($this->koneksiAr()->select($sql, $bind) as $r) {
            $hasil[] = (array) $r;
        }
        return $hasil;
    }
}
