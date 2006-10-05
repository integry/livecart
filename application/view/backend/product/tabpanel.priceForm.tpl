{form handler=$form}
	{formsection num=0}	
	<tr>
		<td colspan="2">	
			<b>Discounts</b> &nbsp; <a href="javascript: discount.addDiscount('{$currency}');">Add discount</a>
			<div id="discountsLayer"></div>	
			<input type="hidden" name="discountLayersIndex">
		</td>
	</tr>		
	{formsection num=1}	
	{formsection num=2}	
	<tr>
		<td>			
		</td>
		<td>
			<span id="metricUnits"></span>
			<span id="englishUnits"></span>
		</td>
	</tr>	
	{formsection num=3}	
{/form}
{literal}
<script language="javascript">
	
	function changeMetrics(type) {
	  
	  	if (type == 0) {
		    
		    documentHelper.getLayer("metricUnits").innerHTML = '<b>{/literal}{translate text="_metricUnits}{literal}</b>';
		    documentHelper.getLayer("englishUnits").innerHTML = '<a href="javascript: changeMetrics(1)">{/literal}{translate text="_englishUnits"}{literal}</a>';		    
		    document.priceForm.unitsType.value = 0;		    
		} else {
		  	
		  	documentHelper.getLayer("englishUnits").innerHTML = '<b>{/literal}{translate text="_englishUnits}{literal}</b>';
		    documentHelper.getLayer("metricUnits").innerHTML = '<a href="javascript: changeMetrics(0)">{/literal}{translate text="_metricUnits"}{literal}</a>';		    
		    document.priceForm.unitsType.value = 1;
		}
	}	
</script>
{/literal}
