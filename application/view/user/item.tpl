{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_files}: [[item.Product.name_lang]]{{% endblock %}
[[ partial("user/layout.tpl") ]]
{include file="user/userMenu.tpl" current="homeMenu"}
{% block content %}

	{if $files}
		{foreach from=$files item="item"}
			<h3>
				<a href="{productUrl product=$item.Product}">[[item.Product.name_lang]]</a>
			</h3>
			{include file="user/fileList.tpl" item=$item}
		{/foreach}
	{/if}

{% endblock %}
