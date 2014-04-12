<div ng-controller="GalleryController">

	<grid controller="backend.gallery" primaryKey="Gallery_ID">
		<menu>
			<div class="btn-toolbar">

				<div class="btn-group">
					<a class="btn btn-primary" ng-click="add()">{t _add_gallery}</a>
				</div>
	
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
				
			</div>
		</menu>
		<actions>
			<edit-button>{t _edit}</edit-button>
		</actions>
		<mass>

		</mass>
	</grid>

</div>
