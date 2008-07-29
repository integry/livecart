<fieldset class="languageForm" id="{$langFormId}">

	<div class="languageTabsContainer">
		<span class="languageFormCaption">
			{t _langform_translate}:
		</span>

		<ul class="languageFormTabs">
		{foreach from=$languageBlock item="language"}
			<li class="languageFormTabs_{$language.ID}">{img src=$language.image} {$language.originalName}</li>
		{/foreach}
		</ul>
	</div>

	<div class="languageFormContent">