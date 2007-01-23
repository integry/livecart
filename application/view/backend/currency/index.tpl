{includeJs file="library/ActiveList.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="backend/Currency.js"}

{includeJs file="library/form/State.js"}
{includeJs file="library/form/Validator.js"}

{includeCss file="library/TabControl.css"}

{includeCss file="library/ActiveList.css"}
{includeCss file="backend/Currency.css"}

{pageTitle}{t _currencies}{/pageTitle}
{include file="layout/backend/header.tpl"}

<div id="tabContainer" class="maxHeight h--20">
	<ul id="tabList">
		<li id="tabManage" class="tab active"><a href="{link controller=backend.currency action=list}">{t _manage}</a></li>
		<li id="tabRates" class="tab inactive"><a href="{link controller=backend.currency action=rates}">{t _adjust}</a></li>
		<li id="tabOptions" class="tab inactive"><a href="{link controller=backend.currency action=options}">{t _options}</a></li>
	</ul>
	<div id="sectionContainer" class="maxHeight h--95">
		<div id="tabManageContent" class="maxHeight">
		
			<ul class="menu" id="currPageMenu">
				<li><a href="#" onClick="curr.showAddForm(); return false;">{t _add_currency}</a></li>
			</ul>
			
			<div class="menuLoadIndicator" id="currAddMenuLoadIndicator"></div>
			<div id="addCurr" class="slideForm"></div>
					
			<div id="noCurrencies" class="noRecords">
				<div>{t _no_currencies}</div>
			</div>
	
			<ul style="display: none;">
				<li id="currencyList_template" class="activeList_add_sort activeList_remove_delete disabled default">
				<div>
					<div class="currListContainer">
						<span>
							<input type="checkbox" class="checkbox" disabled="disabled" />
						</span>	
					
						<span class="currData">	
							<span class="currTitle"></span> 	
							<span class="currInactive">({t _inactive})</span>
						</span>
						
						<div class="currListMenu">
							<a href="{link controller=backend.currency action=setDefault}?id=" class="setDefault listLink">{t _set_as_default}</a>
							<span class="currDefault">{t _default_currency}</span>
						</div>
					</div>
				</div>			
				</li>
			</ul>			
			
			<ul id="currencyList" class="activeList_add_delete activeList_add_sort"></ul>		
			
		</div>
		<div id="tabRatesContent"></div>
		<div id="tabOptionsContent"></div>
	</div>
</div>

{literal}
<script type="text/javascript">
	curr = new Backend.Currency();
	curr.setFormUrl('{/literal}{link controller=backend.currency action=addForm}{literal}');
	curr.setAddUrl('{/literal}{link controller=backend.currency action=add}{literal}');
	curr.setStatusUrl('{/literal}{link controller=backend.currency action=setEnabled}{literal}/');
    
    var messages = 
    { 
        _activeList_edit:    {/literal}'{t _activeList_edit|addslashes}'{literal},
        _activeList_delete:  {/literal}'{t _activeList_delete|addslashes}'{literal}
    }
    
    function initCurrencyList()
    {	
		curr.showNoCurrencyMessage();
		ActiveList.prototype.getInstance('currencyList', {
	         beforeEdit:     function(li) { return 'sort.php?' },
	         beforeSort:     function(li, order) 
			 { 
				 return '{/literal}{link controller=backend.currency action=saveorder}{literal}?draggedId=' + this.getRecordId(li) + '&' + order 
			   },
	         beforeDelete:   function(li)
	         {
	             if(confirm('{/literal}{tn _confirm_delete}{literal}')) return '{/literal}{link controller=backend.currency action=delete}{literal}?id=' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) { curr.resetRatesContainer(); },
	         afterDelete:    function(li, response)  { Element.remove(li); curr.resetRatesContainer(); }
	     }, messages);
	}	
	
	curr.renderList({/literal}{$currencies}{literal});
	initCurrencyList();
	
	new TabControl('tabContainer', 'sectionContainer');

</script>
{/literal}
	
{include file="layout/backend/footer.tpl"}