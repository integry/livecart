<div ng-controller="UserGroupController" ng-init="setTree([[ json(userGroups) ]]); expandRoot();">
	<div class="row">
		<div class="treeContainer col-sm-3">
			[[ partial('block/backend/tree.tpl') ]]
			
			<div class="verticalMenu">
				<a class="btn btn-primary" ng-click="add()">
					<span class="glyphicon glyphicon-plus-sign"></span>
					{t _create_group}
				</a>
				<a class="btn btn-danger" ng-show="activeID > 0" ng-click="remove()">
					<span class="glyphicon glyphicon-remove-sign"></span>
					{t _remove}
				</a>
			</div>
		</div>

		<div class="col-sm-9">
			<section ui-view></section>
		</div>
	</div>
</div>
