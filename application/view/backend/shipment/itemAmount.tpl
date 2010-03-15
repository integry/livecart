<table class="orderShipmentsItem_info">
	<tr>
		<td class="orderShipmentsItem_info_sku_td">
		   <div class="orderShipmentsItem_info_sku">
			   {$item.Product.sku}

				{if $item.Product.DefaultImage.paths.1}
					<a href="{$item.Product.DefaultImage.paths.4}" rel="lightbox"><img src="{$item.Product.DefaultImage.paths.1}" /></a>
				{/if}
		   </div>
		</td>
		<td class="orderShipmentsItem_info_name_td">
			<div class="orderShipmentsItem_info_name">
				{if $item.Product.ID}
					<a href="{backendProductUrl product=$item.Product}">{$item.Product.name_lang}</a>
				{else}
					<span>{$item.Product.name_lang}</span>
				{/if}
				{if $allOptions[$item.Product.ID] || $item.options}
					({$item.formattedBasePrice})
					<div class="productOptions">
						<ul class="itemOptions">
						{foreach from=$item.options item=option}
							<li>
								{$option.Choice.Option.name_lang}:
								{if 0 == $option.Choice.Option.type}
									{t _option_yes}
								{elseif 1 == $option.Choice.Option.type}
									{$option.Choice.name_lang}
								{elseif 3 == $option.Choice.Option.type}
									<a href="{link controller=backend.orderedItem action=downloadOptionFile id=$item.ID query="option=`$option.Choice.Option.ID`"}">{$option.fileName}</a>
									{if $option.small_url}
										<div class="optionImage">
											<a href="{$option.large_url}" rel="lightbox"><img src="{$option.small_url}" /></a>
										</div>
									{/if}
								{else}
									{$option.optionText|@htmlspecialchars}
								{/if}

								{if $option.priceDiff != 0}
									<span class="optionPrice">({$option.formattedPrice})</span>
								{/if}
							</li>
						{/foreach}
						</ul>

						<div class="menu productOptionsMenu">
							<a href="{link controller=backend.orderedItem action=optionForm id=$item.ID}" onclick="Backend.OrderedItem.loadOptionsForm(event);">{t _edit_options}</a>
							<span class="progressIndicator" style="display: none;"></span>
						</div>
					</div>
				{/if}

				{if $item.Product.variations || $variations.variations[$item.Product.ID]}
					<div class="productOptions">
						{include file="order/itemVariations.tpl"}
						<div class="menu productOptionsMenu">
							<a href="{link controller=backend.orderedItem action=variationForm id=$item.ID}" id="variationsMenuLink_{$item.ID}" onclick="Backend.OrderedItem.loadOptionsForm(event);">{t _edit_variations}</a>
							<span class="progressIndicator" style="display: none;"></span>
						</div>

						{if !$item.Product.variations}
							<script type="text/javascript">
								Backend.OrderedItem.loadOptionsForm($("variationsMenuLink_{$item.ID}"));
							</script>
						{/if}
					</div>
				{/if}

				{if $downloadCount[$item.ID]}
					<div class="itemDownloadStats">
						{t _times_downloaded}: {$downloadCount[$item.ID]}
					</div>
				{/if}

			</div>
		</td>
		<td class="orderShipmentsItem_info_price_td">
			<div class="orderShipmentsItem_info_price">
				<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
				<span class="price">{$item.itemPrice|string_format:"%.2f"}</span>
				<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
			</div>
		</td>
		<td class="orderShipmentsItem_info_count_td">
			<div class="orderShipmentsItem_info_count">
				<span class="progressIndicator" style="display: none;"></span>
				<input name="count_{$item.ID}" value="{$item.count}" id="orderShipmentsItem_count_{$item.ID}" class="orderShipmentsItem_count" style="{if $item.Shipment.status == 3 || $shipment.status == 3}display: none;{/if}" {denied role='order.update'}readonly="readonly"{/denied}  />
				<span class="itemCountText">{$item.count}</span>
			</div>
		</td>
		<td class="orderShipmentsItem_info_total_td ">
			<div class="orderShipmentsItem_info_total item_subtotal">
				<span class="pricePrefix">{$shipment.AmountCurrency.pricePrefix}</span>
				<span class="price">{$item.displaySubTotal|string_format:"%.2f"}</span>
				<span class="priceSuffix">{$shipment.AmountCurrency.priceSuffix}</span>
			</div>
		</td>
	</tr>
</table>

