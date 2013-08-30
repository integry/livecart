<table class="subCategories">
{foreach from=$subCategories item="sub" name="subcats"}
	<tr>
		<td class="subCatImage">
			<a href="{categoryUrl data=$sub}">
				{img src=$sub.DefaultImage.urls.1  alt=$sub.name_lang|escape}
			</a>
		</td>
		<td class="details">
			<div class="subCatName">
				<a href="{categoryUrl data=$sub}">[[sub.name_lang]]</a>
				[[ partial('block/count.tpl', ['count': $sub.count]) ]]
			</div>

			{% if $sub.subCategories %}
			<ul class="subSubCats">
				{foreach from=$sub.subCategories item="subSub"}
					<li>
						<a href="{categoryUrl data=$subSub}">[[subSub.name_lang]]</a>
						[[ partial('block/count.tpl', ['count': $subSub.count]) ]]
					</li>
				{/foreach}
			</ul>
			{% endif %}

			<div class="subCatDescr">
				{* $sub.description_lang *}
			</div>
		</td>
	</tr>
	{% if !$smarty.foreach.subcats.last %}
		<tr class="separator">
			<td colspan="2"><div></div></td>
		</tr>
	{% endif %}
{/foreach}
</table>
<div class="clear"></div>