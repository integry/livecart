<div class="narrowByCat">
    <div class="resultStats">
        {t _narrow_by_cat}
    </div>

    <table class="subCategories">
	{foreach from=$categoryNarrow item="sub" name="subcats"}   
        <tr>
            <td class="subCatImage">
                <a href="{categoryUrl data=$sub filters=$appliedFilters}">
                    {img src=$sub.DefaultImage.paths.1 alt=$sub.name_lang|escape }            
                </a>
            </td>
            <td class="details">
                <div class="subCatName">
                    <a href="{categoryUrl data=$sub filters=$appliedFilters}">{$sub.name_lang}</a> 
                    <span class="count">({$sub.searchCount})</span>
                </div>
                <div class="subCatDescr">
                    {* $sub.description_lang *}
                </div>            
            </td>        
        </tr>
        <tr class="separator">
            <td colspan="2">
    		{if !$smarty.foreach.subcats.last}
    		<div></div>
        	{/if}
    		</td>
        </tr>
    {/foreach}    
    </table>
</div>