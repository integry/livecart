<div class="saveConfirmation" id="optsConf" style="display: none;">
	<div>{t _opts_save_conf}</div>
</div>

{form id="options" handle=$form action="controller=backend.currency action=saveOptions" method="post" onsubmit="curr.saveOptions(this); return false;" onchange="curr.checkDisabledFields()"}

	{input name="updateCb"}
		{checkbox}
		{label}{t _update_auto}{/label}
	{/input}

	<fieldset id="feedOptions" class="disabled">

		{input name="frequency"}
			{label}{t _update_freq}:{/label}
			{selectfield options=$frequency}
		{/input}

		<p>{t _to_update}: </p>
		{foreach from=$currencies item=item}
			{input name="curr_`$item.ID`"}
				{label}[[item.ID]]:{/label}
				{t _using} {selectfield options=$feeds}
			{/input}
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