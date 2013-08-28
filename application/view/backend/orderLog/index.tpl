{% set TYPE_ORDERITEM = 2 %}
{% set TYPE_ORDERITEM = 2 %}
{% set TYPE_ORDERITEM = 2 %}
{% set TYPE_SHIPPINGADDRESS = 3 %}
{% set TYPE_BILLINGADDRESS = 4 %}

{% set ACTION_ADD = 0 %}
{% set ACTION_REMOVE = 1 %}
{% set ACTION_CHANGE = 2 %}
{% set ACTION_STATUSCHANGE = 3 %}
{% set ACTION_COUNTCHANGE = 4 %}
{% set ACTION_SHIPPINGSERVICECHANGE = 5 %}
{% set ACTION_SHIPMENTCHANGE = 6 %}
{% set ACTION_ORDER = 7 %}
{% set ACTION_CANCELEDCHANGE = 8 %}
{% set ACTION_REMOVED_WITH_SHIPMENT = 9 %}
{% set ACTION_NEW_DOWNLOADABLE_ITEM_ADDED = 10 %}
{% set ACTION_NEW_DOWNLOADABLE_ITEM_REMOVED = 11 %}


<ul class="logHistory">
{foreach name="logs" item='log' from=$logs}
	<li class="logEntry">
		<table>
			<tr>
				<td class="logEntryAction">
					{if $log.type == $TYPE_ORDER}
						{if $log.action == $ACTION_STATUSCHANGE}
							{t _order_status_changed}
						{elseif $log.action == $ACTION_CANCELEDCHANGE}
							{if $log.newValue.isCancelled}
								{t _order_cancelled}
							{else}
								{t _order_activated}
							{/if}
						{/if}
					{elseif $log.type == $TYPE_SHIPMENT}
						{if $log.action == $ACTION_ADD}{t _new_shipment_added}
						{elseif $log.action == $ACTION_REMOVE}{t _shipment_removed}
						{elseif $log.action == $ACTION_STATUSCHANGE}{t _shipment_status_changed}
						{elseif $log.action == $ACTION_SHIPPINGSERVICECHANGE}{t _shipping_service_changed}{/if}
					{elseif $log.type == $TYPE_ORDERITEM}
						{if $log.action == $ACTION_ADD}{t _new_item_added}
						{elseif $log.action == $ACTION_REMOVE}{t _item_removed}
						{elseif $log.action == $ACTION_COUNTCHANGE}{t _item_quantity_updated}
						{elseif $log.action == $ACTION_SHIPMENTCHANGE}{t _item_moved_to_another_shipment}
						{elseif $log.action == $ACTION_NEW_DOWNLOADABLE_ITEM_ADDED}{t _new_downloadable_item_added}
						{elseif $log.action == $ACTION_NEW_DOWNLOADABLE_ITEM_REMOVED}{t _downloadable_item_removed}{/if}
					{elseif $log.type == $TYPE_SHIPPINGADDRESS}
						{t _shipping_address_changed}
					{elseif $log.type == $TYPE_BILLINGADDRESS}
						{t _billing_address_changed}
					{/if}
				</td>
				<td class="logEntryAuthor">
					<div class="logEntryDate">[[log.formatted_time.date_long]] [[log.formatted_time.time_long]]</div>
					<div class="logEntryUser"><a href="{backendUserUrl user=$log.User}">[[log.User.fullName]]</a></div>
				</td>
			</tr>
			
			{if $log.action != $ACTION_CANCELEDCHANGE}
			<tr>
				<td colspan="2">
					<table class="logEntryDiff">
						
						{if $log.oldValue && $log.newValue}
						<tr>
							<td class="logEntryValueFrom">{t _from}:</td>
							<td class="logEntryValueTo">{t _to}:</td>
						</tr>
						{/if}
						
						<tr>
							{if $log.oldValue}
							<td>
								{if $log.type == $TYPE_ORDER}
									{include file="backend/orderLog/order.tpl" order=$log.oldValue otherOrder=$log.newValue log=$log}
								{elseif $log.type == $TYPE_SHIPMENT}
									{include file="backend/orderLog/shipment.tpl" shipment=$log.oldValue otherShipment=$log.newValue log=$log}	  
								{elseif $log.type == $TYPE_ORDERITEM}
									{include file="backend/orderLog/orderedItem.tpl" orderedItem=$log.oldValue otherOrderedItem=$log.newValue log=$log}
								{elseif $log.type == $TYPE_SHIPPINGADDRESS || $log.type == $TYPE_BILLINGADDRESS}
									{include file="backend/orderLog/address.tpl" address=$log.oldValue otherAddress=$log.newValue log=$log} 
								{/if}
							</td>
							{/if}
							
							{if $log.newValue}
							<td>
								{if $log.type == $TYPE_ORDER}
									{include file="backend/orderLog/order.tpl" order=$log.newValue otherOrder=$log.oldValue log=$log}
								{elseif $log.type == $TYPE_SHIPMENT}
									{include file="backend/orderLog/shipment.tpl" shipment=$log.newValue otherShipment=$log.oldValue log=$log}		
								{elseif $log.type == $TYPE_ORDERITEM}
									{include file="backend/orderLog/orderedItem.tpl" orderedItem=$log.newValue otherOrderedItem=$log.oldValue log=$log}
								{elseif $log.type == $TYPE_SHIPPINGADDRESS || $log.type == $TYPE_BILLINGADDRESS}
									{include file="backend/orderLog/address.tpl" address=$log.newValue otherAddress=$log.oldValue log=$log}  
								{/if}
							</td>
							{/if}
						</tr>
					</table>
				</td>
				<td></td>
			</tr>
			{/if}
			
			{if $log.oldTotal != $log.newTotal}
			<tr>
				<td>&nbsp;</td>
				<td class="logEntryTotalAmount">
					<div class="logEntryTotalAmountTitle">{t _order_total_changed}:</div>
					{t _from_lowercase} 
					<span class="logEntryOldTotalAmount">[[log.Order.Currency.pricePrefix]][[log.oldTotal]][[log.Order.Currency.priceSuffix]]</span>
					{t _to_lowercase} 
					<span class="logEntryNewTotalAmount">[[log.Order.Currency.pricePrefix]][[log.newTotal]][[log.Order.Currency.priceSuffix]]</span>
				</td>
			</tr>
			{/if}
		</table>
	</li>
{foreachelse}
	<div class="noRecords"><div>{t _no_changes}</div></div>
{/foreach}
</ul>