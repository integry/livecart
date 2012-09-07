{assign var="fields" value='USER_FIELDS'|config}

{include file="user/block/nameFields.tpl"}

{include file="user/block/phoneField.tpl"}

{include file="block/eav/fields.tpl" item=$address eavPrefix=$prefix}

{include file="user/block/addressFields.tpl"}

{if $return}
	<input type="hidden" name="return" value="{$return}" />
{/if}

{if $confirmButton}
	<label class="confirmAddressLabel"></label>
	<input type="button" class="button confirmAddress" value="{tn _confirm_address}" />
{/if}