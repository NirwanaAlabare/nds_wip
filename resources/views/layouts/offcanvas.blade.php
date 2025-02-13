<div class="offcanvas offcanvas-end" tabindex="-1" id="user-offcanvas" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title text-sb fw-bold" id="offcanvasExampleLabel">
            <i class="fa-solid fa-circle-user mt-1"></i>
            {{ strtoupper((auth() && auth()->user() ? auth()->user()->name : null)) }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div>
            <div class="card bg-light">
                <div class="card-body">
                    <form action="{{ route('update-user', ["id" => (auth() && auth()->user() ? auth()->user()->id : null)]) }}" method="post" onsubmit="submitForm(this, event);">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">

                        <div class="mb-3">
                            <label><small>Name</small></label>
                            <input type="text" class="form-control form-control-sm" name="name" value="{{ (auth() && auth()->user() ? auth()->user()->name : null) }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label><small>Username</small></label>
                            <input type="text" class="form-control form-control-sm" name="username" value="{{ (auth() && auth()->user() ? auth()->user()->username : null) }}" readonly>
                        </div>
                        @if ((auth() && auth()->user() ? auth()->user()->type : null) == 'admin')
                            <div class="mb-3 d-none">
                                <label><small>Unlock Token</small></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="unlock_token" id="unlock_token" value="{{ (auth() && auth()->user() ? auth()->user()->unlock_token : null) }}" readonly>
                                    <button type="button" class="btn btn-sb" onclick="generateToken('{{ (auth() && auth()->user() ? auth()->user()->id : null) }}', '{{ route('generate-unlock-token') }}')">Generate</button>
                                </div>
                            </div>
                        @endif
                        <div>
                            <label><small>New Password</small></label>
                            <input type="password" class="form-control form-control-sm" name="password">
                        </div>
                        <button class="btn btn-sm btn-sb mt-3" type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a class="btn btn-no w-100 rounded-0" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none" onsubmit="logout(this, event)">
                @csrf
            </form>
        </div>
    </div>
</div>
