<style type="text/css">
    form.unitpay-form .btn_order {
        background-color: #FFF;
        border: solid #CCC 1px;
        width: 450px;
        text-align: left;
        padding-left: 20px;
        margin-right: 20px;
        height: 74px;
    }
    form.unitpay-form .btn_order img {
        margin-right: 10px;
    }

</style>

<div class="well">

    @if (config('unitpay.payment_forms')['cards'])
        <div class="form-group ">
            <form action="https://unitpay.ru/pay/{{ $payment_fields['PUB_KEY'] }}/card" method="POST" class="form unitpay-form">
                <input type="hidden" name="sum" value="{{ $payment_fields['PAYMENT_AMOUNT'] }}">
                <input type="hidden" name="account" value="{{ $payment_fields['PAYMENT_NO'] }}">
                <input type="hidden" name="desc" value="{{ $payment_fields['ITEM_NAME'] }}">
                <input type="hidden" name="currency" value="{{ $payment_fields['CURRENCY'] }}">
                <input type="hidden" name="locale" value="{{ $payment_fields['LOCALE'] }}">
                <input type="hidden" name="signature" value="{{ $payment_fields['SIGN'] }}">
                <input type="hidden" name="hideOtherMethods" value="{{ config('unitpay.hideOtherMethods','false') }}">
                <button type="submit" class="btn btn_order">Оплатить картой российского банка</button>
            </form>
        </div>
    @endif

    @if (config('unitpay.payment_forms')['yandex'])
        <div class="form-group pull-left">
            <form action="https://unitpay.ru/pay/{{ $payment_fields['PUB_KEY'] }}/yandex" method="POST" class="form unitpay-form">
                <input type="hidden" name="sum" value="{{ $payment_fields['PAYMENT_AMOUNT'] }}">
                <input type="hidden" name="account" value="{{ $payment_fields['PAYMENT_NO'] }}">
                <input type="hidden" name="desc" value="{{ $payment_fields['ITEM_NAME'] }}">
                <input type="hidden" name="currency" value="{{ $payment_fields['CURRENCY'] }}">
                <input type="hidden" name="locale" value="{{ $payment_fields['LOCALE'] }}">
                <input type="hidden" name="signature" value="{{ $payment_fields['SIGN'] }}">
                <input type="hidden" name="hideOtherMethods" value="{{ config('unitpay.hideOtherMethods','false') }}">
                <button type="submit" class="btn btn_order btn_small">Yandex</button>
            </form>
        </div>
    @endif

    @if (config('unitpay.payment_forms')['qiwi'])
        <div class="form-group">
            <form action="https://unitpay.ru/pay/{{ $payment_fields['PUB_KEY'] }}/qiwi" method="POST" class="form unitpay-form">
                <input type="hidden" name="sum" value="{{ $payment_fields['PAYMENT_AMOUNT'] }}">
                <input type="hidden" name="account" value="{{ $payment_fields['PAYMENT_NO'] }}">
                <input type="hidden" name="desc" value="{{ $payment_fields['ITEM_NAME'] }}">
                <input type="hidden" name="currency" value="{{ $payment_fields['CURRENCY'] }}">
                <input type="hidden" name="locale" value="{{ $payment_fields['LOCALE'] }}">
                <input type="hidden" name="signature" value="{{ $payment_fields['SIGN'] }}">
                <input type="hidden" name="hideOtherMethods" value="{{ config('unitpay.hideOtherMethods','false') }}">
                <button type="submit" class="btn btn_order btn_small">QIWI</button>
            </form>
        </div>
    @endif

    @if (config('unitpay.payment_forms')['cash'])
        <div class="form-group">
            <form action="https://unitpay.ru/pay/{{ $payment_fields['PUB_KEY'] }}/cash" method="POST" class="form unitpay-form">
                <input type="hidden" name="sum" value="{{ $payment_fields['PAYMENT_AMOUNT'] }}">
                <input type="hidden" name="account" value="{{ $payment_fields['PAYMENT_NO'] }}">
                <input type="hidden" name="desc" value="{{ $payment_fields['ITEM_NAME'] }}">
                <input type="hidden" name="currency" value="{{ $payment_fields['CURRENCY'] }}">
                <input type="hidden" name="locale" value="{{ $payment_fields['LOCALE'] }}">
                <input type="hidden" name="signature" value="{{ $payment_fields['SIGN'] }}">
                <input type="hidden" name="hideOtherMethods" value="{{ config('unitpay.hideOtherMethods','false') }}">
                <button type="submit" class="btn btn_order">Наличными через Евросеть, Связной.</button>
            </form>
        </div>
    @endif

    @if (config('unitpay.payment_forms')['webmoney'])
        <div class="form-group">
            <form action="https://unitpay.ru/pay/{{ $payment_fields['PUB_KEY'] }}/webmoney" method="POST" class="form unitpay-form">
                <input type="hidden" name="sum" value="{{ $payment_fields['PAYMENT_AMOUNT'] }}">
                <input type="hidden" name="account" value="{{ $payment_fields['PAYMENT_NO'] }}">
                <input type="hidden" name="desc" value="{{ $payment_fields['ITEM_NAME'] }}">
                <input type="hidden" name="currency" value="{{ $payment_fields['CURRENCY'] }}">
                <input type="hidden" name="locale" value="{{ $payment_fields['LOCALE'] }}">
                <input type="hidden" name="signature" value="{{ $payment_fields['SIGN'] }}">
                <input type="hidden" name="hideOtherMethods" value="{{ config('unitpay.hideOtherMethods','false') }}">
                <button type="submit" class="btn btn_order">WebMoney</button>
            </form>
        </div>
    @endif
</div>
