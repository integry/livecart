{assign var=maxFilters value='MAX_FILTER_CRITERIA_COUNT'|config}
{% if $sectionFilters.filters|@hasFilters %}
	<div class="filterGroup">
		<h4>{translate text=$title}</h4>
		<ul>
			{foreach from=$sectionFilters.filters item="filter" name="filters"}
				{% if $filter.count && (!$allLink || ($allLink && $smarty.foreach.filters.index < $maxFilters)) %}
					<li>
						<div>
							<a href="{categoryUrl data=$category filters=$filters addFilter=$filter removeFilters=$sectionFilters.filters}">[[filter.name_lang]]</a>
							{% if 'DISPLAY_NUM_FILTER'|config %}
								 [[ partial('block/count.tpl', ['count': filter.count]) ]]
							{% endif %}
						</div>
					</li>
				{% endif %}
			{/foreach}

			{% if $allLink %}
				<li class="showAll"><a href="[[allLink]]">{translate text=$allTitle}</a></li>
			{% endif %}
		</ul>
	</div>
{% endif %}
