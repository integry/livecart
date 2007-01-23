<ul class="menu">
	<li><a href="{link controller=backend.product action=add id=$categoryID}">Add New Product</a></li>
</ul>

<br/>

<p>
{foreach from=$productList item=product}
	<li>
		<a href="{link controller=backend.product action=edit id=$product.ID}">{$product.name.en}</a>
	</li>
{/foreach}
</p>