{literal}
<script type="text/javascript"><!--//--><![CDATA[//><!--

sfHover = function() {
	var sfEls = document.getElementById("dynamicNav").getElementsByTagName("LI");
	for (var i=0; i<sfEls.length; i++) {
		sfEls[i].onmouseover=function() {
			this.className+=" sfhover";
		}
		sfEls[i].onmouseout=function() {
			this.className=this.className.replace(new RegExp(" sfhover\\b"), "");
		}
	}
}
if (window.attachEvent) window.attachEvent("onload", sfHover);

//--><!]]></script>
{/literal}

{defun name="dynamicCategoryTree" node=false filters=false}
	{if $node}
		<ul id="dynamicNav">
		{foreach from=$node item=category}
			{if $category.ID == $currentId}
				<li class="current{if $category.ID == $topCategoryId} topCategory{/if}">
					<a class="currentName" href="{categoryUrl data=$category filters=$category.filters}">{$category.name_lang}</a>
			{else}
				<li{if $category.ID == $topCategoryId} class="topCategory"{/if}>
					<a href="{categoryUrl data=$category filters=$category.filters}">{$category.name_lang}</a>
			{/if}
					{if 'DISPLAY_NUM_CAT'|config}
						<span class="count">({$category.count})</span>
					{/if}
					{if $category.subCategories}
		   				{fun name="dynamicCategoryTree" node=$category.subCategories}
					{/if}
				</li>
		{/foreach}
		</ul>
	{/if}
{/defun}

<div class="box categories dynamicMenu">
	<div class="title">
		<div>{t _categories}</div>
	</div>

	<div class="content">
		{fun name="dynamicCategoryTree" node=$categories}
	</div>

	<div class="clear"></div>
</div>