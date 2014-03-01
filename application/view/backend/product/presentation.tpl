<div ng-controller="ProductPresentationController">
{form model="product" handle=presentationForm role="product.update"}
	<fieldset>
		<legend>{t _presentation}</legend>

		[[ selectfld('isVariationImages', tip( '_theme'), themes) ]]

		[[ checkbox('isVariationImages', tip('_show_variation_images')) ]]

		[[ checkbox('isAllVariations', tip('_allow_all_variations')) ]]
	</fieldset>
{/form}
</div>