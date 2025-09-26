@extends('layouts.admin')

@section('title', 'Invoices')

@section('content')
<div class="page-header d-print-none">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    Invoices
                </h2>
            </div>
        </div>
    </div>
</div>
<div class="page-body">
    <div class="container-xl">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-bordered" id="invoices-table" width="100%">
                    <thead>
                        <tr>
                            <th>Invoice Number</th>
                            <th>Service Order</th>
                            <th>Issue Date</th>
                            <th>Due Date</th>
                            <th>Grand Total</th>
                            <th>Status</th>
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