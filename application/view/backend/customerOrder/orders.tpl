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
                
    <span style="{if $orderGroupID == 8}visibility: hidden;{else}{denied role="order.mass"}visibility: hidden;{/denied}{/if}" id="orderMass_{$orderGroupID}" class="activeGridMass">

	    {form action="controller=backend.customerOrder action=processMass id=$orderGroupID" handle=$massForm onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select">
            <optgroup label="{t _order_status}">
                <option value="setNew">{t _set_new}</option>
                <option value="setProcessing">{t _set_processing}</option>
                <option value="setAwaitingShipment">{t _set_awaiting_shipment}</option>
                <option value="setShipped">{t _set_shipped}</option>
                <option value="setReturned">{t _set_returned}</option>
            </optgroup>
            <option value="setCancel">{t _cancel}</option>
            <option value="delete">{t _delete}</option>
        </select>
        
        <span class="bulkValues" style="display: none;">

        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span class="activeGridItemsCount">
		<span class="orderCount" id="orderCount_{$orderGroupID}" >
			<span class="rangeCount">{t _listing_orders}</span>
			<span class="notFound">{t _no_orders}</span>
		</span>    
	</span>
    
</fieldset>
   
{activeGrid 
	prefix="orders" 
	id=$orderGroupID 
	role="order.mass" 
	controller="backend.customerOrder" action="lists" 
	displayedColumns=$displayedColumns 
	availableColumns=$availableColumns 
	totalCount=$totalCount 
	rowCount=17 
	showID=true
	container="tabPageContainer"
	filters=$filters
}

</div>

{literal}
<script type="text/javascript">
	window.activeGrids['{/literal}orders_{$orderGroupID}{literal}'].setDataFormatter(new Backend.CustomerOrder.GridFormatter());

	if ({/literal}{json array=$userID}{literal} != null)
	{
		Backend.User.OrderGridFormatter.orderUrl = '{/literal}{backendOrderUrl}{literal}';
		window.activeGrids['{/literal}orders_{$orderGroupID}{literal}'].setDataFormatter(Backend.User.OrderGridFormatter);
	}

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
