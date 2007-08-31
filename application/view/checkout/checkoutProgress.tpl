<div id="checkoutProgress" class="{$progress}">

    <span class="progressOrder">1</span><a href="{link controller=order}" id="progressCart">Cart</a>
    <span class="progressOrder">2</span><a href="{link controller=checkout action=selectAddress}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressAddress">Address</a>
    <span class="progressOrder">3</span><a href="{link controller=checkout action=shipping}" {if $order.isShippingSelected}class="shippingSelected"{/if} id="progressShipping">Shipping</a>
    <span class="progressOrder">4</span><a href="{link controller=checkout action=pay}" id="progressPayment">Payment</a>
                
</div>
	   	
<div class="clear"></div>