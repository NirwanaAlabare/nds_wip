<div class="offcanvas offcanvas-end" tabindex="-1" id="user-offcanvas" data-bs-scroll="true">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasExampleLabel">
            <i class="fa-solid fa-circle-user mt-1"></i>
            {{ auth()->user()->name; }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div>
            <div class="card bg-light">
                <div class="card-body">
                    <form action="/user/update/{{ auth()->user()->id }}" method="post"
                        onsubmit="submitForm(this, event);">
                        @csrf
                        <input type="hidden" name="_method" value="PUT">
                        <div class="mb-3">
                            <label><small>Name</small></label>
                            <input type="text" class="form-control form-control-sm" name="name"
                                value="{{ auth()->user()->name }}">
                        </div>
                        <div class="mb-3">
                            <label><small>Username</small></label>
                            <input type="text" class="form-control form-control-sm" name="username"
                                value="{{ auth()->user()->username }}">
                        </div>
                        <div class="mb-3">
                            <label><small>New Password</small></label>
                            <input type="password" class="form-control form-control-sm" name="password">
                        </div>
                        <button class="btn btn-sm btn-sb" type="submit">Update</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="mt-3">
            <a class="btn btn-no w-100 rounded-0" href="{{ route('logout') }}"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Logout
            </a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none"
                onsubmit="logout(this, event)">
                @csrf
            </form>
        </div>
    </div>
</div>
