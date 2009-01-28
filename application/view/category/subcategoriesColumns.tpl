{math count=$subCategories|@count equation="max(1, ceil(count / 2))" assign="perColumn"}

<fieldset class="container">
{foreach from=$subCategories item="sub" name="subcats"}
{if $smarty.foreach.subcats.index % $perColumn == 0}
<table class="subCategories{if $smarty.foreach.subcats.first} first{/if}">
{/if}
	<tr>
		<td class="subCatImage">
			{if $sub.featuredProduct}
				<div class="categoryFeaturedProduct">
					<div class="price">
						{include file="product/block/productPrice.tpl" product=$sub.featuredProduct}
					</div>
					<a href="{productUrl product=$sub.featuredProduct}">{$sub.featuredProduct.name_lang|truncate:25}</a>
					{if $sub.featuredProduct.DefaultImage.ID}
						{include file="product/block/smallImage.tpl" product=$sub.featuredProduct}
					{/if}
				</div>
			{elseif $sub.DefaultImage.paths.1}
				<a href="{categoryUrl data=$sub}">
					{img src=$sub.DefaultImage.paths.1 alt=$sub.name_lang|escape}
				</a>
			{/if}
		</td>
		<td class="details{if $smarty.foreach.subcats.index < ($smarty.foreach.subcats.total / 2)} verticalSep{/if}{if !$sub.subCategories} noSubCats{/if}">
			<div class="subCatContainer">
				<div class="subCatContainer">
					<table><tr><td class="subCatContainer">
						<div class="subCatName">
							<a href="{categoryUrl data=$sub}">{$sub.name_lang}</a>
							<span class="count">(&rlm;{$sub.count})</span>
						</div>

						{if $sub.subCategories}
						<ul class="subSubCats">
							{foreach from=$sub.subCategories item="subSub" max="3" name="subSub"}
								{if $smarty.foreach.subSub.iteration > 3}
									<li class="moreSubCats">
										<a href="{categoryUrl data=$sub}">{t _more_subcats}</a>
									</li>
									{php}break;{/php}
								{/if}
								<li>
									<a href="{categoryUrl data=$subSub}">{$subSub.name_lang}</a>
									<span class="count">(&rlm;{$subSub.count})</span>
								</li>
							{/foreach}
						</ul>
						{/if}

						<div class="subCatDescr">
							{* $sub.description_lang *}
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

</fieldset>