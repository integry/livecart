[[ form('', ['ng-init': ';', 'ng-submit': 'save(form)']) ]] >
<dialog fullHeight=true class="shippingservice-edit" cancel="cancel()">
	<dialog-header>
		{{vals.name}}
	</dialog-header>
	<dialog-body>

		[[ textfld('name', tip('_name')) ]]
		
		<hr />
				
		<div class="row">
			<div class="col-lg-6">
				<table class="table table-striped">
					<thead>
						<th></th>
						<th>
							<span ng-show="isWeight()">{t _weight}</span>
							<span ng-show="!isWeight()">{t _amount}</span>
						</th>
						<th>{tip _flat_charge}</th>
						<th>{tip _per_item_charge}</th>
						
						<th>
							<span ng-show="isWeight()">{tip _per_kg_charge} ([[defaultCurrencyCode]])</span>
							<span ng-show="!isWeight()">{tip _subtotal_percent_charge} (%)</span>
						</th>
					</thead>
					<tbody>
						<tr ng-repeat="rate in vals.rates">
							<td>
								<a ng-click="deleteRate(rate)" class="glyphicon glyphicon-remove-sign"></a>
							</td>
							<td>
								<input money type="number" class="form-control" ng-model="rate.weightRangeEnd" ng-show="isWeight()" />
								<input money type="number" class="form-control" ng-model="rate.subtotalRangeEnd" ng-show="!isWeight()" />
							</td>
							<td>
								<input money type="number" class="form-control" ng-model="rate.flatCharge" />
							</td>
							<td>
								<input money type="number" class="form-control" ng-model="rate.perItemCharge" />
							</td>
							<td>
								<input money type="number" class="form-control" ng-model="rate.subtotalPercentCharge" ng-show="isWeight()" />
								<input money type="number" class="form-control" ng-model="rate.perKgCharge"  ng-show="!isWeight()" />
							</td>
						</tr>
					</tbody>
				</table>
			
			</div>
			
			<div class="col-lg-6">
		
				[[ checkbox('isFinal', tip('_disable_other_services')) ]]
				
				[[ checkbox('isLocalPickup', tip('_is_local_pickup')) ]]
				
				[[ selectfld('rangeType', tip( '_type'), [tip('_weight_based_calculations'), tip('_subtotal_based_calculations')]) ]]
				
				[[ textareafld('description', tip('_description'), ['ui-my-tinymce': '']) ]]
				
				[[ textfld('deliveryTimeMinDays', tip('_expected_delivery_time')) ]]
				
				[[ textfld('deliveryTimeMaxDays', tip('_expected_delivery_time')) ]]
				
			</div>
		</div>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save}</submit>
	</dialog-footer>
</dialog>
</my-form>
