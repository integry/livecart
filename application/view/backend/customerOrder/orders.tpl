<div>

	<ul class="menu" >
		<li class="createNewOrder"><a href="#" id="createNewOrderLink_[[orderGroupID]]"  {denied role='order.create' }style="display: none"{/denied}>{t _create_order}</a><span class="progressIndicator" style="display: none;"></span></li>
	</ul>


	<script type="text/javascript">
		if ({json array=$userID} != null)
		{
			Backend.CustomerOrder.Links.createOrder = '{link controller="backend.customerOrder" action=create}';
			Event.observe($("createNewOrderLink_[[orderGroupID]]"), "click", function(e)
			{
				Backend.CustomerOrder.prototype.createUserOrder('[[userID]]', $("createNewOrderLink_[[orderGroupID]]"), '{link controller="backend.customerOrder"}');

				e.preventDefault();
			});
		}
		else
		{
			Event.observe($("createNewOrderLink_[[orderGroupID]]"), "click", function(e)
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



<script type="text/javascript">
	Backend.CustomerOrder.GridFormatter.orderUrl = '{backendOrderUrl}';
	Backend.User.OrderGridFormatter.orderUrl = '{backendOrderUrl}';


{% if !empty(userID) %}
	{assign var=dataFormatter value="Backend.User.OrderGridFormatter"};
{% else %}
	{assign var=dataFormatter value="Backend.CustomerOrder.GridFormatter"};
{% endif %}

{% if req('userOrderID') %}
	Backend.User.OrderGridFormatter = Backend.CustomerOrder.GridFormatter;
{% endif %}

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


<script type="text/javascript">

/*
	var detailedExport = $('detailedExportContainer_[[orderGroupID]]');
	var menu = detailedExport.up('.tabPageContainer').down('.activeGridColumns').down('.menu', 1);
	menu.insertBefore(detailedExport, menu.firstChild);
*/

	var massHandler = new ActiveGrid.MassActionHandler($('orderMass_[[orderGroupID]]'),
													   window.activeGrids['orders_[[orderGroupID]]'],
														{
															onComplete:
																function()
																{
																	Backend.CustomerOrder.Editor.prototype.resetEditors();
																}
														}
													   );
	massHandler.deleteConfirmMessage = '[[ addslashes({t _are_you_sure_you_want_to_delete_this_order}) ]]' ;
	massHandler.nothingSelectedMessage = '[[ addslashes({t _nothing_selected}) ]]' ;
	ordersActiveGrid['[[orderGroupID]]'] = window.activeGrids['orders_[[orderGroupID]]'];
</script>

