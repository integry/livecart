{foreach $lists as $list}
	<h2>[[list.0.ProductList.name()]]</h2>
	[[ partial('category/productGrid.tpl', ['products': list]) ]]
{/foreach}