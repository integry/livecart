<div id="translationDialog">
	<div style="border: 1px solid #CCCCCC; background-color: #FFFFFF; padding: 10px; width: 300px; height: 77px;">
		{pageTitle}Live Translate (English){/pageTitle}
		<span style="font-size: smaller; color: #CCCCCC;">{$id}</span>
		<form action="{link controller=backend.language action=saveTranslationDialog}" method="POST" onSubmit="cust.saveTranslationDialog(this); return false;">
			<input type="hidden" name="id" value="{$id}" />
			<input type="hidden" name="file" value="{$file}" />
			<input type="hidden" name="translation" id="translation" />
			<input type="text" onMouseDown="this.focus()" onKeyUp="cust.previewTranslations('{$id}', this.value);" name="translate_{$file}_{$id}" id="trans" value="{$translation|escape}" style="width: 300px;">
			<input type="submit" class="submit" id="transDialogSave" value="Save Translation"> or <a class="cancel" href="#" onClick='return cust.cancelTransDialog();'>Cancel</a>
		</form>
		<img src="image/indicator.gif" id="transSaveIndicator" style="display:none;">
	</div>
</div>