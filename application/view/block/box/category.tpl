<div class="box">
	<div class="title">
		<div>Categories</div>
	</div>
	<div class="content">
		<ul>
		{foreach from=$categories item=category}
			<li> {$category.name}</li>	
		{/foreach}
		</ul>
	</div>
</div>