<div id="imageContainer" ng-init="img = [[ json(product.get_DefaultImage().getPath(3)) ]]">
	<div id="largeImage" class="{% if count(images) == 0 %}missingImage{% endif %} {% if count(images) > 1 %}multipleImages{% endif %}">
		{% if product.get_DefaultImage() %}
			<img src="[[ product.get_DefaultImage().getPath(4) ]]" ng-src="{{ img }}" />
		{% else %}
			<img src="[[ config('MISSING_IMG_LARGE') ]]" alt="" id="mainImage" />
		{% endif %}
	</div>
	{% if count(images) > 1 %}
		<div id="moreImages">
			{% for image in images %}
				<a ng-click="img = [[ json(image.getPath(4)) ]]"><img src="[[ image.getPath(2) ]]" alt="[[ product.name() ]]" /></a>
			{% endfor %}
		</div>
	{% endif %}
</div>
