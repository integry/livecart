<table class="subCategories">
{foreach from=$subCategories item="sub" name="subcats"}   
	<tr>
		<td class="subCatImage">
			<a href="{categoryUrl data=$sub}">
				{img src=$sub.DefaultImage.paths.1  alt=$sub.name_lang|escape}			
			</a>
		</td>
		<td class="details">
			<div class="subCatName">
				<a href="{categoryUrl data=$sub}">{$sub.name_lang}</a> 
				<span class="count">({$sub.count})</span>
			</div>
			
			{if $sub.subCategories}
			<ul class="subSubCats">
				{foreach from=$sub.subCategories item="subSub"}
					<li>
						<a href="{categoryUrl data=$subSub}">{$subSub.name_lang}</a>
						<span class="count">({$subSub.count})</span>
					</li>
				{/foreach}
			</ul>
			{/if}
			
			<div class="subCatDescr">
				{* $sub.description_lang *}
			</div>			
		</td>		
	</tr>
	{if !$smarty.foreach.subcats.last}
		<tr class="separator">
			<td colspan="2"><div></div></td>
		</tr>
	{/if}
{/foreach}	
</table>
<div class="clear"></div>