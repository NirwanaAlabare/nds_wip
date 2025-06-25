<?php

namespace App\Http\Livewire\QC\Master;

use Livewire\Component;
use App\Models\qc\MasterDefect;
use Yajra\DataTables\Facades\DataTables;

class Defect extends Component
{
    public function render()
    {
        return view('livewire.qc.master.defect', ['page' => 'dashboard-warehouse']);
    }

    public function getDatatables()
    {
        return DataTables::of(MasterDefect::query())
            ->addColumn('action', function($row) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        request()->validate([
            'critical_defect' => 'required|string',
            'point_defect' => 'required|string',
        ]);
        
        try {
            $masterDefect = new MasterDefect();
            $masterDefect->critical_defect = request('critical_defect');
            $masterDefect->point_defect = request('point_defect');
            $masterDefect->save();
            
            return redirect()->route('qc-inspect-master-defect')->with('message', 'Defect berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan defect: '.$e->getMessage());
        }
    }

    public function update()
    {
        $id = request('id');
        
        request()->validate([
            'critical_defect' => 'required|string',
            'point_defect' => 'required|string',
        ]);
        
        try {
            $masterDefect = MasterDefect::findOrFail($id);
            $masterDefect->critical_defect = request('critical_defect');
            $masterDefect->point_defect = request('point_defect');
            $masterDefect->save();
            
            return redirect()->route('qc-inspect-master-defect')->with('message', 'Defect berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui defect: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $masterDefect = MasterDefect::findOrFail($id);
            $masterDefect->delete();
            
            return redirect()->route('qc-inspect-master-defect')->with('message', 'Defect berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus defect: '.$e->getMessage());
        }
    }
}