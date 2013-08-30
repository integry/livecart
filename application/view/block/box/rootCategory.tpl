<div id="topMenu" class="navbar">
	<ul class="nav navbar-nav">
		<li class="active"><a href="#">Home</a></li>
		{foreach from=$categories item=category name=categories}
			<li class="top dropdown"><a href="{categoryUrl data=$category}" class="dropdown-toggle" data-toggle="dropdown disabled">[[category.name_lang]]</a>
			{% if $subCategories[$category.ID] %}
				<ul class="dropdown-menu">
					{foreach from=$subCategories[$category.ID] item=sub}
						<li><a href="{categoryUrl data=$sub}">[[sub.name_lang]]</a></li>
					{/foreach}
				</ul>
			{% endif %}
			</li>
		{/foreach}

		{foreach from=$pages item=page name=pages}
			<li class="top dropdown"><a href="{pageUrl data=$page}" {% if $subPages[$page.ID] %}class="dropdown-toggle" data-toggle="dropdown disabled"{% endif %}>[[page.title_lang]]</a>
			{% if $subPages[$page.ID] %}
				<ul class="dropdown-menu">
					{foreach from=$subPages[$page.ID] item=subpage}
						<li><a href="{pageUrl data=$subpage}">[[subpage.title_lang]]</a></li>
					{/foreach}
				</ul>
			{% endif %}
			</li>
		{/foreach}
	</ul>

	{block SEARCH}
</div>