{if !$hideShippingEstimationForm && 'ENABLE_SHIPPING_ESTIMATE'|config}
<tr id="shippingEstimation">
	<td colspan="{math equation="$extraColspanSize + 5"}" class="form-horizontal">
		<h2>{t _estimate_shipping}</h2>

		{assign var=fields value='SHIP_ESTIMATE_FIELDS'|config}

		<div {if !$fields.COUNTRY}style="display: none;"{/if}>
			{input name="estimate_country"}
				{label}{t _country}{/label}
				{selectfield options=$countries id="{uniqid assign=id_country}"}
			{/input}
		</div>

		{if $fields.STATE}
			{include file="user/addressFormState.tpl" states=$states notRequired=true prefix="estimate_"}
		{/if}

		{if $fields.POSTALCODE}
			{input name="estimate_postalCode"}
				{label}{t _postal_code}{/label}
				{textfield}
			{/input}
		{/if}

		{if $fields.CITY}
			{input name="estimate_city"}
				{label}{t _city}{/label}
				{textfield}
			{/input}
		{/if}

		<div class="control-group">
			<div class="controls">
				<button type="submit" class="btn" name="updateEstimateAddress">{tn _update_address}</button>
			</div>
		</div>
	</td>
</tr>
{/if}
