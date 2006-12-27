{form id="options" handle=$form action="controller=backend.currency action=saveOptions" method="post" onsubmit="curr.saveRates(this); return false;" onChange="curr.checkDisabledFields(this);"}

	<p>
		{checkbox class="checkbox" id="updateCb" name="updateCb" style="width: 20px; height: 20px; margin-right: 10px; vertical-align: middle;" value="on"} 
		<label for="updateCb" id="updateLabel" style="width: auto; vertical-align: middle;"> {t _update_auto}</label>
	</p>

	<br /><br />	

	<fieldset id="feedOptions" style="margin-left: 30px;" class="disabled">
	
		<label for="frequency" id="freqLabel">{t _update_freq}:</label> 
		{selectfield options=$frequency id="frequency" name="frequency"}
		<br /><br />	
		
		<p>{t _to_update}: </p>
		{foreach from=$currencies item=item}
		<p>
			{checkbox class="checkbox" id="curr_`$item.ID`" name="curr_`$item.ID`" style="width: 20px; height: 20px; margin-right: 10px; vertical-align: middle;" value="on"} 
			<span>
				<label for="curr_{$item.ID}">{$item.ID}</label> {t _using}
				{selectfield class="select" options=$feeds id="feeds_" name="frequency"}
			</span>
		</p>
		{foreachelse}
			{t _no_currencies}
		{/foreach}

	</fieldset>
	
	<p>
		<input type="submit" class="submit" value="{t _save}"> {t _or} 
		<a href="#" onclick="$('options').reset(); $('options').onchange(); return false;" class="cancel">{t _cancel}</a>
	</p>

{/form}

<script type="text/javascript">
	curr.checkDisabledFields($('options'));
</script>