<ul class="downloadFile">
{foreach from=$item.Product.Files item="file"}
	{% if $file.ProductFileGroup.ID != $prev.ProductFileGroup.ID %}
		<li class="fileGroup">
			[[file.ProductFileGroup.name_lang]]
		</li>
	{% endif %}

	<li class="ext_[[file.extension]]">
		<a href="{link controller=user action=download id=$item.ID query="fileID=`$file.ID`"}">[[file.title_lang]]</a>
	</li>
	{% set prev = $file %}
{/foreach}
</ul>
