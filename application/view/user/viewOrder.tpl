<div class="userViewOrder">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">

    <h1>{t _view_order} {$order.ID} ({$order.formatted_dateCompleted.date_long})</h1>
    
	{include file="user/userMenu.tpl" current="ordersMenu"}    
    
    <div id="userContent">
    
        <fieldset class="container">
    
        <label class="title">{t Order ID}:</label>
        <label>{$order.ID}</label>
        <div class="clear"></div>       
    
        <label class="title">{t Order placed}:</label>
        <label>{$order.formatted_dateCompleted.date_long}</label>
        <div class="clear"></div>   
    
        <label class="title">{t Order total}:</label>
        <label>{$order.formattedTotal[$order.Currency.ID]}</label>
        <div class="clear"></div>   
    
    	{foreach from=$order.shipments item="shipment" name="shipments"}
    	   
            {if !$shipment.isShippable}
                <h2>{t _downloads}</h2>        
            {elseif $smarty.foreach.shipments.total > 1}
                <h2>{t Shipment} #{$smarty.foreach.shipments.iteration}</h2>        
            {else}
                <h2>{t _ordered_products}</h2>
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
                            
                    {include file="order/orderTableDetails.tpl"}
                            
                    {foreach from=$shipment.taxes item="tax"}
                        <tr>                    
                            <td colspan="3" class="tax">{$tax.TaxRate.Tax.name_lang}:</td>
                            <td>{$tax.formattedAmount[$order.Currency.ID]}</td>
                        </tr>
                    {/foreach}        
                      
                    <tr>
                        <td colspan="3" class="subTotalCaption">
                            {if $smarty.foreach.shipments.total > 1}
                                {t _shipment_total}:
                            {else}
                                {t _order_total}:                        
                            {/if}
                        </td>
                        <td class="subTotal">{$shipment.formattedSubTotal[$order.Currency.ID]}</td>                        
                    </tr>
                                            
                </tbody>
            
            </table>
    	
    	{/foreach}
    	
    	</fieldset>
	
	</div>

</div>

{include file="layout/frontend/footer.tpl"}    

</div>