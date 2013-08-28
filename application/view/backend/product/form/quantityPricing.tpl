<quantity-price ng-init="init('[[currency]]')">
	<a class="menu setQuantPrice" ng-click="isActive=true" ng-show="!isActive">{t _set_quant}</a>
	<table class="table table-condensed table-bordered" ng-show="isActive">
		<thead>
			<tr class="quantityRow">
				<th>
					<div class="quantityLabel">{tip _quantity} ▸</div>
					<div class="groupLabel">▾ {tip _group}</div>
				</th>

				<th ng-repeat="quant in quantities" ng_init="quant.oldValue = quant.quantity">
					{textfield number=true class="text quantity number" ng_model="quant.quantity" ng_change="updateQuantities(quant)" ngblur="updateOnBlur(quant)" noFormat=true}
				</th>
			</tr>
		</thead>
		<tbody>
			<tr ng-repeat="group in groups">
				<td class="groupColumn">{selectfield options=$userGroups ng_model="group.id" ng_change="addGroup(group)" noFormat=true}</td>
				<td ng-repeat="quant in quantities">
					{textfield money=true class="text qprice number" ng_model="quant[group.id]" noFormat=true}
				</td>
			</tr>
		</tbody>
	</table>
</quantity-price>
