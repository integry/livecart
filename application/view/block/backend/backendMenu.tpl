<script type="text/javascript">
	window.menuArray = {$menuArray};
</script>

<div ng-controller="MenuController">
	<div id="menuDescription" ng-show="description">{{description}}</div>

	<ul id="nav">
		<li ng-repeat="item in items">
			<div><div><div>
				<a style="background-image:url('{{item.icon}}')" ng-mouseout="setDescription('')" ng-mouseover="setDescription(item.descr)" ng-href="{{menuRoute(item)}}">{{item.title}}</a>
				<ul ng-show="item.items">
					<li ng-repeat="subitem in item.items">
						<a style="background-image:url('{{subitem.icon}}')" ng-mouseout="setDescription('')" ng-mouseover="setDescription(subitem.descr)" ng-href="{{menuRoute(subitem)}}">{{subitem.title}}</a>
					</li>
				</ul>
			</div></div></div>
		</li>
	</ul>
</div>