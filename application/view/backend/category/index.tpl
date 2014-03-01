<div ng-controller="CategoryController" ng-init="setTree([[ json(categoryList) ]]); expandRoot();">
	<div class="row">
		<div class="treeContainer col-sm-3">
			[[ partial('block/backend/tree.tpl', ['sortable': true]) ]]
			
			<ul id="categoryBrowserActions" class="verticalMenu">
				<li class="addTreeNode">
					<a ng-click="add(activeID)">
						{t _create_subcategory}
					</a>
				</li>

				<li class="removeTreeNode" ng-show="activeID > 1">
					<a ng-click="remove()">
						{t _remove_category}
					</a>
				</li>
			</ul>

		</div>

		<div class="col-sm-9">
			<section ui-view></section>
		</div>
	</div>
</div>
