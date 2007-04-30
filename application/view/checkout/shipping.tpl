{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _shipping}</h1>
	
	{if $shipments|@count > 1}
		<div class="message">
			{t _info_multi_shipments}
		</div>
	{/if}
	
    {form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form}
        {foreach from=$shipments key="key" item="shipment"}
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
                            <td class="productName"><a href="{productUrl product=$item.Product}">{$item.Product.name_lang}</a></td>
                            <td>{$item.Product.formattedPrice.$currency}</td>
                            <td>{$item.count}</td>
                            <td>{$item.formattedSubTotal.$currency}</td>
                        </tr>
                    {/foreach}            
                
                    <tr>
                        <td colspan="3" class="subTotalCaption">{t _subtotal}:</td>
                        <td class="subTotal">{$shipment.formattedSubTotal.$currency}</td>                        
                    </tr>
                </tbody>        
            </table>    
        
            <p>
                Select shipping method:
            </p>
            
            <div class="shippingMethods">
            
                {foreach from=$rates.$key item="rate"}                
                    <p>
                        {radio name="shipping_`$key`" id="shipping_`$key`_`$rate.serviceID`" value=$rate.serviceID class="radio"}
                        <label for="shipping_{$key}_{$rate.serviceID}">
                            {$rate.serviceName}
                            {$rate.formattedPrice.$currency}
                        </label>
                    </p>            
                {/foreach}
                
        		<div class="errorText hidden{error for="shipping_`$key`"} visible{/error}" style="clear: both;">
					<div>{error for="shipping_`$key`"}{$msg}{/error}</div>
                	<div class="clear"></div>
                </div>            
                
            </div>                
        
            <div class="clear"></div>
            
        {/foreach}
    
    <input type="submit" class="submit" value="{tn _continue}" />
    
    {/form}

</div>

{include file="layout/frontend/footer.tpl"}