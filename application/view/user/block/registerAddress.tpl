{assign var="fields" value='USER_FIELDS'|config}

{block FORM-NEW-CUSTOMER-TOP}

{if $fields.FIRSTNAME}
<p class="required">
	{err for="`$prefix`firstName"}
		{{label {t _your_first_name}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.LASTNAME}
<p class="required">
	{err for="`$prefix`lastName"}
		{{label {t _your_last_name}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.COMPANYNAME}
<p>
	{err for="`$prefix`companyName"}
		{{label {t _company_name}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

<p class="required">
	{err for="email"}
		{{label {t _your_email}:}}
		{textfield class="text"}
	{/err}
</p>

{if $fields.PHONE}
<p{if 'REQUIRE_PHONE'|config} class="required"{/if}>
	{err for="`$prefix`phone"}
		{{label {t _your_phone}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if 'PASSWORD_GENERATION'|config != 'PASSWORD_AUTO'}
	{if 'PASSWORD_GENERATION'|config == 'PASSWORD_REQUIRE'}
		{assign var="passRequired" value=true}
	{/if}
	{include file="user/block/passwordFields.tpl" required=$passRequired}
{/if}

{include file="block/eav/fields.tpl" item=$user filter="isDisplayed"}
{include file="block/eav/fields.tpl" eavPrefix=$prefix}

{if $showHeading && $order.isShippingRequired && !'REQUIRE_SAME_ADDRESS'|config}
	<h3>{t _billing_address}</h3>
{/if}

{if $fields.ADDRESS1}
<p class="required">
	{err for="`$prefix`address1"}
		{{label {t _address}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.ADDRESS2}
<p>
	<label></label>
	{textfield name="billing_address2" class="text"}
</p>
{/if}

{if $fields.CITY}
<p class="required">
	{err for="`$prefix`city"}
		{{label {t _city}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.COUNTRY}
<p class="required">
	{err for="`$prefix`country"}
		{{label {t _country}:}}
		{selectfield options=$countries id="`$prefix`country"}
		<span class="progressIndicator" style="display: none;"></span>
	{/err}
</p>
{/if}

{if $fields.STATE}
{if !'DISABLE_STATE'|config}
	<p class="required">
		{err for="`$prefix`state_select"}
			{{label {t _state}:}}
			{selectfield style="display: none;" options=$states id="`$prefix`state_select"}
			{textfield name="billing_state_text" class="text" id="`$prefix`state_text"}
		{/err}

		{literal}
		<script type="text/javascript">
		{/literal}
			new User.StateSwitcher($('{$prefix}country'), $('{$prefix}state_select'), $('{$prefix}state_text'),
					'{link controller=user action=states}');
		</script>
	</p>
{/if}
{/if}

{if $fields.POSTALCODE}
<p class="required">
	{err for="`$prefix`postalCode"}
		{{label {t _postal_code}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}
