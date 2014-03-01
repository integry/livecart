{capture assign="searchUrl"}{categoryUrl data=$category}{/capture}
{form action="controller=category" class="form-search navbar-form pull-right" handle=$form}
    <div class="input-group">
		{textfield type="text" class="col-sm-2 search-query" name="q" noFormat=true}
		<span class="input-group-btn">
			<button type="submit" class="btn btn-default">{tn _search}</button>
		</span>
    </div>

	{% if 'HIDE_SEARCH_CATS'|config %}
		{hidden name="id" value="1"}
	{% else %}
		{* selectfield name="id" options=$categories *}
	{% endif %}

	<input type="hidden" name="cathandle" value="search" />
{/form}
