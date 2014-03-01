{% extends "layout/frontend.tpl" %}

{assign var="fields" value=config('USER_FIELDS')}

{% if req('action') == 'checkout' %}
	{% title %}{t _order_checkout}{% endblock %}
{% else %}
	{% title %}{t _user_registration}{% endblock %}
{% endif %}

[[ partial("checkout/layout.tpl") ]]
{% block content %}

	<div class="returningCustomer">
		<h2>{t _returning}</h2>

		{% if req('action') == 'checkout' %}
		<p>
			{t _please_login}
		</p>
		{% endif %}

		{capture assign="return"}[[ url("checkout/selectAddress") ]]{/capture}
		[[ partial('user/loginForm.tpl', ['return': return]) ]]
	</div>

	<div class="newCustomer">
		<h2>{t _new_cust}</h2>

		{form handle=form action="user/processCheckoutRegistration" method="POST" class="form-horizontal"}

			{% if !config('REQUIRE_SAME_ADDRESS') %}
				<h3>{t _contact_info}</h3>
			{% endif %}

			[[ partial('user/block/registerAddress.tpl', ['prefix': "billing_", 'showHeading': true]) ]]

			{% if order.isShippingRequired && !config('REQUIRE_SAME_ADDRESS') %}
			<h3>{t _shipping_address}</h3>

				{input name="sameAsBilling"}
					{checkbox checked="checked"}
					{label}{t _the_same_as_shipping_address}{/label}
				{/input}

				<div id="shippingForm">
					[[ partial('user/addressForm.tpl', ['prefix': "shipping_", 'eavPrefix': "shipping_", 'states': shippingStates]) ]]
				</div>

			{% endif %}

			{block FORM-SUBMIT-REGISTER-CHECKOUT}

			{hidden name="return"}
			{hidden name="regType"}

			[[ partial('block/submit.tpl', ['caption': "_continue"]) ]]
		{/form}
	</div>
	<div class="clear"></div>

{% endblock %}


<script type="text/javascript">
	new User.ShippingFormToggler(('sameAsBilling'), ('shippingForm'));
</script>


