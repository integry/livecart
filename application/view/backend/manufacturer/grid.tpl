{literal}
<script type="text/javascript">
	Backend.Manufacturer.GridFormatter.url = '{/literal}{link controller="backend.manufacturer"}{literal}';
</script>
{/literal}

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

{literal}
<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('manufacturerMass'), window.activeGrids['manufacturer_0']);
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_manufacturer|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;
</script>
{/literal}
