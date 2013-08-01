{if $specFieldList}
	{form handle=$form}
	<div class="specFieldContainer">
		{include file="backend/product/form/specFieldList.tpl" angular="product" product=$product cat=$cat specFieldList=$specFieldList}
	</div>
	{/form}
{/if}
