{% if !empty(files) %}
	<div id="filesSection" class="productSection files">
		<h2>{t _preview_files}</h2>

		{foreach files as file}
			{% if file.productFileGroupID && (file.productFileGroupID != previousFileGroupID) %}
				<h3>[[file.ProductFileGroup.name]]</h3>
			{% endif %}

			{% if file.isEmbedded %}
				[[ partial("product/files/embed.tpl") ]]
			{% else %}
				[[ partial("product/files/link.tpl") ]]
			{% endif %}
		{% endfor %}
	</div>
{% endif %}