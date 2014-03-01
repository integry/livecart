{assign var=maxFilters value=config('MAX_FILTER_CRITERIA_COUNT')}
{% if sectionFilters.filters|@hasFilters %}
	<div class="filterGroup">
		<h4>[[ t(title) ]]</h4>
		<ul>
			{foreach from=sectionFilters.filters item="filter" name="filters"}
				{% if filter.count && (!allLink || (allLink && smarty.foreach.filters.index < maxFilters)) %}
					<li>
						<div>
							<a href="{categoryUrl data=category filters=filters addFilter=filter removeFilters=sectionFilters.filters}">[[filter.name()]]</a>
							{% if config('DISPLAY_NUM_FILTER') %}
								 [[ partial('block/count.tpl', ['count': filter.count]) ]]
							{% endif %}
						</div>
					</li>
				{% endif %}
			{% endfor %}

			{% if !empty(allLink) %}
				<li class="showAll"><a href="[[allLink]]">[[ t(allTitle) ]]</a></li>
			{% endif %}
		</ul>
	</div>
{% endif %}
