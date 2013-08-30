{includeJs file="library/ActiveList.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="backend/Currency.js"}

{includeJs file="library/form/State.js"}
{includeJs file="library/form/ActiveForm.js"}
{includeJs file="library/form/Validator.js"}

{includeCss file="library/TabControl.css"}

{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Currency.css"}

{pageTitle help="settings.currencies"}{t _currencies}{/pageTitle}
[[ partial("layout/backend/header.tpl") ]]

<div id="tabContainer" class="tabContainer maxHeight h--20">
	<ul class="tabList tabs">
		<li id="tabManage" class="tab active"><a href="[[ url("backend.currency/list") ]]">{t _manage}</a></li>
		<li id="tabRates" class="tab inactive"><a href="[[ url("backend.currency/rates") ]]">{t _adjust}</a></li>
{*		<li id="tabOptions" class="tab inactive"><a href="[[ url("backend.currency/options") ]]">{t _options}</a></li> *}
	</ul>
	<div class="sectionContainer maxHeight h--95">
		<div id="tabManageContent" class="maxHeight tabPageContainer">
			<ul class="menu" id="currPageMenu" {denied role="currency.create"}style="display: none;"{/denied}>
				<li class="addNewCurrency">
					<a href="#" onClick="curr.showAddForm(); return false;">{t _add_currency}</a>
					<span class="progressIndicator" id="currAddMenuLoadIndicator" style="display: none;"></span>
				</li>
			</ul>

			<div class="clear"></div>

			<div id="addCurr" class="slideForm"></div>

			<div id="noCurrencies" class="noRecords">
				<div>{t _no_currencies}</div>
			</div>

			<ul style="display: none;">
				<li id="currencyList_template" class="{allowed role="currency.sort"}activeList_add_sort{/allowed} activeList_add_edit {allowed role="currency.remove"}activeList_remove_delete{/allowed} disabled default" style="position: relative">
				<div>
					<div class="currListContainer">
						<span {denied role="currency.status"}style="display: none;"{/denied}>
							<input type="checkbox" class="checkbox" disabled="disabled"  />
						</span>

						<span class="currData">
							<span class="currTitle"></span>
							<span class="currInactive">({t _inactive})</span>
						</span>

						<div class="currListMenu">
							<a href="[[ url("backend.currency/setDefault") ]]?id=" class="setDefault listLink" {denied role="currency.status"}style="display: none;"{/denied} onclick="return confirm('[[ escape({t _base_currency_warning}) ]]')">{t _set_as_default}</a>
							<span class="currDefault">{t _default_currency}</span>
						</div>
						<div class="currEdit activeList_editContainer activeList_container"></div>
					</div>
				</div>
				</li>
			</ul>

			<ul id="currencyList" class="{allowed role="currency.remove"}activeList_add_delete{/allowed} activeList_add_edit {allowed role="currency.sort"}activeList_add_sort"{/allowed}></ul>

		</div>
		<div id="tabRatesContent" class="tabPageContainer"></div>
		<div id="tabOptionsContent"></div>
	</div>
</div>


<script type="text/javascript">
	curr = new Backend.Currency();
	curr.setFormUrl('[[ url("backend.currency/addForm") ]]');
	curr.setStatusUrl('[[ url("backend.currency/setEnabled") ]]/');

	var messages =
	{
		_activeList_edit:	'[[ addslashes({t _activeList_edit}) ]]',
		_activeList_delete:  '[[ addslashes({t _activeList_delete}) ]]'
	}

	function initCurrencyList()
	{
		curr.showNoCurrencyMessage();
		ActiveList.prototype.getInstance('currencyList', {
			 beforeEdit:	 function(li)
			 {
				 if (!this.isContainerEmpty(li, 'edit'))
				 {
					 this.toggleContainer(li, 'edit');
					 return;
				 }

				 return '[[ url("backend.currency/edit") ]]?id=' + this.getRecordId(li);
			 },
			 beforeSort:	 function(li, order)
			 {
				 return '[[ url("backend.currency/saveorder") ]]?draggedId=' + this.getRecordId(li) + '&' + order
			   },
			 beforeDelete:   function(li)
			 {
				 if(confirm('{tn _confirm_delete}')) return '[[ url("backend.currency/delete") ]]?id=' + this.getRecordId(li)
			 },
			 afterEdit:	  function(li, response) { document.getElementsByClassName('currEdit', li)[0].update(response); },
			 afterSort:	  function(li, response) { curr.resetRatesContainer(); },
			 afterDelete:	function(li, response)  { curr.resetRatesContainer(); }
		 }, messages);
	}

	curr.renderList([[currencies]]);
	initCurrencyList();

	TabControl.prototype.getInstance('tabContainer', Backend.Currency.prototype.getTabUrl, Backend.Currency.prototype.getContentTabId);

	//new TabControl('tabContainer', 'sectionContainer');

</script>


[[ partial("layout/backend/footer.tpl") ]]