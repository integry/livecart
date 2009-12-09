<div class="rootCategoriesWrapper1">
	<div class="rootCategoriesWrapper2">
		<ul class="rootCategories{if $currentId == $categories.0.ID} firstActive{/if}" id="rootCategories">
			{foreach $categories as $category}
				<li class="top {if $category.ID == $currentId}current{/if}{if !$subCategories[$category.ID]}noSubs{/if}"><a href="{categoryUrl data=$category}"><span class="name">{$category.name_lang}</span>
				{if $subCategories[$category.ID]}
					<div class="wrapper">
						<div class="block"><div class="block">
							<ul>
								{foreach $subCategories[$category.ID] as $category}
									<li><a href="{categoryUrl data=$category}"><span>{$category.name_lang}</span></a></li>
								{/foreach}
							</ul>
						</div></div>
					</div>
				{/if}
				</a></li>
				<li style="width: 1px;">&nbsp;</li>
			{/foreach}
			<div class="clear"></div>
		</ul>
	</div>
</div>

{literal}
<!--[if lte IE 6]>
<script type="text/javascript">
	$A($('rootCategories').getElementsBySelector('li.top')).each(function(li)
	{
		Event.observe(li, 'mouseover', function()
		{
			li.addClassName('hover');
			var wrapper = li.down('div.wrapper');
			if (wrapper)
			{
				wrapper.style.width = 120;
			}
		});
		Event.observe(li, 'mouseout', function() { li.removeClassName('hover'); });
	});
</script>
<![endif]-->
{/literal}