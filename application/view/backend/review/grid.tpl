<fieldset class="container activeGridControls">

	<span class="activeGridMass" {denied role="product.mass"}style="visibility: hidden;"{/denied} id="manufacturerMass" >

		{form action="controller=backend.manufacturer action=processMass" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select">
			<option value="delete">{t _delete}</option>
		</select>

		<span class="bulkValues" style="display: none;"></span>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span id="userCount_{$userGroupID}">
			<span class="rangeCount">{t _listing_manufacturers}</span>
			<span class="notFound" style="display: none;">{t _no_manufacturers}</span>
		</span>
	</span>

</fieldset>

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
}

{literal}
<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('manufacturerMass'), window.activeGrids['manufacturer_0']);
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_manufacturer|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;
</script>
{/literal}
