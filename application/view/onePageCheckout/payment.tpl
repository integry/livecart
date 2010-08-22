<h2><span class="step">{$steps.payment}</span>{t _payment_info}</h2>
{form action="controller=onePageCheckout action=setPaymentMethod" method="POST" handle=$form id="checkout-select-payment-method"}
	<p class="selectMethodMsg">
		{t _select_payment_method}
	</p>

	{if 'CC_ENABLE'|config}
		<p>
			<input type="radio" class="radio" name="payMethod" value="cc" id="pay_cc" />
			<label class="radio" for="pay_cc">{t _credit_card}</label>
		</p>
	{/if}

	{foreach from=$offlineMethods key="key" item="method"}
		<p>
			<input type="radio" class="radio" name="payMethod" value="{$method}" id="{$method}" />
			<label class="radio" for="{$method}">{"OFFLINE_NAME_`$key`"|config}</label>
		</p>
	{/foreach}

	{if $otherMethods}
		<table class="checkout-otherMethods">
			{foreach from=$otherMethods item=method}
				<tr>
					<td style="vertical-align: middle;">
						<input type="radio" class="radio" name="payMethod" value="{link controller=onePageCheckout action=redirect query="id=`$method`"}" id="{$method}" />
					</td>
					<td>
						<label class="radio" for="{$method}">
							<img src="image/payment/{$method}.gif" class="paymentLogo" alt="{$method}" />
						</label>
					</td>
				</p>
			{/foreach}
		</table>
	{/if}

	{if $requireTos}
		{include file="order/block/tos.tpl"}
	{/if}
{/form}

<div class="form">
	<div id="paymentForm"></div>

	<div id="checkout-place-order">
		<div class="errorText hidden" id="no-payment-method-selected">
			{t _no_payment_method_selected}
		</div>

		<div class="completeOrderButton">
			{include file="onePageCheckout/block/submitButton.tpl"}
		</div>

		<div class="grandTotal">
			{t _total}:
			<span class="orderTotal">{$order.formattedTotal.$currency}</span>
		</div>
	</div>
</div>

<div id="paymentMethodForms" style="display: none;">
	{if 'CC_ENABLE'|config}
		<div id="payForm_cc">
			{include file="checkout/block/ccForm.tpl" controller="onePageCheckout"}
		</div>
	{/if}

	{foreach from=$offlineMethods key="key" item="method"}
		<div id="payForm_{$method}">
			{form action="controller=onePageCheckout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST"}
				{sect}
					{header}
						<h2>{"OFFLINE_NAME_`$key`"|config}</h2>
					{/header}
					{content}
						{include file="checkout/offlineMethodInfo.tpl" method=$key}
						{include file="block/eav/fields.tpl" fieldList=$offlineVars[$method].specFieldList}
					{/content}
				{/sect}
			{/form}
		</div>
	{/foreach}
</div>

<div class="notAvailable">
	<p>{t _payment_not_ready}</p>
</div>

<div class="clear"></div>