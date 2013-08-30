<div>

	<ul class="menu" >
		<li class="createNewOrder"><a href="#" id="createNewOrderLink_[[orderGroupID]]"  {denied role='order.create' }style="display: none"{/denied}>{t _create_order}</a><span class="progressIndicator" style="display: none;"></span></li>
	</ul>

	{literal}
	<script type="text/javascript">
		if ({/literal}{json array=$userID}{literal} != null)
		{
			Backend.CustomerOrder.Links.createOrder = '{/literal}{link controller="backend.customerOrder" action=create}{literal}';
			Event.observe($("{/literal}createNewOrderLink_[[orderGroupID]]{literal}"), "click", function(e)
			{
				Backend.CustomerOrder.prototype.createUserOrder('{/literal}[[userID]]{literal}', $("{/literal}createNewOrderLink_[[orderGroupID]]"), '{link controller="backend.customerOrder"}');
				{literal}
				e.preventDefault();
			});
		}
		else
		{
			Event.observe($("{/literal}createNewOrderLink_[[orderGroupID]]{literal}"), "click", function(e)
			{
				e.preventDefault();

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

{if req('userOrderID')}
	Backend.User.OrderGridFormatter = Backend.CustomerOrder.GridFormatter;
{/if}

</script>


{activeGrid
	prefix="orders"
	id=$orderGroupID
	role="order.mass"
	controller="backend.customerOrder"
	action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	rowCount=15
	showID=true
	container="tabPageContainer"
	filters=$filters
	dataFormatter=$dataFormatter
	count="backend/customerOrder/count.tpl"
	massAction="backend/customerOrder/massAction.tpl"
	advancedSearch=true
}

<li class="detailedExport" id="detailedExportContainer_[[orderGroupID]]">
	<a href="#" onclick="var grid = window.activeGrids['[[prefix]]_[[id]]']; window.location.href='{link controller="backend.customerOrder" action=exportDetailed}?' + grid.ricoGrid.getQueryString()+ '&selectedIDs=' + grid.getSelectedIDs().toJSON() + '&isInverse=' + (grid.isInverseSelection() ? 1 : 0); return false;">{t _detailed_export}</a>
</li>

{literal}
<script type="text/javascript">

/*
	var detailedExport = $('detailedExportContainer_{/literal}[[orderGroupID]]{literal}');
	var menu = detailedExport.up('.tabPageContainer').down('.activeGridColumns').down('.menu', 1);
	menu.insertBefore(detailedExport, menu.firstChild);
*/

	var massHandler = new ActiveGrid.MassActionHandler($('{/literal}orderMass_[[orderGroupID]]{literal}'),
													   window.activeGrids['{/literal}orders_[[orderGroupID]]{literal}'],
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
	ordersActiveGrid['{/literal}[[orderGroupID]]{literal}'] = window.activeGrids['{/literal}orders_[[orderGroupID]]{literal}'];
</script>
{/literal}
