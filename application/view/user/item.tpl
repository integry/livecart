{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_files}: [[item.Product.name()]]{% endblock %}
[[ partial("user/layout.tpl") ]]
[[ partial('user/userMenu.tpl', ['current': "homeMenu"]) ]]
{% block content %}

	{% if !empty(files) %}
		{foreach from=files item="item"}
			<h3>
				<a href="{productUrl product=item.Product}">[[item.Product.name()]]</a>
			</h3>
			[[ partial('user/fileList.tpl', ['item': item]) ]]
		{% endfor %}
	{% endif %}

{% endblock %}
