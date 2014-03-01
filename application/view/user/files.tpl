{% extends "layout/frontend.tpl" %}

{% title %}{t _your_files}{% endblock %}

[[ partial("user/layout.tpl") ]]

[[ partial('user/userMenu.tpl', ['current': "fileMenu"]) ]]
{% block content %}

	<div class="resultStats">
		{% if !empty(files) %}
			{maketext text=_files_found params=files|@count}
		{% else %}
			{t _no_files_found}
		{% endif %}
	</div>

	{foreach from=files item="item"}
		<h3>
			<a href="[[ url("user/item/" ~ item.ID) ]]">[[item.Product.name()]]</a>
		</h3>
		[[ partial('user/fileList.tpl', ['item': item]) ]]
	{% endfor %}

{% endblock %}



</div>