{assign var="columns" value='CATEGORY_COLUMNS'|config}
{if $subCategories}
{math count=$subCategories|@count equation="max(1, ceil(count / $columns))" assign="rows"}
{math count=$subCategories|@count equation="min(12, ceil(12 / (count / $columns)))" assign="width"}
{/if}

<ul class="subCategories thumbnails-fluid row-fluid">
{section name="rows" start=0 loop=$rows}{assign var="row" value=$smarty.section.rows}

	{section name="columns" start=0 loop=$columns}{assign var="col" value=$smarty.section.columns}

		{if 'CAT_HOR' == 'CATEGORY_COL_DIRECTION'|config}
			{assign var=colOffset value=$row.index*$columns}
			{assign var=index value=$colOffset+$col.index}
		{else}
			{assign var=colOffset value=$col.index*$rows}
			{assign var=index value=$colOffset+$row.index}
		{/if}

		{assign var=cat value=$subCategories[$index]}

		{if $cat}
		<li class="col-span-{$width} thumbnail subCategoryItem">
			{include file="category/block/categoryItem.tpl" sub=$cat}
		</li>
		{/if}

	{/section}

{/section}
</ul>

<script type="text/javascript">
	evenHeights('.subCategories .subCategoryItem');
</script>
