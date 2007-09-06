{math count=$subCategories|@count equation="max(2, ceil(count / 2))" assign="perColumn"}

<fieldset class="container">
{foreach from=$subCategories item="sub" name="subcats"}   
{if $smarty.foreach.subcats.index % $perColumn == 0}
<table class="subCategories" style="float: left;">
{/if}
    <tr>
        <td class="subCatImage">
            {if $sub.DefaultImage.paths.1}
                <a href="{categoryUrl data=$sub}">
                    {img src=$sub.DefaultImage.paths.1 alt=$sub.name_lang|escape}
                </a>
            {/if}
        </td>
        <td class="details" style="height: 140px; ">
            <div class="subCatName">
                <a href="{categoryUrl data=$sub}">{$sub.name_lang}</a> 
                <span class="count">({$sub.count})</span>
            </div>
            
            {if $sub.subCategories}
            <ul class="subSubCats">
                {foreach from=$sub.subCategories item="subSub" max="3" name="subSub"}
                    {if $smarty.foreach.subSub.iteration > 3}
                    	<li style="font-size: smaller;">
                    		<a href="{categoryUrl data=$sub}">more...</a>
                    	</li>
                    	{php}break;{/php}
                    {/if}
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