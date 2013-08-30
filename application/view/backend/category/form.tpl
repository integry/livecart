<div ng-controller="CategoryFormController" ng-show="category.ID">
{form model="category" ng_submit="save()" handle=$catalogForm role="category.update"}
	[[ checkbox('isEnabled', tip('_active')) ]]

	[[ textfld('name', '_category_name') ]]

	[[ textarea('description', tip('_descr'), class: 'tinyMCE') ]]

	[[ textarea('keywords', tip('_keywords'), class: 'categoryKeywords') ]]

	[[ textfld('pageTitle', tip('_pageTitle _hint_pageTitle'), class: 'wide') ]]

	[[ partial('backend/eav/fields.tpl', ['angular': "category", 'item': category]) ]]

	<fieldset>
		<legend>{t _presentation}</legend>

		[[ checkbox('presentation.isSubcategories', tip('_theme_subcategories')) ]]

		[[ checkbox('presentation.isVariationImages', tip('_show_variation_images')) ]]

		[[ checkbox('presentation.isAllVariations', tip('_allow_all_variations')) ]]

		{input name="presentation.listStyle"}
			{label}{tip _list_style}:{/label}
			{selectfield id="listStyle_`$categoryId`" options=$listStyles}
		{/input}

		<div style="float: left; width: 550px;">
			{input name="presentation.theme"}
				{label}{tip _theme}:{/label}
				{selectfield id="theme_`$categoryId`" options=$themes}
			{/input}
		</div>

		<div style="float: left;" id="categoryThemePreview_[[categoryId]]"></div>

	</fieldset>

	{block FORM-CATEGORY-BOTTOM}

	{language}
		[[ textfld('name_`$lang.ID`', '_category_name') ]]

		[[ textarea('description_`$lang.ID`', '_descr', class: 'tinyMCE') ]]

		[[ textarea('keywords_`$lang.ID`', '_keywords', class: 'categoryKeywords') ]]

		[[ textfld('pageTitle_`$lang.ID`', '_pageTitle', class: 'wide') ]]

		[[ partial('backend/eav/fields.tpl', ['angular': "instance", 'item': category, 'language': lang.ID]) ]]
	{/language}

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" id="submit" value="{tn _save}"/> or
		<a href="#" class="cancel" onClick="$('categoryForm_[[categoryId]]').reset(); return false;">{t _cancel}</a>
		<div class="clear"></div>
	</fieldset>

{/form}

{literal}
<script type="text/javascript">
{/literal}
	//new Backend.ThemePreview($('categoryThemePreview_[[categoryId]]'), $('theme_[[categoryId]]'));
	//ActiveForm.prototype.initTinyMceFields("categoryForm_[[categoryId]]");
</script>

</div>