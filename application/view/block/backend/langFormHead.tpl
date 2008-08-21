<fieldset class="languageForm" id="{$langFormId}">

	<div class="languageTabsContainer">
		<span class="languageFormCaption">
			{t _langform_translate}:
		</span>

		<ul class="languageFormTabs">
		{foreach from=$languageBlock item="language"}
			<li class="languageFormTabs_{$language.ID} langTab {$classNames[$language.ID]}" id="{$langFormId}__lang_{$language.ID}">{img src=$language.image} {$language.originalName}</li>
		{/foreach}
			<li class="moreTabs">
				<a href="#">{t _more_tabs} &#9662;</a>
				<div class="moreTabsMenu" style="display: none;"></div>
			</li>
		</ul>
	</div>

	<div class="languageFormContent">