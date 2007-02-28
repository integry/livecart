{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}</h1>

	<div class="resultStats">
		Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
	</div>

	<ul class="productList"> 
	{foreach from=$products item=product}
		<li>			
			<span class="title">
				<a href="{productUrl product=$product}">{$product.name_lang}</a>
			</span>
			
			<p class="descr">
				{$product.shortDescription_lang}
			</p>
				
			<p class="spec">
				specification
			</p>
		
		</li>
	{/foreach}
	</ul>

	<div class="resultPages">
		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
	</div>

</div>		
{include file="layout/frontend/footer.tpl"}