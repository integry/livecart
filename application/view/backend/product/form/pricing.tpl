<div class="pricing">
	<h2>{t _pricing}</h2>

	<div ng-controller="ProductPricingController" ng-init="setCurrencies([[ json(baseCurrency) ]], [[ json(otherCurrencies) ]])">
		<div ng-repeat="currency in currencies">
		
			<h3>{{ currency }}</h3>
			
			<div class="row">
				<div class="col-lg-6">
					[[ textfld('price.defined[currency]', t('ProductPrice.price')) ]]
				</div>
				<div class="col-lg-6">
					[[ textfld('price.definedlistPrice[currency]', t('ProductPrice.listPrice')) ]]
				</div>
			</div>
		
			{#
			<quantity-price ng-init="init(currency)">
				<a class="menu setQuantPrice" ng-click="isActive=true" ng-show="!isActive">{t _set_quant}</a>
				<table class="table table-condensed table-bordered" ng-show="isActive">
					<thead>
						<tr class="quantityRow">
							<th>
								<div class="quantityLabel">{tip _quantity} ▸</div>
								<div class="groupLabel">▾ {tip _group}</div>
							</th>

							<th ng-repeat="quant in quantities" ng_init="quant.oldValue = quant.quantity">
								<input type="text" class="text quantity number" ng-model="quant.quantity" ng-change="updateQuantities(quant)" ngblur="updateOnBlur(quant)" />
							</th>
						</tr>
					</thead>
					<tbody>
						<tr ng-repeat="group in groups">
							<td class="groupColumn">{selectfield options=userGroups ng_model="group.id" ng_change="addGroup(group)" noFormat=true}</td>
							<td ng-repeat="quant in quantities">
								<input type="text" class="text qprice number" ng-model="quant[group.id]" />
							</td>
						</tr>
					</tbody>
				</table>
			</quantity-price>
			#}
		</div>
	</div>

	{#
	<div class="row">
	<div class="priceRow">
		{input name="defined.baseCurrency" class="basePrice"}
			{label}{tip baseCurrency _tip_main_currency_price}:{/label}
			<div class="controls">
				{textfield money=true class="money price" noFormat=true}
			</div>
		{/input}

		{input name="definedlistPrice.baseCurrency" class="listPrice"}
			{label}{tip _list_price}:{/label}
			<div class="controls">
				{textfield money=true class="money price" noFormat=true}
			</div>
		{/input}

		[[ partial('backend/product/form/quantityPricing.tpl', ['currency': baseCurrency]) ]]
	</div>
	</div>

	{foreach from=otherCurrencies item="currency"}

		<div class="row">
		<div class="priceRow">
			{input name="defined.currency" class="basePrice"}
				{label}{tip currency _tip_secondary_currency_price}:{/label}
				<div class="controls">
					{textfield money=true class="money price" noFormat=true}
				</div>
			{/input}

			{input name="definedlistPrice.currency" class="listPrice"}
				{label}{tip _list_price}:{/label}
				<div class="controls">
					{textfield money=true class="money price" noFormat=true}
				</div>
			{/input}

			[[ partial('backend/product/form/quantityPricing.tpl', ['currency': currency]) ]]
		</div>
		</div>

	{% endfor %}
	#}
</div>
