
<script type="text/javascript">
	Backend.Discount.GridFormatter.url = '[[ url("backend.discount") ]]';
</script>


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


<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('discountMass'), window.activeGrids['discount_0']);
	massHandler.deleteConfirmMessage = '[[ addslashes({t _are_you_sure_you_want_to_delete_rule}) ]]' ;
	massHandler.nothingSelectedMessage = '[[ addslashes({t _nothing_selected}) ]]' ;
</script>

