
<div id="footpanel">
	<ul id="mainpanel">
		{block BACKEND-TOOLBAR-BEFORE-ALL}

		<li><a href="[[ url("index") ]]" class="storeFrontend" target="_blank">{t _store_frontend}{*<small>{t _store_frontend}</small>*}</a></li>
		<li><a href="[[ url("backend.index") ]]" class="storeBackend">{t _admin_dashboard}{*<small>{t _admin_dashboard}</small>*}</a></li>

		{block BACKEND-TOOLBAR-BEFORE-BUTTONS}

		{foreach from=$dropButtons item=item}
			<li class="uninitializedDropButton" style="" id="button[[item.menuID]]">
				<a href="">
					<small></small>
				</a>
			</li>
		{foreachelse}
			<li id="noToolbarButtons">{t _tip_toolbar_drag}</li>
		{/foreach}

		{block BACKEND-TOOLBAR-AFTER-BUTTONS}

		<li id="toolbarQS">
			[[ partial('backend/quickSearch/form.tpl', ['formid': "TBQuickSearch", 'classNames': "-SearchableTemplate"]) ]]
		</li>

		{block BACKEND-TOOLBAR-BEFORE-LASTVIEWED}

		<li id="lastviewed" class="lastviewed invalid"><a href="#" class="lastviewed">{t _last_viewed}</a>
			<div class="subpanel">
				<h3><span> &ndash; </span>{t _last_viewed}</h3>
				<div id="lastViewedIndicator" class="progressIndicator" style="display:none;"></div>
				<ul>
				</ul>
			</div>
		</li>

		{block BACKEND-TOOLBAR-AFTER-LASTVIEWED}

	</ul>

	<li style="display:none;" class="uninitializedDropButton" id="dropButtonTemplate">
		<a href="">
			<small></small>
		</a>
	</li>
</div>


<script type="text/javascript">
// global variable footerToolbar
	footerToolbar = new BackendToolbar("footpanel",
		{
			addIcon: "[[ url("backend.backendToolbar/addIcon") ]]?id=_id_&position=_position_",
			removeIcon: "[[ url("backend.backendToolbar/removeIcon") ]]?id=_id_&position=_position_",
			sortIcons: "[[ url("backend.backendToolbar/sortIcons") ]]?order=_order_",
			lastViewed: "[[ url("backend.backendToolbar/lastViewed", "'where=__where__'") ]]"
		}
	);

</script>

