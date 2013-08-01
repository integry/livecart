<div ng-controller="ProductPresentationController">
{form model="product" handle=$presentationForm role="product.update"}
	<fieldset>
		<legend>{t _presentation}</legend>

		{input name="isVariationImages"}
			{label}{tip _theme}:{/label}
			{selectfield options=$themes}
		{/input}

		{input name="isVariationImages"}
			{checkbox}
			{label}{tip _show_variation_images}{/label}
		{/input}

		{input name="isAllVariations"}
			{checkbox}
			{label}{tip _allow_all_variations}{/label}
		{/input}
	</fieldset>
{/form}
</div>