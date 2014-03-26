<dialog fullHeight=true class="deliveryzone-edit" cancel="cancel()">
	<dialog-header>
		{{vals.name}}
	</dialog-header>
	<dialog-body>
		<tabset-lazy>
			<tab-lazy class="main" title="{t _zone_settings}" template-url="[[ url('backend/deliveryzone/settings') ]]"></tab-lazy>
			<tab-lazy class="shipping" title="{t _shipping_rates}" template-url="[[ url('backend/shippingservice') ]]"></tab-lazy>
			<tab-lazy class="taxes" title="{t _tax_rates}" template-url="[[ url('backend/deliveryzone/taxes') ]]"></tab-lazy>
		</tabset-lazy>
	</dialog-body>
	<dialog-footer>
		<dialog-cancel>{t _cancel}</dialog-cancel>
		<submit tabform="main">{t _save}</submit>
	</dialog-footer>
</dialog>
