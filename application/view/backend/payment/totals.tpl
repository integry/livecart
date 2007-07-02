<p>
    <label>{t Order total}:</label>
    <label>{$order.formattedTotal[$order.Currency.ID]}</label>
</p>

<p>
    <label>{t Amount paid}:</label>
    <label>{$order.formatted_amountPaid}</label>
</p>

<div class="clear amountSection"></div>

{if $order.amountNotCaptured != 0}
    <p>
        <label>{t Amount not captured}:</label>
        <label>{$order.formatted_amountNotCaptured}</label>
    </p>
{/if}

{if $order.amountDue > 0}    
	<p>
        <label>{t Amount due}:</label>
        <label>{$order.formatted_amountDue}</label>
    </p>
{/if}