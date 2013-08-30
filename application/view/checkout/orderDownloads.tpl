{% if $files %}
	<div id="orderDownloads">
		<h2>{t _download}</h2>
		{foreach from=$files item="item"}
			{include file="user/fileList.tpl" item=$item}
		{/foreach}
	</div>
{% endif %}
