<div ng-controller="EavController" ng-init="setTree([[ json(nodes) ]]);">
	<div class="row">
		<div class="treeContainer col-lg-3">
			[[ partial('block/backend/tree.tpl', ['sortable': true]) ]]
		</div>

		<div class="col-lg-9">
			<section ui-view></section>
		</div>
	</div>
</div>
