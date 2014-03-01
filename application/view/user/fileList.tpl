<ul class="downloadFile">
{foreach from=$item.Product.Files item="file"}
	{% if $file.ProductFileGroup.ID != $prev.ProductFileGroup.ID %}
		<li class="fileGroup">
			[[file.ProductFileGroup.name()]]
		</li>
	{% endif %}

	<li class="ext_[[file.extension]]">
		<a href="[[ url("user/download/" ~ item.ID, "fileID=`$file.ID`") ]]">[[file.title()]]</a>
	</li>
	{% set prev = $file %}
{/foreach}
</ul>
