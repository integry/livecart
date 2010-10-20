
<div id="footpanel">
	<ul id="mainpanel">
		<li><a href="{link controller=index}" class="storeFrontend" target="_blank">{t _store_frontend}{*<small>{t _store_frontend}</small>*}</a></li>
		<li><a href="{link controller=backend.index}" class="storeBackend">{t _admin_dashboard}{*<small>{t _admin_dashboard}</small>*}</a></li>

		{foreach from=$dropButtons item=item}
			<li class="uninitializedDropButton" style="" id="button{$item.menuID}">
				<a href="">
					<small></small>
				</a>
			</li>
		{/foreach}

		<li id="toolbarQS">
			{include file="backend/quickSearch/form.tpl" formid="TBQuickSearch"}
		</li>

		<li id="lastviewed" class="lastviewed invalid"><a href="#" class="lastviewed">{t _last_viewed}</a>
			<div class="subpanel">
				<h3><span> &ndash; </span>{t _last_viewed}</h3>
				<div id="lastViewedIndicator" class="progressIndicator" style="display:none;"></div>
				<ul>
				</ul>
			</div>
		</li>
	</ul>

	<li style="display:none;" class="uninitializedDropButton" id="dropButtonTemplate">
		<a href="">
			<small></small>
		</a>
	</li>
</div>


{literal}
<script type="text/javascript">
// global variable footerToolbar
	footerToolbar = new BackendToolbar("footpanel",
		{
			addIcon :  "{/literal}{link controller=backend.backendToolbar action=addIcon}?id=_id_&position=_position_{literal}",
			removeIcon: "{/literal}{link controller=backend.backendToolbar action=removeIcon}?id=_id_&position=_position_{literal}",
			sortIcons: "{/literal}{link controller=backend.backendToolbar action=sortIcons}?order=_order_{literal}",
			lastViewed: "{/literal}{link controller=backend.backendToolbar action=lastViewed}{literal}"
		}
	);
</script>
{/literal}

