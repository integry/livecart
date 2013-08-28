{% extends "layout/frontend.tpl" %}

{% block title %}{t _your_files}{{% endblock %}

{include file="user/layout.tpl"}

{include file="user/userMenu.tpl" current="fileMenu"}
{% block content %}

	<div class="resultStats">
		{if $files}
			{maketext text=_files_found params=$files|@count}
		{else}
			{t _no_files_found}
		{/if}
	</div>

	{foreach from=$files item="item"}
		<h3>
			<a href="{link controller=user action=item id=$item.ID}">[[item.Product.name_lang]]</a>
		</h3>
		{include file="user/fileList.tpl" item=$item}
	{/foreach}

{% endblock %}



</div>