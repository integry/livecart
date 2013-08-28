{% set SHOW_UPSELL_GROUPS = false %}
{if $upsell}
<tr>
	<td colspan="2" id="upsellProducts">
		<fieldset id="upsellProducts">
			<legend>{t _you_may_also_like}</legend>
			{foreach from=$upsell item=group}

				{if $SHOW_UPSELL_GROUPS}
					<fieldset>
						{if $group.0.ProductRelationshipGroup.name_lang}
							<legend>[[group.0.ProductRelationshipGroup.name_lang]]</legend>
						{/if}
				{/if}

				<ul class="productList">
					{foreach from=$group item=product name="productList"}
						<li class="{if $product.isFeatured}featured{/if}">
							<div class="checkProduct">
								<input type="checkbox" name="productIDs[]" value="[[product.ID]]" />
								<input type="hidden" name="product_[[product.ID]]_count" value="1" />
							</div>
							[[ partial("/block/box/menuProductListItem.tpl") ]]
							{if !$smarty.foreach.productList.last && $SHOW_UPSELL_GROUPS}
								<div class="productSeparator"></div>
							{/if}
						</li>
					{/foreach}
				</ul>

				{if $SHOW_UPSELL_GROUPS}
					</fieldset>
				{/if}

			{/foreach}
		</fieldset>
	</td>
</tr>
{/if}