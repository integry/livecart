<div>
<div class="menuContainer" id="paymentMenu_[[order.ID]]">

	<ul class="menu paymentMenu" {denied role='order.update'}style="display: none;"{/denied}>
		<li class="offlinePayment"><a href="#addOfflinePayment" class="addOfflinePayment">{t _add_offline_payment}</a></li>
		<li class="offlinePaymentCancel done" style="display: none;"><a href="#cancelOfflinePayment" class="cancelOfflinePayment">{t _cancel_offline_payment}</a></li>

		<li class="ccPayment"><a onclick="window.open('{link controller="backend.payment" action=ccForm id=$order.ID}', 'creditCard', 'directories=no, height=440, width=540, resizable=yes, scrollbars=no, toolbar=no'); return false;" href="#" class="addCreditCardPayment">{t _add_credit_card_payment}</a></li>
	</ul>

	<div class="slideForm addOffline" style="display: none;">
		<fieldset class="addForm addOfflinePayment">

			<legend>{t _add_offline_payment|capitalize}</legend>

			{form action="controller=backend.payment action=addOffline id=`$order.ID`" method="POST" handle=$offlinePaymentForm onsubmit="Backend.Payment.submitOfflinePaymentForm(event);"}

				{input name="amount"}
					{label}{t _amount}:{/label}
					{textfield class="text number"} [[order.Currency.ID]]
				{/input}

				[[ textareafld('comment', '_comment') ]]

				<fieldset class="controls" style="margin-right: 40px;">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" value="{tn _add_payment}" />
					{t _or} <a class="cancel offlinePaymentCancel" href="#">{t _cancel}</a>
				</fieldset>

			{/form}

		</fieldset>
	</div>

</div>

<form class="paymentSummary">

	[[ partial("backend/payment/totals.tpl") ]]

</form>

<div class="clear"></div>

<fieldset class="container transactionContainer">
	[[ partial('backend/payment/transactions.tpl', ['transactions': transactions]) ]]
</fieldset>

{literal}
<script type="text/javascript">
	Backend.Payment.init($('{/literal}paymentMenu_[[order.ID]]{literal}'));
</script>
{/literal}

<div class="clear"></div>

</div>