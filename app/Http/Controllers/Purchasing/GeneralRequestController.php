<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;

/**
 * MOCKUP General Request.
 * Semua data masih dummy (belum ada tabel / query ke database).
 */
class GeneralRequestController extends Controller
{
    public function index()
    {
        return view('purchasing.general_request.index', [
            'page' => 'dashboard-purchasing',
            'subPageGroup' => 'general-request',
            'subPage' => 'general-request',
            'containerFluid' => true,
            'requests' => $this->dummyRequests(),
            // Untuk modal Buat Request
            'no_request' => 'GR/2611/0007',
            'units' => ['PCS', 'RIM', 'BOX', 'PACK', 'UNIT', 'SET', 'LITER', 'ROLL'],
        ]);
    }

    /**
     * Item: tgl_po & no_po per item (bisa beda PO),
     * terima = riwayat penerimaan gudang per item (bisa bertahap).
     */
    private function dummyRequests()
    {
        $data = [
            [
                'no_request' => 'GR/2611/0006',
                'tgl_request' => '2026-11-17',
                'tgl_butuh' => '2026-11-24',
                'departemen' => 'IT',
                'keperluan' => 'Kebutuhan ATK kantor IT',
                'status' => 'Menunggu Approval',
                'timeline' => [
                    ['tgl' => '17-11-2026 09:12', 'judul' => 'Request diajukan', 'oleh' => 'User IT', 'ket' => null],
                ],
                'items' => [
                    ['nama' => 'Kertas A4 80gr', 'qty' => 10, 'satuan' => 'RIM', 'no_po' => null, 'tgl_po' => null, 'qty_po' => 0, 'terima' => []],
                    ['nama' => 'Tinta Printer Epson 003 Hitam', 'qty' => 6, 'satuan' => 'PCS', 'no_po' => null, 'tgl_po' => null, 'qty_po' => 0, 'terima' => []],
                ],
            ],
            [
                'no_request' => 'GR/2611/0005',
                'tgl_request' => '2026-11-14',
                'tgl_butuh' => '2026-11-21',
                'departemen' => 'IT',
                'keperluan' => 'Penggantian perangkat jaringan line sewing',
                'status' => 'Proses PO',
                'timeline' => [
                    ['tgl' => '14-11-2026 10:05', 'judul' => 'Request diajukan', 'oleh' => 'User IT', 'ket' => null],
                    ['tgl' => '14-11-2026 15:40', 'judul' => 'Disetujui', 'oleh' => 'Manager IT', 'ket' => null],
                    ['tgl' => '15-11-2026 08:30', 'judul' => 'PO dibuat', 'oleh' => 'Purchasing', 'ket' => 'PO/2611/0112'],
                ],
                'items' => [
                    ['nama' => 'Switch 24 Port Gigabit', 'qty' => 2, 'satuan' => 'UNIT', 'no_po' => 'PO/2611/0112', 'tgl_po' => '15-11-2026 08:30', 'qty_po' => 2, 'terima' => []],
                    ['nama' => 'Kabel LAN Cat6 305m', 'qty' => 3, 'satuan' => 'ROLL', 'no_po' => 'PO/2611/0112', 'tgl_po' => '15-11-2026 08:30', 'qty_po' => 3, 'terima' => [
                        ['tgl' => '17-11-2026 13:20', 'no_bpb' => 'BPB/2611/0471', 'qty' => 1],
                    ]],
                    ['nama' => 'Konektor RJ45', 'qty' => 5, 'satuan' => 'BOX', 'no_po' => null, 'tgl_po' => null, 'qty_po' => 0, 'terima' => []],
                ],
            ],
            [
                'no_request' => 'GR/2611/0004',
                'tgl_request' => '2026-11-10',
                'tgl_butuh' => '2026-11-17',
                'departemen' => 'IT',
                'keperluan' => 'Perlengkapan kebersihan ruang server',
                'status' => 'Diterima Sebagian',
                'timeline' => [
                    ['tgl' => '10-11-2026 08:20', 'judul' => 'Request diajukan', 'oleh' => 'User IT', 'ket' => null],
                    ['tgl' => '10-11-2026 13:02', 'judul' => 'Disetujui', 'oleh' => 'Manager IT', 'ket' => null],
                    ['tgl' => '11-11-2026 09:15', 'judul' => 'PO dibuat', 'oleh' => 'Purchasing', 'ket' => 'PO/2611/0098, PO/2611/0101'],
                    ['tgl' => '14-11-2026 14:00', 'judul' => 'Barang diterima gudang', 'oleh' => 'Gudang', 'ket' => 'BPB/2611/0450'],
                ],
                'items' => [
                    ['nama' => 'Tisu Basah Elektronik', 'qty' => 10, 'satuan' => 'PACK', 'no_po' => 'PO/2611/0098', 'tgl_po' => '11-11-2026 09:15', 'qty_po' => 10, 'terima' => [
                        ['tgl' => '14-11-2026 14:00', 'no_bpb' => 'BPB/2611/0450', 'qty' => 10],
                    ]],
                    ['nama' => 'Kuas Pembersih Debu', 'qty' => 5, 'satuan' => 'PCS', 'no_po' => 'PO/2611/0098', 'tgl_po' => '11-11-2026 09:15', 'qty_po' => 5, 'terima' => [
                        ['tgl' => '14-11-2026 14:00', 'no_bpb' => 'BPB/2611/0450', 'qty' => 2],
                        ['tgl' => '16-11-2026 10:30', 'no_bpb' => 'BPB/2611/0462', 'qty' => 1],
                    ]],
                    ['nama' => 'Blower Mini', 'qty' => 1, 'satuan' => 'UNIT', 'no_po' => 'PO/2611/0101', 'tgl_po' => '12-11-2026 11:00', 'qty_po' => 1, 'terima' => []],
                ],
            ],
            [
                'no_request' => 'GR/2611/0003',
                'tgl_request' => '2026-11-05',
                'tgl_butuh' => '2026-11-12',
                'departemen' => 'IT',
                'keperluan' => 'Mouse & keyboard pengganti admin produksi',
                'status' => 'Selesai',
                'timeline' => [
                    ['tgl' => '05-11-2026 09:00', 'judul' => 'Request diajukan', 'oleh' => 'User IT', 'ket' => null],
                    ['tgl' => '05-11-2026 11:30', 'judul' => 'Disetujui', 'oleh' => 'Manager IT', 'ket' => null],
                    ['tgl' => '06-11-2026 08:10', 'judul' => 'PO dibuat', 'oleh' => 'Purchasing', 'ket' => 'PO/2611/0071'],
                    ['tgl' => '09-11-2026 10:45', 'judul' => 'Barang diterima gudang', 'oleh' => 'Gudang', 'ket' => 'BPB/2611/0388'],
                    ['tgl' => '09-11-2026 10:45', 'judul' => 'Selesai', 'oleh' => 'Sistem', 'ket' => 'Semua item sudah diterima'],
                ],
                'items' => [
                    ['nama' => 'Mouse USB Logitech', 'qty' => 5, 'satuan' => 'PCS', 'no_po' => 'PO/2611/0071', 'tgl_po' => '06-11-2026 08:10', 'qty_po' => 5, 'terima' => [
                        ['tgl' => '09-11-2026 10:45', 'no_bpb' => 'BPB/2611/0388', 'qty' => 5],
                    ]],
                    ['nama' => 'Keyboard USB Logitech', 'qty' => 5, 'satuan' => 'PCS', 'no_po' => 'PO/2611/0071', 'tgl_po' => '06-11-2026 08:10', 'qty_po' => 5, 'terima' => [
                        ['tgl' => '08-11-2026 15:10', 'no_bpb' => 'BPB/2611/0379', 'qty' => 3],
                        ['tgl' => '09-11-2026 10:45', 'no_bpb' => 'BPB/2611/0388', 'qty' => 2],
                    ]],
                ],
            ],
            [
                'no_request' => 'GR/2611/0002',
                'tgl_request' => '2026-11-03',
                'tgl_butuh' => '2026-11-10',
                'departemen' => 'IT',
                'keperluan' => 'Monitor tambahan',
                'status' => 'Ditolak',
                'timeline' => [
                    ['tgl' => '03-11-2026 14:20', 'judul' => 'Request diajukan', 'oleh' => 'User IT', 'ket' => null],
                    ['tgl' => '04-11-2026 09:00', 'judul' => 'Ditolak', 'oleh' => 'Manager IT', 'ket' => 'Gunakan stok monitor di gudang IT'],
                ],
                'items' => [
                    ['nama' => 'Monitor LED 24 inch', 'qty' => 2, 'satuan' => 'UNIT', 'no_po' => null, 'tgl_po' => null, 'qty_po' => 0, 'terima' => []],
                ],
            ],
            [
                'no_request' => 'GR/2611/0001',
                'tgl_request' => '2026-11-02',
                'tgl_butuh' => '2026-11-09',
                'departemen' => 'IT',
                'keperluan' => 'Draft kebutuhan UPS',
                'status' => 'Draft',
                'timeline' => [],
                'items' => [
                    ['nama' => 'UPS 1200VA', 'qty' => 3, 'satuan' => 'UNIT', 'no_po' => null, 'tgl_po' => null, 'qty_po' => 0, 'terima' => []],
                ],
            ],
        ];

        // Qty diterima = total dari riwayat penerimaan
        foreach ($data as &$req) {
            foreach ($req['items'] as &$item) {
                $item['qty_terima'] = collect($item['terima'])->sum('qty');
            }
        }

        return $data;
    }
}
