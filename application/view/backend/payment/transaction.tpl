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
				{t _type_sale}

			{elseif 1 == $transaction.type}
				{t _type_authorization}

			{elseif 2 == $transaction.type}
				{t _type_capture}

			{elseif 3 == $transaction.type}
				{t _type_void}

			{/if}
		</div>

		<div class="transactionUser">

			{if $transaction.User}
				{t _processed_by}: <a href="{backendUserUrl user=$transaction.User}">{$transaction.User.fullName}</a>
			{/if}

		</div>

		{if $transaction.comment}
			<div class="transactionComment">
				{$transaction.comment}
			</div>
		{/if}

		{if $transaction.attributes}
			<div class="overview">
				{include file="backend/eav/view.tpl" item=$transaction}
			</div>
		{/if}

	</div>

	<div class="transactionDetails">

		<ul class="transactionMenu" {denied role='order.update'}style="display: none;"{/denied}>
			{if $transaction.isCapturable}
				<li class="captureMenu">
					<a href="" onclick="Backend.Payment.showCaptureForm({$transaction.ID}, event);">{t _capture}</a>
				</li>
			{/if}
			{if $transaction.isVoidable}
				<li class="voidMenu">
					<a href="#void" onclick="Backend.Payment.showVoidForm({$transaction.ID}, event);">{t _void}</a>
				</li>
			{/if}
			{if $transaction.hasFullNumber}
				<li class="delCcNumMenu">
					<span class="progressIndicator" style="display: none;"></span>
					<a href="{link controller=backend.payment action=deleteCcNumber id=$transaction.ID}" onclick="Backend.Payment.deleteCcNum({$transaction.ID}, event);">{t _delete_cc_num}</a>
				</li>
			{/if}
		</ul>

		<div class="clear"></div>

		<div class="transactionForm voidForm" style="display: none;">
			<form action="{link controller=backend.payment action=void id=$transaction.ID}" method="post" onsubmit="Backend.Payment.voidTransaction({$transaction.ID}, this, event);">

				<span class="confirmation" style="display: none">{t _void_conf}</span>

				<fieldset>
						<legend>{t _void}</legend>
								<p>
					{t _void_reason}:
				</p>
				<textarea name="comment"></textarea>
						</fieldset>

				<fieldset class="controls">
					<span class="progressIndicator" style="display: none;"></span>
					<input type="submit" class="submit" value="{tn _void_button}" />
					{t _or} <a class="menu" href="#" onclick="Backend.Payment.hideVoidForm({$transaction.ID}, event);">{t _cancel}</a>
				</fieldset>

			</form>
		</div>

		{if $transaction.isCapturable}
		<div class="transactionForm captureForm" style="display: none;">
				{form action="controller=backend.payment action=capture id=`$transaction.ID`" method="POST" onsubmit="Backend.Payment.captureTransaction(`$transaction.ID`, this, event);" handle=$capture}

			<fieldset>
				<legend>{t _capture}</legend>

				<span class="confirmation" style="display: none">{t _capture_conf}</span>

				<p>
					{t _capture_amount}:<Br />
					{textfield name="amount" class="text number" value=$transaction.amount} {$transaction.Currency.ID}
				</p>

				<p class="captureComment">
					{t _comment}:
					<textarea name="comment"></textarea>
				</p>

				{if $transaction.isMultiCapture}
					<p>
						{checkbox name="complete" class="checkbox"}
						<label for="complete">{t _finalize}</label>
						<div class="clear"></div>
					</p>
				{else}
					<input type="checkbox" name="complete" id="complete" value="ON" checked="checked" style="display: none;" />
				{/if}
				</fieldset>

		<fieldset class="controls">
				<span class="progressIndicator" style="display: none;"></span>
				<input type="submit" class="submit" value="{tn _process_capture}" />
			{t _or} <a class="menu" href="#" onclick="Backend.Payment.hideCaptureForm({$transaction.ID}, event);">{t _cancel}</a>
		</fieldset>

			{/form}
		</div>
		{/if}

		<fieldset class="transactionMethod">

			{if $transaction.methodName}
				<legend>{$transaction.methodName}</legend>
			{elseif $transaction.serializedData.handler}
				<legend>{$transaction.serializedData.handler}</legend>
			{/if}

			{if $transaction.ccLastDigits}
				<div class="ccDetails">
					<div>{$transaction.ccName}</div>
					<div>{$transaction.ccType} <span class="ccNum">{if $transaction.hasFullNumber} / {else}...{/if}{$transaction.ccLastDigits}</span></div>
					<div>{$transaction.ccExpiryMonth} / {$transaction.ccExpiryYear}</div>
				</div>
			{/if}

			{if $transaction.gatewayTransactionID}
				<div class="gatewayTransactionID">
					{t _transaction_id}: {$transaction.gatewayTransactionID}
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