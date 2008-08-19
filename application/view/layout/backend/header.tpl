{include file="layout/backend/meta.tpl"}

<div id="topShadeContainer">
	<div id="topShadeContainerLeft"></div>
	<div id="topShadeContainerRight">
		<div></div>
	</div>
</div>

<div id="pageContainer">

	<div id="pageHeader">

		<div id="topAuthInfo">
			{block USER_MENU}
		</div>

		<div id="topBackground" >
			<div id="topBackgroundLeft" >

				<div id="topHomeContainer">
					<div id="homeButtonWrapper">
						<a href="{link controller=backend.index action=index}">
							{img src="image/backend/layout/top_home_button.jpg" id="homeButton"}
						</a>
					</div>

					<div id="navContainer">
						<div id="nav"></div>
						{backendMenu}
					</div>
				</div>

				<div id="topLogoContainer">

					<div id="systemMenu">
							<a id="help" href="#" target="_blank" {if !'BACKEND_SHOW_HELP'|config}style="display:none;"{/if}>{t _base_help}</a>
							{backendLangMenu}
					</div>

					<div id="topLogoImageContainer">
					 	<a href="{link controller=backend.index action=index}">
						 	{img src='BACKEND_LOGO'|config|@or:"image/backend/layout/logo_tr.png" id="topLogoImage"}
						</a>
					</div>

				</div>

			</div>

		</div>

		<div id="pageTitleContainer">
			<div id="pageTitle">{$PAGE_TITLE}</div>
			<div id="breadcrumb_template" class="dom_template">
				<span class="breadcrumb_item"><a href=""></a></span>
				<span class="breadcrumb_separator"> &gt; </span>
				<span class="breadcrumb_lastItem"></span>
			</div>
			<div id="breadcrumb"></div>
		</div>

	</div>

	<div id="pageContentContainer">

		<div id="pageContentInnerContainer" class="maxHeight h--20"  >
