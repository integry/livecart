<div class="rootCategoriesWrapper1">
	<div class="rootCategoriesWrapper2">
		<div class="ul rootCategories{if $currentId == $categories.0.ID} firstActive{/if}" id="rootCategories">
			{foreach $categories as $category}
				<div class="li top {if $category.ID == $currentId}current{/if}{if !$subCategories[$category.ID]}noSubs{/if}"><a href="{categoryUrl data=$category}"><span class="name">{$category.name_lang}</span></a>
				{if $subCategories[$category.ID]}
					<div class="wrapper">
						<div class="block"><div class="block">
							<div class="ul">
								{foreach $subCategories[$category.ID] as $category}
									<div class="li"><a href="{categoryUrl data=$category}"><span>{$category.name_lang}</span></a></div>
								{/foreach}
							</div>
						</div></div>
					</div>
				{/if}
				</div>
			{/foreach}
			{foreach $pages as $page}
				<div class="li top {if $page.ID == $currentId}current{/if}{if !$subPages[$page.ID]}noSubs{/if}"><a href="{pageUrl data=$page}"><span class="name">{$page.title_lang}</span></a>
				{if $subPages[$page.ID]}
					<div class="wrapper">
						<div class="block"><div class="block">
							<div class="ul">
								{foreach $subPages[$page.ID] as $page}
									<div class="li"><a href="{pageUrl data=$page}"><span>{$page.title_lang}</span></a></div>
								{/foreach}
							</div>
						</div></div>
					</div>
				{/if}
				</div>
			{/foreach}
			<div class="li" style="width: 1px; background: none;">&nbsp;</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
</div>

{literal}
<!--[if lte IE 6]>
<script type="text/javascript">
	$A($('rootCategories').getElementsBySelector('.top')).each(function(li)
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
