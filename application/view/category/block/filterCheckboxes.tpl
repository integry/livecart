{% if $sectionFilters.filters %}
	<div class="filterGroup filterTypeCheckbox" id="filterGroup_[[sectionFilters.ID]]">
		<div class="nav-header">{translate text=$title}</div>
		{foreach from=$sectionFilters.filters item="filter" name="filters"}
			<div class="checkbox">
				<label>
					<input
						class="checkbox" type="checkbox"
						name="[[filter.handle]]-[[filter.ID]]"
						{% if in_array($filter.ID, $filtersIDs) %}checked="checked"{% endif %}
					/>
					[[filter.name_lang]]
					{% if 'DISPLAY_NUM_FILTER'|config %}
						[[ partial('block/count.tpl', ['count': filter.count]) ]]
					{% endif %}
				</label>
			</div>
		{/foreach}
	</div>
	{literal}
		<script type="text/javascript">
			function showCheckboxFilterControls()
			{
				var
					IDs = ["multipleChoiceFilter_top", "multipleChoiceFilter_bottom"],
					ID;
				while(ID = IDs.pop())
				{
					$(ID).removeClassName("hidden");
				}
			}
			if(_checkboxFilterLoadHookObserved == false)
			{
				Event.observe(window, 'load', showCheckboxFilterControls);
				_checkboxFilterLoadHookObserved = true;
			}
		</script>
	{/literal}
{% endif %}