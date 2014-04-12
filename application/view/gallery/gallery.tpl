{% extends "layout/frontend.tpl" %}
{% block title %}[[ title(gallery.name) ]]{% endblock %}

{% block content %}

<div class="row gallery" ng-controller="GalleryController" ng-init="setImages([[ json(imageArray) ]])">
	{% for image in gallery.galleryImages %}
	<div class="col-xs-2">
		<div class="thumbnail" style="background-image: url('[[ image.getPath(0) ]]')" ng-click="openImage([[ gallery.ID ]], [[ image.ID ]])">
			<img src="[[ image.getPath(0) ]]" alt="[[ gallery.name ]]">
		</div>
	</div>
	{% endfor %}
</div>

{% endblock %}
