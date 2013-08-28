{function name="headLink" title="" sortVar=""}
	{if $title}
		{assign var="sortOrder" value='_'|@explode:$sortField|@array_pop|default:'asc'}
		{if ($sortOrder != 'asc') && ($sortOrder != 'desc')}{assign var="sortOrder" value='asc'}{/if}
		{if $sortField == "`$sortVar`_`$sortOrder`"}
			{% set currentOrder = $sortOrder %}
			{if $sortOrder == "asc"}{% set sortOrder = "desc" %}{else}{% set sortOrder = "asc" %}{/if}
		{/if}
		<a href="{link self=true sort="`$sortVar`_`$sortOrder`"}" class="{if $currentOrder}direction_[[currentOrder]]{/if}">{translate text=$title}</a>
	{/if}
{/function}
{assign var="columns" value='TABLE_VIEW_COLUMNS'|config}

<table class="table table-striped productTable">
	<thead>
		<tr>
			{if $columns.IMAGE}
				<th class="productImage">{t _image}</th>
			{/if}

			{if $columns.SKU}
				<th class="productSku">{headLink title=_sku sortVar="sku"}</th>
			{/if}

			{if $columns.NAME}
				<th class="productName">{headLink title=_name sortVar="product_name"}</th>
			{/if}

			{foreach from=$listAttributes item=attribute}
				<th class="attr_[[attribute.ID]]">{headLink title=$attribute.name_lang sortVar="`$attribute.ID`-`$attribute.handle`"}</th>
			{/foreach}

			{if $columns.PRICE && 'DISPLAY_PRICES'|config}
				<th class="productPrice">{headLink title=_price sortVar="price"}</th>
			{/if}

			{if $columns.DETAILS}
				<th class="productDetails">{t _view_details}</th>
			{/if}
		</tr>
	</thead>
	<tbody>
		{foreach from=$products item=product name="productList"}
			<tr class="{if !$smarty.foreach.productList.last}last{/if}">

				{if $columns.IMAGE}
					<td class="productImage">
						<a href="{productUrl product=$product category=$category}">
						{if $product.DefaultImage.ID}
							{img src=$product.DefaultImage.urls.1 alt=$product.name_lang|escape}
						{else}
							{img src='MISSING_IMG_THUMB'|config alt=$product.name_lang|escape}
						{/if}
						</a>
					</td>
				{/if}

				{if $columns.SKU}
					<td class="productSku text"><a href="{productUrl product=$product filterChainHandle=$filterChainHandle category=$category}">[[product.sku]]</a></td>
				{/if}

				{if $columns.NAME}
					<td class="productName text"><a href="{productUrl product=$product filterChainHandle=$filterChainHandle category=$category}">[[product.name_lang]]</a></td>
				{/if}

				{foreach from=$listAttributes item=attribute}
					<td class="attribute attr_[[attribute.ID]]">{include file="product/attributeValue.tpl" attr=$product.attributes[$attribute.ID]}</td>
				{/foreach}

				{if $columns.PRICE && 'DISPLAY_PRICES'|config}
					<td class="productPrice">{include file="product/block/productPrice.tpl"}</td>
				{/if}

				{if $columns.DETAILS}
					<td class="productDetails"><a href="{productUrl product=$product filterChainHandle=$filterChainHandle category=$category}">{t _view_details}</a></td>
				{/if}
			</tr>
		{/foreach}
	</tbody>
</table>