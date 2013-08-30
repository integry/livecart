{includeJs file="library/ActiveList.js"}
{includeCss file="library/ActiveList.css"}

{includeJs file="backend/SiteNews.js"}
{includeCss file="backend/SiteNews.css"}

{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}
{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{*includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"*}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}

{pageTitle help="content.site"}{t _site_news}{/pageTitle}

[[ partial("layout/backend/header.tpl") ]]

{allowed role="news.create"}

	<ul class="menu" id="newsMenu">
		<li class="addNews"><a href="#add" id="addNewsLink">{t _add_news}</a></li>
		<li class="addNewsCancel done" style="display: none;"><a href="#cancel" id="addNewsCancelLink">{t _cancel_adding_news}</a></li>
	</ul>

{/allowed}

<fieldset id="addNews" class="slideForm addForm" style="display: none;">

	<legend>{t _add_news|capitalize}</legend>

	{form action="controller=backend.siteNews action=add" method="POST" onsubmit="new Backend.SiteNews.Add(this); return false;" handle=$form id="newsForm" class="enabled"}
		<input type="hidden" name="id" />

		{input name="time"}
			{label}{t _date}:{/label}
			{calendar id="time"}
		{/input}

		[[ textfld('title', '_title') ]]

		[[ textarea('text', '_text', class: 'tinyMCE') ]]

		{input name="text"}
			{label}{t _text}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{input name="moreText"}
			{label}{t _more_text}:{/label}
			{textarea class="tinyMCE"}
		{/input}

		{language}
			{input name="title_`$lang.ID`"}
				{label}{t _title}:{/label}
				{textfield}
			{/input}

			{input name="text_`$lang.ID`"}
				{label}{t _text}:{/label}
				{textarea class="tinyMCE"}
			{/input}

			{input name="moreText_`$lang.ID`"}
				{label}{t _more_text}:{/label}
				{textarea class="tinyMCE"}
			{/input}
		{/language}

		<fieldset class="controls" {denied role="news"}style="display: none;"{/denied}>
			<span class="progressIndicator" style="display: none;"></span>
			<input type="submit" class="submit save" value="{tn _save}" />
			<input type="submit" class="submit add" value="{tn _add}" />
			{t _or} <a class="cancel" href="#" onclick="Backend.SiteNews.prototype.hideAddForm(); return false;">{t _cancel}</a>
		</fieldset>
	{/form}

</fieldset>

<ul ng-controller="SiteNewsController" id="newsList" class="activeList {allowed role="news.sort"}activeList_add_sort{/allowed} {allowed role="news.delete"}activeList_add_delete{/allowed} {allowed role="news.update"}activeList_add_edit{/allowed}" active-list>
	<li ng-repeat="friend in friends">
		<div>
			<div class="newsListContainer">

				<span class="newsCheckBox"{denied role="news.status"} style="display: none;"{/denied}>
					<input type="checkbox" class="checkbox" name="isEnabled" onclick="this.up('li').handler.setEnabled(this);" />
					<span class="progressIndicator" style="float: left; padding: 0; display: none;"></span>
				</span>

				<span class="progressIndicator" style="display: none; "></span>

				<span class="newsData">
					<span class="newsTitle"></span>
					<span class="newsDate"></span>
					<br class="clear" />
					<span class="newsText"></span>
				</span>

			</div>

			<div class="formContainer activeList_editContainer" style="display: none;"></div>

		</div>
		<div class="clear"></div>
	</li>
</ul>

<div style="display: none">
	<span id="deleteUrl">{link controller="backend.siteNews" action=delete}?id=</span>
	<span id="confirmDelete">{t _del_conf}</span>
	<span id="sortUrl">{link controller="backend.siteNews" action=saveOrder}</span>
	<span id="statusUrl">{link controller="backend.siteNews" action=setEnabled}</span>
	<span id="saveUrl">{link controller="backend.siteNews" action=save}</span>
</div>


 <!-- Load the app module and its classes. -->
<script type="text/javascript">


// Define our AngularJS application module.
var demo = angular.module( "Demo", [] );

// -------------------------------------------------- //
// -------------------------------------------------- //


// I am the main controller for the application.
demo.controller(
"SiteNewsController",
function( $scope ) {


// -- Define Scope Methods. ----------------- //


// I remove the given friend from the list of
// selected friends.
$scope.deselectFriend = function( friend ) {

// NOTE: indexOf() works in IE 9+.
var index = $scope.selectedFriends.indexOf( friend );

if ( index >= 0 ) {

$scope.selectedFriends.splice( index, 1 );

}

};


// I add the given friend to the list of selected
// friends.
$scope.selectFriend = function( friend ) {

$scope.selectedFriends.push( friend );

};


// -- Define Scope Variables. --------------- //


// I am the list of friends to show.
$scope.friends = [
{
id: 1,
name: "Tricia",
nickname: "Sugar Pie"
},
{
id: 2,
name: "Joanna",
nickname: "Honey Dumpling"
},
{
id: 3,
name: "Kit",
nickname: "Sparky"
}
];


// I am the list of friend that have been selected
// by the current user.
$scope.selectedFriends = [];


}
);


// -------------------------------------------------- //
// -------------------------------------------------- //


// I am the controller for the list item in the ngRepeat.
// Each instance of the LI in the list will bet its own
// instance of the ItemController.
demo.controller(
"ItemController",
function( $scope ) {


// -- Define Scope Methods. ----------------- //


// I deactivate the list item, if possible.
$scope.deactivate = function() {

// If the list item is currently selected, then
// ignore any request to deactivate.
if ( $scope.isSelected ) {

return;

}

$scope.isShowingNickname = false;

};


// I activate the list item.
$scope.activate = function() {

$scope.isShowingNickname = true;

};


// I toggle the selected-states of the current item.
// Remember, since ngRepeat creates a new $scope for
// each list item, we have a reference to our
// contextual "friend" instance.
$scope.toggleSelection = function() {

$scope.isSelected = ! $scope.isSelected;

// If the item has been selected, then we have to
// tell the parent controller to selected the
// relevant friend.
if ( $scope.isSelected ) {

$scope.selectFriend( $scope.friend );

// If the item has been unselected, then we have
// to tell the parent controller to DEselected the
// relevant friend.
} else {

$scope.deselectFriend( $scope.friend );

}

};


// -- Define Scope Variables. --------------- //


// I determine if the nichkame is showing.
$scope.isShowingNickname = false;

// I determine if the list item has been selected.
$scope.isSelected = false;


}
);

</script>

[[ partial("layout/backend/footer.tpl") ]]