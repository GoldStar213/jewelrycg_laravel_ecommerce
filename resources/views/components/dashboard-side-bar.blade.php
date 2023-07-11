<div class="card d-none d-sm-block">
    <div class="card-body">
        <div class="seller-side-nav">
            <a href="{{ route('seller.dashboard') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.dashboard' ? "active" : "" }}">Dashboard</a>
            <a href="{{ route('seller.services.list') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.services.list' ? "active" : "" }}">Services</a>
            <a href="{{ route('seller.service.orders') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.service.orders' ? "active" : "" }}">Orders</a>
            <a href="{{ route('seller.withdraw.get') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.withdraw.get' ? "active" : "" }}">Withdraw</a>
            <a href="{{ route('seller.transaction.history') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.transaction.history' ? "active" : "" }}">Wallet History</a>
            <a href="{{ route('seller.payment.details') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.payment.details' ? "active" : "" }}">Payment Details</a>
            <a href="{{ route('seller.profile') }}" class="w-100 d-block mb-2 {{ \Route::currentRouteName() == 'seller.profile' ? "active" : "" }}">Profile</a>
        </div>
    </div>
</div>
