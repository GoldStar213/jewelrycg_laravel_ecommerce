<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>
    <div class="py-9">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <x-dashboard-side-bar />
                </div>
                <div class="col-lg-9">
                    @if (session('success'))
                    <h4 class="text-center text-primary mt-3">
                        {{session('success')}}
                    </h4>
                    @endif
                    @if (session('error'))
                    <h4 class="text-center text-danger mt-3">
                        {{session('error')}}
                    </h4>
                    @endif
                    <div class="card">
                        <div class="card-header">Transaction History</div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $transaction_names = ['Pending', 'Credit', 'Canceled', 'Chargeback'];
                                        @endphp
                                        @foreach ($transactions as $key=> $transaction)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>$ {{ number_format($transaction->amount/100, 2, ".", ",") }}</td>
                                                <td>{{ $transaction->type == 'add' ? ( $transaction->sale_type == 0 ? "Product Sale" : ($transaction->sale_type == 1 ? "Service Sale" : "Course Sale")) : 'Withdrawn' }}</td>
                                                <td>{{ $transaction->created_at->format('M d, Y') }}</td>
                                                <td>{{ $transaction_names[$transaction->status] }}</td>
                                            </tr>                                
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>    
            </div>
        </div>
    </div>
    <style>
        .blance-title{
            color: rgb(1, 119, 189);
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</x-app-layout>
