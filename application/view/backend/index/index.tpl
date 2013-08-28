{pageTitle help=""}{t _livecart_backend|branding}{/pageTitle}

{includeCss file="backend/Index.css"}
{includeCss file="backend/User.css"}

{include file="layout/backend/header.tpl"}

<fieldset>
	<legend>{t _order_overview}</legend>
	<span class="orderPeriod">
	<select id="period" onchange="jQuery('#count').html(''); new LiveCart.AjaxUpdater('{link controller="backend.index" action=totalOrders}?period=' + this.value, $('count'), $('periodProgress'));">
				<option value="-1 hours | now" selected="selected">{maketext text=_last_hours params=1}</option>
				<option value="-3 hours | now" selected="selected">{maketext text=_last_hours params=3}</option>
				<option value="-6 hours | now" selected="selected">{maketext text=_last_hours params=6}</option>
				<option value="-12 hours | now" selected="selected">{maketext text=_last_hours params=12}</option>
				<option value="-24 hours | now" selected="selected">{maketext text=_last_hours params=24}</option>
				<option value="-3 days | now">{maketext text=_last_days params=3}</option>
				<option value="w:Monday ~ -1 week | w:Monday">{t _last_week}</option>
				<option value="January 1 | now">{t _this_year}</option>
				<option value="January 1 last year | January 1">{t _last_year}</option>
			</select>:
			<label class="checkbox"><span class="progressIndicator" style="display: none;" id="periodProgress"></span></label>
			<label class="periodCount" id="count">[[orderCount.last]]</label><span class="sep"> | </span>

	<span class="orderPeriod">{translate|@strtolower text=_this_week}:</span> <span class="periodCount">[[ordersThisWeek]]</span><span class="sep"> | </span>
	<span class="orderPeriod">{translate|@strtolower text=_this_month}:</span> <span class="periodCount">[[ordersThisMonth]]</span><span class="sep"> | </span>
	<span class="orderPeriod">{translate|@strtolower text=_last_month}:</span> <span class="periodCount">[[ordersLastMonth]]</span><span class="sep"> | </span>

	<span class="orderStat"><a href="{link controller="backend.customerOrder"}#group_3#tabOrders__">{t _status_new}</a>:</span>
	<span class="statCount">[[orderCount.new]]</span>
	<span class="sep"> | </span>

	<span class="orderStat"><a href="{link controller="backend.customerOrder"}#group_4#tabOrders__">{t _status_processing}</a>:</span>
	<span class="statCount">[[orderCount.processing]]</span>
	<span class="sep"> | </span>

	<span class="orderStat">{t _unread_msg}:</span>
	<span class="statCount">[[orderCount.messages]]</span>

</fieldset>

{if $lastOrders}
<fieldset class="dashboardOrders stats">
	<legend>{t _last_orders}</legend>
	{if $lastOrders}
		<table class="qeOrders">
			<thead>
				<tr>
					<th>{t _invoice_number}</th>
					<th>{t _date}</th>
					<th>{t _ammount}</th>
					<th>{t _status}</th>
				</tr>
			</thead>
			<tbody>
				{foreach from=$lastOrders item=order name=lastOrders}
					<tr>
						<td><a href="{backendOrderUrl order=$order}">{$order.invoiceNumber|escape}</a></td>
						<td title="{$order.formatted_dateCreated.date_medium|escape} {$order.formatted_dateCreated.time_short|escape}">{$order.formatted_dateCreated.date_short|escape}</td>
						<td>{$order.formatted_totalAmount|escape}</td>
						<td>{t `$order.status_name`}</td>
					</tr>
				{/foreach}
			</tbody>
		</table>
	{/if}
</fieldset>
{/if}

<fieldset class="stats">
	<legend>{t _inventory}</legend>

	<form>
		<p>
			<label>{t _low_stock}:</label>
			<label>[[inventoryCount.lowStock]]</label>
		</p>

		<p>
			<label>{t _out_stock}:</label>
			<label>[[inventoryCount.outOfStock]]</label>
		</p>
	</form>
</fieldset>

<fieldset class="stats">
	<legend>{t _overall}</legend>

	<form>
		<p>
			<label>{t _active_pr}:</label>
			<label>[[rootCat.activeProductCount]]</label>
		</p>

		<p>
			<label>{t _inactive_pr}:</label>
			<label>[[rootCat.inactiveProductCount]]</label>
		</p>

		<p>
			<label>{t _total_orders}:</label>
			<label>[[orderCount.total]]</label>
		</p>

	</form>
</fieldset>

<div class="clear"></div>

{capture assign="tips"}{include file="backend/index/tips.tpl"}{/capture}
{$tips|branding}

{include file="layout/backend/footer.tpl"}