<div id="translationDialog">
	<div class="container">
		<form action="[[ url("backend.language/saveTranslationDialog") ]]" method="post" onSubmit="cust.saveTranslationDialog(this); return false;">
			<input type="hidden" name="id" value="[[id]]" />
			<input type="hidden" name="file" value="[[file]]" />
			<input type="hidden" name="translation" id="translation" />

				{% if $language.image %}
					{img src=$language.image id="transFlag"}
				{% endif %}

				<div>[[language.originalName]]:</div>
				<input type="text" class="text" onkeyup="cust.previewTranslations('[[id]]', this.value);" name="translate_[[file]]_[[id]]" id="trans" value="{$translation|escape}">

				<div id="defTrans">[[defaultTranslation]]</div>
			<input type="submit" class="submit" id="transDialogSave" value="{tn _save_trans}">
			<label class="cancel">
				{t _or}
				<a class="cancel" href="#" onClick='return cust.cancelTransDialog();'>{t _cancel}</a>
			</label>
		</form>
		{img src="image/indicator.gif" id="transSaveIndicator" style="display:none;"}
	</div>
</div>