{defun name="headLink" title="" sortVar=""}
	{if $title}
		{assign var="sortOrder" value='_'|@explode:$sortField|@array_pop|default:'asc'}
		{if $sortField == "`$sortVar`_`$sortOrder`"}
			{assign var="currentOrder" value=$sortOrder}
			{if $sortOrder == "asc"}{assign var="sortOrder" value="desc"}{else}{assign var="sortOrder" value="asc"}{/if}
		{/if}
		<a href="{link self=true sort="`$sortVar`_`$sortOrder`"}" class="{if $currentOrder}direction_{$currentOrder}{/if}">{translate text=$title}</a>
	{/if}
{/defun}

<table class="table productTable">
	<thead>
		<tr>
			<th class="productImage">{t _image}</th>
			<th class="productName">{fun name="headLink" title=_name sortVar="product_name"}</th>

			{foreach from=$listAttributes item=attribute}
				<th class="attr_{$attribute.ID}">{fun name="headLink" title=$attribute.name_lang sortVar="`$attribute.ID`-`$attribute.handle`"}</th>
			{/foreach}

			{if 'DISPLAY_PRICES'|config}
				<th class="productPrice">{fun name="headLink" title=_price sortVar="price"}</th>
			{/if}
			<th class="productDetails">{t _view_details}</th>
		</tr>
	</thead>
	<tbody>
		{foreach from=$products item=product name="productList"}
			<tr class="{zebra loop="productList"} {if !$smarty.foreach.productList.last}last{/if}">
				<td class="productImage">
					<a href="{productUrl product=$product}">
					{if $product.DefaultImage.ID}
						{img src=$product.DefaultImage.paths.1 alt=$product.name_lang|escape}
					{else}
						{img src=image/missing_mini.jpg alt=$product.name_lang|escape}
					{/if}
					</a>
				</td>
				<td class="productName text"><a href="{productUrl product=$product}">{$product.name_lang}</a></td>

				{foreach from=$listAttributes item=attribute}
					<td class="attribute attr_{$attribute.ID}">{include file="product/attributeValue.tpl" attr=$product.attributes[$attribute.ID]}</td>
				{/foreach}

				{if 'DISPLAY_PRICES'|config}
					<td class="productPrice">{include file="product/block/productPrice.tpl"}</td>
				{/if}

				<td class="productDetails"><a href="{productUrl product=$product}">{t _view_details}</a></td>
			</tr>
		{/foreach}
	</tbody>
</table>