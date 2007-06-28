<ul class="menu" style="margin: 0;">
	<li><a href="#captureAll" id="captureAll">{t _capture_all_payments}</a></li>
	<li><a href="#addOfflinePayment" id="addOfflinePayment">{t _add_offline_payment}</a></li>
	<li><a href="#addCreditCardPayment" id="addCreditCardPayment">{t _add_credit_card_payment}</a></li>
</ul>

<div class="clear"></div>

<form>
    <p>
        <label>{t Order total}:</label>
        <label></label>
    </p>
    
    <p>
        <label>{t Amount paid}:</label>
        <label>({t _not_captured})</label>
    </p>
    
    <p>
        <label>{t Amount due}:</label>
        <label></label>
    </p>
</form>

<div class="clear"></div>

<ul class="transactions">

    {foreach from=$transactions item="transaction"}
        
        <li class="transaction_{$transaction.type}">
            
            <div class="transactionMainDetails">
                <div class="transactionAmount">
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
                            <a href="">{t Void}</a>
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

        </li>
    
    {/foreach}

</ul>