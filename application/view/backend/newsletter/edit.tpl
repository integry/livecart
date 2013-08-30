{form handle=$form action="backend.newsletter/save" method="POST" onsubmit="Backend.Newsletter.saveForm(this); return false;" onreset="Backend.Newsletter.resetAddForm(this);"}
<div class="newsletterform">

	{hidden name="id" value=$newsletter.ID}

	<fieldset>
		<legend>[[ capitalize({t _edit_message}) ]]</legend>
		[[ partial("backend/newsletter/form.tpl") ]]
	</fieldset>

	<fieldset class="controls">

		<input type="checkbox" name="afterAdding" value="new" style="display: none;" />

		<span class="progressIndicator" style="display: none;"></span>
		<input type="submit" name="save" class="submit" value="{t _save_message}" onclick="this.form.elements.namedItem('sendFlag').value = '';" />
		{t _or} <a class="cancel" href="#" onclick="Backend.Newsletter.cancelAdd(); return false;">{t _cancel}</a>

	</fieldset>

</div>

<div class="confirmations">
	<div class="yellowMessage messageSaved" style="display: none;">
		<div>{t _message_successfully_saved}</div>
	</div>
	<div class="yellowMessage messageComplete stick" style="display: none;">
		<div>{t _message_successfully_sent}</div>
	</div>
</div>

<div class="sendContainer">
	<fieldset>
		<legend>[[ @capitalize({t _send_message}) ]]</legend>
		<p>
			<label class="sendLabel">{t _send_to}:</label>
			<div style="float: left;">
				{foreach from=$groupsArray item=groupItem}
					<p>
						{checkbox class="checkbox userGroupCheckbox" name="group" name="group_[[groupItem.ID]]" onchange="Backend.Newsletter.updateRecipientCount(this)"}
						<label class="checkbox" for="group_[[groupItem.ID]]">{$groupItem.name|escape}</label>
					</p>
				{/foreach}
				<input type="hidden" value="" name="userGroupIDs" id="userGroupIDs" />
				<p>
					{checkbox class="checkbox" name="users" onchange="Backend.Newsletter.updateRecipientCount(this)"}
					<label class="checkbox" for="users">{t _all_users}</label>
				</p>
				<p>
					{checkbox class="checkbox" name="subscribers" onchange="Backend.Newsletter.updateRecipientCount(this)"}
					<label class="checkbox" for="subscribers">{t _all_subscribers}</label>
				<p>
				<fieldset class="container">
					<p class="recipientCount">
						[[ partial('backend/newsletter/recipientCount.tpl', ['count': recipientCount]) ]]
					</p>
				</fieldset>

				<input type="submit" name="send" class="submit" value="{t _send_message}"  onclick="this.form.elements.namedItem('sendFlag').value = 'send';" />
			</div>
		</p>
	</fieldset>

	<fieldset>
		<legend>{t _status}</legend>
		<p>
			<label>{t _status}:</label>
			<label class="statusString">{translate text="_status_`$newsletter.status`"}</label>
		</p>
		<p>
			<label>{t _messages_sent}:</label>
			<label class="sentCount">[[sentCount]]</label>
		</p>
		<div style="display: none;">
			<span class="statusPartial">{t _status_1}</span>
			<span class="statusSent">{t _status_2}</span>
		</div>
	</fieldset>

	<fieldset class="sendProgress" style="display: none;">
		<legend>{t _sending}</legend>
		<div class="progressBarIndicator"></div>
		<div class="progressBar">
			<span class="progressCount"></span>
			<span class="progressSeparator"> / </span>
			<span class="progressTotal"></span>
		</div>
		<a class="cancel" href="#" onclick="Backend.Newsletter.cancel(this); return false;">{t _cancel}</a>
	</fieldset>
</div>

<input type="hidden" name="sendFlag" value="" />

{/form}

<div class="clear"></div>