{% if !empty(files) %}
	<div id="orderDownloads">
		<h2>{t _download}</h2>
		{foreach from=files item="item"}
			[[ partial('user/fileList.tpl', ['item': item]) ]]
		{% endfor %}
	</div>
{% endif %}
