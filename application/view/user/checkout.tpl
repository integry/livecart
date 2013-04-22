{assign var="fields" value='USER_FIELDS'|config}

{loadJs form=true}
{if $request.action == 'checkout'}
	{pageTitle}{t _order_checkout}{/pageTitle}
{else}
	{pageTitle}{t _user_registration}{/pageTitle}
{/if}

{include file="checkout/layout.tpl"}
{include file="block/content-start.tpl"}

	<div class="returningCustomer">
		<h2>{t _returning}</h2>

		{if $request.action == 'checkout'}
		<p>
			{t _please_login}
		</p>
		{/if}

		{capture assign="return"}{link controller=checkout action=selectAddress}{/capture}
		{include file="user/loginForm.tpl" return=$return}
	</div>

	<div class="newCustomer">
		<h2>{t _new_cust}</h2>

		{form handle=$form action="controller=user action=processCheckoutRegistration" method="POST" class="form-horizontal"}

			{if !'REQUIRE_SAME_ADDRESS'|config}
				<h3>{t _contact_info}</h3>
			{/if}

			{include file="user/block/registerAddress.tpl" prefix="billing_" showHeading=true}

			{if $order.isShippingRequired && !'REQUIRE_SAME_ADDRESS'|config}
			<h3>{t _shipping_address}</h3>

				{input name="sameAsBilling"}
					{checkbox checked="checked"}
					{label}{t _the_same_as_shipping_address}{/label}
				{/input}

				<div id="shippingForm">
					{include file="user/addressForm.tpl" prefix="shipping_" eavPrefix="shipping_" states=$shippingStates}
				</div>

			{/if}

			{block FORM-SUBMIT-REGISTER-CHECKOUT}

			{hidden name="return"}
			{hidden name="regType"}

			{include file="block/submit.tpl" caption="_continue"}
		{/form}
	</div>
	<div class="clear"></div>

{include file="block/content-stop.tpl"}

{literal}
<script type="text/javascript">
	new User.ShippingFormToggler($('sameAsBilling'), $('shippingForm'));
</script>
{/literal}

{include file="layout/frontend/footer.tpl"}