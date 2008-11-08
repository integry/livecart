<div class="userAddresses">

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="addressMenu"}
<div id="userContent">

	<h1>{t _manage_addresses}</h1>

	<fieldset class="container">

	<h2 id="billingAddresses">{t _billing_addresses}</h2>

	<a href="{link controller=user action=addBillingAddress returnPath=true}" class="menu">
		{t _add_billing_address}
	</a>

	<table class="addressSelector">
	{foreach from=$billingAddresses item="item"}
		{include file="user/address.tpl"}
		<div class="addressControl">
			<a href="{link controller=user action=editBillingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
			|
			<a href="{link controller=user action=deleteBillingAddress id=$item.ID returnPath=true}">{t _remove_address}</a>
		</div>
	{/foreach}
	</table>

	<div style="clear: both;"></div>

	<h2 id="shippingAddresses">{t _shipping_addresses}</h2>

	<a href="{link controller=user action=addShippingAddress returnPath=true}" class="menu">
		{t _add_shipping_address}
	</a>

	{foreach from=$shippingAddresses item="item"}
		{include file="user/address.tpl"}
		<div class="addressControl">
			<a href="{link controller=user action=editShippingAddress id=$item.ID returnPath=true}">{t _edit_address}</a>
			|
			<a href="{link controller=user action=deleteShippingAddress id=$item.ID returnPath=true}">{t _remove_address}</a>
		</div>
	{/foreach}

	</fieldset>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>