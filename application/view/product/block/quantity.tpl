<div class="quantity-select">
	<button class="btn btn-default btn-xs btn-dec" ng-click="decCount(item)" ng-disabled="item.count <= 1">-</button>
	<input class="form-control quantity" ng-model="item.count" />
	<button class="btn btn-default btn-xs btn-inc" ng-click="incCount(item)">+</button>
</div>
