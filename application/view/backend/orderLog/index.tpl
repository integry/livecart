{assign var="TYPE_ORDER" value=0}
{assign var="TYPE_SHIPMENT" value=1}
{assign var="TYPE_ORDERITEM" value=2}
{assign var="TYPE_SHIPPINGADDRESS" value=3}
{assign var="TYPE_BILLINGADDRESS" value=4}

{assign var="ACTION_ADD" value=0}
{assign var="ACTION_REMOVE" value=1}
{assign var="ACTION_CHANGE" value=2}
{assign var="ACTION_STATUSCHANGE" value=3}
{assign var="ACTION_COUNTCHANGE" value=4}
{assign var="ACTION_SHIPPINGSERVICECHANGE" value=5}
{assign var="ACTION_SHIPMENTCHANGE" value=6}
{assign var="ACTION_ORDER" value=7}
{assign var="ACTION_CANCELEDCHANGE" value=8}


{literal}
<style>
.logEntryTotalAmount
{
    height: 30px;
    width: 200px;
}

.logEntry
{
    width: 100%;
}

.logEntry td
{
    border: 1px solid;
}
 
</style>
{/literal}

<h1>Order log</h1>

{foreach item='log' from=$logs}
    TYPE: {$log.type} ACTION: {$log.action}
    <table class="logEntry" border="1" s>
        <tr>
            <td class="logEntryAction">
                {if $log.type == $TYPE_ORDER}
                    {if $log.action == $ACTION_STATUSCHANGE}
                        {t _order_status_changed}
                    {elseif $log.action == $ACTION_CANCELEDCHANGE}
                        {t _order_status_changed}
                    {/if}
                {elseif $log.type == $TYPE_SHIPMENT}
                    {if $log.action == $ACTION_ADD}
                        {t _new_shipment_added}
                    {elseif $log.action == $ACTION_REMOVE}
                        {t _shipment_removed}
                    {elseif $log.action == $ACTION_STATUSCHANGE}
                        {t _shipment_status_changed}
                    {elseif $log.action == $ACTION_SHIPPINGSERVICECHANGE}
                        {t _shipping_service_changed}
                    {/if}
                {elseif $log.type == $TYPE_ORDERITEM}
                    {if $log.action == $ACTION_ADD}
                        {t _new_item_added}
                    {elseif $log.action == $ACTION_REMOVE}
                        {t _item_removed}
                    {elseif $log.action == $ACTION_COUNTCHANGE}
                        {t _item_quantity_updated}
                    {elseif $log.action == $ACTION_SHIPMENTCHANGE}
                        {t _item_moved_to_another_shipment}
                    {/if}
                {elseif $log.type == $TYPE_SHIPPINGADDRESS}
                    {t _shipping_address_changed}
                {elseif $log.type == $TYPE_BILLINGADDRESS}
                    {t _billing_address_changed}
                {/if}
            </td>
            <td class="logEntryAuthor">
                <div class="logEntryDate">{$log.formatted_time.date_full} {$log.formatted_time.time_full}</div>
                <div class="logEntryUser">{$log.user.full_name}</div>
            </td>
        </tr>
        <tr>
            <td rowspan="2">
                {if $log.type == $TYPE_ORDER}
                    {t _from}<br />
                        {include file="backend/orderLog/order.tpl" order=$log.oldValue otherOrder=$log.newValue}
                    <br />{t _to}<br /><br />
                        {include file="backend/orderLog/order.tpl" order=$log.newValue otherOrder=$log.oldValue}
                {elseif $log.type == $TYPE_SHIPMENT}
                    {t _from}<br />
                        {include file="backend/orderLog/shipment.tpl" shipment=$log.oldValue}
                    <br />{t _to}<br /><br />
                        {include file="backend/orderLog/shipment.tpl" shipment=$log.newValue}        
                {elseif $log.type == $TYPE_ORDERITEM}
                    {t _from}<br />
                        {include file="backend/orderLog/orderedItem.tpl" orderedItem=$log.oldValue}
                    <br />{t _to}<br /><br />
                        {include file="backend/orderLog/orderedItem.tpl" orderedItem=$log.newValue}
                {elseif $log.type == $TYPE_SHIPPINGADDRESS || $log.type == $TYPE_BILLINGADDRESS}
                    {t _from}<br />
                        {include file="backend/orderLog/address.tpl" address=$log.oldValue}                        
                    <br />{t to}<br /><br />
                        {include file="backend/orderLog/address.tpl" address=$log.newValue}  
                {/if}
            </td>
            <td></td>
        </tr>
        <tr>
            <td class="logEntryTotalAmount">Order total changed: <b>{$log.Order.Currency.pricePrefix}{$log.oldTotal}{$log.Order.Currency.priceSuffix}</b> to <b>{$log.Order.Currency.pricePrefix}{$log.oldTotal}{$log.Order.Currency.priceSuffix}</b></td>
        </tr>
    </table>
    <br />
    <br />
    <br />
{/foreach}