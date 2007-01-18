<ul class="menu">
	<li><a href="{link controller=backend.product action=form}">Add New Product</a></li>
</ul>

<br/>

<p>
{foreach from=$productList item=product}
	<li>
		<a href="{link controller=backend.product action=form id=$product.ID}">{$product.name.en}</a>
	</li>
{/foreach}
</p>