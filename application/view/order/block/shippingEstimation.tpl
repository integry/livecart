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
			[[ textfld('estimate_postalCode', '_postal_code') ]]
		{/if}

		{if $fields.CITY}
			[[ textfld('estimate_city', '_city') ]]
		{/if}

		<div class="row">
			<div class="controls">
				<button type="submit" class="btn btn-default" name="updateEstimateAddress">{tn _update_address}</button>
			</div>
		</div>
	</td>
</tr>
{/if}
