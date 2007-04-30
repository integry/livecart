{include file="layout/frontend/header.tpl"}
{* include file="layout/frontend/leftSide.tpl" *}
{* include file="layout/frontend/rightSide.tpl" *}

<div id="content" class="left right">
	
	<h1>{t _your_account}</h1>
	
	<a href="{link controller=user action=logout}">{t Sign Out}</a>

</div>

{include file="layout/frontend/footer.tpl"}