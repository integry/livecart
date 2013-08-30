<table class="orderShipmentsItem_info">
	<tr>
		<td class="orderShipmentsItem_info_sku_td">
		   <div class="orderShipmentsItem_info_sku">
			   [[item.Product.sku]]

				{% if $item.Product.DefaultImage.urls.1 %}
					<a href="[[item.Product.DefaultImage.urls.4]]" rel="lightbox"><img src="[[item.Product.DefaultImage.urls.1]]" /></a>
				{% endif %}
		   </div>
		</td>
		<td class="orderShipmentsItem_info_name_td">
			<div class="orderShipmentsItem_info_name">
				{% if $item.Product.ID %}
					<a href="{backendProductUrl product=$item.Product}">[[item.Product.name_lang]]</a>
					<a class="external" href="{productUrl product=$item.Product}" target="_blank"></a>
				{% else %}
					<span>[[item.Product.name_lang]]</span>
				{% endif %}
				{% if $allOptions[$item.Product.ID] || $item.options %}
					<span class="basePrice">([[item.formattedBasePrice]])</span>
					<div class="productOptions">
						<ul class="itemOptions">
						{foreach from=$item.options item=option}
							<li>
								[[option.Choice.Option.name_lang]]:
								{% if 0 == $option.Choice.Option.type %}
									{t _option_yes}
								{% elseif 1 == $option.Choice.Option.type %}
									[[option.Choice.name_lang]]
								{% elseif 3 == $option.Choice.Option.type %}
									<a href="[[ url("backend.orderedItem/downloadOptionFile/" ~ item.ID, "option=`$option.Choice.Option.ID`") ]]">[[option.fileName]]</a>
									{% if $option.small_url %}
										<div class="optionImage">
											<a href="[[option.large_url]]" rel="lightbox"><img src="[[option.small_url]]" /></a>
										</div>
									{% endif %}
								{% else %}
									{$option.optionText|@htmlspecialchars}
								{% endif %}

								{% if $option.priceDiff != 0 %}
									<span class="optionPrice">([[option.formattedPrice]])</span>
								{% endif %}
							</li>
						{/foreach}
						</ul>

						<div class="menu productOptionsMenu">
							<a href="[[ url("backend.orderedItem/optionForm/" ~ item.ID) ]]" onclick="Backend.OrderedItem.loadOptionsForm(event);">{t _edit_options}</a>
							<span class="progressIndicator" style="display: none;"></span>
						</div>
					</div>
				{% endif %}

				{% if $item.Product.variations || $variations.variations[$item.Product.ID] %}
					<div class="productOptions">
						[[ partial("order/itemVariations.tpl") ]]
						<div class="menu productOptionsMenu">
							<a href="[[ url("backend.orderedItem/variationForm/" ~ item.ID) ]]" id="variationsMenuLink_[[item.ID]]" onclick="Backend.OrderedItem.loadOptionsForm(event);">{t _edit_variations}</a>
							<span class="progressIndicator" style="display: none;"></span>
						</div>

						{% if !$item.Product.variations %}
							<script type="text/javascript">
								Backend.OrderedItem.loadOptionsForm($("variationsMenuLink_[[item.ID]]"));
							</script>
						{% endif %}
					</div>
				{% endif %}

				{% if $downloadCount[$item.ID] %}
					<div class="itemDownloadStats">
						{t _times_downloaded}: {$downloadCount[$item.ID]}
					</div>
				{% endif %}

			</div>
		</td>
		<td class="orderShipmentsItem_info_price_td">
			<div class="orderShipmentsItem_info_price">
				<span class="pricePrefix">[[shipment.AmountCurrency.pricePrefix]]</span>
				<input name="price_[[item.ID]]" id="orderShipmentsItem_price_[[item.ID]]" class="price orderShipmentsItem_price" {% if $item.Shipment.status == 3 || $shipment.status == 3 %}readonly="readonly"{% endif %} {denied role='order.update'}readonly="readonly"{/denied} value="{$item.itemPrice|string_format:"%.2f"}" />
				<span class="priceSuffix">[[shipment.AmountCurrency.priceSuffix]]</span>
			</div>
		</td>
		<td class="orderShipmentsItem_info_count_td">
			<div class="orderShipmentsItem_info_count">
				<span class="progressIndicator" style="display: none;"></span>
				<input name="count_[[item.ID]]" value="[[item.count]]" id="orderShipmentsItem_count_[[item.ID]]" class="orderShipmentsItem_count" style="{% if $item.Shipment.status == 3 || $shipment.status == 3 %}display: none;{% endif %}" {denied role='order.update'}readonly="readonly"{/denied}  />
				<span class="itemCountText">[[item.count]]</span>
			</div>
		</td>
		<td class="orderShipmentsItem_info_total_td ">
			<div class="orderShipmentsItem_info_total item_subtotal">
				<span class="pricePrefix">[[shipment.AmountCurrency.pricePrefix]]</span>
				<span class="price">{$item.displaySubTotal|string_format:"%.2f"}</span>
				<span class="priceSuffix">[[shipment.AmountCurrency.priceSuffix]]</span>
			</div>
		</td>
	</tr>
</table>

