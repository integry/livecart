{assign var="action" value="create"}
{form id="categoryForm_$categoryId" handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post" onsubmit="Backend.Category.updateBranch(this); return false;" role="category.update"}
	<fieldset class="container">

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

		{include file="backend/eav/fields.tpl" item=$category}

		<fieldset>
			<legend>{t _presentation}</legend>

			{input name="isSubcategories"}
				{checkbox}
				{label}{tip _theme_subcategories}{/label}
			{/input}

			{input name="isVariationImages"}
				{checkbox}
				{label}{tip _show_variation_images}{/label}
			{/input}

			{input name="isAllVariations"}
				{checkbox}
				{label}{tip _allow_all_variations}{/label}
			{/input}

			{input name="listStyle"}
				{label}{tip _list_style}:{/label}
				{selectfield id="listStyle_`$categoryId`" options=$listStyles}
			{/input}

			<div style="float: left; width: 550px;">
				{input name="theme"}
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

			{include file="backend/eav/language.tpl" item=$category language=$lang.ID}
		{/language}
	</fieldset>

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
	new Backend.ThemePreview($('categoryThemePreview_{$categoryId}'), $('theme_{$categoryId}'));
	ActiveForm.prototype.initTinyMceFields("categoryForm_{$categoryId}");
</script>
