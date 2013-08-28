{assign var="fields" value='USER_FIELDS'|config}

<div class="registerColumn">
	<fieldset>
		<legend>{t _your_personal_details}</legend>
		{include file="user/block/nameFields.tpl"}
		{include file="user/block/phoneField.tpl"}
	</fieldset>
</div>

<div class="registerColumn">
	<fieldset>
		<legend>{t _your_address}</legend>
		{include file="user/block/addressFields.tpl"}
		{include file="block/eav/fields.tpl" item=$address eavPrefix=$prefix}
	</fieldset>
</div>

{if $return}
	<input type="hidden" name="return" value="[[return]]" />
{/if}