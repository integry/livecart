{if !$hideShippingEstimationForm}
<tr id="shippingEstimation">
	<td colspan="5">
		<div class="container">
			<h2>{t _estimate_shipping}</h2>

			{assign var=fields value='SHIP_ESTIMATE_FIELDS'|config}

			<p {if !$fields.COUNTRY}style="display: none;"{/if}>
				<label>{t _country}</label>
				{selectfield name="estimate_country" options=$countries id="{uniqid assign=id_country}"}
			</p>

			{if $fields.STATE}
				{include file="user/addressFormState.tpl" states=$states notRequired=true prefix="estimate_"}
			{/if}

			{if $fields.POSTALCODE}
				<p>
					<label>{t _postal_code}</label>
					{textfield name="estimate_postalCode"}
				</p>
			{/if}

			{if $fields.CITY}
				<p>
					<label>{t _city}</label>
					{textfield name="estimate_city"}
				</p>
			{/if}

			<p>
				<label></label>
				<input type="submit" class="submit" value="{tn _update_address}" name="updateEstimateAddress" />
			</p>
		</div>
	</td>
</tr>
{/if}