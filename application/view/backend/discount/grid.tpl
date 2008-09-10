<fieldset class="container activeGridControls">

	<span class="activeGridMass" {denied role="product.mass"}style="visibility: hidden;"{/denied} id="discountMass" >

		{form action="controller=backend.discount action=processMass" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select">
			<option value="delete">{t _delete}</option>
			<option value="enable_isEnabled">{t _enable}</option>
			<option value="disable_isEnabled">{t _disable}</option>
		</select>

		<span class="bulkValues" style="display: none;"></span>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span id="userCount_{$userGroupID}">
			<span class="rangeCount" style="display: none;">{t _listing_rules}</span>
			<span class="notFound" style="display: none;">{t _no_rules}</span>
		</span>
	</span>

</fieldset>

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
}

{literal}
<script type="text/javascript">
	var massHandler = new ActiveGrid.MassActionHandler($('discountMass'), window.activeGrids['discount_0']);
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_rule|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;
</script>
{/literal}
