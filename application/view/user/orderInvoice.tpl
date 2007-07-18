{pageTitle}{t _invoice} #{$order.ID}{/pageTitle}
<div class="userOrderInvoice">

{* include file="layout/frontend/header.tpl" *}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <div id="invoice">
    
        <div style="text-align: center; border-bottom: 1px solid black; position: relative; margin-top: 20px;">
        
            <img src="image/promo/logo_small.jpg" style="position: absolute; left: 0; top: 0;" />
            
            <h1 style="padding-top: 20px;">{t Invoice} #{$order.ID}</h1>
            <div id="invoiceDate">{$order.formatted_dateCompleted.date_long}</div>
        
        </div>
    
        <div id="invoiceContacts">
        
            <div style="width: 50%; float: left;">
                <h2>{t Buyer}</h2>
                <p>
                    {$order.BillingAddress.fullName}                
                </p>
                <p>
                    {$order.BillingAddress.companyName}                
                </p>
                <p>
                    {$order.BillingAddress.address1}
                </p>
                <p>
                    {$order.BillingAddress.address2}
                </p>
                <p>
                    {$order.BillingAddress.city}
                </p>
                <p>
                    {$order.BillingAddress.stateName}, {$order.BillingAddress.postalCode}
                </p>
                <p>
                    {$order.BillingAddress.countryName}
                </p>
            </div>

            <div style="width: 50%; float: left;">
                <h2>{t Seller}</h2>
                <p>
                    Seller's contact information goes here
                </p>
            </div>
        
        </div>
    
        <div class="clear"></div>
    
    	{foreach from=$order.shipments item="shipment" name="shipments"}
    	   
            {if !$shipment.isShippable}
                <h2>{t _downloads}</h2>        
            {else}
                <h2>{t Shipment} #{$smarty.foreach.shipments.iteration}</h2>        
            {/if}
    	
            <table class="table shipment">
            
                <thead>
                    <tr>
                        <th class="productName">Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>                            
                </thead>
                
                <tbody>
                            
                    {foreach from=$shipment.items item="item" name="shipment"}
                        <tr{zebra loop="shipment"}>                    
                            <td class="productName">{$item.Product.name_lang}</td>
                            <td>{$item.Product.formattedPrice[$order.Currency.ID]}</td>
                            <td>{$item.count}</td>
                            <td class="amount">{$item.formattedSubTotal[$order.Currency.ID]}</td>
                        </tr>
                    {/foreach}            
                    
                    {if $order.isShippingRequired && $shipment.isShippable}
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
                      
                    <tr>
                        <td colspan="3" class="subTotalCaption">
                            {t _shipment_total}:
                        </td>
                        <td class="amount subTotal">{$shipment.formattedSubTotal[$order.Currency.ID]}</td>
                    </tr>
                                            
                </tbody>
            
            </table>
    	
    	{/foreach}    
    	
    	<h2>{t Payment Information}</h2>
    	
    	<table id="invoicePaymentInfo">    	
            <tr class="itemSubtotal">
                <td>{t Item(s) Subtotal}:</td>
                <td class="amount">{$order.formatted_itemSubtotal}</td>
            </tr>
            <tr class="shippingSubtotal">
                <td>{t Shipping & Handling}:</td>
                <td class="amount">{$order.formatted_shippingSubtotal}</td>
            </tr>
            {if $order.taxes}
                <tr class="beforeTaxSubtotal">
                    <td>{t Total Before Tax}:</td>
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
                <td>{t Grand Total}:</td>
                <td class="amount">{$order.formattedTotal[$order.Currency.ID]}</td>
            </tr>
            <tr class="amountPaid">
                <td>{t Amount Paid}:</td>
                <td class="amount">{$order.formatted_amountPaid}</td>
            </tr>
            <tr class="amountDue">
                <td>{t Amount Due}:</td>
                <td class="amount">{$order.formatted_amountDue}</td>
            </tr>    	
    	</table>
    	
    	{*$order|@var_dump*}
    
    </div>    

</div>

{* include file="layout/frontend/footer.tpl" *}

</div>

<script type="text/javascript">
{*    window.print(); *}
</script>