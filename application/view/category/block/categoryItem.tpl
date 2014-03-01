<div class="row">
{sect}
	{head}
		<div class="subCatImage col-sm-4">
	{cont}
		{% if sub.featuredProduct.ID %}
			<div class="categoryFeaturedProduct">
				<div class="price">
					[[ partial('product/block/productPrice.tpl', ['product': sub.featuredProduct]) ]]
				</div>
				<a href="{productUrl product=sub.featuredProduct}">{sub.featuredProduct.name()|truncate:25}</a>
				{% if sub.featuredProduct.DefaultImage.ID %}
					[[ partial('product/block/smallImage.tpl', ['product': sub.featuredProduct]) ]]
				{% endif %}
			</div>
		{% elseif sub.DefaultImage.urls.1 && config('CAT_MENU_IMAGE') %}
			<a href="{categoryUrl data=sub}">
				{img src=sub.DefaultImage.urls.1 alt=sub.name()|escape}
			</a>
		{% endif %}
	{foot}
		</div>
{/sect}
<div class="details col-sm-8 {% if !sub.subCategories %} noSubCats{% endif %}">
	<div class="subCatContainer">
		<div class="subCatName">
			<a href="{categoryUrl data=sub filters=filters}">[[sub.name()]]</a>
			[[ partial('block/count.tpl', ['count': sub.searchCount|default:sub.count]) ]]
		</div>

		{% if sub.subCategories %}
		<ul class="subSubCats">
			{foreach from=sub.subCategories item="subSub" name="subSub"}
				{% if smarty.foreach.subSub.iteration > config('CAT_MENU_SUBS') %}
					<li class="moreSubCats">
						<a href="{categoryUrl data=sub filters=filters}">{t _more_subcats}</a>
					</li>
					{break}
				{% endif %}
				<li>
					<a href="{categoryUrl data=subSub}">[[subSub.name()]]</a>
					[[ partial('block/count.tpl', ['count': subSub.count]) ]]
				</li>
			{% endfor %}
		</ul>
		{% endif %}

		{% if config('CAT_MENU_DESCR') %}
			<div class="subCatDescr">
				[[sub.description()]]
			</div>
		{% endif %}
	</div>
</div>
</div>
