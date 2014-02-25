<div ng-controller="ProductCategoriesController">
	[[ form('', ['ng-submit': 'save()', 'ng-init': ';']) ]] >
		<h2>{t _primary_category}</h2>

		<div ng-controller="ProductMainCategoryController">
			[[ partial('block/backend/tree.tpl') ]]
		</div>

		<h2>{t _additional_categories}</h2>
		
		[[ partial('block/backend/tree.tpl', ['checkboxes': 'categories.extra']) ]]
	</form>
</div>
