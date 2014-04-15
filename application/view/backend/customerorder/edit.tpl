<dialog fullHeight=true cancel="cancel()">
	<dialog-header>
		<span ng-show="vals.ID">{{order.invoiceNumber}}</span>
		<span ng-show="!vals.ID">{t _create_order}</span>
	</dialog-header>
	<dialog-body>
		
		<div class="row">
			<div class="col-md-4">
				<dl class="dl-horizontal">
					<dt>{t CustomerOrder.invoiceNumber}</dt>
					<dd>{{ order.invoiceNumber }} </dd>

					<dt>{t CustomerOrder.dateCompleted}</dt>
					<dd>{{ order.dateCompleted }}</dd>

					<dt>{t CustomerOrder.totalAmount}</dt>
					<dd>{{ order.formattedTotal[order.currencyID] }}</dd>
				</dl>
			</div>
			
			<div class="col-md-4">
				<dl class="dl-horizontal">
					<div ng-repeat="field in eav">
						<dt>{{ field.name }}</dt>
						<dd>{{ order.eav[field.ID] }}</dd>
					</div>
				</dl>
			</div>

			<div class="col-md-2">
				[[ selectfld('status', '_status', statuses, ['ng-model': 'order.status']) ]]
			</div>

			<div class="col-md-2 text-right">
				<a class="btn btn-danger">{t _cancel_order}</a>
			</div>
		</div>
		
		<hr />
		
		<div class="row" ng-controller="OrderController" ng-init="updateURL = '../backend/customerorder/preview'">
			<table class="table table-striped shopping-cart">
				<thead>
					<tr>
						<th></th>
						<th>{t OrderedItem.price}</th>
						<th>{t OrderedItem.count}</th>
						<th>{t _subtotal}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr ng-repeat="item in order.OrderedItems">
						<td class="col-lg-5">
							{{ item.name }}
						
							<div class="selectOptions" ng-show="getOptions(item)">
								<div ng-repeat="option in getOptions(item)" class="option">
									<span class="param">{{ option.name }}:</span>
									<span class="value">
										<prod-option option="option"></prod-option>
									</span>
								</div>
							</div>						
						</td>
						<td class="col-lg-2">{{ item.formattedDisplayPrice }}</td>
						<td class="col-lg-2">
							[[ partial("product/block/quantity.tpl") ]]
						</td>
						<td class="col-lg-2">{{ item.formattedDisplaySubTotal }}</td>
						<td class="col-lg-1"><button class="btn btn-default btn-xs btn-remove" ng-click="remove(item)"><span class="glyphicon glyphicon-remove"></span></button></td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3" class="text-right">{t CustomerOrder.totalAmount}:</td>
						<td>{{ order.formattedTotal[order.currencyID] }}</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
		
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<button class="btn btn-primary" ng-click="save()">{t _save_order}</button>
	</dialog-footer>
</dialog>
