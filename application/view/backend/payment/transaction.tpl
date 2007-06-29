<li class="transaction_{$transaction.type}">
    
    <div class="transactionMainDetails">
        <div class="transactionAmount{if $transaction.isVoided} isVoided{/if}">
            {$transaction.formattedAmount}
        </div>
        
        <div class="transactionStatus">

            {if 0 == $transaction.type}
                {t Sale}

            {elseif 1 == $transaction.type}
                {t Authorization}

            {elseif 2 == $transaction.type}
                {t Capture}

            {elseif 3 == $transaction.type}
                {t Void}
                
            {/if}
        </div>
    </div>

    <div class="transactionDetails" style="float: right;">

        <ul class="transactionMenu">
            {if !$transaction.isCompleted}
                <li>
                    <a href="">{t Capture}</a>
                </li>
            {/if}
            {if $transaction.isVoidable}
                <li>
                    <a href="{link controller=backend.payment action=void id=$transaction.ID}">{t Void}</a>
                </li>
            {/if}
        </ul>

        <div class="transactionMethod">
            {$transaction.methodName}
        </div>
        <div class="transactionTime">
            {$transaction.formatted_time.date_full} {$transaction.formatted_time.time_full}
        </div>

    </div>

    <div class="clear"></div>
    
    {if $transaction.transactions}       
        {include file="backend/payment/transactions.tpl" transactions=$transaction.transactions} 
    {/if}
    
</li>