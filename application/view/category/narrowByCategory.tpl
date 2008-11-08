{math count=$categoryNarrow|@count equation="max(1, ceil(count / 2))" assign="perColumn"}

<fieldset class="container">

<div class="narrowByCat">
	<div class="resultStats">
		{t _narrow_by_cat}
	</div>

	{foreach from=$categoryNarrow item="sub" name="subcats"}

		{if $smarty.foreach.subcats.index % $perColumn == 0}
			<table class="subCategories{if $smarty.foreach.subcats.first} first{/if}">
		{/if}

		<tr>
			<td class="subCatImage">
				{if $sub.DefaultImage.paths.1}
					<a href="{categoryUrl data=$sub filters=$appliedFilters}">
						{img src=$sub.DefaultImage.paths.1 alt=$sub.name_lang|escape}
					</a>
				{/if}
			</td>
			<td class="details{if $smarty.foreach.subcats.index < ($smarty.foreach.subcats.total / 2)} verticalSep{/if}{if !$sub.subCategories} noSubCats{/if}">
				<div class="subCatContainer">
					<div class="subCatContainer">
						<table><tr><td class="subCatContainer">
							<div class="subCatName">
								<a href="{categoryUrl data=$sub filters=$appliedFilters}">{$sub.name_lang}</a>
								<span class="count">(&rlm;{$sub.searchCount})</span>
							</div>
						</td></tr></table>
					</div>
				</div>
			</td>
		</tr>

		{if !$smarty.foreach.subcats.last && ($smarty.foreach.subcats.iteration % $perColumn != 0)}
			<tr class="separator">
				<td colspan="2"><div></div></td>
			</tr>
		{/if}
		{if $smarty.foreach.subcats.iteration % $perColumn == 0 || $smarty.foreach.subcats.last}
			</table>
		{/if}

	{/foreach}
	</table>

	<div class="clear"></div>

</div>

</fieldset>