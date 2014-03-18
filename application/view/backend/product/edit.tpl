<dialog fullHeight=true class="" cancel="cancel()">
	<dialog-header>
		<span ng-show="vals.id">{{vals.name}}</span>
		<span ng-show="!vals.id">{t _add_product}</span>
	</dialog-header>
	<dialog-body>
		<tabset-lazy>
			<tab-lazy ng-show="vals.id" class="main" title="{t _product_details}" template-url="[[ url('backend/product/basicData') ]]"></tab-lazy>

			<tab-lazy ng-show="vals.id" class="categories" title="{t _product_categories}" template-url="[[ url('backend/product/editCategories') ]]"></tab-lazy>

			<tab-lazy ng-show="vals.id" class="images" title="{t _images}" template-url="[[ url('backend/product/editImages') ]]"></tab-lazy>

			{# <tab heading="{t _presentation}">[[ partial("backend/product/presentation.tpl") ]]</tab> #}
		</tabset-lazy>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save_product_details}</submit>
	</dialog-footer>
</dialog>
