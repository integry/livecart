<dialog fullHeight=true class="col-lg-11" cancel="cancel()">
	<dialog-header>{{product.name}}</dialog-header>
	<dialog-body>
		<tabset>
			<tab heading="{t _product_details}">{include file="backend/product/basicData.tpl"}</tab>
			{* <tab heading="{t _presentation}">{include file="backend/product/presentation.tpl"}</tab> *}
		</tabset>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabForm="productDetails" ng-click="save()">{t _save_product}</submit>
	</dialog-footer>
</dialog>