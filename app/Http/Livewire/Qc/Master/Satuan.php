<?php

namespace App\Http\Livewire\Qc\Master;

use Livewire\Component;
use App\Models\qc\MasterSatuan;
use Yajra\DataTables\Facades\DataTables;

class Satuan extends Component
{
    public function render()
    {
        return view('livewire.qc.master.satuan', ["page" => "dashboard-warehouse"]);
    }

    public function getDatatables()
    {
        return DataTables::of(MasterSatuan::query())
            ->addColumn('action', function($row) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        request()->validate([
            'satuan' => 'required|unique:qc_inspect_master_satuan,satuan|max:50'
        ]);
        
        try {
            $satuan = new MasterSatuan();
            $satuan->satuan = request('satuan');
            $satuan->save();
            
            return redirect()->route('qc-inspect-master-satuan')->with('message', 'Satuan berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan satuan: '.$e->getMessage());
        }
    }

    public function update($id)
    {
        request()->validate([
            'satuan' => 'required|unique:qc_inspect_master_satuan,satuan,'.$id.'|max:50'
        ]);
        
        try {
            $satuan = MasterSatuan::findOrFail($id);
            $satuan->satuan = request('satuan');
            $satuan->save();
            
            return redirect()->route('qc-inspect-master-satuan')->with('message', 'Satuan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui satuan: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $satuan = MasterSatuan::findOrFail($id);
            $satuan->delete();
            
            return redirect()->route('qc-inspect-master-satuan')->with('message', 'Satuan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus satuan: '.$e->getMessage());
        }
    }
}