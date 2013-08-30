{* frontend toolbar *}

<div id="footpanel">
	<ul id="mainpanel">
		{block FRONTEND-TOOLBAR-LEFT}
		{block FRONTEND-TOOLBAR-CENTER}
		{block FRONTEND-TOOLBAR-RIGHT}
	</ul>
</div>


<script type="text/javascript">
	// global variable footerToolbar
	footerToolbar = new FrontendToolbar("footpanel");
</script>

