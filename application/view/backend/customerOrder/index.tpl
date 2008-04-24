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

{pageTitle help="order"}{t _livecart_orders}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="orderGroupsWrapper" class="treeContainer maxHeight h--50">
	<div id="orderGroupsBrowser" class="treeBrowser"></div>
</div>

{include file="backend/customerOrder/orderContainer.tpl"}
{include file="backend/customerOrder/groupContainer.tpl"}
{include file="backend/userGroup/userContainer.tpl"}


{literal}
<script type="text/javascript">
	Backend.CustomerOrder.Editor.prototype.Links.switchCancelled = '{/literal}{link controller=backend.customerOrder action=switchCancelled}{literal}';

	Backend.CustomerOrder.Links.selectCustomer = '{/literal}{link controller=backend.customerOrder action=selectCustomer}{literal}';
	Backend.CustomerOrder.Links.createOrder = '{/literal}{link controller=backend.customerOrder action=create}{literal}';
	Backend.CustomerOrder.Messages.selecCustomerTitle = '{/literal}{t _select_customer_title}{literal}';
	Backend.CustomerOrder.Messages.areYouSureYouWantToUpdateOrderStatus = '{/literal}{t _are_you_sure_you_want_to_update_order_status|escape}{literal}';

	Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToActivateThisOrder = '{/literal}{t _are_you_sure_you_want_activate_this_order|escape}{literal}';
	Backend.CustomerOrder.Editor.prototype.Messages.areYouSureYouWantToCancelThisOrder = '{/literal}{t _are_you_sure_you_want_cancel_this_order|escape}{literal}';
	Backend.CustomerOrder.Editor.prototype.Messages.orderNum = '{/literal}{t _order_number|escape}{literal}';
	new Backend.CustomerOrder({/literal}{json array=$orderGroups}{literal});

	{/literal}
		{allowed role="order"}
			Backend.CustomerOrder.prototype.ordersMiscPermission = true;
		{/allowed}

		{allowed role="user"}
			Backend.CustomerOrder.prototype.usersMiscPermission = true;
		{/allowed}
	{literal}
	Backend.showContainer("orderGroupsManagerContainer");
	window.ordersActiveGrid = {};
</script>
{/literal}


{include file="layout/backend/footer.tpl"}