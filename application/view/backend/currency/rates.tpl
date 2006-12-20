<ul id="currencyRateList">
{form id="rateForm" handle=$rateForm action="controller=backend.currency action=saveRates" method="post" onsubmit="curr.saveRates(this); return false;"}

	<fieldset id="rates">
	
	{foreach from=$currencies key=key item=item}
		<li{if $item.isEnabled == 0} class="disabled"{/if}>
			<div class="title">{$item.name}</div>
			<p>
				<label for="rate_{$item.ID}">1 {$item.ID} = </label>
				{textfield name="rate_`$item.ID`" id="rate_`$item.ID`"}
				{$defaultCurrency}
				
				{error for="rate_`$item.ID`" msg=err}<div class="error">{$err}</div>{/error}
				
			</p>
		</li>
	{/foreach}
	
	<label for="submit"> </label>
	<span id="rateSaveIndicator" class="progressIndicator" style="display: none;"></span><input type="submit" class="submit" id="submit" value="{t _save}"/> or
	<a href="#" class="cancel" onClick="$('rateForm').reset(); return false;">{t _cancel}</a>
	
	</fieldset>

</ul>
{/form}