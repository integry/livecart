{assign var="action" value="create"}
{form id="categoryForm_$categoryId" handle=$catalogForm action="controller=backend.category action=update id=$categoryId" method="post" onsubmit="Backend.Category.updateBranch(this); return false;" role="category.update"}
	<fieldset class="container">

		<p class="checkbox">
			{checkbox name="isEnabled" id="isEnabled_$categoryId" class="checkbox"} <label class="checkbox" for="isEnabled_{$categoryId}">{t _active}</label>
		</p>

		<p class="required">
			<label for="name_{$categoryId}">{t _category_name}:</label>
			{textfield name="name" id="name_$categoryId"}
		</p>

		<p>
			<label for="details_{$categoryId}">{t _descr}:</label>
			{textarea name="description" id="details_$categoryId" class="tinyMCE"}
		</p>

		<p>
			<label for="keywords_{$categoryId}">{t _keywords}:</label>
			{textarea name="keywords" id="keywords_$categoryId" class="categoryKeywords"}
		</p>

		<p>
			<label for="pageTitle_{$categoryId}" class="acronym"><a>{t _pageTitle}<div>{t _hint_pageTitle}</div></a>:</label>
			{textfield name="pageTitle" id="pageTitle_$categoryId" class="wide"}
		</p>

		{include file="backend/eav/fields.tpl" item=$category}

		<fieldset>
			<legend>{t _presentation}</legend>

			<div style="float: left; width: 550px;">
				<p>
					<label for="theme_{$categoryId}">{t _theme}:</label>
					{selectfield name="theme" id="theme_`$categoryId`" options=$themes}
				</p>

				<p>
					<label></label>
					{checkbox name="isSubcategories" class="checkbox" id="isSubcategories_`$categoryId`"}
					<label class="checkbox" for="isSubcategories_{$categoryId}">{t _theme_subcategories}</label>
				</p>
			</div>

			<div style="float: left;" id="categoryThemePreview_{$categoryId}"></div>
		</fieldset>

		{language}
			<p>
				<label>{t _category_name}:</label>
				{textfield name="name_`$lang.ID`"}
			</p>
			<p>
				<label>{t _descr}:</label>
				{textarea name="description_`$lang.ID`" class="tinyMCE"}
			</p>
			<p>
				<label>{t _keywords}:</label>
				{textarea name="keywords_`$lang.ID`" class="categoryKeywords"}
			</p>
			<p>
				<label>{t _pageTitle}:</label>
				{textfield name="pageTitle_`$lang.ID`" class="wide"}
			</p>

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