<dialog fullHeight=true class="" cancel="cancel()">
	[[ form('', ['ng-submit': 'save()', 'ng-init': ';']) ]] >
	<dialog-header>{{vals.name}}</dialog-header>
	<dialog-body>
		<tabset>
			<tab heading="{t _product_details}">[[ partial("backend/product/basicData.tpl") ]]</tab>
			{# <tab heading="{t _presentation}">[[ partial("backend/product/presentation.tpl") ]]</tab> #}
		</tabset>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit>{t _save_product}</submit>
	</dialog-footer>
	</form>
</dialog>
