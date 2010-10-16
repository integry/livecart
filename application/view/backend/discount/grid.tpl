{literal}
<script type="text/javascript">
	Backend.Discount.GridFormatter.url = '{/literal}{link controller="backend.discount"}{literal}';
</script>
{/literal}

{activeGrid
	prefix="discount"
	id=0
	role="product.mass"
	controller="backend.discount" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	container="discountGrid"
	dataFormatter="Backend.Discount.GridFormatter"
	massAction="backend/discount/massAction.tpl"
	count="backend/discount/gridCount.tpl"
}

{literal}
<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('discountMass'), window.activeGrids['discount_0']);
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_rule|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;
</script>
{/literal}
