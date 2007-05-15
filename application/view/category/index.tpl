{pageTitle}{$category.name_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}{if $searchQuery} &gt;&gt; "<span class="keywords">{$searchQuery}</span>"{/if}</h1>

	{if $allFilters.filters}
    	{include file="category/allFilters.tpl"}
	{/if}

	{if $categoryNarrow}
	<div class="resultStats">
        Narrow results by category
    </div>
    <table class="subCategories">
	{foreach from=$categoryNarrow item="sub" name="subcats"}   
        <tr>
            <td class="subCatImage">
                <a href="{categoryUrl data=$sub query="q=`$searchQuery`"}">
                    <img src="{$sub.DefaultImage.paths.1}" alt="{$sub.name_lang|escape}" />            
                </a>
            </td>
            <td class="details">
                <div class="subCatName">
                    <a href="{categoryUrl data=$sub query="q=`$searchQuery`"}">{$sub.name_lang}</a> 
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
	{/if}

    {if !$searchQuery && $subCategories}
	<table class="subCategories">
	{foreach from=$subCategories item="sub" name="subcats"}   
        <tr>
            <td class="subCatImage">
                <a href="{categoryUrl data=$sub}">
                    <img src="{$sub.DefaultImage.paths.1}"  alt="{$sub.name_lang|escape}" />            
                </a>
            </td>
            <td class="details">
                <div class="subCatName">
                    <a href="{categoryUrl data=$sub}">{$sub.name_lang}</a> 
                    <span class="count">({$sub.availableProductCount})</span>
                </div>
                
                {if $sub.subCategories}
                <ul class="subSubCats">
                    {foreach from=$sub.subCategories item="subSub"}
                        <li>
                            <a href="{categoryUrl data=$subSub}">{$subSub.name_lang}</a>
                            <span class="count">({$subSub.availableProductCount})</span>
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
    {/if}

    {if $searchQuery && !$products}
        <p>
            {t No products were found. Please try different keywords for your search query.}
        </p>    
    {/if}

	{if $subCatFeatured}
		<h2>{t Featured Products}</h2>
        {include file="category/productList.tpl" products=$subCatFeatured}	
	{/if}

    {if $products}	
        <div class="resultStats">
        	<div style="float: left; margin-top: 7px;">
                Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
            </div>
            <div style="float: right;">
                Sort by
                {form handle=$sortForm action="self" method="get"}
                {selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
                {/form}
            </div>  
            <div style="clear: right;"></div>
        </div>
        
        {include file="category/productList.tpl" products=$products}
    
        {if $count > $perPage}
        	<div class="resultPages">
        		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
        	</div>
        {/if}
    {/if}
		
</div>		
{include file="layout/frontend/footer.tpl"}