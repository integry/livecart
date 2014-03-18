<dialog fullHeight=true class="col-sm-11" cancel="cancel()">
	<dialog-header>{{product.name}}</dialog-header>
	<dialog-body>
		<tabset-lazy>
			<tab-lazy class="main" title="{t _product_details}" template-url="[[ url('backend/product/basicData') ]]"></tab-lazy>
		</tabset-lazy>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabForm="main">{t _save_product}</submit>
	</dialog-footer>
</dialog>
