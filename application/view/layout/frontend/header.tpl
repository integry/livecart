<div class="row">
<div class="col col-lg-12" id="header">

	<div class="col col-lg-6" id="logoContainer">
		<a href="[[ url("/") ]]">{img src='LOGO'|config alt="LiveCart Logo"}</a>
	</div>

	<div class="col col-lg-6" id="topMenuContainer">
		<div class="clearfix">
			{block CURRENCY}
			{block LANGUAGE}
		</div>

		{block CART}
	</div>

	{block HEADER}

</div>
</div>

<div class="col col-lg-12">
	{block ROOT_CATEGORIES}
</div>
