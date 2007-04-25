<div style="width: 150px; font-size: smaller;">

	There are <strong>{$order.basketCount}</strong> items
	<br /> in your <a href="{link controller=order returnPath=true}">shopping cart</a>
	
	<div style="margin-top: 6px;">
		Total: <strong>{$order.formattedTotal.$currency}</strong>
	</div>

	<div style="margin-top: 6px;">
		<a href="{link controller=checkout returnPath=true}">{t Complete Purchase}</a>
	</div>

</div>