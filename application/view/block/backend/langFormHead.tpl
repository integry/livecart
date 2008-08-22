<fieldset class="languageForm" id="{$langFormId}">

	<div class="languageTabsContainer">
		<ul class="languageFormTabs">
			<li class="languageFormCaption">
				{t _langform_translate}:
			</li>
		{foreach from=$languageBlock item="language"}
			<li class="languageFormTabs_{$language.ID} langTab {$classNames[$language.ID]}" id="{$langFormId}__lang_{$language.ID}">{img src=$language.image} {$language.originalName}</li>
		{/foreach}

		</ul>
		<li class="moreTabs">
				<a href="#" class="moreLink">{t _more_tabs} &#9662;</a>
				<div class="moreTabsMenu" style="display: none;"></div>
			</li>
	</div>

	<div class="languageFormContent">