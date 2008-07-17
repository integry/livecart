{if $product.longDescription_lang || $product.shortDescription_lang}
<h2>{t _description}</h2>
<div id="productDescription">
	{if $product.longDescription_lang}
		{$product.longDescription_lang}
	{else}
		{$product.shortDescription_lang}
	{/if}
</div>
{/if}

{if $product.attributes}
<h2>{t _spec}</h2>
<div id="productSpecification">
	<table class="productTable">
		{foreach from=$product.attributes item="attr" name="attributes"}

			{if $prevAttr.SpecField.SpecFieldGroup.ID != $attr.SpecField.SpecFieldGroup.ID}
				<tr class="specificationGroup heading{if $smarty.foreach.attributes.first} first{/if}">
					<td class="param">{$attr.SpecField.SpecFieldGroup.name_lang}</td>
					<td class="value"></td>
				</tr>
			{/if}
			<tr class="{zebra loop="attributes"} {if $smarty.foreach.attributes.first && !$attr.SpecField.SpecFieldGroup.ID}first{/if}{if $smarty.foreach.attributes.last} last{/if}">
				<td class="param">{$attr.SpecField.name_lang}</td>
				<td class="value">
					{if $attr.values}
						<ul class="attributeList{if $attr.values|@count == 1} singleValue{/if}">
							{foreach from=$attr.values item="value"}
								<li> {$value.value_lang}</li>
							{/foreach}
						</ul>
					{elseif $attr.value_lang}
						{$attr.value_lang}
					{elseif $attr.value}
						{$attr.SpecField.valuePrefix_lang}{$attr.value}{$attr.SpecField.valueSuffix_lang}
					{/if}
				</td>
			</tr>
			{assign var="prevAttr" value=$attr}

		{/foreach}
	</table>
</div>
{/if}

{if $related}
<h2>{t _recommended}</h2>
<div id="relatedProducts">

	{foreach from=$related item=group}

	   {if $group.0.ProductRelationshipGroup.name_lang}
		   <h3>{$group.0.ProductRelationshipGroup.name_lang}</h3>
	   {/if}

	   {include file="category/productList.tpl" products=$group}

	{/foreach}

</div>
{/if}

{if $together}
<h2>{t _purchased_together}</h2>
<div id="purchasedTogether">
	{include file="category/productList.tpl" products=$together}
</div>
{/if}