{loadJs form=true}
{pageTitle}{t _order_checkout}{/pageTitle}

<div class="userCheckout">

{include file="checkout/layout.tpl"}

<div id="content" class="left right">

	<h1>{t _order_checkout}</h1>

	<h2>{t _returning}</h2>

	<p>
		{t _please_login}
	</p>

	{capture assign="return"}{link controller=checkout action=selectAddress}{/capture}
	{include file="user/loginForm.tpl" return=$return}

	<h2>{t _new_cust}</h2>

	{form handle=$form action="controller=user action=processCheckoutRegistration" method="POST"}

		<h3>{t _contact_info}</h3>

			<p class="required">
				{err for="firstName"}
					{{label {t _your_first_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p class="required">
				{err for="lastName"}
					{{label {t _your_last_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p>
				{err for="companyName"}
					{{label {t _company_name}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p class="required">
				{err for="email"}
					{{label {t _your_email}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p{if $form|isRequired:"phone"} class="required"{/if}>
				{err for="phone"}
					{{label {t _your_phone}:}}
					{textfield class="text"}
				{/err}
			</p>

			{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}

		<h3>{t _billing_address}</h3>

			<p class="required">
				{err for="billing_address1"}
					{{label {t _address}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p>
				<label></label>
				{textfield name="billing_address_2" class="text"}
			</p>

			<p class="required">
				{err for="billing_city"}
					{{label {t _city}:}}
					{textfield class="text"}
				{/err}
			</p>

			<p class="required">
				{err for="billing_country"}
					{{label {t _country}:}}
					{selectfield options=$countries id="billing_country"}
					<span class="progressIndicator" style="display: none;"></span>
				{/err}
			</p>

			<p class="required">
				{err for="billing_state_select"}
					{{label {t _state}:}}
					{selectfield style="display: none;" options=$states id="billing_state_select"}
					{textfield name="billing_state_text" class="text" id="billing_state_text"}
				{/err}

				{literal}
				<script type="text/javascript">
				{/literal}
					new User.StateSwitcher($('billing_country'), $('billing_state_select'), $('billing_state_text'),
							'{link controller=user action=states}');
				</script>
			</p>

			<p class="required">
				{err for="billing_zip"}
					{{label {t _postal_code}:}}
					{textfield class="text"}
				{/err}
			</p>

		<h3>{t _shipping_address}</h3>

			<p>
				{checkbox name="sameAsBilling" checked="checked" class="checkbox"}
				<label for="sameAsBilling" class="checkbox">{t _the_same_as_shipping_address}</label>
			</p>

			<div id="shippingForm">

				<p class="required">
					{err for="shipping_address1"}
						{{label {t _address}:}}
						{textfield class="text"}
					{/err}
				</p>

				<p>
					<label for="shipping_address_2"></label>
					{textfield name="shipping_address_2" class="text"}
				</p>

				<p class="required">
					{err for="shipping_city"}
						{{label {t _city}:}}
						{textfield class="text"}
					{/err}
				</p>

				<p class="required">
					{err for="shipping_country"}
						{{label {t _country}:}}
						{selectfield options=$countries id="shipping_country"}
						<span class="progressIndicator" style="display: none;"></span>
					{/err}
				</p>

				<p class="required">
					{err for="shipping_state_select"}
						{{label {t _state}:}}
						{selectfield style="display: none;" options=$states id="shipping_state_select"}
						{textfield name="shipping_state_text" class="text" id="shipping_state_text"}
					{/err}

					{literal}
					<script type="text/javascript">
					{/literal}
						new User.StateSwitcher($('shipping_country'), $('shipping_state_select'), $('shipping_state_text'),
								'{link controller=user action=states}');
						new User.ShippingFormToggler($('sameAsBilling'), $('shippingForm'));
					</script>
				</p>

				<p class="required">
					{err for="shipping_zip"}
						{{label {t _postal_code}:}}
						{textfield class="text"}
					{/err}
				</p>

			</div>

			<p>
				<input type="submit" class="submit" value="{tn _continue}" />
			</p>

	{/form}

</div>

{include file="layout/frontend/footer.tpl"}

</div>