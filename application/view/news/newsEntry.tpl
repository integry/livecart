<div class="newsEntry">
	<h3><a href="{newsUrl news=$entry}">{$entry.title_lang}</a></h3>
	<p class="newsEntryPreview">{$entry.text_lang}</p>
	<p class="newsEntryProperties">
		<a href="{newsUrl news=$entry}">{t _read_more}</a>
		| <i class="glyphicon glyphicon-calendar"></i> {$entry.formatted_time.date_medium}
	</p>
</div>