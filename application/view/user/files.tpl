{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_files}{{% endblock %}

[[ partial("user/layout.tpl") ]]

[[ partial('user/userMenu.tpl', ['current': "fileMenu"]) ]]
{% block content %}

	<div class="resultStats">
		{% if !empty(files) %}
			{maketext text=_files_found params=$files|@count}
		{% else %}
			{t _no_files_found}
		{% endif %}
	</div>

	{foreach from=$files item="item"}
		<h3>
			<a href="{link controller=user action=item id=$item.ID}">[[item.Product.name_lang]]</a>
		</h3>
		[[ partial('user/fileList.tpl', ['item': item]) ]]
	{/foreach}

{% endblock %}



</div>