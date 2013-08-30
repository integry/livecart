{includeJs file="library/form/Validator.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/State.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="library/ActiveList.js"}
{includeJs file="library/rico/ricobase.js"}
{includeJs file="library/rico/ricoLiveGrid.js"}
{includeJs file="library/ActiveGrid.js"}
{includeJs file="library/editarea/edit_area_full.js"}
{includeJs file="backend/Newsletter.js"}

{includeCss file="library/ActiveGrid.css"}
{includeCss file="library/TabControl.css"}
{includeCss file="backend/Newsletter.css"}

{includeJs file="library/dhtmlCalendar/calendar.js"}
{includeJs file="library/dhtmlCalendar/lang/calendar-en.js"}
{*includeJs file="library/dhtmlCalendar/lang/calendar-`$curLanguageCode`.js"*}
{includeJs file="library/dhtmlCalendar/calendar-setup.js"}
{includeCss file="library/dhtmlCalendar/calendar-win2k-cold-2.css"}

{pageTitle help="tools.newsletter"}{t _newsletters}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="newsletterTabContainer" class="tabContainer maxHeight h--20">

	<div id="loadingNewsletter" style="display: none; position: absolute; text-align: center; width: 100%; padding-top: 200px; z-index: 50000;">
		<span style="padding: 40px; background-color: white; border: 1px solid black;">{t _loading_newsletter}<span class="progressIndicator"></span></span>
	</div>

	<ul class="tabList tabs">
		<li id="tabMessages" class="tab active"><a href="{link controller="backend.newsletter" action=list}">{t _messages}</a></li>
		<li id="tabSubscribers" class="tab inactive"><a href="{link controller="backend.newsletterSubscriber"}">{t _subscribers}</a></li>
	</ul>
	<div class="sectionContainer maxHeight h--95">
		<div id="tabMessagesContent" class="maxHeight tabPageContainer">
			<ul class="menu">
				<li class="addNewsletterMenu">
					<a href="#" onclick="Backend.Newsletter.showAddForm(this); return false;">
						{t _create_message}
					</a>
					<span class="progressIndicator" id="currAddMenuLoadIndicator" style="display: none;"></span>
				</li>
			</ul>

			<div class="clear"></div>

			{activeGrid
				prefix="newsletters"
				id=0
				controller="backend.newsletter" action="lists"
				displayedColumns=$displayedColumns
				availableColumns=$availableColumns
				totalCount=0
				filters=$filters
				container="tabMessages"
				dataFormatter="Backend.Newsletter.GridFormatter"
				count="backend/newsletter/count.tpl"
				massAction="backend/newsletter/massAction.tpl"
			}

		</div>
		<div id="tabSubscribersContent" class="tabPageContainer"></div>
	</div>
</div>

<div id="addMessageContainer" style="display: none;"></div>

<div id="newsletterManagerContainer" class="maxHeight h--90" style="display: none;">

	<fieldset class="container">
		<ul class="menu cancelEditing">
			<li class="done">
				<a href="#cancelEditing" id="cancel_newsletter_edit" class="cancel">{t _done_editing_message}</a>
			</li>
		</ul>
	</fieldset>

	<div class="tabContainer">
		<ul class="tabList tabs">
			<li id="tabMessageInfo" class="tab active">
				<a href="{link controller="backend.newsletter" action=edit id=_id_}">{t _edit_message}</a>
				<span class="tabHelp">products.edit</span>
			</li>

			<li id="tabSubmissionStats" class="tab inactive">
				<a href="{link controller="backend.productPrice" action=index id=_id_}">{t _submission_info}</a>
				<span class="tabHelp">products.edit.pricing</span>
			</li>
		</ul>
	</div>
	<div class="sectionContainer maxHeight h--50"></div>
</div>


<script type="text/javascript">
	Event.observe($("cancel_newsletter_edit"), "click", function(e) {
		e.preventDefault();
		var message = Backend.Newsletter.Editor.prototype.getInstance(Backend.Newsletter.Editor.prototype.getCurrentId(), false);
		message.removeTinyMce();
		message.cancelForm();
		Backend.Newsletter.Editor.prototype.showMainContainer();
	});

	TabControl.prototype.getInstance('newsletterTabContainer', Backend.Newsletter.getTabUrl, Backend.Newsletter.getContentTabId);

	Backend.Newsletter.links =
	{
		add: '{link controller="backend.newsletter" action=add}',
		recipientCount: '{link controller="backend.newsletter" action=recipientCount}',
		plaintext: '{link controller="backend.newsletter" action=plaintext}',
	}



	Backend.Newsletter.GridFormatter.url = '{link controller="backend.newsletter" action=edit}?id=';
	window.activeGrids['newsletters_0'].setDataFormatter(Backend.Newsletter.GridFormatter);

	var massHandler = new ActiveGrid.MassActionHandler(
						$('newslettersMass_0'),
						window.activeGrids['newsletters_0'],

						{
							'onComplete':
								function()
								{
									Backend.Newsletter.resetEditors();
								}
						}

						);
	massHandler.deleteConfirmMessage = '[[ addslashes({t _newsletter_delete_confirm}) ]]' ;
	massHandler.nothingSelectedMessage = '[[ addslashes({t _nothing_selected}) ]]' ;

</script>

[[ partial("layout/backend/footer.tpl") ]]