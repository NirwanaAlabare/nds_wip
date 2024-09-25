<div>
    <div class="loading-container-fullscreen" wire:loading wire:target='transferAll, transferRft, transferDefect, transferReject, fromDate, toDate, fromLine, toLine, fromSelectedMasterPlan, toSelectedMasterPlan, fromMasterPlans, toMasterPlans, fromSoDet, toSoDet, fromMasterPlanOutput, toMasterPlanOutput'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="loading-container-fullscreen hidden" id="loadingOrderOutput">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-header bg-sb">
                    <h5 class="card-title text-light text-center">FROM</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" class="form-select" wire:model="fromDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Line</label>
                        <select class="form-select" wire:model="fromLine">
                            <option value="">Select Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->username }}">{{ $line->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Master Plan</label>
                        <select class="form-select" wire:model="fromSelectedMasterPlan">
                            <option value="">Select Master Plan</option>
                            @foreach ($fromMasterPlans as $fromMasterPlan)
                                <option value="{{ $fromMasterPlan->id }}" {{ $toSelectedMasterPlan == $fromMasterPlan->id ? "disabled" : "" }}>{{ $fromMasterPlan->no_ws." - ".$fromMasterPlan->style." - ".$fromMasterPlan->color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="card bg-rft">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        RFT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->fromMasterPlanOutput ? $this->fromMasterPlanOutput->rft : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-defect">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        DEFECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->fromMasterPlanOutput ? $this->fromMasterPlanOutput->defect : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-rework">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REWORK
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->fromMasterPlanOutput ? $this->fromMasterPlanOutput->rework : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-reject">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REJECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->fromMasterPlanOutput ? $this->fromMasterPlanOutput->reject : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-2">
            <div class="d-flex justify-content-center align-items-center h-100">
                <i class="fa-solid fa-arrow-right fa-5x text-sb"></i>
            </div>
        </div>
        <div class="col-12 col-md-5">
            <div class="card">
                <div class="card-header bg-sb">
                    <h5 class="card-title text-light text-center">TO</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" class="form-select" wire:model="toDate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Line</label>
                        <select class="form-select" wire:model="toLine">
                            <option value="">Select Line</option>
                            @foreach ($lines as $line)
                                <option value="{{ $line->username }}">{{ $line->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Master Plan</label>
                        <select class="form-select" wire:model="toSelectedMasterPlan">
                            <option value="">Select Master Plan</option>
                            @foreach ($toMasterPlans as $toMasterPlan)
                                <option value="{{ $toMasterPlan->id }}" {{ $fromSelectedMasterPlan == $toMasterPlan->id ? "disabled" : "" }}>{{ $toMasterPlan->no_ws." - ".$toMasterPlan->style." - ".$toMasterPlan->color }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <div class="card bg-rft">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        RFT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->toMasterPlanOutput ? $this->toMasterPlanOutput->rft : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-defect">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        DEFECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->toMasterPlanOutput ? $this->toMasterPlanOutput->defect : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-rework">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REWORK
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->toMasterPlanOutput ? $this->toMasterPlanOutput->rework : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-reject">
                                <div class="card-body">
                                    <h5 class="card-title text-light fw-bold">
                                        REJECT
                                    </h5>
                                    <br>
                                    <h5 class="text-light fw-bold">
                                        {{ $this->toMasterPlanOutput ? $this->toMasterPlanOutput->reject : '-' }}
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3 mt-3">
        <div class="col-6 col-md-3">
            <button class="btn btn-sb w-100 h-100 fw-bold" wire:click="transferAll()">TRANSFER ALL <i class="fa-solid fa-arrow-right fa-sm"></i></button>
        </div>
        <div class="col-6 col-md-3">
            <button class="btn btn-rft w-100 h-100 fw-bold" wire:click="transferRft()">TRANSFER RFT <i class="fa-solid fa-arrow-right fa-sm"></i></button>
        </div>
        <div class="col-6 col-md-3">
            <button class="btn btn-defect w-100 h-100 fw-bold" wire:click="transferDefect()">TRANSFER DEFECT/REWORK <i class="fa-solid fa-arrow-right fa-sm"></i></button>
        </div>
        <div class="col-6 col-md-3">
            <button class="btn btn-reject w-100 h-100 fw-bold" wire:click="transferReject()">TRANSFER REJECT <i class="fa-solid fa-arrow-right fa-sm"></i></button>
        </div>
    </div>
</div>
