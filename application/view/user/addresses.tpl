<div class="userAddresses">

{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _manage_addresses}</h1>
	
	{include file="user/userMenu.tpl" current="addressMenu"}
	
    <h2>{t _billing_addresses}</h2>

    <a href="{link controller=user action=addBillingAddress returnPath=true}" class="menu">
        {t _add_billing_address}
    </a>

    <table class="addressSelector">
	{foreach from=$billingAddresses item="item"}
        {include file="user/address.tpl"} 
        <a href="{link controller=user action=editBillingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
        |
        <a href="{link controller=user action=deleteBillingAddress id=$item.ID returnPath=true}">{t _remove_address}</a>
	{/foreach}
	</table>	
    
    <div style="clear: both;"></div>
    
    <h2>{t _shipping_addresses}</h2>

    <a href="{link controller=user action=addShippingAddress returnPath=true}" class="menu">
        {t _add_shipping_address}
    </a>

	{foreach from=$shippingAddresses item="item"}
        {include file="user/address.tpl"} 
        <a href="{link controller=user action=editShippingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
        |
        <a href="{link controller=user action=deleteShippingAddress id=$item.ID returnPath=true}">{t _remove_address}</a>
	{/foreach}
    
</div>

{include file="layout/frontend/footer.tpl"}

</div>