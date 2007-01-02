<h1>This is products section for:</h1>
<p>{foreach from=$path item=category}{$category}: {/foreach}</p>

<h3>Product list</h3>
<p>
{foreach from=$productList item=product}
	<li>
		<a href="{link controller=backend.product action=form id=$product.ID}">{$product.name.en}</a>
	</li>
{/foreach}
</p>

<p>
	<a href="{link controller=backend.product action=form}">New Product</a>
</p>
