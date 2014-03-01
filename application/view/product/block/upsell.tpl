{% set SHOW_UPSELL_GROUPS = false %}
{% if !empty(upsell) %}
<tr>
	<td colspan="2" id="upsellProducts">
		<fieldset id="upsellProducts">
			<legend>{t _you_may_also_like}</legend>
			{foreach from=$upsell item=group}

				{% if !empty(SHOW_UPSELL_GROUPS) %}
					<fieldset>
						{% if $group.0.ProductRelationshipGroup.name() %}
							<legend>[[group.0.ProductRelationshipGroup.name()]]</legend>
						{% endif %}
				{% endif %}

				<ul class="productList">
					{foreach from=$group item=product name="productList"}
						<li class="{% if $product.isFeatured %}featured{% endif %}">
							<div class="checkProduct">
								<input type="checkbox" name="productIDs[]" value="[[product.ID]]" />
								<input type="hidden" name="product_[[product.ID]]_count" value="1" />
							</div>
							[[ partial("/block/box/menuProductListItem.tpl") ]]
							{% if !$smarty.foreach.productList.last && $SHOW_UPSELL_GROUPS %}
								<div class="productSeparator"></div>
							{% endif %}
						</li>
					{/foreach}
				</ul>

				{% if !empty(SHOW_UPSELL_GROUPS) %}
					</fieldset>
				{% endif %}

			{/foreach}
		</fieldset>
	</td>
</tr>
{% endif %}