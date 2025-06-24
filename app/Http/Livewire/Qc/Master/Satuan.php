<?php

namespace App\Http\Livewire\Qc\Master;

use Livewire\Component;
use App\Models\qc\MasterSatuan;
use Yajra\DataTables\Facades\DataTables;

class Satuan extends Component
{
    public $satuan, $satuanId;

    protected $rules = [
        'satuan' => 'required|string|max:255',
    ];

    public function render()
    {
        return view('livewire.qc.master.satuan');
    }

    public function getDatatables()
    {
        return DataTables::of(MasterSatuan::query())
            ->addColumn('action', function($row) {
                return '
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-warning btn-sm" 
                                wire:click="edit('.$row->id.')"
                                data-bs-toggle="modal" 
                                data-bs-target="#modal-edit-satuan">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger btn-sm" 
                                wire:click="delete('.$row->id.')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        $this->validate();
        MasterSatuan::create(['satuan' => $this->satuan]);
        $this->resetInput();
        session()->flash('success', 'Satuan berhasil ditambahkan!');
        $this->emit('refreshDatatable');
    }

    public function edit($id)
    {
        $satuan = MasterSatuan::findOrFail($id);
        $this->satuanId = $id;
        $this->satuan = $satuan->satuan;
    }

    public function update()
    {
        $this->validate();
        $satuan = MasterSatuan::find($this->satuanId);
        $satuan->update(['satuan' => $this->satuan]);
        $this->resetInput();
        session()->flash('success', 'Satuan berhasil diperbarui!');
        $this->emit('refreshDatatable');
    }

    public function delete($id)
    {
        MasterSatuan::destroy($id);
        session()->flash('success', 'Satuan berhasil dihapus!');
        $this->emit('refreshDatatable');
    }

    private function resetInput()
    {
        $this->satuan = '';
        $this->satuanId = null;
    }
}