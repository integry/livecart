<div class="accordion-group">
	<div class="stepTitle accordion-heading">
		[[ partial('onePageCheckout/block/title.tpl', ['title': "_payment_info"]) ]]
	</div>

	<div class="accordion-body">
		<div class="accordion-inner">
			{form action="onePageCheckout/setPaymentMethod" method="POST" handle=$form id="checkout-select-payment-method" class="form-horizontal"}
				<p class="selectMethodMsg">
					{t _select_payment_method}
				</p>

				{% if 'CC_ENABLE'|config %}
					<div class="radio">
						<label>
							<input type="radio" name="payMethod" value="cc" id="pay_cc" {% if $selectedMethod == 'cc' %}checked="checked"{% endif %} />
							{t _credit_card}
						</label>
					</div>
				{% endif %}

				{foreach from=$offlineMethods key="key" item="method"}
					<div class="radio">
						<label>
							<input type="radio" name="payMethod" value="[[method]]" id="[[method]]"  {% if $selectedMethod == $method %}checked="checked"{% endif %} />
							{"OFFLINE_NAME_`$key`"|config}
						</label>
					</div>
				{/foreach}

				{% if !empty(otherMethods) %}
					<div class="checkout-otherMethods">
						{foreach from=$otherMethods item=method}
							<div class="radio">
								<label>
									<input type="radio" name="payMethod" value="[[ url("onePageCheckout/redirect", "id=`$method`") ]]" id="[[method]]" {% if $selectedMethod == $method %}checked="checked"{% endif %} />
									<img src="{s image/payment/[[method]].gif}" class="paymentLogo" alt="[[method]]" />
								</label>
							</div>
						{/foreach}
					</div>
				{% endif %}

				{% if !empty(requireTos) %}
					[[ partial("order/block/tos.tpl") ]]
				{% endif %}
			{/form}

			<div class="form">
				<div id="paymentForm"></div>

				<div id="checkout-place-order">
					<div class="text-error hidden" id="no-payment-method-selected">
						{t _no_payment_method_selected}
					</div>

					<hr />

					<div class="row">
						<div class="col col-lg-6">
							<div class="grandTotal">
								{t _total}:
								<span class="orderTotal">{$order.formattedTotal.$currency}</span>
							</div>
						</div>

						<div class="completeOrderButton text-right col col-lg-6">
							[[ partial("onePageCheckout/block/submitButton.tpl") ]]
						</div>
					</div>
				</div>
			</div>

			<div id="paymentMethodForms" style="display: none;">
				{% if 'CC_ENABLE'|config %}
					<div id="payForm_cc">
						[[ partial('checkout/block/ccForm.tpl', ['controller': "onePageCheckout"]) ]]
					</div>
				{% endif %}

				{foreach from=$offlineMethods key="key" item="method"}
					<div id="payForm_[[method]]">
						{form action="controller=onePageCheckout action=payOffline query=id=$method" handle=$offlineForms[$method] method="POST" class="form-horizontal"}
							{sect}
								{header}
									<h2>{"OFFLINE_NAME_`$key`"|config}</h2>
								{/header}
								{content}
									[[ partial('checkout/offlineMethodInfo.tpl', ['method': key]) ]]
									[[ partial('block/eav/fields.tpl', ['fieldList': offlineVars[$method].specFieldList]) ]]
								{/content}
							{/sect}
						{/form}
					</div>
				{/foreach}
			</div>

			<div class="notAvailable">
				<p>{t _payment_not_ready}</p>
			</div>
		</div>
	</div>
</div>