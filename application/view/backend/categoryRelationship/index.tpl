{function name="catPath" category=null}
	{% if !empty(category) %}
		{% if $category.ParentNode %}
			{catPath category=$category.ParentNode} &gt;
		{% endif %}
		[[category.name()]]
	{% endif %}
{/function}

<ul class="menu">
	<li class="addAdditionalCategory"><a href="#">{t _add_category}</a></li>
</ul>
<ul class="additionalCategories activeList_add_sort">
</ul>
<li class="categoryTemplate" style="display: none;">
	<span class="recordDeleteMenu">
		<img src="image/silk/cancel.png" class="recordDelete" />
		<span class="progressIndicator" style="display: none;"></span>
	</span>
	<span class="categoryName"></span>
</li>

<script type="text/javascript">
	new Backend.CategoryRelationship($('tabRelatedCategoryContent_[[category.ID]]'), {json array=$category}, {json array=$categories});
</script>