<div class="row">
{sect}
	{head}
		<div class="subCatImage col-span-4">
	{cont}
		{if $sub.featuredProduct.ID}
			<div class="categoryFeaturedProduct">
				<div class="price">
					{include file="product/block/productPrice.tpl" product=$sub.featuredProduct}
				</div>
				<a href="{productUrl product=$sub.featuredProduct}">{$sub.featuredProduct.name_lang|truncate:25}</a>
				{if $sub.featuredProduct.DefaultImage.ID}
					{include file="product/block/smallImage.tpl" product=$sub.featuredProduct}
				{/if}
			</div>
		{elseif $sub.DefaultImage.urls.1 && 'CAT_MENU_IMAGE'|config}
			<a href="{categoryUrl data=$sub}">
				{img src=$sub.DefaultImage.urls.1 alt=$sub.name_lang|escape}
			</a>
		{/if}
	{foot}
		</div>
{/sect}
<div class="details col-span-8 {if !$sub.subCategories} noSubCats{/if}">
	<div class="subCatContainer">
		<div class="subCatName">
			<a href="{categoryUrl data=$sub filters=$filters}">{$sub.name_lang}</a>
			<span class="count">(&rlm;{$sub.searchCount|default:$sub.count})</span>
		</div>

		{if $sub.subCategories}
		<ul class="subSubCats">
			{foreach from=$sub.subCategories item="subSub" name="subSub"}
				{if $smarty.foreach.subSub.iteration > 'CAT_MENU_SUBS'|config}
					<li class="moreSubCats">
						<a href="{categoryUrl data=$sub filters=$filters}">{t _more_subcats}</a>
					</li>
					{break}
				{/if}
				<li>
					<a href="{categoryUrl data=$subSub}">{$subSub.name_lang}</a>
					<span class="count">(&rlm;{$subSub.count})</span>
				</li>
			{/foreach}
		</ul>
		{/if}

		{if 'CAT_MENU_DESCR'|config}
			<div class="subCatDescr">
				{$sub.description_lang}
			</div>
		{/if}
	</div>
</div>
</div>
