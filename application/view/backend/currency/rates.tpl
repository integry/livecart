<div id="currencyRateList">
{form id="rateform" handle=$rateForm action="controller=backend.currency action=saveRates" method="post" onsubmit="curr.saveRates(this); return false;" role="currency.update"}

	<fieldset id="rates">
		{foreach from=$currencies key=key item=item}
			<div{if $item.isEnabled == 0} class="disabled"{/if}>
				<div class="title">[[item.name]]</div>
				{input name="rate_`$item.ID`"}
					{label}1 [[item.ID]] = {/label}
					{textfield} [[defaultCurrency]]
				{/input}
			</div>
		{/foreach}

	</fieldset>

	<fieldset id="saveRates" class="controls">

		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" id="submit" value="{t _save}"/> or
		<a href="#" class="cancel" onClick="$('rateForm').reset(); return false;">{t _cancel}</a>

	</fieldset>

{/form}
</div>