<div ng-controller="UserGroupController" ng-init="setTree([[ json(userGroups) ]]); expandRoot();">
	<div class="row">
		<div class="treeContainer col-lg-3">
			[[ partial('block/backend/tree.tpl') ]]
			
			<ul id="userActions" class="verticalMenu">
				<li class="addTreeNode">
					<a ng-click="add()">
						{t _create_group}
					</a>
				</li>

				<li class="removeTreeNode" ng-show="activeID > 0">
					<a ng-click="remove()">
						{t _remove_group}
					</a>
				</li>
			</ul>

		</div>

		<div class="col-lg-9">
			<section ui-view></section>
		</div>
	</div>
</div>
