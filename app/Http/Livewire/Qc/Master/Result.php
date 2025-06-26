<?php

namespace App\Http\Livewire\QC\Master;

use Livewire\Component;
use App\Models\qc\MasterResult;
use Yajra\DataTables\Facades\DataTables;

class Result extends Component
{
    public function render()
    {
        return view('livewire.qc.master.result', ['page' => 'dashboard-warehouse']);
    }

    public function getDatatables()
    {
        return DataTables::of(MasterResult::query())
            ->addColumn('action', function($row) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        request()->validate([
            'result' => 'required|string|max:250|unique:qc_inspect_master_result,result',
        ]);
        
        try {
            $masterResult = new MasterResult();
            $masterResult->result = request('result');
            $masterResult->save();
            
            return redirect()->route('qc-inspect-master-result')->with('message', 'Result berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan result: '.$e->getMessage());
        }
    }

    public function update()
    {
        $id = request('id');
        
        request()->validate([
            'result' => 'required|string|max:250|unique:qc_inspect_master_result,result,'.$id,
        ]);
        
        try {
            $masterResult = MasterResult::findOrFail($id);
            $masterResult->result = request('result');
            $masterResult->save();
            
            return redirect()->route('qc-inspect-master-result')->with('message', 'Result berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui result: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $masterResult = MasterResult::findOrFail($id);
            $masterResult->delete();
            
            return redirect()->route('qc-inspect-master-result')->with('message', 'Result berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus result: '.$e->getMessage());
        }
    }
}