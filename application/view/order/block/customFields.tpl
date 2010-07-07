{if 'CART_PAGE' == 'CHECKOUT_CUSTOM_FIELDS'|config}
{sect}
	{header}
		<tr id="cartFields">
			<td colspan="{math equation="$extraColspanSize + 5"}">
				<div class="container">
	{/header}
	{content}
			{include file="block/eav/fields.tpl" item=$cart filter="isDisplayed"}
	{/content}
	{footer}
				<p>
					<label></label>
					<input type="submit" class="submit" value="{tn _update}" name="saveFields" />
				</p>
				</div>
			</td>
		</tr>
	{/footer}
{/sect}
{/if}
