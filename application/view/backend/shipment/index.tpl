<fieldset class="container" {denied role="order.update"}style="display: none"{/denied}>
	<ul class="menu" id="orderShipments_menu_{$orderID}">
	    <li><a href="#new" id="orderShipments_new_{$orderID}_show">{t _add_new_shipment}</a></li>
	    <li><a href="#new" id="orderShipments_new_{$orderID}_cancel" class="hidden">{t _cancel_adding_new_shipment}</a></li>
	</ul>
</fieldset>

<fieldset id="orderShipments_new_{$orderID}_form" style="display: none;">
    {include file="backend/shipment/form.tpl" shipment=$newShipment shipmentForm=$newShipmentForm}
</fieldset>

<div id="orderShipment_{$orderID}_info_empty" style="display: none">
    {include file="backend/shipment/shipmentAmount.tpl"}
</div>

<ul id="orderShipments_list_{$orderID}" class="activeList_add_delete activeList_add_edit">
{foreach item="shipment" from=$shipments}
    <li id="orderShipments_list_{$orderID}_{$shipment.ID}" class="orderShipment">
        <h3 class="orderShipment_title">{$shipment.ShippingService.name}</h3>
        {include file="backend/shipment/shipmentAmount.tpl"}
        
        <ul id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}" class="activeList_add_sort activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem">
        {foreach item="item" from=$shipment.items}
            <li id="orderShipmentsItems_list_{$item.ID}_{$shipment.ID}_{$item.ID}" >
                <h3 class="orderShipmentsItem_title">{$item.Product.name}</h3>
                {include file="backend/shipment/itemAmount.tpl"}
            </li>
        {/foreach}
        </ul>
    </li>
{/foreach}
</ul>

{literal}
<script type="text/javascript">
    Backend.OrderedItem.Links = {};
    Backend.OrderedItem.Links.remove             = '{/literal}{link controller=backend.orderedItem action=delete}{literal}';
    Backend.OrderedItem.Links.changeShipment    = '{/literal}{link controller=backend.orderedItem action=changeShipment}{literal}';

    Backend.Shipment.Links = {};
    Backend.Shipment.Links.update     = '{/literal}{link controller=backend.shipment action=update}{literal}';
    Backend.Shipment.Links.create     = '{/literal}{link controller=backend.shipment action=create}{literal}';
    Backend.Shipment.Links.remove   = '{/literal}{link controller=backend.shipment action=delete}{literal}';
    Backend.Shipment.Links.edit     = '{/literal}{link controller=backend.shipment action=edit}{literal}';
    
    Backend.Shipment.Messages = {};
    Backend.Shipment.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_group|addslashes}{literal}'
    
    Backend.OrderedItem.Messages = {};
    Backend.OrderedItem.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete|addslashes}{literal}';
    
    try
    {
        Event.observe($("{/literal}orderShipments_new_{$orderID}_show{literal}"), "click", function(e) 
        {
            Event.stop(e);
            
            var newForm = Backend.Shipment.prototype.getInstance(
                $("{/literal}orderShipments_new_{$orderID}_form{literal}").down('form'),
                {/literal}{$orderID}{literal}
            );
            
            newForm.showNewForm();
        });   
    }
    catch(e)
    {
        console.info(e);
    }
        
    try
    {   
        {/literal}    
        var groupList = ActiveList.prototype.getInstance('orderShipments_list_{$orderID}', Backend.Shipment.Callbacks);  
        {foreach item="shipment" from=$shipments}
            console.info($('orderShipmentsItems_list_{$orderID}_{$shipment.ID}'))
            ActiveList.prototype.getInstance('orderShipmentsItems_list_{$orderID}_{$shipment.ID}', Backend.OrderedItem.activeListCallbacks);
        {/foreach}
        groupList.createSortable();
        {literal}
    }
    catch(e)
    {
        console.info(e);
    }
</script>
{/literal}