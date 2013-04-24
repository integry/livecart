{if 'CC_ENABLE'|config}
	<h2>{t _pay_securely}</h2>

	{include file="checkout/testHandler.tpl"}

	{if $id}{assign var=ccId value=" id=`$id`"}{/if}
	{assign var=controller value=$controller|default:'checkout'}
	{form action="controller=`$controller` action=payCreditCard`$ccId`" handle=$ccForm method="POST" class="form-horizontal"}

		<div id="ccForm">

		{error for="creditCardError"}
			<div class="errorMsg ccPayment">
				{$msg}
			</div>
		{/error}

		{input name="ccName"}
			{label}{t _cc_name}:{/label}
			{textfield autoComplete="off"}
		{/input}

		{input name="ccNum"}
			{label}{t _cc_number}:{/label}
			{textfield autoComplete="off"}
		{/input}

		{if $ccTypes}
			{input name="ccType"}
				{label}{t _cc_type}:{/label}
				{selectfield id="ccType" options=$ccTypes}
			{/input}
		{/if}

		{input name="ccExpiryYear"}
			{label}{t _card_exp}:{/label}
			<div class="controls">
				{selectfield name="ccExpiryMonth" id="ccExpiryMonth" options=$months noFormat=true}
				/
				{selectfield name="ccExpiryYear" id="ccExpiryYear" options=$years noFormat=true}
			</div>
		{/input}

		{input name="ccCVV"}
			{label}{t _cvv_descr}:{/label}
			<div class="controls">
				{textfield maxlength="4" id="ccCVV" noFormat=true}
				<a class="cvv" href="{link controller=checkout action=cvv}" onclick="Element.show($('cvvHelp')); return false;">{t _what_is_cvv}</a>
			</div>
		{/input}

		{if $ccVars}
			{include file="block/eav/fields.tpl" fieldList=$ccVars.specFieldList}
		{/if}

		{include file="block/submit.tpl" caption="_complete_now"}

		</div>
	{/form}

	<div id="cvvHelp" style="display: none;">
		{include file="checkout/cvvHelp.tpl"}
	</div>

	<div class="clear"></div>
{else}
	{form action="controller=checkout action=payCreditCard" handle=$ccForm method="POST" id="paymentError" class="form-horizontal"}
		{error for="creditCardError"}
			<div class="clear"></div>
			<div class="errorMsg ccPayment">
				<p>{$msg}</p>
			</div>
			<div class="clear"></div>
		{/error}
	{/form}
{/if}