<?php

namespace App\Http\Livewire\QC\Master;

use Livewire\Component;
use App\Models\qc\MasterGroupInspect;
use Yajra\DataTables\Facades\DataTables;

class GroupInspect extends Component
{
    public function render()
    {
        return view('livewire.qc.master.group-inspect', ['page' => 'dashboard-warehouse']);
    }

    public function getDatatables()
    {
        return DataTables::of(MasterGroupInspect::query())
            ->addColumn('action', function($row) {
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        request()->validate([
            'group_inspect' => 'required|integer|unique:qc_inspect_master_group_inspect,group_inspect',
            'name_fabric_group' => 'required|string|max:250'
        ]);
        
        try {
            $groupInspect = new MasterGroupInspect();
            $groupInspect->group_inspect = (int) request('group_inspect');
            $groupInspect->name_fabric_group = request('name_fabric_group');
            $groupInspect->individu = (int) request('individu');
            $groupInspect->shipment = (int) request('shipment');
            $groupInspect->save();
            
            return redirect()->route('qc-inspect-master-group-inspect')->with('message', 'Group Inspect berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menambahkan group inspect: '.$e->getMessage());
        }
    }

    public function update()
    {
        $id = request('id');
        
        request()->validate([
            'group_inspect' => 'required|integer|unique:qc_inspect_master_group_inspect,group_inspect,'.$id,
            'name_fabric_group' => 'required|string|max:250'
        ]);
        
        try {
            $groupInspect = MasterGroupInspect::findOrFail($id);
            $groupInspect->group_inspect = (int) request('group_inspect');
            $groupInspect->name_fabric_group = request('name_fabric_group');
            $groupInspect->individu = (int) request('individu');
            $groupInspect->shipment = (int) request('shipment');
            $groupInspect->save();
            
            return redirect()->route('qc-inspect-master-group-inspect')->with('message', 'Group Inspect berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui group inspect: '.$e->getMessage());
        }
    }

    public function delete($id)
    {
        try {
            $groupInspect = MasterGroupInspect::findOrFail($id);
            $groupInspect->delete();
            
            return redirect()->route('qc-inspect-master-group-inspect')->with('message', 'Group Inspect berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus group inspect: '.$e->getMessage());
        }
    }
}