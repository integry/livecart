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
    {include file="backend/shipment/shipmentTotal.tpl"}
</div>

<div id="orderShipment_report_{$orderID}" class="orderShipment_report">
    <h2>{t _report}</h2>
    <div class="orderShipment_report_values">
        <fieldset class="error">
            <label>{t _subtotal_price}:</label>
            <span class="orderShipment_report_subtotal">
                <span class="pricePrefix">{$order.Currency.pricePrefix}</span>
                <span class="price">{$subtotalAmount}</span>
                <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
            </span>
        </fieldset >
        <fieldset class="error">
            <label>{t _shipping_price}:</label>
            <span class="orderShipment_report_shippingAmount">
                <span class="pricePrefix">{$order.Currency.pricePrefix}</span>
                <span class="price">{$shippingAmount}</span>
                <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
            </span>
        </fieldset >
        <fieldset class="error">
            <label>{t _taxes}:</label>
            <span class="orderShipment_report_tax">
                <span class="pricePrefix">{$order.Currency.pricePrefix}</span>
                <span class="price">{$taxAmount}</span>
                <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
            </span>
        </fieldset >
        
        <hr />
        
        <fieldset class="error">
            <label>{t _total_price}:</label>
            <span class="orderShipment_report_total">
                <span class="pricePrefix">{$order.Currency.pricePrefix}</span>
                <span class="price">{$totalAmount}</span>
                <span class="priceSuffix">{$order.Currency.priceSuffix}</span>
            </span>
        </fieldset >
    </div>
</div>

<div class="orderShipment_shipments">
    <h2>{t _shipments}</h2>
    <ul id="orderShipments_list_{$orderID}" class="orderShipments">
    {foreach item="shipment" from=$shipments}
        <li id="orderShipments_list_{$orderID}_{$shipment.ID}" class="orderShipment">
        <form>
            {include file="backend/shipment/shipmentControls.tpl"}
            
            <ul id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}" class="activeList_add_sort activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem">
            {foreach item="item" from=$shipment.items}
                <li id="orderShipmentsItems_list_{$item.ID}_{$shipment.ID}_{$item.ID}" >
                    {include file="backend/shipment/itemAmount.tpl"}
                </li>
            {/foreach}
            </ul>
            
            {include file="backend/shipment/shipmentTotal.tpl"}
        </form>
        </li>
    {/foreach}
    </ul>
</div>



{literal}
<script type="text/javascript">
    Backend.OrderedItem.Links = {};
    Backend.OrderedItem.Links.remove             = '{/literal}{link controller=backend.orderedItem action=delete}{literal}';
    Backend.OrderedItem.Links.changeShipment     = '{/literal}{link controller=backend.orderedItem action=changeShipment}{literal}';
    Backend.OrderedItem.Links.addProduct         = '{/literal}{link controller=backend.productRelationship action=selectProduct}#cat_1#tabProducts__{literal}';
    Backend.OrderedItem.Links.createNewItem      = '{/literal}{link controller=backend.orderedItem action=create}{literal}';
    Backend.OrderedItem.Links.changeItemCount    = '{/literal}{link controller=backend.orderedItem action=changeCount}{literal}';


    Backend.Shipment.Links = {};
    Backend.Shipment.Links.update               = '{/literal}{link controller=backend.shipment action=update}{literal}';
    Backend.Shipment.Links.create               = '{/literal}{link controller=backend.shipment action=create}{literal}';
    Backend.Shipment.Links.remove               = '{/literal}{link controller=backend.shipment action=delete}{literal}';
    Backend.Shipment.Links.edit                 = '{/literal}{link controller=backend.shipment action=edit}{literal}';
    Backend.Shipment.Links.getAvailableServices = '{/literal}{link controller=backend.shipment action=getAvailableServices}{literal}';
    Backend.Shipment.Links.changeService        = '{/literal}{link controller=backend.shipment action=changeService}{literal}';
    Backend.Shipment.Links.changeStatus         = '{/literal}{link controller=backend.shipment action=changeStatus}{literal}';
    
    Backend.Shipment.Statuses = {};
    {/literal}{foreach key="statusID" item="status" from=$statuses}{literal}
        Backend.Shipment.Statuses["{$statusID|html}"] = "{$status}";
    {/literal}{/foreach}{literal}
     
    Backend.Shipment.Messages = {};
    Backend.Shipment.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete_group|addslashes}{literal}'
    
    Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToAwaiting = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_awaiting|addslashes}{literal}'
    Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToPending = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_pending|addslashes}{literal}'
    Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToNew = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_new|addslashes}{literal}'
    Backend.Shipment.Messages.areYouSureYouWantToChangeShimentStatusToShipped = '{/literal}{t _are_you_sure_you_want_to_change_shipment_status_to_shipped|addslashes}{literal}'
    Backend.Shipment.Messages.youWontBeAableToRevertStatusFromShipped = '{/literal}{t _you_wont_be_able_to_revert_status_from_shipped|addslashes}{literal}'
    
    Backend.OrderedItem.Messages = {};
    Backend.OrderedItem.Messages.areYouSureYouWantToDelete = '{/literal}{t _are_you_sure_you_want_to_delete|addslashes}{literal}';
    Backend.OrderedItem.Messages.selectProductTitle = '{/literal}{t _select_product|addslashes}{literal}';
    
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

        
        var groupList = ActiveList.prototype.getInstance('{/literal}orderShipments_list_{$orderID}{literal}', Backend.Shipment.Callbacks);  
        {/literal}{foreach item="shipment" from=$shipments}{literal}
            ActiveList.prototype.getInstance('{/literal}orderShipmentsItems_list_{$orderID}_{$shipment.ID}{literal}', Backend.OrderedItem.activeListCallbacks);
            
            
            Event.observe("{/literal}orderShipment_change_usps_{$shipment.ID}{literal}", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').toggleUSPS();  });
            Event.observe("{/literal}orderShipment_USPS_{$shipment.ID}_submit{literal}", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').toggleUSPS();  });       
            Event.observe("{/literal}orderShipment_USPS_{$shipment.ID}_cancel{literal}", 'click', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').toggleUSPS(true);  });
            Event.observe("{/literal}orderShipment_USPS_{$shipment.ID}_select{literal}", 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').USPSChanged();  });
            
            $("{/literal}orderShipment_status_{$shipment.ID}{literal}").lastValue = $("{/literal}orderShipment_status_{$shipment.ID}{literal}").value;
            Event.observe("{/literal}orderShipment_status_{$shipment.ID}{literal}", 'change', function(e) { Event.stop(e); Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').changeStatus();  });
              
                
            Event.observe($("{/literal}orderShipment_addProduct_{$shipment.ID}{literal}"), 'click', function(e) {
                Event.stop(e);
                new Backend.OrderedItem.SelectProductPopup(
                    Backend.OrderedItem.Links.addProduct, 
                    Backend.OrderedItem.Messages.selectProductTitle, 
                    {
                        onProductSelect: function() { 
                            Backend.Shipment.prototype.getInstance('{/literal}orderShipments_list_{$orderID}_{$shipment.ID}{literal}').addNewProductToShipment(this.productID, {/literal}{$orderID}{literal}); 
                        }
                    }
                );
            });
        
            {/literal}{foreach item="item" from=$shipment.items}{literal}
                $("{/literal}orderShipmentsItem_count_{$item.ID}{literal}").lastValue = $("{/literal}orderShipmentsItem_count_{$item.ID}{literal}").value;
                                
                Event.observe("{/literal}orderShipmentsItem_count_{$item.ID}{literal}", 'keyup', function(e) { Backend.OrderedItem.updateProductCount({/literal}this, {$orderID}, {$item.ID}, {$shipment.ID}{literal}) });
                Event.observe("{/literal}orderShipmentsItem_count_{$item.ID}{literal}", 'change', function(e) { Backend.OrderedItem.changeProductCount({/literal}this, {$orderID}, {$item.ID}, {$shipment.ID}{literal}) }, false);
                Event.observe("{/literal}orderShipmentsItems_list_{$item.ID}_{$shipment.ID}_{$item.ID}{literal}", 'click', function(e) { var input = this.down('.orderShipmentsItem_info_count').down('input'); if(input.value != input.lastValue) { input.blur(); } });
            {/literal}{/foreach}{literal}
        
        {/literal}{/foreach}{literal}
        groupList.createSortable();
        
    }
    catch(e)
    {
        console.info(e);
    }
</script>
{/literal}