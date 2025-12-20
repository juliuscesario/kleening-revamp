@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <h2 class="page-title">
                        Payments
                    </h2>
                </div>
            </div>
        </div>
    </div>
    <div class="page-body">
        <div class="container-xl">
            <!-- Summary Cards -->
            <div class="row row-deck row-cards mb-3">
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Revenue</div>
                            </div>
                            <div class="h1 mb-3" id="summary-total-revenue">Rp 0</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Total Transactions</div>
                            </div>
                            <div class="h1 mb-3" id="summary-total-transactions">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="subheader">Average Transaction</div>
                            </div>
                            <div class="h1 mb-3" id="summary-avg-transaction">Rp 0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="row g-3 w-100 align-items-start">
                        <div class="col-lg-7">
                            <p class="mb-2 fw-bold">Filter By Date</p>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <input type="text" id="filter-start-date"
                                        class="form-control form-control-sm js-filter-date" placeholder="From (dd/mm/yyyy)"
                                        inputmode="numeric">
                                </div>
                                <div class="col-md-6">
                                    <input type="text" id="filter-end-date"
                                        class="form-control form-control-sm js-filter-date" placeholder="To (dd/mm/yyyy)"
                                        inputmode="numeric">
                                </div>
                            </div>
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-primary btn-sm" id="apply-filter">Apply</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                    id="reset-filter">Reset</button>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <p class="mb-2 fw-bold">Filter by Payment Method</p>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-primary btn-sm filter-method-btn" data-method="all">All</button>
                                <button class="btn btn-outline-primary btn-sm filter-method-btn"
                                    data-method="bank_transfer">Transfer</button>
                                <button class="btn btn-outline-primary btn-sm filter-method-btn"
                                    data-method="cash">Cash</button>
                                <button class="btn btn-outline-primary btn-sm filter-method-btn"
                                    data-method="qr_payment">QRIS</button>
                                <button class="btn btn-outline-primary btn-sm filter-method-btn"
                                    data-method="virtual_account">VA</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-bordered" id="payments-table" width="100%">
                        <thead>
                            <tr>
                                <th>Invoice Number</th>
                                <th>Payment Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection