{assign var="columns" value='CATEGORY_COLUMNS'|config}
{if $subCategories}
{math count=$subCategories|@count equation="max(1, ceil(count / $columns))" assign="rows"}
{math count=$subCategories|@count equation="min(12, ceil(12 / $columns))" assign="width"}
{/if}

<div class="subCategories row">
{section name="rows" start=0 loop=$rows}{% set row = $smarty.section.rows %}

	{section name="columns" start=0 loop=$columns}{% set col = $smarty.section.columns %}

		{if 'CAT_HOR' == 'CATEGORY_COL_DIRECTION'|config}
			{assign var=colOffset value=$row.index*$columns}
			{assign var=index value=$colOffset+$col.index}
		{else}
			{assign var=colOffset value=$col.index*$rows}
			{assign var=index value=$colOffset+$row.index}
		{/if}

		{assign var=cat value=$subCategories[$index]}

		{if $cat}
		<div class="col col-lg-[[width]] subCategoryItem">
			<div class="thumbnail">
				{include file="category/block/categoryItem.tpl" sub=$cat}
			</div>
		</div>
		{/if}

	{/section}

{/section}
</div>
