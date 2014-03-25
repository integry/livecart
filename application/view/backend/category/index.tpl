<div ng-controller="CategoryController" ng-init="setTree([[ json(categoryList) ]]); expandRoot();">
	<div class="row">
		<div class="treeContainer col-sm-3">
			[[ partial('block/backend/tree.tpl', ['sortable': true]) ]]
			
			<div class="verticalMenu">
				<a class="btn btn-primary" ng-click="add(activeID)">
					<span class="glyphicon glyphicon-plus-sign"></span>
					{t _create_subcategory}
				</a>

				<a class="btn btn-danger" ng-click="remove()" ng-show="activeID > 1">
					<span class="glyphicon glyphicon-remove-sign"></span>
					{t _remove_category}
				</a>
			</div>

		</div>

		<div class="col-sm-9">
			<section ui-view></section>
		</div>
	</div>
</div>
