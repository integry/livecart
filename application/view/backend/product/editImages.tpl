<div ng-controller="ProductImagesController">
	[[ form('', ['ng-submit': 'save()', 'ng-init': ';']) ]] >
  
		<div class="row">
			<div class="col-sm-6 col-md-3">
				<span class="thumbnail">
					<image-field ng-model="newimage.path"></image-field>
				</span>
			</div>

			<div ui-sortable ng-model="images">
			<div class="col-sm-6 col-md-3" ng-repeat="image in images">
				<a class="thumbnail">
					<img ng-src="{{getPath(image)}}" />
				</a>
				<div class="imageMenu">
					<a class="delete" ng-click="remove(image)">{t _delete}</a>
				</div>
			</div>
			</div>
		</div>
  
	</form>
</div>
