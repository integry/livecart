{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}</h1>

	<ul>
	{foreach from=$products item=product}
		<li>{$product.name_lang}</li>
	{/foreach}
	</ul>

</div>		
{include file="layout/frontend/footer.tpl"}