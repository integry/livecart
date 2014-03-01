[[ partial("layout/backend/meta.tpl") ]]

<div id="topAuthInfo">
	{block USER_MENU}
</div>

<div id="pageContainer">

	<div id="pageHeader">

		<div id="systemMenu">
				{% if config('BACKEND_SHOW_HELP') %}
					<a id="updates-link" {% if config('MODULE_STATS_NEED_UPDATING') %}class="updateAvailable"{% endif %} href="[[ url("backend.module") ]]">{t _modules_updates}</a>
					{% if config('MODULE_STATS_NEED_UPDATING') %}
						<span id="moduleUpdateAvailable">([[ config('MODULE_STATS_NEED_UPDATING') ]])</span>
					{% endif %}
					|
				{% endif %}
				<a id="help" href="#" target="_blank" {% if !config('BACKEND_SHOW_HELP') %}style="display:none;"{% endif %}>{t _base_help}</a>
				{% if config('BACKEND_SHOW_HELP') %} | {% endif %}
				{backendLangMenu}
				<a href="[[ url("/") ]]" target="_blank">{t _frontend}</a> |
				[[ partial('backend/quickSearch/form.tpl', ['formid': "QuickSearch", 'hint': _hint_quick_search, 'classNames': "-SearchableTemplate"]) ]]
		</div>

		<div id="topLogoImageContainer">
			<a href="[[ url("backend.index/index") ]]">
				{img src=config('BACKEND_LOGO_SMALL')|@or:"image/promo/transparentlogo_small.png" id="topLogoImage"}
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
