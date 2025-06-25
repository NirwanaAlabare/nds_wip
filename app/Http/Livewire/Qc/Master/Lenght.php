<?php

namespace App\Http\Livewire\QC\Master;

use Livewire\Component;
use App\Models\qc\MasterLenght;
use Yajra\DataTables\Facades\DataTables;

class Lenght extends Component
{
    public function render()
    {
        return view('livewire.qc.master.lenght', ['page' => 'dashboard-warehouse']);
    }

    public function getDatatables()
    {
        return DataTables::of(MasterLenght::query())
            ->addColumn('action', function($row) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        request()->validate([
            'from' => 'required|numeric',
            'to' => 'required|numeric|gt:from',
        ]);
        
        try {
            $masterLenght = new MasterLenght();
            $masterLenght->from = request('from');
            $masterLenght->to = request('to');
            $masterLenght->save();
            
            return redirect()->route('qc-inspect-master-lenght')->with('message', 'Length range berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan length range: '.$e->getMessage());
        }
    }

    public function update()
    {
        $id = request('id');
        
        request()->validate([
            'from' => 'required|numeric',
            'to' => 'required|numeric|gt:from',
        ]);
        
        try {
            $masterLenght = MasterLenght::findOrFail($id);
            $masterLenght->from = request('from');
            $masterLenght->to = request('to');
            $masterLenght->save();
            
            return redirect()->route('qc-inspect-master-lenght')->with('message', 'Length range berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui length range: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $masterLenght = MasterLenght::findOrFail($id);
            $masterLenght->delete();
            
            return redirect()->route('qc-inspect-master-lenght')->with('message', 'Length range berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus length range: '.$e->getMessage());
        }
    }
}