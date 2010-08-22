{assign var="fields" value='USER_FIELDS'|config}

{if $fields.FIRSTNAME}
<p class="required">
	{{err for="`$prefix`firstName"}}
	   {{label {t _your_first_name}:}}
	   {textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.LASTNAME}
<p class="required">
	{{err for="`$prefix`lastName"}}
		{{label {t _your_last_name}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.COMPANYNAME}
<p>
	{{err for="`$prefix`companyName"}}
		{{label {t _company_name}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.PHONE}
<p{if 'REQUIRE_PHONE'|config} class="required"{/if}>
	{{err for="`$prefix`phone"}}
		{{label {t _your_phone}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{include file="block/eav/fields.tpl" item=$address eavPrefix=$prefix}

{if $fields.ADDRESS1}
<p class="required">
	{{err for="`$prefix`address1"}}
		{{label {t _address}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.ADDRESS2}
<p>
	<label>&nbsp;</label>
	<fieldset class="error">
		{textfield name="`$prefix`address2" class="text"}
	</fieldset>
</p>
{/if}

{if $fields.CITY}
<p class="required">
	{{err for="`$prefix`city"}}
		{{label {t _city}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $fields.COUNTRY}
<p class="required">
	{{err for="`$prefix`country"}}
		{{label {t _country}:}}
		{selectfield options=$countries id="{uniqid assign=id_country}"}
		<span class="progressIndicator" style="display: none;"></span>
	{/err}
</p>
{else}
	{hidden name="`$prefix`country" id="{uniqid assign=id_country}"}
{/if}

{if $fields.STATE}
{include file="user/addressFormState.tpl" prefix=$prefix}
{/if}

{if $fields.POSTALCODE}
<p class="required">
	{{err for="`$prefix`postalCode"}}
		{{label {t _postal_code}:}}
		{textfield class="text"}
	{/err}
</p>
{/if}

{if $return}
	<input type="hidden" name="return" value="{$return}" />
{/if}