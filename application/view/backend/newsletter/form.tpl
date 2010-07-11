{if 'HTML_EMAIL'|config}
	<p class="required">
		<label for="newsletter_{$newsletter.ID}_format">{t _message_format}:</label>
		<fieldset class="error">
			<select id="newsletter_{$newsletter.ID}_format" name="newsletter_{$newsletter.ID}_format">
				<option value="1">{t _html_with_auto_generated_plaintext_version}</option>
				<option value="2" {if $newsletter.format==2}selected="selected"{/if}>{t _html_with_manualy_edited_plaintext_version}</option>
				<option value="3" {if $newsletter.format==3}selected="selected"{/if}>{t _html_only}</option>
				<option value="4" {if $newsletter.format==4}selected="selected"{/if}>{t _plaintext_only}</option>
			</select>
		</fieldset>
	</p>
{else}
	<input type="hidden" id="newsletter_{$newsletter.ID}_format" name="newsletter_{$newsletter.ID}_format" value="4" />
{/if}

<p class="required">
	<label for="newsletter_{$newsletter.ID}_name">{t _subject}:</label>
	<fieldset class="error">
		{textfield name="subject" id="newsletter_`$newsletter.ID`_name" class="wide"}
		<div class="errorText hidden"></div>
	</fieldset>
</p>

<p class="required">
	<label for="newsletter_{$cat}_{$newsletter.ID}_html">{t _message_text_html}:</label>
	<div class="textarea">
		<fieldset class="error">
			{textarea id="newsletter_`$cat`_`$newsletter.ID`_html" name="html" class="tinyMCE"}
			<div class="errorText hidden"></div>
		</fieldset>
	</div>
</p>

<p class="required">
	<label for="newsletter_{$cat}_{$newsletter.ID}_shortdes">{t _text}:</label>
	<div class="textarea">
		<fieldset class="error">
			{textarea id="newsletter_`$cat`_`$newsletter.ID`_shortdes" name="text"}
			<div class="errorText hidden"></div>
		</fieldset>
	</div>
</p>





{*
<p>
	<label for="newsletter_{$cat}_{$newsletter.ID}_shortdes">{t _html_text}:</label>
	<div class="textarea">
		<fieldset class="error">
			{textarea id="newsletter_`$cat`_`$newsletter.ID`_html" name="html"}
			<div class="errorText hidden"></div>
		</fieldset>
	</div>
</p>
*}