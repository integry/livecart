<li class="transactionType_{$transaction.type}" id="transaction_{$transaction.ID}">
    
    <div class="transactionMainDetails">
        <div class="transactionAmount{if $transaction.isVoided} isVoided{/if}">
            {$transaction.formattedAmount}
            {if $transaction.Currency.ID != $transaction.RealCurrency.ID}
                <span class="transactionRealAmount">
                ({$transaction.formattedRealAmount})
                </span>
            {/if}
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
        
        <div class="transactionUser">

    		{if $transaction.User}
                {t Processed by}: <a href="{backendUserUrl user=$transaction.User}">{$transaction.User.fullName}</a>
            {/if}
    		
    		{if $transaction.comment}
    			<div class="transactionComment">
    				{$transaction.comment}
    			</div>
    		{/if}        
        
        </div>
        
    </div>

    <div class="transactionDetails">

        <ul class="transactionMenu" {denied role='order.update'}style="display: none;"{/denied}>
            {if $transaction.isCapturable}
                <li class="captureMenu">
                    <a href="" onclick="Backend.Payment.showCaptureForm({$transaction.ID}, event);">{t Capture}</a>
                </li>
            {/if}
            {if $transaction.isVoidable}
                <li class="voidMenu">
                    <a href="#void" onclick="Backend.Payment.showVoidForm({$transaction.ID}, event);">{t Void}</a>
                </li>
            {/if}
        </ul>
        
        <div class="clear"></div>

        <div class="transactionForm voidForm" style="display: none;">
        	<form action="{link controller=backend.payment action=void id=$transaction.ID}" method="POST" onsubmit="Backend.Payment.voidTransaction({$transaction.ID}, this, event);">

                <span class="confirmation" style="display: none">{t Really void this transaction?}</span>
                
        		<p>
					{t Reason for voiding}:
				</p>
				<textarea name="comment"></textarea>

        		<fieldset class="controls">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" value="{tn Void Transaction}" />
	        		{t _or} <a class="menu" href="#" onclick="Backend.Payment.hideVoidForm({$transaction.ID}, event);">{t _cancel}</a>
	        	</fieldset>
	        	
        	</form>
        </div>

        {if $transaction.isCapturable}
        <div class="transactionForm captureForm" style="display: none;">
        	{form action="controller=backend.payment action=capture id=`$transaction.ID`" method="POST" onsubmit="Backend.Payment.captureTransaction(`$transaction.ID`, this, event);" handle=$capture}

                <span class="confirmation" style="display: none">{t Really capture this payment?}</span>
                
        		<p>
					{t Amount to capture}:<Br />
					{textfield name="amount" class="text number" value=$transaction.amount} {$transaction.Currency.ID}
				</p>

        		<p class="captureComment">
					{t Comment}:
					<textarea name="comment"></textarea>
				</p>

				{if $transaction.isMultiCapture}
                    <p>
    					{checkbox name="complete" class="checkbox"}
    					<label for="complete">{t Finalize transaction}</label>
    					<div class="clear"></div>
    				</p>
                {else}
                    <input type="checkbox" name="complete" id="complete" value="ON" checked="checked" style="display: none;" />
                {/if}

        		<fieldset class="controls">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" value="{tn Process Capture}" />
	        		{t _or} <a class="menu" href="#" onclick="Backend.Payment.hideCaptureForm({$transaction.ID}, event);">{t _cancel}</a>
	        	</fieldset>
	        	
        	{/form}
        </div>
        {/if}

        <fieldset class="transactionMethod">
        
            {if $transaction.methodName}
				<legend>{$transaction.methodName}</legend>
            {/if}
            
            {if $transaction.ccLastDigits}
                <div class="ccDetails">
                    <div>{$transaction.ccName}</div>
                    <div>{$transaction.ccType} <span class="ccNum">...{$transaction.ccLastDigits}</span></div>
                    <div>{$transaction.ccExpiryMonth} / {$transaction.ccExpiryYear}</div>
                </div>
            {/if}

            {if $transaction.gatewayTransactionID}
            	<div class="gatewayTransactionID">
				    {t Transaction ID}: {$transaction.gatewayTransactionID}
				</div>
            {/if}

    		<div class="transactionTime">
                {$transaction.formatted_time.date_full} {$transaction.formatted_time.time_full}
            </div>

        </fieldset>
                		
    </div>

    <div class="clear"></div>
    
    {if $transaction.transactions}       
        {include file="backend/payment/transactions.tpl" transactions=$transaction.transactions} 
    {/if}
    
</li>