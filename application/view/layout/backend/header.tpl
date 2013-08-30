[[ partial("layout/backend/meta.tpl") ]]

<div id="topAuthInfo">
	{block USER_MENU}
</div>

<div id="pageContainer">

	<div id="pageHeader">

		<div id="systemMenu">
				{% if 'BACKEND_SHOW_HELP'|config %}
					<a id="updates-link" {% if 'MODULE_STATS_NEED_UPDATING'|config %}class="updateAvailable"{% endif %} href="[[ url("backend.module") ]]">{t _modules_updates}</a>
					{% if 'MODULE_STATS_NEED_UPDATING'|config %}
						<span id="moduleUpdateAvailable">([[ config('MODULE_STATS_NEED_UPDATING') ]])</span>
					{% endif %}
					|
				{% endif %}
				<a id="help" href="#" target="_blank" {% if !'BACKEND_SHOW_HELP'|config %}style="display:none;"{% endif %}>{t _base_help}</a>
				{% if 'BACKEND_SHOW_HELP'|config %} | {% endif %}
				{backendLangMenu}
				<a href="[[ url("/") ]]" target="_blank">{t _frontend}</a> |
				[[ partial('backend/quickSearch/form.tpl', ['formid': "QuickSearch", 'hint': _hint_quick_search, 'classNames': "-SearchableTemplate"]) ]]
		</div>

		<div id="topLogoImageContainer">
			<a href="[[ url("backend.index/index") ]]">
				{img src='BACKEND_LOGO_SMALL'|config|@or:"image/promo/transparentlogo_small.png" id="topLogoImage"}
			</a>
		</div>

		<div id="navContainer">
			<div id="nav"></div>
			{backendMenu}
			<div class="clear"></div>
		</div>

	</div>

	<div id="pageTitleContainer">
		<div id="pageTitle">[[PAGE_TITLE]]</div>

		<ul id="breadcrumb" ng-controller="BreadCrumbController" ng-visible="items">
			<li ng-repeat="item in items">
				<a href="[[item.url]]">[[item.title]]</a>
			</li>
		</ul>
	</div>

	<div id="pageContentContainer">

		<div id="pageContentInnerContainer" class="maxHeight"  >
