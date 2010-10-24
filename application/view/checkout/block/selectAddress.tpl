{assign var=actionPrefix value=$prefix|@ucfirst}
{if !$addresses}
	<div id="{$prefix}AddressForm">
		{include file="user/addressForm.tpl" prefix="`$prefix`_" states=$states}
	</div>
{else}
	<table class="addressSelector">
		{foreach from=$addresses item="item"}
			<tr>
				<td class="selector">
					{radio class="radio" name="`$prefix`Address" id="`$prefix`_`$item.UserAddress.ID`" value=$item.UserAddress.ID}
				</td>
				<td class="address" onclick="var el = $('{$prefix}_{$item.UserAddress.ID}'); el.checked = true; el.form.onchange(); sendEvent(el, 'click'); sendEvent(el, 'change'); ">
						{include file="user/address.tpl"}
						<a href="{link controller=user action="edit`$actionPrefix`Address" id=$item.ID returnPath=true}">{t _edit_address}</a>
				</td>
			</tr>
		{/foreach}
		<tr>
			<td class="selector addAddress">
				{radio class="radio" name="`$prefix`Address" id="`$prefix`_new" value=""}
			</td>
			<td class="address addAddress">
				<label for="{$prefix}_new" class="radio">{translate text="_new_`$prefix`_address"}</label>
				<div class="address">
					<div class="addressBlock">
						{include file="user/addressForm.tpl" prefix="`$prefix`_" states=$states}
					</div>
				</div>
			</td>
		</tr>
	</table>
{/if}
