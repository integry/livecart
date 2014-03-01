{assign var="columns" value=config('CATEGORY_COLUMNS')}
{% if !empty(subCategories) %}
{math count=subCategories|@count equation="max(1, ceil(count / columns))" assign="rows"}
{math count=subCategories|@count equation="min(12, ceil(12 / columns))" assign="width"}
{% endif %}

<div class="subCategories row">
{section name="rows" start=0 loop=rows}{% set row = smarty.section.rows %}

	{section name="columns" start=0 loop=columns}{% set col = smarty.section.columns %}

		{% if 'CAT_HOR' == config('CATEGORY_COL_DIRECTION') %}
			{assign var=colOffset value=row.index*columns}
			{assign var=index value=colOffset+col.index}
		{% else %}
			{assign var=colOffset value=col.index*rows}
			{assign var=index value=colOffset+row.index}
		{% endif %}

		{assign var=cat value=subCategories[index]}

		{% if !empty(cat) %}
		<div class="col-sm-[[width]] subCategoryItem">
			<div class="thumbnail">
				[[ partial('category/block/categoryItem.tpl', ['sub': cat]) ]]
			</div>
		</div>
		{% endif %}

	{/section}

{/section}
</div>
