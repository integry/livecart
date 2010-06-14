
{if $sectionFilters.filters}
	
	{* addFilter=$filter *}
		<div class="filterGroup filterTypeCheckbox">
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
								<a href="javascript:void(0);">{$filter.name_lang}</a>
								{if 'DISPLAY_NUM_FILTER'|config}
									<span class="count">(&rlm;{$filter.count})</span>
								{/if}
							</label>
						</div>
					</li>
				{/foreach}
			</ul>
		</div>
{/if}
