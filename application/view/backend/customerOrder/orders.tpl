<div>

<fieldset class="container" style="vertical-align: middle;">
                
    <span style="float: left; text-align: right; {if $orderGroupID == 8}visibility: hidden;{else}{denied role="order.mass"}visibility: hidden;{/denied}{/if}" id="orderMass_{$orderGroupID}" >

	    {form action="controller=backend.customerOrder action=processMass id=$orderGroupID" handle=$massForm style="vertical-align: middle;" onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select" style="width: auto;">
            <optgroup label="{t _order_status}">
                <option value="setNew">{t _set_new}</option>
                <option value="setBackordered">{t _set_backordered}</option>
                <option value="setAwaitingShipment">{t _set_awaiting_shipment}</option>
                <option value="setShipped">{t _set_shipped}</option>
                <option value="setReturned">{t _set_returned}</option>
            </optgroup>
            <option value="delete">{t _delete}</option>
        </select>
        
        <span class="bulkValues" style="display: none;">

        </span>
        
        <input type="submit" value="{tn _process}" class="submit" />
        <span class="progressIndicator" style="display: none;"></span>
        
        {/form}
        
    </span>
    
    <span style="float: right; text-align: right; position: relative; padding-bottom: 10px;">
		<span id="orderCount_{$orderGroupID}" class="orderCount">
			<span class="rangeCount">Listing orders %from - %to of %count</span>
			<span class="notFound">No orders found</span>
		</span>    
		<br />
		<div style="padding-top: 5px;">
			<a href="#" onclick="Element.show($('orderColumnMenu_{$orderGroupID}')); return false;" style="margin-top: 15px;">{t Columns}</a>
		</div>
		<div id="orderColumnMenu_{$orderGroupID}" style="left: -250px; position: absolute; z-index: 5; width: auto; display: none;">
  		  <form action="{link controller=backend.customerOrder action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.parentNode.parentNode.parentNode.parentNode.parentNode, document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
			
			<input type="hidden" name="group" value="{$orderGroupID}" />
			
			<div style="background-color: white; border: 1px solid black; float: right; text-align: center; white-space: nowrap; width: 250px;">
				<div style="padding: 5px; position: static; width: 100%;">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('orderColumnMenu_{$orderGroupID}')); return false;" href="#cancel">{t _cancel}</a>
				</div>
			    <div style="padding: 10px; background-color: white; max-height: 300px; overflow: auto; text-align: left;">
					{foreach from=$availableColumns item=item key=column}
					<p class="column_{$column|replace:'.':'_'}">
						<input type="checkbox" name="col[{$column}]" class="checkbox" id="column_{$column}_{$orderGroupID}"{if $displayedColumns.$column}checked="checked"{/if} />
						<label for="column_{$column}_{$orderGroupID}" class="checkbox">
							{$item.name}
						</label>
					</p>
					{/foreach}
				</div>
			</div>
		  </form>
		</div>
	</span>
    
</fieldset>

{activeGrid prefix="orders" id=$orderGroupID role="order.mass" controller="backend.customerOrder" action="lists" displayedColumns=$displayedColumns availableColumns=$availableColumns totalCount=$totalCount rowCount=17}

</div>

{literal}
<script type="text/javascript">
    try
    {
        {/literal}
            {if $userID > 0}
                {assign var="userID" value="?filters[User.ID]=`$userID`"}
            {/if}
        {literal}
    
    	grid.setDataFormatter(Backend.CustomerOrder.GridFormatter);
    	
        var massHandler = new Backend.CustomerOrder.massActionHandler($('{/literal}orderMass_{$orderGroupID}{literal}'), grid);
        massHandler.deleteConfirmMessage = '{/literal}{t _are_you_sure_you_want_to_delete_this_order|addslashes}{literal}' ;
        ordersActiveGrid['{/literal}{$orderGroupID}{literal}'] = grid;
    }
    catch(e)
    {
        console.info(e);
    }
</script>
{/literal}
