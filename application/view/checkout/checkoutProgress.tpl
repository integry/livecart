{literal}
	<style>
        #checkoutProgress
        {
            width: auto;
            text-align: center;
            padding: 20px;
        }

        #checkoutProgress a
        {
            padding: 10px;
            border: 3px solid #ccc;
            background-color: #eee;
            margin-right: 20px;
        }

        /* completed steps */
        #checkoutProgress a.completed, 
        .progressPayment a,
        #progressCart,
        .progressShipping #progressAddress,
        .shippingSelected,
        .addressSelected
        {
            border: 3px solid lightgreen !important;            
            background-color: #FFFCAC !important;
        }

        /* current step */
        .progressPayment #progressPayment,
        .progressCart #progressCart,
        .progressAddress #progressAddress,
        .progressShipping #progressShipping        
        {
            border: 3px solid lightblue !important;
            background-color: yellow !important;
        }

        .progressOrder
        {
            font-size: smaller;
            margin-right: 5px;
        }
	</style>	
	{/literal}	
	
    <div id="checkoutProgress" class="{$progress}" style="float: right;">
    
        <span class="progressOrder">1</span><a href="{link controller=order}" id="progressCart">Cart</a>
        <span class="progressOrder">2</span><a href="{link controller=checkout action=selectAddress}" {if $order.isAddressSelected}class="addressSelected"{/if} id="progressAddress">Address</a>
        <span class="progressOrder">3</span><a href="{link controller=checkout action=shipping}" {if $order.isShippingSelected}class="shippingSelected"{/if} id="progressShipping">Shipping</a>
        <span class="progressOrder">4</span><a href="{link controller=checkout action=pay}" id="progressPayment">Payment</a>
                    
    </div>
    	   	
    <div class="clear"></div>