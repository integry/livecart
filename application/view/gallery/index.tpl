{% extends "layout/frontend.tpl" %}
{% block title %}[[ title('Galerijas') ]]{% endblock %}

{% block content %}

<div class="row">
	{% for gallery in galleries %}
	<div class="col-xs-6 col-md-3">
		<div class="thumbnail">
			<a href="[[ url("gallery/gallery/" ~ gallery.ID) ]]">
				<img src="[[ gallery.defaultImage.getPath(1) ]]" alt="[[ gallery.name ]]" />
			</a>
			
			<div class="caption">
				<a href="[[ url("gallery/gallery/" ~ gallery.ID) ]]"><h3>[[ gallery.name ]]</h3></a>
			</div>
		</div>
	</div>
	{% endfor %}
</div>

</div>

{% endblock %}
