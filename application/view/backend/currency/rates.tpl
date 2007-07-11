<div id="currencyRateList">
{form id="rateForm" handle=$rateForm action="controller=backend.currency action=saveRates" method="post" onsubmit="curr.saveRates(this); return false;" role="currency.update"}

	<fieldset id="rates">	
		{foreach from=$currencies key=key item=item}
			<div{if $item.isEnabled == 0} class="disabled"{/if}>
				<div class="title">{$item.name}</div>
					<label for="rate_{$item.ID}">1 {$item.ID} = </label>
					<fieldset class="error">
						{textfield name="rate_`$item.ID`" class="text"}
						<span class="defaultCurrency">{$defaultCurrency}</span>
						<div class="errorText hidden"></div>
					</fieldset>
			</div>
		{/foreach}

	</fieldset>	

	<fieldset id="saveRates" class="controls">
	
		<label for="submit"> </label>
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" id="submit" value="{t _save}"/> or
		<a href="#" class="cancel" onClick="$('rateForm').reset(); return false;">{t _cancel}</a>
	
	</fieldset>

{/form}
</div>