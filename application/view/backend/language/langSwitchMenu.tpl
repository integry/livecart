<div id="langMenu">
	<div>
		{foreach from=$languages item=item}
		<div class="" onMouseOver="this.className = 'langMenuHover';" onMouseOut="Element.removeClassName(this, 'langMenuHover');" onClick="window.location.href = '{link controller=backend.language action=changeLanguage id=$item.ID query="returnRoute=$returnRoute"}'"{if $item.ID == $currentLanguage} id="langMenuCurrent"{/if}>

			{if file_exists($item.image)}
				{img src=$item.image}
			{/if}

			<span{if 0 == $item.isEnabled} class="disabled"{/if}>{$item.originalName}</span> 
		</div>
		{/foreach}
	</div>
	
	<a href="#" class="cancel" onClick="showLangMenu(false); return false;">{t _cancel}</a>
</div>