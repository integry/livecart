
<script type="text/javascript">
	Backend.Manufacturer.GridFormatter.url = '{link controller="backend.manufacturer"}';
</script>


{activeGrid
	prefix="manufacturer"
	id=0
	role="product.mass"
	controller="backend.manufacturer" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	container="manufacturerGrid"
	dataFormatter="Backend.Manufacturer.GridFormatter"
	count="backend/manufacturer/count.tpl"
	massAction="backend/manufacturer/massAction.tpl"
}


<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('manufacturerMass'), window.activeGrids['manufacturer_0']);
	massHandler.deleteConfirmMessage = '{t _are_you_sure_you_want_to_delete_manufacturer|addslashes}' ;
	massHandler.nothingSelectedMessage = '{t _nothing_selected|addslashes}' ;
</script>

