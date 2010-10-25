{assign var="columns" value='CATEGORY_COLUMNS'|config}
{math count=$subCategories|@count equation="max(1, ceil(count / $columns))" assign="rows"}

<table class="subCategories">
{section name="rows" start=0 loop=$rows}{assign var="row" value=$smarty.section.rows}

	<tr class="subCategoryRow {if $row.first} first{/if}{if $row.last} last{/if}">
	{section name="columns" start=0 loop=$columns}{assign var="col" value=$smarty.section.columns}

		{if 'CAT_HOR' == 'CATEGORY_COL_DIRECTION'|config}
			{assign var=colOffset value=$row.index*$columns}
			{assign var=index value=$colOffset+$col.index}
		{else}
			{assign var=colOffset value=$col.index*$rows}
			{assign var=index value=$colOffset+$row.index}
		{/if}

		{assign var=cat value=$subCategories[$index]}

		<td class="{if !$cat}empty{/if} {if $col.first} first{/if}{if $col.last} last{/if} subCategoryItem">
			{if $cat}
				{include file="category/block/categoryItem.tpl" sub=$cat}
			{/if}
		</td>

	{/section}
	</tr>
{/section}
</table>