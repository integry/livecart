{if $order.isOrderable}
<div id="checkoutProgress" class="{$progress}">

    <span class="progressOrder">1</span><a href="{link controller=order}" id="progressCart">{t _cart}</a>
    <span class="progressOrder">2</span><a href="{link controller=checkout action=selectAddress}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressAddress">{t _address}</a>
    <span class="progressOrder">3</span><a href="{link controller=checkout action=shipping}" {if $order.isShippingSelected}class="shippingSelected"{/if} id="progressShipping">{t _shipping}</a>
    <span class="progressOrder">4</span><a href="{link controller=checkout action=pay}" id="progressPayment">{t _payment}</a>
                
</div>
{/if}
	   	
<div class="clear"></div>