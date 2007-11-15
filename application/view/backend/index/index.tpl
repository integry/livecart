{pageTitle help=""}{t _livecart_backend}{/pageTitle}

{includeCss file="backend/Index.css"}

{include file="layout/backend/header.tpl"}

<fieldset class="stats">
	<legend>{t _order_overview}</legend>
	<form>
		<p>
			<label>
			<select id="period" onchange="$('count').innerHTML = ''; new LiveCart.AjaxUpdater('{link controller=backend.index action=totalOrders}?period=' + this.value, $('count'), $('periodProgress'));">
				<option value="-1 hours | now" selected="selected">{maketext text=_last_hours params=1}</option>
				<option value="-3 hours | now" selected="selected">{maketext text=_last_hours params=3}</option>
				<option value="-6 hours | now" selected="selected">{maketext text=_last_hours params=6}</option>
				<option value="-12 hours | now" selected="selected">{maketext text=_last_hours params=12}</option>
				<option value="-24 hours | now" selected="selected">{maketext text=_last_hours params=24}</option>
				<option value="-3 days | now">{maketext text=_last_days params=3}</option>
				<option value="w:Monday | now">{t _this_week}</option>				
				<option value="w:Monday ~ -1 week | w:Monday">{t _last_week}</option>
				<option value="{$thisMonth}/1 | now">{t _this_month}</option>
				<option value="{$lastMonth}-1 | {$thisMonth}/1">{t _last_month}</option>
				<option value="January 1 | now">{t _this_year}</option>
				<option value="January 1 last year | January 1">{t _last_year}</option>
			</select>:</label>
			<label class="checkbox"><span class="progressIndicator" style="display: none;" id="periodProgress"></span></label>
			<label id="count">{$orderCount.last}</label>
		</p>
	
		<p>
			<label>{t _status_new}:</label>
			<label>{$orderCount.new}</label>		
		</p>
	
		<p>
			<label>{t _status_processing}:</label>
			<label>{$orderCount.processing}</label>		
		</p>
	
		<p>
			<label>{t _unread_msg}:</label>
			<label>{$orderCount.messages}</label>		
		</p>
	
	</form>
</fieldset>

<fieldset class="stats">
	<legend>{t _inventory}</legend>
	
	<form>
		<p>
			<label>{t _low_stock}:</label>
			<label>{$inventoryCount.lowStock}</label>		
		</p>
	
		<p>
			<label>{t _out_stock}:</label>
			<label>{$inventoryCount.outOfStock}</label>
		</p>
	</form>
</fieldset>

<fieldset class="stats">
	<legend>{t _overall}</legend>
	
	<form>
		<p>
			<label>{t _active_pr}:</label>
			<label>{$rootCat.availableProductCount}</label>		
		</p>
	
		<p>
			<label>{t _inactive_pr}:</label>
			<label>{$rootCat.unavailableProductCount}</label>
		</p>

		<p>
			<label>{t _total_orders}:</label>
			<label>{$orderCount.total}</label>
		</p>

	</form>
</fieldset>

{include file="layout/backend/footer.tpl"}