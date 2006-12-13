{includeJs file=library/ActiveList.js}
{includeJs file=library/KeyboardEvent.js}
{includeJs file=backend/Currency.js}
{includeJs file=library/Debug.js}
{includeCss file=library/ActiveList.css}
{includeCss file=backend/Currency.css}

{pageTitle}{t _currencies}{/pageTitle}
{include file=layout/header.tpl}

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

{literal}
<script type="text/javascript">
	curr = new Backend.Currency();
	curr.setFormUrl('{/literal}{link controller=backend.currency action=addForm}{literal}');
	curr.setAddUrl('{/literal}{link controller=backend.currency action=add}{literal}');
	curr.setStatusUrl('{/literal}{link controller=backend.currency action=enable}{literal}');
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
	             if(confirm('{/literal}{tn _confirm_delete}{literal}')) return '{/literal}{link controller=backend.currency action=delete}{literal}/' + this.getRecordId(li)
	         },
	         afterEdit:      function(li, response) {  },
	         afterSort:      function(li, response) {  },
	         afterDelete:    function(li, response)  { Element.remove(li); }
	     });
	}	
	initCurrencyList();
</script>
{/literal}


{include file=layout/footer.tpl}