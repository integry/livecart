{pageTitle}{t _invoice} #{$order.ID}{/pageTitle}
<div class="userOrderInvoice">

{* include file="layout/frontend/header.tpl" *}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

{defun name="address"}
{if $address}
    <p>
        {$address.fullName}                
    </p>
    <p>
        {$address.companyName}                
    </p>
    <p>
        {$address.address1}
    </p>
    <p>
        {$address.address2}
    </p>
    <p>
        {$address.city}
    </p>
    <p>
        {if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
    </p>
    <p>
        {$address.countryName}
    </p>
{/if}
{/defun}  

<div id="content" class="left right">

    <div id="invoice">
    
        <div style="text-align: center; border-bottom: 1px solid black; position: relative; margin-top: 20px;">
        
            {img src="image/promo/logo_small.jpg" style="position: absolute; left: 0; top: 0;"}
            
            <h1 style="padding-top: 20px;">{t _invoice} #{$order.ID}</h1>
            <div id="invoiceDate">{$order.formatted_dateCompleted.date_long}</div>
        
        </div>
    
        <div id="invoiceContacts">
        
            <div style="width: 50%; float: left;">
                <h2>{t _buyer}</h2>
                {fun name="address" address=$order.BillingAddress}
            </div>

            <div style="width: 50%; float: left;">
                <h2>{t _seller}</h2>
                <p>
                    Seller's contact information goes here
                </p>
            </div>
        
        </div>
    
        <div class="clear"></div>
    
    	{foreach from=$order.shipments item="shipment" name="shipments"}
    	   
    	    {if $shipment.items}
    	   
            {if !$shipment.isShippable}
                <h2>{t _downloads}</h2>        
            {else}
                <h2>{t _shipment} #{$smarty.foreach.shipments.iteration}</h2>
            {/if}
    	
            <table class="table shipment">
            
                <thead>
                    <tr>
                        <th class="productName">{t _product}</th>
                        <th>{t _price}</th>
                        <th>{t _quantity}</th>
                        <th>{t _subtotal}</th>
                    </tr>                            
                </thead>
                
                <tbody>
                            
                    {foreach from=$shipment.items item="item" name="shipment"}
                        <tr{zebra loop="shipment"}>                    
                            <td class="productName">{$item.Product.name_lang}</td>
                            <td>{$item.formattedPrice}</td>
                            <td>{$item.count}</td>
                            <td class="amount">{$item.formattedSubTotal}</td>
                        </tr>
                    {/foreach}            
                    
                    {if $order.isShippingRequired && $shipment.isShippable && $shipment.selectedRate.formattedPrice[$order.Currency.ID]}
                        <tr>
                            <td colspan="3" class="subTotalCaption">
                                {t _shipping} ({$shipment.ShippingService.name_lang}):
                            </td>
                            <td class="amount">
                                {$shipment.selectedRate.formattedPrice[$order.Currency.ID]}
                            </td>
                        </tr>
                    {/if}
                            
                    {foreach from=$shipment.taxes item="tax"}
                        <tr>                    
                            <td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang}:</td>
                            <td class="amount">{$tax.formattedAmount[$order.Currency.ID]}</td>
                        </tr>
                    {/foreach}        
                      
                    {if $shipment.isShippable}
                    <tr>
                        <td colspan="3" class="subTotalCaption">
                            {t _shipment_total}:
                        </td>
                        <td class="amount subTotal">{$shipment.formatted_totalAmount}</td>
                    </tr>
                    {/if}
                                            
                </tbody>
            
            </table>
            
            {/if}
    	
    	{/foreach}    
    	
    	<h2>{t _payment_info}</h2>
    	
    	<table id="invoicePaymentInfo">    	
            <tr class="itemSubtotal">
                <td>{t _item_subtotal}:</td>
                <td class="amount">{$order.formatted_itemSubtotal}</td>
            </tr>
            <tr class="shippingSubtotal">
                <td>{t _shipping_handling}:</td>
                <td class="amount">{$order.formatted_shippingSubtotal}</td>
            </tr>
            {if $order.taxes}
                <tr class="beforeTaxSubtotal">
                    <td>{t _before_tax}:</td>
                    <td class="amount">{$order.formatted_subtotalBeforeTaxes}</td>
                </tr>
                {foreach from=$order.taxes[$order.Currency.ID] item=tax}
                    <tr class="taxSubtotal">
                        <td>{$tax.name_lang}:</td>
                        <td class="amount">{$tax.formattedAmount}</td>
                    </tr>
                {/foreach}
            {/if}
            <tr class="grandTotal">
                <td>{t _grand_total}:</td>
                <td class="amount">{$order.formatted_totalAmount}</td>
            </tr>
            <tr class="amountPaid">
                <td>{t _amount_paid}:</td>
                <td class="amount">{$order.formatted_amountPaid}</td>
            </tr>
            <tr class="amountDue">
                <td>{t _amount_due}:</td>
                <td class="amount">{$order.formatted_amountDue}</td>
            </tr>    	
    	</table>
    
    </div>    

</div>

{* include file="layout/frontend/footer.tpl" *}

</div>

<script type="text/javascript">
{*    window.print(); *}
</script>