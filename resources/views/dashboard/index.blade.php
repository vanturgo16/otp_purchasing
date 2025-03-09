@extends('layouts.master')

@section('konten')

<div class="page-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Dashboard</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Dashboard</a></li>
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        @include('layouts.alert')
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row justify-content-center mt-3">
                            <div class="col-xl-5 col-lg-8">
                                <div class="text-center">
                                    <h5>Welcome to the "Purchasing Dashboard"</h5>
                                    <p class="text-muted">Here you are able to Manage & Monitoring <b>Purchasing Data</b> on the PT Olefina Tifaplas Polikemindo system</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Purchase Requisition (PR)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Request / Un Post</span>
                                        <h4 class="mb-3">
                                            <span class="counter-value" data-target="{{ $totalPRReq }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPRReqToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-info mb-3 lh-1 d-block text-truncate">Posted / Created PO</span>
                                        <h4 class="mb-3">
                                            <span class="text-info counter-value" data-target="{{ $totalPRPosted }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPRPostedToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-success mb-3 lh-1 d-block text-truncate">Closed</span>
                                        <h4 class="mb-3">
                                            <span class="text-success counter-value" data-target="{{ $totalPRClosed }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPRClosedToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="fw-bold text-primary mb-3 lh-1 d-block text-truncate">Total</span>
                                        <h4 class="mb-3">
                                            <span class="text-primary counter-value" data-target="{{ $totalPR }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPRToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Purchase Order (PO)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-muted mb-3 lh-1 d-block text-truncate">Request / Un Post</span>
                                        <h4 class="mb-3">
                                            <span class="counter-value" data-target="{{ $totalPOReq }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPOReqToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-info mb-3 lh-1 d-block text-truncate">Posted</span>
                                        <h4 class="mb-3">
                                            <span class="text-info counter-value" data-target="{{ $totalPOPosted }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPOPostedToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="text-success mb-3 lh-1 d-block text-truncate">Closed</span>
                                        <h4 class="mb-3">
                                            <span class="text-success counter-value" data-target="{{ $totalPOClosed }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPOClosedToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="card card-h-100">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-12">
                                        <span class="fw-bold text-primary mb-3 lh-1 d-block text-truncate">Total</span>
                                        <h4 class="mb-3">
                                            <span class="text-primary counter-value" data-target="{{ $totalPO }}">0</span>
                                        </h4>
                                    </div>
                                </div>
                                <div class="text-nowrap">
                                    <span class="badge bg-success-subtle text-success">+{{ $totalPOToday }}</span>
                                    <span class="ms-1 text-muted font-size-13">Hari Ini</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

@endsection