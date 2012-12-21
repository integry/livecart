{if $sectionFilters.filters}
	<div class="filterGroup filterTypeCheckbox" id="filterGroup_{$sectionFilters.ID}">
		<h4>{translate text=$title}</h4>
		<ul>
			{foreach from=$sectionFilters.filters item="filter" name="filters"}
				<li>
					<div>
						<input
							class="checkbox" type="checkbox"
							id="{$filter.ID}" name="{$filter.handle}-{$filter.ID}"
							{if in_array($filter.ID, $filtersIDs)}checked="checked"{/if}
						/>
						<label class="checkbox" for="{$filter.ID}">
							{$filter.name_lang}
							{if 'DISPLAY_NUM_FILTER'|config}
								<span class="count">(&rlm;{$filter.count})</span>
							{/if}
						</label>
					</div>
				</li>
			{/foreach}
		</ul>
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
{/if}