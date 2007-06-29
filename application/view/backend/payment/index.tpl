<div style="float: left;">
<div class="menuContainer" id="paymentMenu_{$order.ID}">

    <ul class="menu paymentMenu" style="margin: 0;">
    	<li><a href="#captureAll" onclick="return false;" class="captureAll">{t _capture_all_payments}</a></li>
    	<li><a href="#addOfflinePayment" onclick="return false;" class="addOfflinePayment">{t _add_offline_payment}</a></li>
    	<li><a href="#addCreditCardPayment" onclick="return false;" class="addCreditCardPayment">{t _add_credit_card_payment}</a></li>
    </ul>
    
    <div class="clear"></div>
    
    <div class="slideForm addOffline" style="display: none;">
        <fieldset class="addOfflinePayment">
        
            <legend>{t _add_offline_payment}</legend>
        
            {form action="controller=backend.payment action=addOffline id=`$order.ID`" method="POST" handle=$offlinePaymentForm onsubmit="Backend.Payment.submitOfflinePaymentForm(event); return false;"}
            
                <p>
                    <label>{t Amount}:</label>
                    <fieldset class="error">
                        {textfield name="amount" class="text number"} {$order.Currency.ID}
                        <div class="errorText hidden"></div> 
                    </fieldset>
                </p>        
            
                <p>
                    <label>{t Comment}:</label>
                    {textarea name="comment"}
                </p>        
        
                <fieldset class="controls">
                    <label></label>
                    <span class="progressIndicator" style="display: none;"></span>
                    <input type="submit" class="submit" value="{tn Add payment}" />
                    {t _or} <a class="cancel offlinePaymentCancel" href="#" onclick="return false;">{t _cancel}</a>
                </fieldset>
        
            {/form}
        
        </fieldset>
    </div>

</div>

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

<fieldset class="container transactionContainer">
    {include file="backend/payment/transactions.tpl" transactions=$transactions}
</fieldset>

<script type="text/javascript">
    Backend.Payment.init($('paymentMenu_{$order.ID}'));
</script>

</div>