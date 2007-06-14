<fieldset class="container" {denied role="order.update"}style="display: none"{/denied}>
	<ul class="menu" id="orderShipments_menu_{$orderID}">
	    <li><a href="#new" id="orderShipments_new_{$orderID}_show">{t _add_new_shipment}</a></li>
	    <li><a href="#new" id="orderShipments_new_{$orderID}_cancel" class="hidden">{t _cancel_adding_new_shipment}</a></li>
	</ul>
</fieldset>

<fieldset id="orderShipments_new_{$orderID}_form" style="display: none;">
    {include file="backend/shipment/form.tpl" shipment=$newShipment shipmentForm=$newShipmentForm}
</fieldset>



<ul id="orderShipments_list_{$orderID}" class="activeList_add_delete activeList_add_edit">
{foreach item="shipment" from=$shipments}
    <li id="orderShipments_list_{$orderID}_{$shipment.ID}" class="orderShipment">
        <h3 class="orderShipment_title">{$shipment.ShippingService.name}</h3>
        <div class="orderShipment_info">
            <fieldset class="error">
                <label>{t _subtotal_price}:</label>
                {$shipment.AmountCurrency.pricePrefix}<span class="orderShipment_info_subtotal">{$shipment.amount}</span>{$shipment.AmountCurrency.priceSuffix}
            </fieldset >
            <fieldset class="error">
                <label>{t _shipping_price}:</label>
                {$shipment.AmountCurrency.pricePrefix}<span class="orderShipment_info_shippingAmount">{$shipment.shippingAmount}</span>{$shipment.AmountCurrency.priceSuffix}
            </fieldset >
            
            <hr />
            
            <fieldset class="error">
                <label>{t _total_price}:</label>
                {$shipment.AmountCurrency.pricePrefix}<span class="orderShipment_info_total">{math equation="x + y" x=$shipment.shippingAmount y=$shipment.amount}</span>{$shipment.AmountCurrency.priceSuffix}
            </fieldset >
        </div>
        
        <ul id="orderShipmentsItems_list_{$orderID}_{$shipment.ID}" class="activeList_add_sort activeList_add_delete orderShipmentsItem activeList_accept_orderShipmentsItem">
        {foreach item="item" from=$shipment.items}
            <li id="orderShipmentsItems_list_{$item.ID}_{$shipment.ID}_{$item.ID}" >
                <h3 class="orderShipmentsItem_title">{$item.Product.name}</h3>
                <div class="orderShipmentsItem_info">
                    <dl>
                        <dt>{t _price}: </dt>
                    	<dd>
                    	    {$item.count}
                            x 
                            {$shipment.AmountCurrency.pricePrefix}{$item.price}{$shipment.AmountCurrency.priceSuffix}
                            = 
                            {$shipment.AmountCurrency.pricePrefix}{math equation="x * y" x=$item.price y=$item.count}{$shipment.AmountCurrency.priceSuffix}
                        </dd>
                    </dl>
                </div >
                
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