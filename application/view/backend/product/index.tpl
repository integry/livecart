<div>

<fieldset class="container">
	<ul class="menu">
		<li>
			<a href="#" onclick="Backend.Product.showAddForm(this.parentNode.parentNode.parentNode, {$categoryID}); return false;">
				Add New Product
			</a>
			<span class="progressIndicator" style="display: none;"></span>
		</li>
	</ul>  
</fieldset>

<br/>

<span id="bookmark"></span>

<div style="width: 98%;">
<table class="productHead" id="products_{$categoryID}_header">
	<tr class="headRow">
		<th class="cell_cb"></th>
		<th class="first cell_sku">SKU</th>
		<th class="cell_name">Name</th>	
		<th class="cell_manuf">Manufacturer</th>	
		<th class="cell_price">Price <small>({$currency})</small></th>
		<th class="cell_stock">In stock</th>	
		<th class="cell_enabled">Enabled</th>	
	</tr>
</table>
</div>

<div style="width: 98%;">
<table class="productList" id="products_{$categoryID}">
	<tbody>
		{include file="backend/product/productList.tpl"}
	</tbody>
</table>
</div>

</div>

{literal}
<script type="text/javascript">
   function updateHeader( liveGrid, offset ) {
      $('bookmark').innerHTML = "Listing products " + (offset+1) + " - " + (offset+liveGrid.metaData.getPageSize()) + " of " + 
      liveGrid.metaData.getTotalRows();
      var sortInfo = "";
      if (liveGrid.sortCol) {
         sortInfo = "&data_grid_sort_col=" + liveGrid.sortCol + "&data_grid_sort_dir=" + liveGrid.sortDir;
      }
   }
	
	new ActiveGrid($('products_{/literal}{$categoryID}'), '{link controller=backend.product action=lists}');{literal}

</script>
{/literal}