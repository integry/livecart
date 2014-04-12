<div ng-controller="CategoryFormController">
[[ form('', ['ng-submit': "save()", 'ng-init': ';'] ) ]] >
	[[ checkbox('isEnabled', tip('_active')) ]]

	[[ textfld('name', '_category_name') ]]

	[[ textareafld('description', tip('_descr'), ['ui-my-tinymce': '']) ]]

	[[ textareafld('keywords', tip('_keywords')) ]]

	[[ textfld('pageTitle', tip('_pageTitle _hint_pageTitle')) ]]

	{#
	[[ partial('backend/eav/fields.tpl', ['angular': "category", 'item': category]) ]]

	<fieldset>
		<legend>{t _presentation}</legend>

		[[ checkbox('presentation.isSubcategories', tip('_theme_subcategories')) ]]

		[[ checkbox('presentation.isVariationImages', tip('_show_variation_images')) ]]

		[[ checkbox('presentation.isAllVariations', tip('_allow_all_variations')) ]]

		{input name="presentation.listStyle"}
			{label}{tip _list_style}:{/label}
			{selectfield id="listStyle_`categoryId`" options=listStyles}
		{/input}

		<div style="float: left; width: 550px;">
			{input name="presentation.theme"}
				{label}{tip _theme}:{/label}
				{selectfield id="theme_`categoryId`" options=themes}
			{/input}
		</div>

		<div style="float: left;" id="categoryThemePreview_[[categoryId]]"></div>

	</fieldset>

	{block FORM-CATEGORY-BOTTOM}

	{language}
		[[ textfld('name_`lang.ID`', '_category_name') ]]

		[[ textarea('description_`lang.ID`', '_descr', class: 'tinyMCE') ]]

		[[ textarea('keywords_`lang.ID`', '_keywords', class: 'categoryKeywords') ]]

		[[ textfld('pageTitle_`lang.ID`', '_pageTitle', class: 'wide') ]]

		[[ partial('backend/eav/fields.tpl', ['angular': "instance", 'item': category, 'language': lang.ID]) ]]
	{/language}

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" id="submit" value="{t _save}"/> or
		<a href="#" class="cancel" onClick="('categoryForm_[[categoryId]]').reset(); return false;">{t _cancel}</a>
		<div class="clear"></div>
	</fieldset>
	#}
	
	<submit>{t _save}</submit>

</form>
