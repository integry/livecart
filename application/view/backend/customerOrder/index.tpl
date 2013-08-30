{includeJs file="library/dhtmlxtree/dhtmlXCommon.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree.js"}
{includeJs file="library/dhtmlxtree/dhtmlXTree_start.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/SectionExpander.js"}

{includeCss file="library/dhtmlxtree/dhtmlXTree.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="library/ActiveList.css"}

{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/Backend.css"}

{includeJs file="backend/Shipment.js"}
{includeCss file="backend/Shipment.css"}

{includeJs file="backend/User.js"}
{includeCss file="backend/User.css"}

{includeJs file="backend/Payment.js"}
{includeCss file="backend/Payment.css"}

{includeJs file="backend/OrderNote.js"}
{includeCss file="backend/OrderNote.css"}

{includeCss file="backend/OrderLog.css"}

{includeJs file="backend/CustomerOrder.js"}
{includeCss file="backend/CustomerOrder.css"}

{includeJs file="frontend/Frontend.js"} {*variations*}

[[ partial("backend/eav/includes.tpl") ]]

{pageTitle help="order"}{t _livecart_orders}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="orderGroupsWrapper" class="treeContainer">
	<div id="orderGroupsBrowser" class="treeBrowser"></div>
</div>

[[ partial("backend/customerOrder/orderContainer.tpl") ]]
[[ partial("backend/customerOrder/groupContainer.tpl") ]]
[[ partial("backend/userGroup/userContainer.tpl") ]]



<script type="text/javascript">
	Backend.CustomerOrder.Editor.prototype.Links.switchCancelled = '{link controller="backend.customerOrder" action=switchCancelled}';

	Backend.CustomerOrder.Links.selectCustomer = '{link controller="backend.customerOrder" action=selectCustomer}';
	Backend.CustomerOrder.Links.createOrder = '{link controller="backend.customerOrder" action=create}';
	Backend.CustomerOrder.Messages.selecCustomerTitle = '{t _select_customer_title}';
	Backend.CustomerOrder.Messages.areYouSureYouWantToUpdateOrderStatus = '{t _are_you_sure_you_want_to_update_order_status|escape}';

	Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToActivateThisOrder = '{t _are_you_sure_you_want_activate_this_order|escape}';
	Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToCancelThisOrder = '{t _are_you_sure_you_want_cancel_this_order|escape}';
	Backend.CustomerOrder.Editor.prototype.Messages.orderNum = '{t _order_number|escape}';
	new Backend.CustomerOrder({json array=$orderGroups});


		{allowed role="order"}
			Backend.CustomerOrder.prototype.ordersMiscPermission = true;
		{/allowed}

		{allowed role="user"}
			Backend.CustomerOrder.prototype.usersMiscPermission = true;
		{/allowed}

	Backend.showContainer("orderGroupsManagerContainer");
	window.ordersActiveGrid = {};
</script>



[[ partial("layout/backend/footer.tpl") ]]