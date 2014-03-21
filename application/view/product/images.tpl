<div id="imageContainer">
	<div id="largeImage" class="{% if count(images) == 0 %}missingImage{% endif %} {% if count(images) > 1 %}multipleImages{% endif %}">
		{% if product.get_DefaultImage() %}
			<a href="[[ product.get_DefaultImage().getPath(3) ]]">
				<img src="[[ product.get_DefaultImage().getPath(3) ]]" />
			</a>
		{% else %}
			<img src="[[ config('MISSING_IMG_LARGE') ]]" alt="" id="mainImage" />
		{% endif %}
	</div>
	{% if count(images) > 1 %}
		<div id="moreImages">
			{% for image in images %}
				<a href="[[ image.urls['4'] ]]" target="_blank" onclick="return false;">{img src=image.urls.1 id="img_`image.ID`" alt=image.name()|escape onclick="return false;"}</a>
			{% endfor %}
		</div>
	{% endif %}
</div>
