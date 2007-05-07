{pageTitle}{$category.name_lang}{/pageTitle}

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}</h1>

{if $products}	
    {include file="category/productList.tpl"}
{/if}
		
</div>		
{include file="layout/frontend/footer.tpl"}