{pageTitle}{$category.name_lang}{/pageTitle}

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}{if $searchQuery} &gt;&gt; "<span class="keywords">{$searchQuery}</span>"{/if}</h1>


	{if $categoryNarrow}
	Narrow by category
    <table class="subCategories">
	{foreach from=$categoryNarrow item="sub"}   
        <tr>
            <td class="subCatImage">
                <a href="{categoryUrl data=$sub}">
                    <img src="{$sub.DefaultImage.paths.1}" />            
                </a>
            </td>
            <td class="details">
                <div class="subCatName">
                    <a href="{categoryUrl data=$sub}">{$sub.name_lang}</a> 
                    <span class="count">({$sub.searchCount})</span>
                </div>
                <div class="subCatDescr">
                    {$sub.description_lang}
                </div>            
            </td>        
        </tr>
        <tr class="separator">
            <td colspan="2"></td>
        </tr>
	{/foreach}    
    </table>	
	{/if}

    {if !$searchQuery}
	<table class="subCategories">
	{foreach from=$subCategories item="sub"}   
        <tr>
            <td class="subCatImage">
                <a href="{categoryUrl data=$sub}">
                    <img src="{$sub.DefaultImage.paths.1}" />            
                </a>
            </td>
            <td class="details">
                <div class="subCatName">
                    <a href="{categoryUrl data=$sub}">{$sub.name_lang}</a> 
                    <span class="count">({$sub.availableProductCount})</span>
                </div>
                <div class="subCatDescr">
                    {$sub.description_lang}
                </div>            
            </td>        
        </tr>
        <tr class="separator">
            <td colspan="2"></td>
        </tr>
	{/foreach}    
    </table>
    {/if}

    {if $searchQuery && !$products}
        <p>
            {t No products were found. Please try different keywords for your search query.}
        </p>    
    {/if}

    {if $products}	
        <div class="resultStats">
        	<div style="float: left; margin-top: 7px;">
                Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
            </div>
            <div style="float: right;">
                Sort by
                {form handle=$sortForm action="self" method="GET"}
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