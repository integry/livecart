{sect}{header}
<div class="filterTop" id="filterTop_{uniqid}">
<form action="{categoryUrl data=$category filters=$filters}" method="post" id="{uniqid last=true}" class="form-horizontal">
{/header}{content}

	{% if 'TOP_FILTER_PRICE'|config %}
		[[ partial('category/block/filterSelect.tpl', ['sectionFilters': priceGroup, 'title': _by_price]) ]]
	{% endif %}

	{% if 'TOP_FILTER_MANUFACTURER'|config %}
		[[ partial('category/block/filterSelect.tpl', ['sectionFilters': manGroup, 'title': _by_brand]) ]]
	{% endif %}

	{foreach from=$groups item="group"}
		{% if $group.displayLocation == 1 %}
			[[ partial('category/block/filterSelect.tpl', ['sectionFilters': group, 'title': group.name_lang, 'allLink': group.more, 'allTitle': _show_all]) ]]
		{% endif %}
	{/foreach}

{/content}{footer}
{% if !'TOP_FILTER_RELOAD'|config %}
	<input type="submit" class="submit" value="{t _search}" />
{% endif %}
</form>
</div>

<script type="text/javascript">
	var filters = new Filter.SelectorMenu($("filterTop_{uniqid last=true}"), [[ config('TOP_FILTER_RELOAD') ]]);
</script>
{/footer}{/sect}