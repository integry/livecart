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
	{/if}

    {if !$searchQuery && $subCategories}
    	{include file="category/subcategoriesColumns.tpl"}
    {/if}

    {if $searchQuery && !$products}
        <p class="notFound">
            {t <span class='notFoundMain'>No products were found.</span> Please try different keywords for your search query.}
        </p>    
    {/if}

	{if $subCatFeatured}
		<h2>{t Featured Products}</h2>
        {include file="category/productList.tpl" products=$subCatFeatured}	
	{/if}

    {if $products}	
        <fieldset class="container">
        <div class="resultStats">
        	<div class="pagingInfo">
                Showing {$offsetStart} to {$offsetEnd} of {$count} found products.
            </div>
            
            <div style="float: right;">
                {if $sortOptions}
                    Sort by
                    {form handle=$sortForm action="self" method="get"}
                    {selectfield id="productSort" name="sort" options=$sortOptions onchange="this.form.submit();"}
                    {/form}
                {/if}
                &nbsp;
            </div>  
            <div style="clear: both;"></div>
        </div>
        </fieldset>
        
        {include file="category/productList.tpl" products=$products}
    
        {if $count > $perPage}
        	<div class="resultPages">
        		Pages: {paginate current=$currentPage count=$count perPage=$perPage url=$url}
        	</div>
        {/if}
    {/if}
		
</div>		
{include file="layout/frontend/footer.tpl"}

</div>