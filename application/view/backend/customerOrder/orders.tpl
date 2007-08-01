<div>

<fieldset class="container">
                
    <span style="{if $orderGroupID == 8}visibility: hidden;{else}{denied role="order.mass"}visibility: hidden;{/denied}{/if}" id="orderMass_{$orderGroupID}" class="activeGridMass">

	    {form action="controller=backend.customerOrder action=processMass id=$orderGroupID" handle=$massForm onsubmit="return false;"}
	    
	    <input type="hidden" name="filters" value="" />
	    <input type="hidden" name="selectedIDs" value="" />
	    <input type="hidden" name="isInverse" value="" />
	    
        {t _with_selected}:
        <select name="act" class="select">
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
    
    <span class="activeGridItemsCount">
		<span id="orderCount_{$orderGroupID}" >
			<span class="rangeCount">Listing orders %from - %to of %count</span>
			<span class="notFound">No orders found</span>
		</span>    
		<br />
		<div>
			<a href="#" onclick="Element.show($('orderColumnMenu_{$orderGroupID}')); return false;">{t Columns}</a>
		</div>
		<div id="orderColumnMenu_{$orderGroupID}" class="activeGridColumnsRoot" style="display: none;">
  		  <form action="{link controller=backend.customerOrder action=changeColumns}" onsubmit="new LiveCart.AjaxUpdater(this, this.parentNode.parentNode.parentNode.parentNode.parentNode, document.getElementsByClassName('progressIndicator', this)[0]); return false;" method="POST">
			
			<input type="hidden" name="group" value="{$orderGroupID}" />
			
			<div class="activeGridColumnsSelect">
				<div class="activeGridColumnsSelectControls">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" name="sm" value="{tn Change columns}" /> {t _or} <a class="cancel" onclick="Element.hide($('orderColumnMenu_{$orderGroupID}')); return false;" href="#cancel">{t _cancel}</a>
				</div>
			    <div class="activeGridColumnsList">
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

{activeGrid prefix="orders" id=$orderGroupID role="order.mass" controller="backend.customerOrder" action="lists" displayedColumns=$displayedColumns availableColumns=$availableColumns totalCount=$totalCount rowCount=17 showID=true}

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
