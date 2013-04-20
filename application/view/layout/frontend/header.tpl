<div class="col-span-12" id="header">

	<div id="logoContainer">
		<a href="{link}">{img src='LOGO'|config alt="LiveCart Logo"}</a>
	</div>

	<div id="topMenuContainer">
		<div class="row">
			{block CURRENCY}
			{block LANGUAGE}
		</div>

		{block CART}
	</div>

	<div class="clear"></div>

	{block HEADER}

</div>

<div class="col-span-12">
	{block ROOT_CATEGORIES}
</div>
