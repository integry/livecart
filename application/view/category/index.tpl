{pageTitle}{$category.name_lang}{/pageTitle}
{assign var="metaDescription" value=$category.description_lang}
{assign var="metaKeywords" value=$category.keywords_lang}

<div class="categoryIndex category_{$category.ID}">

{include file="layout/frontend/header.tpl"}
{include file="layout/frontend/leftSide.tpl"}
{include file="layout/frontend/rightSide.tpl"}

<div id="content">
	<h1>{$category.name_lang}{if $searchQuery} &gt;&gt; "<span class="keywords">{$searchQuery}</span>"{/if}</h1>

	{if $allFilters.filters}
    	{include file="category/allFilters.tpl"}
	{/if}

	{if $foundCategories}
    	{include file="category/foundCategories.tpl"}		
	{/if}

	{if $categoryNarrow}
    	{include file="category/narrowByCategory.tpl"}
    {elseif !$searchQuery && $subCategories}
    	{include file="category/subcategoriesColumns.tpl"}
    {/if}

    {if $searchQuery && !$products}
        <p class="notFound">
            {t _not_found}
        </p>    
    {/if}

	{if $subCatFeatured}
		<h2>{t _featured_products}</h2>
        {include file="category/productList.tpl" products=$subCatFeatured}	
	{/if}

    {if $products}	
        <fieldset class="container">
        <div class="resultStats">
        	<div class="pagingInfo">
                {maketext text=_showing_products params=$offsetStart,$offsetEnd,$count}
            </div>
            
            <div class="sortOptions">
                {if $sortOptions && ($sortOptions|@count > 1)}
                    {t _sort_by}
                    {form handle=$sortForm action="self" method="get"}
                    {selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
                    {/form}
                {/if}
                &nbsp;
            </div>  
            <div class="clear"></div>
        </div>
        </fieldset>
        
        {include file="category/productList.tpl" products=$products}
    
        {if $count > $perPage}
        	<div class="resultPages">
        		{t _pages}: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
        	</div>
        {/if}
    {/if}
		
</div>		
{include file="layout/frontend/footer.tpl"}

</div>