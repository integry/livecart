<div class="box">
	<div class="title">
		<div>Categories</div>
	</div>
	<div class="content">
		<ul>
		{foreach from=$categories item=category}
			<li> <a href="{category data=$category}">{$category.name}</a></li>	
		{/foreach}
		</ul>
	</div>
</div>