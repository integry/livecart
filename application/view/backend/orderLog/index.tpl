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
                    {if $log.action == $ACTION_STATUSCHANGE}
                        {t _from}
                            {if $log.oldValue.value == 1}
                                {t _backordered}
                            {elseif $log.oldValue.value == 2}
                                {t _awaiting_shipment}
                            {elseif $log.oldValue.value == 3}
                                {t _shipped}
                            {elseif $log.oldValue.value == 4}
                                {t _returned}
                            {/if}
                        {t _to}
                            {if $log.newValue.value == 1}
                                {t _backordered}
                            {elseif $log.newValue.value == 2}
                                {t _awaiting_shipment}
                            {elseif $log.newValue.value == 3}
                                {t _shipped}
                            {elseif $log.newValue.value == 4}
                                {t _returned}
                            {/if}
                    {elseif $log.action == $ACTION_CANCELEDCHANGE}
                        {t _from}
                            {if $log.oldValue.value == 0}
                                {t _activated}
                            {elseif $log.oldValue.value == 1}
                                {t _canceled}
                            {/if}
                        {t _to}
                            {if $log.newValue.value == 0}
                                {t _activated}
                            {elseif $log.newValue.value == 1}
                                {t _canceled}
                            {/if}
                    {/if}
                {elseif $log.type == $TYPE_SHIPMENT}
                    {if $log.action == $ACTION_ADD}
                        {t _new_shipment_added} [ID: {$log.oldValue.ID}]
                    {elseif $log.action == $ACTION_REMOVE}
                        {t _shipment_removed} [ID: {$log.newValue.ID}]
                    {elseif $log.action == $ACTION_STATUSCHANGE}
                        {t _from}
                            {if $log.oldValue.status == 1}
                                {t _pending}
                            {elseif $log.oldValue.status == 2}
                                {t _awaiting_shipment}
                            {elseif $log.oldValue.value == 3}
                                {t _shipped}
                            {elseif $log.oldValue.value == 4}
                                {t _confirmed_as_delivered}
                            {elseif $log.oldValue.value == 5}
                                {t _confirmed_as_lost}
                            {/if}
                        {t _to}
                            {if $log.newValue.status == 1}
                                {t _pending}
                            {elseif $log.newValue.status == 2}
                                {t _awaiting_shipment}
                            {elseif $log.newValue.value == 3}
                                {t _shipped}
                            {elseif $log.newValue.value == 4}
                                {t _confirmed_as_delivered}
                            {elseif $log.newValue.value == 5}
                                {t _confirmed_as_lost}
                            {/if}
                        
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
            <td></td>
        </tr>
        <tr>
            <td class="logEntryTotalAmount">Order total changed: <b>{$log.Order.Currency.pricePrefix}{$log.oldTotal}{$log.Order.Currency.priceSuffix}</b> to <b>{$log.Order.Currency.pricePrefix}{$log.oldTotal}{$log.Order.Currency.priceSuffix}</b></td>
        </tr>
    </table>
{/foreach}