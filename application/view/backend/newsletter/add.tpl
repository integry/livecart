<div class="newsletterform">
	<fieldset class="container">
		<ul class="menu">
			<li class="done"><a class="cancel" href="#" onclick="Backend.Newsletter.cancelAdd(); return false;">{t _cancel_create_newsletter}</a></li>
		</ul>
	</fieldset>

	{form handle=$form action="backend.newsletter/save" method="POST" onsubmit="Backend.Newsletter.saveForm(this); return false;" onreset="Backend.Newsletter.resetAddForm(this);"}

		<fieldset>
			<legend>{t _create_message|capitalize}</legend>
			[[ partial("backend/newsletter/form.tpl") ]]
		</fieldset>

		<fieldset class="controls">

			<input type="checkbox" name="afterAdding" value="new" style="display: none;" />

			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" name="adAd_continue" class="submit" value="{t _save_and_continue}" onclick="this.form.elements.namedItem('afterAdding').checked = false;" />
			{t _or} <a class="cancel" href="#" onclick="Backend.Newsletter.cancelAdd(); return false;">{t _cancel}</a>

		</fieldset>

	{/form}

	{literal}
	<script type="text/javascript">
		Backend.Newsletter.initAddForm();
		// Backend.Product.setPath({/literal}[[product.Category.ID]], {json array=$path}{literal})
	</script>
	{/literal}

</div>