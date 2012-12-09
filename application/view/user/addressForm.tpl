{assign var="fields" value='USER_FIELDS'|config}

<div class="registerColumn">
	<h3>{t _your_personal_details}</h3>
	{include file="user/block/nameFields.tpl"}
	{include file="user/block/phoneField.tpl"}
</div>

<div class="registerColumn">
	<h3>{t _your_address}</h3>
	{include file="user/block/addressFields.tpl"}
	{include file="block/eav/fields.tpl" item=$address eavPrefix=$prefix}
</div>

{if $return}
	<input type="hidden" name="return" value="{$return}" />
{/if}