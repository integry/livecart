<h1>Manage product categories</h1>
<div>
	<span>
		<a href="{link controller=backend.catalog action=form id=$catalog.ID}" target="catalogFrame">Main information</a>
	</span> 
	<span>
		<a href="{link controller=backend.catalog action=specFieldList id=$catalog.ID}" target="catalogFrame">Extra fields</a>
	</span>
</div>
<iframe src="{link controller=backend.catalog action=form}" name="catalogFrame" scrolling="auto" width="670" height="450"></iframe>