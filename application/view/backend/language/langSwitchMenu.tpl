<div id="langMenu">
	<div>
		{foreach from=$languages item=item}
		<div class="" onMouseOver="this.className = 'langMenuHover';" onMouseOut="Element.removeClassName(this, 'langMenuHover');" onClick="window.location.href = '{link controller=backend.language action=changeLanguage id=$item.ID query="returnRoute=$returnRoute"}'"{if $item.ID == $currentLanguage} id="langMenuCurrent"{/if}>
			<img src="image/localeflag/{$item.ID}.png" />
			<span>{$item.name}</span> 
		</div>
		{/foreach}
	</div>
	
	<a href="#" class="cancel" onClick="showLangMenu(false); return false;">{t _cancel}</a>
</div>