{% if !hideShippingEstimationForm && config('ENABLE_SHIPPING_ESTIMATE') %}
<tr id="shippingEstimation">
	<td colspan="{math equation="extraColspanSize + 5"}" class="form-horizontal">
		<h2>{t _estimate_shipping}</h2>

		{assign var=fields value=config('SHIP_ESTIMATE_FIELDS')}

		<div {% if !fields.COUNTRY %}style="display: none;"{% endif %}>
			{input name="estimate_country"}
				{label}{t _country}{/label}
				{selectfield options=countries id="{uniqid assign=id_country}"}
			{/input}
		</div>

		{% if fields.STATE %}
			[[ partial('user/addressFormState.tpl', ['states': states, 'notRequired': true, 'prefix': "estimate_"]) ]]
		{% endif %}

		{% if fields.POSTALCODE %}
			[[ textfld('estimate_postalCode', '_postal_code') ]]
		{% endif %}

		{% if fields.CITY %}
			[[ textfld('estimate_city', '_city') ]]
		{% endif %}

		<div class="row">
			<div class="controls">
				<button type="submit" class="btn btn-default" name="updateEstimateAddress">{tn _update_address}</button>
			</div>
		</div>
	</td>
</tr>
{% endif %}
