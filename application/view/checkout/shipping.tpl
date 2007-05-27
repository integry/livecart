<div class="checkoutShipping">

{loadJs form=true}
{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right checkoutShipping">
	
	<h1>{t _shipping}</h1>
	
	{if $shipments|@count > 1}
		<div class="message">
			{t _info_multi_shipments}
		</div>
	{/if}
	
    {form action="controller=checkout action=doSelectShippingMethod" method="POST" handle=$form}
        {foreach from=$shipments key="key" item="shipment"}
            
			{include file="checkout/shipmentProductList.tpl"}
			
			{include file="checkout/shipmentSelectShipping.tpl"}
                   
        {/foreach}
    
    <input type="submit" class="submit" value="{tn _continue}" />
    
    {/form}

</div>

{include file="layout/frontend/footer.tpl"}

</div>