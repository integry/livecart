<div ng-controller="CustomerOrderRootController" ng-init="setTree([[ json(orderGroups) ]]); expandRoot();">
	<div class="row">
		<div class="treeContainer col-sm-3">
			[[ partial('block/backend/tree.tpl') ]]
		</div>

		<div class="col-sm-9">
			<section ui-view></section>
		</div>
	</div>
</div>
