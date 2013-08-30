<span>
	<fieldset class="container">
		<div class="productRelationship_image">
			{% if $product.DefaultImage %}
				{img src=$product.DefaultImage.urls[1] alt=$product.DefaultImage.title title=$product.DefaultImage[1].title }
			{% endif %}
		</div>
		{% if !empty(template) %}
			[[ partial(template) ]]
		{% endif %}
		<span class="productRelationship_title">[[product.name_lang]]</span>
		<a href="{backendProductUrl product=$product}" onclick="Backend.Product.openProduct([[product.ID]]); return false;" class="openRelatedProduct"></a>
	</fieldset>
	<div class="clear: both"></div>
</span>