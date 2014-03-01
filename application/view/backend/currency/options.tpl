<div class="saveConfirmation" id="optsConf" style="display: none;">
	<div>{t _opts_save_conf}</div>
</div>

{form id="options" handle=form action="backend.currency/saveOptions" method="post" onsubmit="curr.saveOptions(this); return false;" onchange="curr.checkDisabledFields()"}

	[[ checkbox('updateCb', '_update_auto') ]]

	<fieldset id="feedOptions" class="disabled">

		[[ selectfld('frequency', '_update_freq', frequency) ]]

		<p>{t _to_update}: </p>
		{% for item in currencies %}
			{input name="curr_`item.ID`"}
				{label}[[item.ID]]:{/label}
				{t _using} {selectfield options=feeds}
			{/input}
		{foreachelse}
			{t _no_currencies}
		{% endfor %}

	</fieldset>

	<p>
		<span id="optsSaveIndicator" class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" value="{t _save}"> {t _or}
		<a href="#" onclick="('options').reset(); ('options').onchange(); return false;" class="cancel">{t _cancel}</a>
	</p>

{/form}

<script type="text/javascript">
	curr.checkDisabledFields(('options'));
</script>