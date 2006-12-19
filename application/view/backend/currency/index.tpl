{includeJs file="library/ActiveList.js"}
{includeJs file="library/KeyboardEvent.js"}
{includeJs file="library/TabControl.js"}
{includeJs file="backend/Currency.js"}

{includeCss file="library/TabControl.css"}

{includeCss file=library/ActiveList.css}
{includeCss file=backend/Currency.css}

{pageTitle}{t _currencies}{/pageTitle}
{include file=layout/header.tpl}

<div id="tabContainer" style="height: 100%;">
	<ul id="tabList">
		<li id="tabManage" class="tab active"><a href="{link controller=backend.currency action=list}">Manage</a></li>
		<li id="tabRates" class="tab inactive"><a href="{link controller=backend.currency action=rates}">Adjust Rates</a></li>
		<li id="tabOptions" class="tab inactive"><a href="{link controller=backend.currency action=options}">Options</a></li>
	</ul>
	<div id="sectionContainer" style="height: 95%; border: 2px solid red;">
		<div id="tabManageContent">
		
			<ul class="menu" id="currPageMenu">
				<li><a href="#" onClick="curr.showAddForm(); return false;">{t _add_currency}</a></li>
			</ul>
			
			<div class="menuLoadIndicator" id="currAddMenuLoadIndicator"></div>
			<div id="addCurr" class="slideForm"></div>
			
			<br />
			
			<ul id="currencyList" class="activeList_add_delete">
			{foreach from=$currencies item=item}
				{include file="backend/currency/listItem.tpl" showContainer=true}	
			{foreachelse}		
				No currencies found	
			{/foreach}
			</ul>		
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
	curr.setStatusUrl('{/literal}{link controller=backend.currency action=setEnabled}{literal}');
    function initCurrencyList()
    {	
		new ActiveList('currencyList', {
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
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); }
	     });
	}	
	initCurrencyList();
	
	new TabControl('tabContainer', 'sectionContainer');

</script>
{/literal}
	
{include file=layout/footer.tpl}