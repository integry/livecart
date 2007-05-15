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
    	{include file="category/narrowByCategory.tpl"}
	{/if}

    {if !$searchQuery && $subCategories}
    	{include file="category/subcategoriesColumns.tpl"}
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