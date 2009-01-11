{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="backend/Report.js"}

{includeCss file="backend/Report.css"}

{pageTitle help="content.pages"}{t _reports}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="staticPageContainer">
	<div class="treeContainer">
		<ul class="verticalMenu">
			<li id="menuSales">
				<a href="#">{t _sales}</a>
			</li>
			<li id="menuBest">
				<a href="#">{t _bestsellers}</a>
			</li>
			<li id="menuCarts">
				<a href="#">{t _carts}</a>
			</li>
			<li id="menuSearch">
				<a href="#">{t _search}</a>
			</li>
			<li id="menuCustomers">
				<a href="#">{t _customers}</a>
			</li>
			<li id="menuPages">
				<a href="#">{t _pages}</a>
			</li>
		</ul>

	</div>

	<div class="treeManagerContainer maxHeight h--100">
		<span id="settingsIndicator" class="progressIndicator" style="display: none;"></span>

		<div id="pageContent" class="maxHeight">
			{include file="backend/staticPage/emptyPage.tpl"}
		</div>
	</div>
</div>

{include file="layout/backend/footer.tpl"}