<div class="row">
<div class="col-span-12" id="header">

	<div class="col-span-6" id="logoContainer">
		<a href="{link}">{img src='LOGO'|config alt="LiveCart Logo"}</a>
	</div>

	<div class="col-span-6" id="topMenuContainer">
		<div class="clearfix">
			{block CURRENCY}
			{block LANGUAGE}
		</div>

		{block CART}
	</div>

	{block HEADER}

</div>
</div>

<div class="col-span-12">
	{block ROOT_CATEGORIES}
</div>
