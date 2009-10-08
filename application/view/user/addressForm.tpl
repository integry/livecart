<p class="required">
	{{err for="`$prefix`firstName"}}
	   {{label {t _your_first_name}:}}
	   {textfield class="text"}
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`lastName"}}
		{{label {t _your_last_name}:}}
		{textfield class="text"}
	{/err}
</p>

<p>
	{{err for="`$prefix`companyName"}}
		{{label {t _company_name}:}}
		{textfield class="text"}
	{/err}
</p>

<p{if 'REQUIRE_PHONE'|config} class="required"{/if}>
	{{err for="`$prefix`phone"}}
		{{label {t _your_phone}:}}
		{textfield class="text"}
	{/err}
</p>

{include file="block/eav/fields.tpl" item=$address eavPrefix=$prefix}

<p class="required">
	{{err for="`$prefix`address1"}}
		{{label {t _address}:}}
		{textfield class="text"}
	{/err}
</p>

<p>
	<label>&nbsp;</label>
	<fieldset class="error">
		{textfield name="`$prefix`address2" class="text"}
	</fieldset>
</p>

<p class="required">
	{{err for="`$prefix`city"}}
		{{label {t _city}:}}
		{textfield class="text"}
	{/err}
</p>

<p class="required">
	{{err for="`$prefix`country"}}
		{{label {t _country}:}}
		{selectfield options=$countries id="{uniqid assign=id_country}"}
		<span class="progressIndicator" style="display: none;"></span>
	{/err}
</p>

{include file="user/addressFormState.tpl" prefix=$prefix}

<p class="required">
	{{err for="`$prefix`postalCode"}}
		{{label {t _postal_code}:}}
		{textfield class="text"}
	{/err}
</p>

{if $return}
	<input type="hidden" name="return" value="{$return}" />
{/if}