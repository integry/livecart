<p class="required">
	<label for="newsletter_{$newsletter.ID}_subject">{t _subject}:</label>
	<fieldset class="error">
		{textfield name="subject" id="newsletter_`$newsletter.ID`_name" class="wide"}
		<div class="errorText hidden"></div>
	</fieldset>
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