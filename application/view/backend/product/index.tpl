<ul class="menu">
	<li>
		<a href="#" onclick="Backend.Product.showAddForm(this.parentNode.parentNode.parentNode, {$categoryID}); return false;">
			Add New Product
		</a>
		<span class="progressIndicator" style="display: none;"></span>
	</li>
</ul>

<br/>

<p>
{foreach from=$productList item=product}
	<li>
		<a href="{link controller=backend.product action=edit id=$product.ID}">{$product.name.en}</a>
	</li>
{/foreach}
</p>