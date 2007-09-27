<p>
    <label>{t _order_total}:</label>
    <label>{$order.formattedTotal[$order.Currency.ID]}</label>
</p>

<p>
    <label>{t _amount_paid}:</label>
    <label>{$order.formatted_amountPaid}</label>
</p>

<div class="clear amountSection"></div>

{if $order.amountNotCaptured != 0}
    <p>
        <label>{t _not_captured}:</label>
        <label>{$order.formatted_amountNotCaptured}</label>
    </p>
{/if}

{if $order.amountDue > 0}    
	<p>
        <label>{t _amount_due}:</label>
        <label>{$order.formatted_amountDue}</label>
    </p>
{/if}