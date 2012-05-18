{if 'HTML_EMAIL'|config}
	{input name="newsletter_`$newsletter.ID`_format"}
		{label}{t _message_format}:{/label}
		<select id="newsletter_{$newsletter.ID}_format" name="newsletter_{$newsletter.ID}_format">
			<option value="1">{t _html_with_auto_generated_plaintext_version}</option>
			<option value="2" {if $newsletter.format==2}selected="selected"{/if}>{t _html_with_manualy_edited_plaintext_version}</option>
			<option value="3" {if $newsletter.format==3}selected="selected"{/if}>{t _html_only}</option>
			<option value="4" {if $newsletter.format==4}selected="selected"{/if}>{t _plaintext_only}</option>
		</select>
	{/input}
{else}
	<input type="hidden" id="newsletter_{$newsletter.ID}_format" name="newsletter_{$newsletter.ID}_format" value="4" />
{/if}

{input name="subject"}
	{label}{t _subject}:{/label}
	{textfield class="wide"}
{/input}

{input name="html"}
	{label}{t _message_text_html}:{/label}
	{textarea class="tinyMCE"}
{/input}

{input name="text"}
	{label}{t _text}:{/label}
	{textarea}
{/input}