{math count=$subCategories|@count equation="max(1, ceil(count / 2))" assign="perColumn"}

<fieldset class="container">
{foreach from=$subCategories item="sub" name="subcats"}
{if $smarty.foreach.subcats.index % $perColumn == 0}
<table class="subCategories{if $smarty.foreach.subcats.first} first{/if}">
{/if}
	<tr >
		<td class="subCatImage">
			{if $sub.DefaultImage.paths.1}
				<a href="{categoryUrl data=$sub}">
					{img src=$sub.DefaultImage.paths.1 alt=$sub.name_lang|escape}				</a>
			{/if}		</td>
		<td class="details{if $smarty.foreach.subcats.index < ($smarty.foreach.subcats.total / 2)} verticalSep{/if}{if !$sub.subCategories} noSubCats{/if}">
			<div class="subCatContainer">
				<div class="subCatContainer">
					<table><tr><td class="subCatContainer">
						<div class="subCatName">
							<a href="{categoryUrl data=$sub}">{$sub.name_lang}</a>
							<span class="count">({$sub.count})</span>						</div>

						{if $sub.subCategories}
						<ul class="subSubCats">
							{foreach from=$sub.subCategories item="subSub" max="3" name="subSub"}
								{if $smarty.foreach.subSub.iteration > 3}
									<li class="moreSubCats">
										<a href="{categoryUrl data=$sub}">{t _more_subcats}</a>									</li>
									{php}break;{/php}
								{/if}
								<li>
									<a href="{categoryUrl data=$subSub}">{$subSub.name_lang}</a>
									<span class="count">({$subSub.count})</span>								</li>
							{/foreach}
						</ul>
						{/if}

						<div class="subCatDescr">
							{* $sub.description_lang *}						</div>
					</td></tr></table>
				</div>
			</div>		</td>
	</tr>
	<tr class="content_area_ror_sep" >
	  <td colspan="2" class="content_area_ror_sep"  ><img src="upload/sep_hor.gif" height="1px" width="1px"></td>
  </tr>
	{if !$smarty.foreach.subcats.last && ($smarty.foreach.subcats.iteration % $perColumn != 0)}

	{/if}
	{if $smarty.foreach.subcats.iteration % $perColumn == 0 || $smarty.foreach.subcats.last}
		</table>
	{/if}
{/foreach}

</fieldset>