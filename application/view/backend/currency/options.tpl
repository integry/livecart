<div class="saveConfirmation" id="optsConf" style="display: none;">
	<div>{t _opts_save_conf}</div>
</div>

{form id="options" handle=$form action="controller=backend.currency action=saveOptions" method="post" onsubmit="curr.saveOptions(this); return false;" onChange="curr.checkDisabledFields(this);"}

	<p>
		{checkbox class="checkbox" id="updateCb" name="updateCb" value="1"} 
		<label for="updateCb" id="updateLabel"> {t _update_auto}</label>
	</p>

	<br /><br />	

	<fieldset id="feedOptions" class="disabled">
	
		<label for="frequency" id="freqLabel">{t _update_freq}:</label> 
		{selectfield options=$frequency id="frequency" name="frequency"}
		<br /><br />	
		
		<p>{t _to_update}: </p>
		{foreach from=$currencies item=item}
		<p>
			{checkbox class="checkbox" id="curr_`$item.ID`" name="curr_`$item.ID`" value="1"} 
			<span>
				<label for="curr_{$item.ID}">{$item.ID}</label> {t _using}
				{selectfield class="select" options=$feeds id="feed_`$item.ID`" name="feed_`$item.ID`"}
			</span>
		</p>
		{foreachelse}
			{t _no_currencies}
		{/foreach}

	</fieldset>
	
	<p>
		<span id="optsSaveIndicator" class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save}"> {t _or} 
		<a href="#" onclick="$('options').reset(); $('options').onchange(); return false;" class="cancel">{t _cancel}</a>
	</p>

{/form}

<script type="text/javascript">
	curr.checkDisabledFields($('options'));
</script>