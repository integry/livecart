<fieldset class="languageForm" >
	
    <div class="languageTabsContainer">
    	<span class="languageFormCaption">
    		Translate: 
    	</span>
    	
    	<ul class="languageFormTabs">
    	{foreach from=$languageBlock item="language"}	
    		<li class="languageFormTabs_{$language.ID}">{img src=$language.image} {$language.originalName}</li>
    	{/foreach}
    	</ul>
    </div>
    
	<div class="languageFormContent">