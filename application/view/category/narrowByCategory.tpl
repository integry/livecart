{math count=$categoryNarrow|@count equation="max(1, ceil(count / 2))" assign="perColumn"}

<div class="narrowByCat">
	<div class="resultStats">
		{t _narrow_by_cat}
	</div>

	{include file="category/subcategoriesColumns.tpl" subCategories=$categoryNarrow filters=$appliedFilters}

</div>