{literal}
	<style type="text/css">
	.languageForm
	{
		margin-top: 1em;
		margin-bottom: 1em;		
	}

	.languageFormCaption
	{
		float: left;	
		line-height: 2em;
		margin-right: 10px;		
	}
	
	ul.languageFormTabs
	{
		height: 2em;		
	}

	ul.languageFormTabs li
	{
		position: relative;
		float: left;
		list-style-type: none;
		padding-left: 6px;
		padding-right: 6px;
		cursor: pointer;
		line-height: 2em;
		border: 1px solid #ccc;
		border-bottom: 0px;
		background-color: #f5f5f5;
		margin-left: 2px;
		overflow: visible;
		height: 2em;
	}	

	ul.languageFormTabs li:hover, ul.languageFormTabs li.active
	{
		border-top: 2px solid red;		
		background-color: #fafafa;
		z-index: 5;
		padding-bottom: 2px;
	}
	
	ul.languageFormTabs li.active
	{
		background-color: #fff;
		font-weight: bold;
	}

	.languageFormContent
	{
		padding: 10px; 
		border: 1px solid #b3d4ef;
		background-color: #f5f5f5;
		position: relative;
	}

	.languageFormContainer
	{
	 	display: none;
	}	
	
	.languageFormContainer.active
	{
	 	display: block;
	}	

	</style>
{/literal}

<div class="languageForm" >
	
	<span class="languageFormCaption">
		Translate: 
	</span>
	
	<ul class="languageFormTabs">
	{foreach from=$languageBlock item="language"}	
		<li class="languageFormTabs_{$language.ID}"><img src="{$language.image}" /> {$language.originalName}</li>
	{/foreach}
		<div class="clear"></div>
	</ul>

	<div class="languageFormContent">