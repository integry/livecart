<div id="currencyRateList">
{form id="rateForm" handle=$rateForm action="controller=backend.currency action=saveRates" method="post" onsubmit="curr.saveRates(this); return false;"}

	<fieldset id="rates">
	
	{if $saved}
		<div class="saveConfirmation" id="rateConf">
			<div>Currency rates were saved successfuly</div>
		</div>
	{/if}

	{foreach from=$currencies key=key item=item}
		<p{if $item.isEnabled == 0} class="disabled"{/if}>
			<div class="title">{$item.name}</div>
				<label for="rate_{$item.ID}">1 {$item.ID} = </label>
				{textfield name="rate_`$item.ID`" id="rate_`$item.ID`"}
				<span class="defaultCurrency">{$defaultCurrency}</span>
				
				{error for="rate_`$item.ID`" msg=err}<span class="feedback">{$err}</span>{/error}
				
		</p>
	{/foreach}

	</fieldset>	

	<fieldset id="saveRates">
	
	<label for="submit"> </label>
	<span id="rateSaveIndicator" class="progressIndicator" style="display: none;"></span><input type="submit" class="submit" id="submit" value="{t _save}"/> or
	<a href="#" class="cancel" onClick="$('rateForm').reset(); return false;">{t _cancel}</a>
	
	</fieldset>

{/form}
</div>

<script>
	hideSaveConfirmation();
</script>