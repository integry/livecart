<div ng-controller="ProductController">

	{# <a class="btn btn-primary" ng-click="add()">{t _add_product}</a> #}
	
	<grid controller="backend.product" primaryKey="Product_ID">
		<menu>
			<div class="btn-toolbar">
				<div class="btn-group">
					<a class="btn btn-default" ng-click="refresh()" title="{t _refresh}">
						<span class="glyphicon glyphicon-refresh"></span>
					</a>
				</div>
				
				<div class="btn-group" ng-show="isMassAllowed()">
					<a class="btn btn-danger" ng-click="remove()" title="{t _delete}">
						<span class="glyphicon glyphicon-trash"></span>
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
