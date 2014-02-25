<div ng-controller="ProductController">

	{# <a class="btn btn-primary" ng-click="add()">{t _add_product}</a> #}
	
	<grid controller="backend.product" primaryKey="product_Product_ID">
		<menu>
			<div class="btn-toolbar">
				<div class="btn-group">
					<a class="btn btn-default" ng-click="refresh()" title="{t _refresh}">
						<span class="glyphicon glyphicon-refresh"></span>
					</a>
					<a class="btn btn-default" ng-click="search()" title="{t _search}">
						<span class="glyphicon glyphicon-search"></span>
					</a>
				</div>
				
				<div class="btn-group" ng-show="isMassAllowed()">
					<a class="btn btn-danger" ng-click="remove()" title="{t _delete}">
						<span class="glyphicon glyphicon-trash"></span>
					</a>

					<a class="btn btn-default" ng-click="enable()" title="{t _enable}">
						<span class="glyphicon glyphicon-check"></span>
					</a>
				</div>
				
				<div class="btn-group" ng-show="isMassAllowed()">
					<a class="btn btn-default" ng-click="set('priority', 1)" title="1">
						1
					</a>
					<a class="btn btn-default" ng-click="set('priority', 2)" title="2">
						2
					</a>
					<a class="btn btn-default" ng-click="set('priority', 3)" title="3">
						3
					</a>
				</div>
				
			</div>
		</menu>
		<actions>
			<edit-button>{t _edit}</edit-button>
		</actions>
		<mass>

		</mass>
	</grid>

</div>
