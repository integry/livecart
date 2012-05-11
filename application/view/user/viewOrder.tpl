{pageTitle}{t _view_order} #{$order.invoiceNumber}{/pageTitle}
{loadJs form=true}
<div class="userViewOrder">

{include file="user/layout.tpl"}
{include file="user/userMenu.tpl" current="ordersMenu"}

<div id="content">

	<h1>{t _view_order} {$order.invoiceNumber} ({$order.formatted_dateCompleted.date_long})</h1>
		<fieldset class="container">
		{include file="block/message.tpl"}
		<label class="title">{t _order_id}:</label>
		<label class="text">{$order.invoiceNumber}</label>
		<div class="clear"></div>

		<label class="title">{t _placed}:</label>
		<label class="text">{$order.formatted_dateCompleted.date_long}</label>
		<div class="clear"></div>

		<label class="title">{t _order_total}:</label>
		<label class="text">{$order.formattedTotal[$order.Currency.ID]}</label>
		<div class="clear"></div>

		<label class="title">{t _order_status}:</label>
		<label class="text">{include file="user/orderStatus.tpl" order=$order}</label>
		<div class="clear"></div>

		{if $order.isRecurring}
			<label class="title"></label>
			<label class="text">
				{if $subscriptionStatus > 0}
					{t _active_subscription}
				{else}
					{t _inactive_subscription}
				{/if}
			</label>
			<div class="clear"></div>

			{* Rebills every x months *}
			{foreach from=$recurringProductPeriodsByItemId item="period"}
				<label class="title"></label>
				{if $period.periodLength == 1}
					{assign var="length" value=''}
					{capture name="a" assign="period"}{t `$periodTypesSingle[$period.periodType]`}{/capture}
				{else}
					{assign var="length" value=$period.periodLength}
					{capture name="a" assign="period"}{t `$periodTypesPlural[$period.periodType]`}{/capture}
				{/if}
				<label class="text">{maketext text=_rebills_every params="`$length`,`$period`"}</label>
				<div class="clear"></div>
			{/foreach}

			{if $nextRebillDate}
				<label class="title">{t _next_rebill}:</label>
				<label class="text">{$nextRebillDate.date_medium}</label>
				<div class="clear"></div>
			{/if}

			<label class="title">{t _remaining_rebills}:</label>
			<label class="text">{if $order.rebillsLeft != -1}{$order.rebillsLeft}{else}{t _remaining_rebills_till_canceled}{/if}
				{if $canCancelRebills}
					<span class="cancelFurtherRebills">
						{if $currentPage > 1}
							{assign var='rebillQuery' value="page=`$currentPage`"}
						{else}
							{assign var='rebillQuery' value=''}
						{/if}
						<a href="{link controller=user action=cancelFurtherRebills id=$order.ID query=$rebillQuery}" onclick="return confirm('{t _are_you_sure_want_to_cancel_subscription}');" />{t _cancel_this_subscription}</a>
					</span>
				{/if}
			</label>
			<div class="clear"></div>
		{/if}

		<p>
			{if !$order.isCancelled && !'DISABLE_INVOICES'|config}
				<a href="{link controller=user action=orderInvoice id=`$order.ID`}" target="_blank" class="invoice">{t _order_invoice}</a>
			{/if}
			<a href="{link controller=user action=reorder id=`$order.ID`}" class="reorder">{t _reorder}</a>
		</p>

		{foreach from=$order.shipments item="shipment" name="shipments"}
			{if $shipment.items}

				{if !$shipment.isShippable}
					<h2>{t _downloads}</h2>
				{elseif $smarty.foreach.shipments.total > 1}
					<h2>{t _shipment} #{$smarty.foreach.shipments.iteration}</h2>
					<p>
						{t _status}: {include file="user/shipmentStatus.tpl" shipment=$shipment}
					</p>
				{else}
					<h2>{t _ordered_products}</h2>
				{/if}
				{include file="user/shipmentEntry.tpl" downloadLinks=true}

			{/if}
		{/foreach}

		{function name="address"}
			{if $address}
				<p>
					{$address.fullName}
				</p>
				<p>
					{$address.companyName}
				</p>
				<p>
					{$address.address1}
				</p>
				<p>
					{$address.address2}
				</p>
				<p>
					{$address.city}
				</p>
				<p>
					{if $address.stateName}{$address.stateName}, {/if}{$address.postalCode}
				</p>
				<p>
					{$address.countryName}
				</p>
				<p>
					{include file="order/addressFieldValues.tpl" showLabels=false}
				</p>
			{/if}
		{/function}

		{include file="order/fieldValues.tpl"}

		<div id="overviewAddresses">$order.shipments

			{if $order.ShippingAddress && !$order.isMultiAddress}
			<div class="addressContainer">
				<h3>{t _is_shipped_to}:</h3>
                {if $order.isLocalPickup}
                    {foreach $order.shipments as $shipment}
                        <div class="ShippingServiceDescription">
                            {$shipment.ShippingService.description_lang|escape}
                        </div>
                    {/foreach}

                {else}
                    {address address=$order.ShippingAddress}
                {/if}
			</div>
			{/if}

			<div class="addressContainer">
				<h3>{t _is_billed_to}:</h3>
				{address address=$order.BillingAddress}
			</div>

		</div>

		<div class="clear"></div>

		{if $order.isRecurring && $orders}
			<h2>{t _invoices}</h2>
			{include file="user/invoicesTable.tpl"
				itemList=$orders
				paginateAction="viewOrder"
				textDisplaying=_displaying_invoices
				textFound=_invoices_found
				id=$order.ID
				query='page=_000_'
			}
		{/if}

		<h2 id="m_s_g">{t _support}</h2>
		<p class="noteAbout">{t _have_questions}</p>

		{if $notes}
		   <ul class="notes">
			   {foreach from=$notes item=note}
				   {include file="user/orderNote.tpl" note=$note}
			   {/foreach}
		   </ul>
		{/if}
		{form action="controller=user action=addNote id=`$order.ID`" method=POST id="noteForm" handle=$noteForm}
			{err for="text"}
				{{label {t _enter_question}:}}
				{textarea}
			{/err}
			<p class="submit">
				<label></label>
				<input type="submit" class="submit" value="{tn _submit_response}" />
			</p>
		{/form}
		</fieldset>

	</div>

</div>

{include file="layout/frontend/footer.tpl"}

</div>
