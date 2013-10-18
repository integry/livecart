<div class="tree ui-widget-content">
	<ul
		{% if !empty(sortable) %}
		ui-nested-sortable="{
		listType: 'ul',
		doNotClear: true,
		placeholder: 'ui-state-highlight',
		forcePlaceholderSize: true,
		toleranceElement: '> div'
		}"
		ui-nested-sortable-stop="updatePosition($event, $ui)"
		{% endif %}
		>
		<li ya-tree="child in data.children at ul" class="minimized-{{child.open != true}}">
			<div ng-init="mouseover=0" ng-mouseover="mouseover=1" ng-mouseout="mouseover=0" ng-class="{'ui-state-hover': mouseover, 'ui-state-active': tree.selectedID == child.id}">
				<ins ng-click="child.open = !child.open" ng-show="child.children.length" class="expand ui-icon ui-icon-triangle-1-e"> </ins>
				<span ng-click="tree.select(child)" ng-class="child.attr.class">
					<ins class="ui-icon" ng-class="{'ui-icon-folder-open': (child.open && child.children.length), 'ui-icon-folder-collapsed': (child.children.length && !child.open), 'ui-icon-document': !child.children.length}"> </ins>
					<strong>{{child.title}}</strong>
				</span>
			</div>
			<ul><branch></ul>
		</li>
	</ul>
</div>
