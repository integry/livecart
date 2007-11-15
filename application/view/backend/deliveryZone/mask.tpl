<span class="maskTitle">{$mask.mask}</span>
<div class="countriesAndStates_existingMaskForm" style="display: none">
	<input type="text" value="{$mask.mask}"  class="countriesAndStates_mask" {denied role='delivery.update'}readonly="readonly"{/denied} />
	
	<input class="submit button countriesAndStates_saveMaskButton" type="button"  value="{t _save}" />
	{t _or} 
	<a href="#cancel" class="cancel countriesAndStates_cancelMask">{t _cancel}</a>
	<span class="errorText hidden"> </span>
</div>