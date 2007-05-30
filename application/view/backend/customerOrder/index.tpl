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
{includeCss file="library/ActiveGrid.css"}

{includeCss file="backend/Backend.css"}

{includeJs file="backend/CustomerOrder.js"}
{includeCss file="backend/CustomerOrder.css"}

{pageTitle help="orderGroups"}{t _livecart_orders}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="orderGroupsWrapper" class="maxHeight h--50">
	<div id="orderGroupsBrowser" class="treeBrowser"></div>
   
    {include file="backend/customerOrder/groupContainer.tpl"}
    {include file="backend/customerOrder/orderContainer.tpl"}
</div>

<div id="activeUserPath"></div>

{literal}
<script type="text/javascript">
    new Backend.CustomerOrder({/literal}{json array=$orderGroups}{literal});
    window.ordersActiveGrid = {};
</script>
{/literal}


{include file="layout/backend/footer.tpl"}