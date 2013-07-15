<div ng-controller="CategoryFormController" ng-show="category.ID">
{form model="category" ng_submit="save()" handle=$catalogForm role="category.update"}
	{input name="isEnabled"}
		{checkbox}
		{label}{tip _active}{/label}
	{/input}

	{input name="name"}
		{label}{t _category_name}:{/label}
		{textfield}
	{/input}

	{input name="description"}
		{label}{tip _descr}:{/label}
		{textarea class="tinyMCE"}
	{/input}

	{input name="keywords"}
		{label}{tip _keywords}:{/label}
		{textarea class="categoryKeywords"}
	{/input}

	{input name="pageTitle"}
		{label}{tip _pageTitle _hint_pageTitle}:{/label}
		{textfield class="wide"}
	{/input}

	{include file="backend/eav/fields.tpl" angular="category" item=$category}

	<fieldset>
		<legend>{t _presentation}</legend>

		{input name="presentation.isSubcategories"}
			{checkbox}
			{label}{tip _theme_subcategories}{/label}
		{/input}

		{input name="presentation.isVariationImages"}
			{checkbox}
			{label}{tip _show_variation_images}{/label}
		{/input}

		{input name="presentation.isAllVariations"}
			{checkbox}
			{label}{tip _allow_all_variations}{/label}
		{/input}

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

		<div style="float: left;" id="categoryThemePreview_{$categoryId}"></div>

	</fieldset>

	{block FORM-CATEGORY-BOTTOM}

	{language}
		{input name="name_`$lang.ID`"}
			{label}{t _category_name}:{/label}
			{textfield}
		{/input}

		{input name="description_`$lang.ID`"}
			{label}{t _descr}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{input name="keywords_`$lang.ID`"}
			{label}{t _keywords}:{/label}
			{textarea class="categoryKeywords"}
		{/input}

		{input name="pageTitle_`$lang.ID`"}
			{label}{t _pageTitle}:{/label}
			{textfield class="wide"}
		{/input}

		{include file="backend/eav/fields.tpl" angular="instance" item=$category language=$lang.ID}
	{/language}

	<fieldset class="controls">
		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" class="submit" id="submit" value="{tn _save}"/> or
		<a href="#" class="cancel" onClick="$('categoryForm_{$categoryId}').reset(); return false;">{t _cancel}</a>
		<div class="clear"></div>
	</fieldset>

{/form}

{literal}
<script type="text/javascript">
{/literal}
	//new Backend.ThemePreview($('categoryThemePreview_{$categoryId}'), $('theme_{$categoryId}'));
	//ActiveForm.prototype.initTinyMceFields("categoryForm_{$categoryId}");
</script>

</div>