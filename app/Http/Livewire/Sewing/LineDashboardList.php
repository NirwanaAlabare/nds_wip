<?php

namespace App\Http\Livewire\Sewing;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\UserPassword;
use DB;

class LineDashboardList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = "";
    public $date;
    public $line;

    public function mount()
    {
        $this->date = date('Y-m-d');
    }

    public function render()
    {
        $this->lines = UserPassword::select('username')->
            where('Groupp', 'SEWING')->
            whereRaw('(Locked != 1 OR Locked IS NULL)')->
            whereRaw('REPLACE(username, "_", " ") LIKE "%'.$this->search.'%"')->
            orderBy('username', 'asc')->
            get();

        return view('livewire.sewing.line-dashboard-list');
    }
}
