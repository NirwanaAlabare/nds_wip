<div>
    <div class="mb-3">
        <input type="text" class="form-control" placeholder="Search Line..." wire:model='search'>
    </div>
    <div class="row g-3">
        @foreach ($lines as $line)
            <div class="col-md-3">
                <a class="btn btn-sb w-100" href="http://10.10.5.62:8000/dashboard-wip/line/dashboard1/{{ $line->username }}" target="_blank">
                    {{ strtoupper(str_replace('_', ' ', $line->username)) }}
                </a>
            </div>
        @endforeach
    </div>
</div>
