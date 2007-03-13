<?xml version="1.0" encoding="UTF-8"?>
<ajax-response>
	<response type="object" id="products_{$categoryID}_updater">
		<rows update_ui="true" total_rows="{$totalCount}">
			{include file="backend/product/productList.tpl"}	
		</rows>
	</response>
</ajax-response>