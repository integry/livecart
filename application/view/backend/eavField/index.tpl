<div ng-controller="EavController" ng-init="setTree([[ json(nodes) ]]);">
	<div class="row">
		<div class="treeContainer col-lg-3">
			[[ partial('block/backend/tree.tpl', ['sortable': true]) ]]
		</div>

		<div class="col-lg-9">
			<section ui-view></section>
		</div>
	</div>

	<div id="managerContainer" class="treeManagerContainer maxHeight h--60">
		<div ng-repeat="category in categories" ng-show="category.id == activeID">
			<tabset>
				<tab-route heading="{t _products}" template="[[ url("backend.product/index") ]]" route="{{route('backend.product', 'list', {id: category.id})}}"></tab-route>
				<tab-route heading="{t _category_details}" template="[[ url("backend.category/form") ]]" route="{{route('backend.category', 'category', {id: category.id})}}"></tab-route>
			</tabset>
		</div>
	</div>

</div>
