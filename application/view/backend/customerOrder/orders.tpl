<div>

<fieldset class="container activeGridControls">


	<ul class="menu" >
		<li class="createNewOrder"><a href="#" id="createNewOrderLink_{$orderGroupID}"  {denied role='order.create' }style="display: none"{/denied}>{t _create_order}</a><span class="progressIndicator" style="display: none;"></span></li>
	</ul>
	<br class="clear" />
	{literal}
	<script type="text/javascript">
		if ({/literal}{json array=$userID}{literal} != null)
		{
			Backend.CustomerOrder.Links.createOrder = '{/literal}{link controller=backend.customerOrder action=create}{literal}';
			Event.observe($("{/literal}createNewOrderLink_{$orderGroupID}{literal}"), "click", function(e)
			{
				Backend.CustomerOrder.prototype.createUserOrder('{/literal}{$userID}{literal}', $("{/literal}createNewOrderLink_{$orderGroupID}"), '{link controller=backend.customerOrder}');
				{literal}
				Event.stop(e);
			});
		}
		else
		{
			Event.observe($("{/literal}createNewOrderLink_{$orderGroupID}{literal}"), "click", function(e)
			{
				Event.stop(e);

				Backend.CustomerOrder.prototype.customerPopup = new Backend.SelectPopup(
					Backend.CustomerOrder.Links.selectCustomer,
					Backend.CustomerOrder.Messages.selecCustomerTitle,
					{
						onObjectSelect: function()
						{
						   this.popup.document.getElementById('userIndicator_' + this.objectID).show();
						   Backend.CustomerOrder.prototype.instance.createNewOrder(this.objectID);
						}
					}
				);
			});
		}
	</script>
	{/literal}

	<span style="{denied role="order.mass"}visibility: hidden;{/denied}" id="orderMass_{$orderGroupID}" class="activeGridMass">

		{form action="controller=backend.customerOrder action=processMass id=$orderGroupID" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select">
			{if $orderGroupID != 9}
				<optgroup label="{t _order_status}" class="massStatus">
					<option value="setNew">{t _set_new}</option>
					<option value="setProcessing">{t _set_processing}</option>
					<option value="setAwaitingShipment">{t _set_awaiting_shipment}</option>
					<option value="setShipped">{t _set_shipped}</option>
					<option value="setReturned">{t _set_returned}</option>
				</optgroup>
				<option value="setCancel" class="massCancel">{t _cancel}</option>
			{/if}
			<option value="delete" class="delete" {if $orderGroupID == 8}selected="selected"{/if}>{t _delete}</option>
		</select>

		<span class="bulkValues" style="display: none;">

		</span>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span class="orderCount" id="orderCount_{$orderGroupID}" >
			<span class="rangeCount" style="display: none;">{t _listing_orders}</span>
			<span class="notFound" style="display: none;">{t _no_orders}</span>
		</span>
	</span>

</fieldset>

{literal}
<script type="text/javascript">
	Backend.CustomerOrder.GridFormatter.orderUrl = '{/literal}{backendOrderUrl}{literal}';
	Backend.User.OrderGridFormatter.orderUrl = '{/literal}{backendOrderUrl}{literal}';
{/literal}

{if $userID}
	{assign var=dataFormatter value="Backend.User.OrderGridFormatter"};
{else}
	{assign var=dataFormatter value="Backend.CustomerOrder.GridFormatter"};
{/if}

{if $request.userOrderID}
	Backend.User.OrderGridFormatter = Backend.CustomerOrder.GridFormatter;
{/if}

</script>


{activeGrid
	prefix="orders"
	id=$orderGroupID
	role="order.mass"
	controller="backend.customerOrder" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	rowCount=15
	showID=true
	container="tabPageContainer"
	filters=$filters
	dataFormatter=$dataFormatter
}

<li class="detailedExport" id="detailedExportContainer_{$orderGroupID}">
	<a href="#" onclick="var grid = window.activeGrids['{$prefix}_{$id}']; window.location.href='{link controller=backend.customerOrder action=exportDetailed}?' + grid.ricoGrid.getQueryString()+ '&selectedIDs=' + grid.getSelectedIDs().toJSON() + '&isInverse=' + (grid.isInverseSelection() ? 1 : 0); return false;">{t _detailed_export}</a>
</li>

{literal}
<script type="text/javascript">

	var detailedExport = $('detailedExportContainer_{/literal}{$orderGroupID}{literal}');
	var menu = detailedExport.up('.tabPageContainer').down('.activeGridColumns').down('.menu', 1);
	menu.insertBefore(detailedExport, menu.firstChild);

	var massHandler = new ActiveGrid.MassActionHandler($('{/literal}orderMass_{$orderGroupID}{literal}'),
													   window.activeGrids['{/literal}orders_{$orderGroupID}{literal}'],
														{
															onComplete:
																function()
																{
																	Backend.CustomerOrder.Editor.prototype.resetEditors();
																}
														}
													   );
	massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_this_order|addslashes}{literal}' ;
	massHandler.nothingSelectedMessage = '{/literal}{t _nothing_selected|addslashes}{literal}' ;
	ordersActiveGrid['{/literal}{$orderGroupID}{literal}'] = window.activeGrids['{/literal}orders_{$orderGroupID}{literal}'];
</script>
{/literal}
