<h3><a href="{newsUrl news=$entry}">{$entry.title_lang}</a></h3>
<div class="newsDate">{$entry.formatted_time.date_medium}</div>
<p>{$entry.text_lang}</p>
{if $entry.moreText_lang}
    <div class="newsReadMore">
        <a href="{newsUrl news=$entry}">{t _read_more}</a>
    </div>
{/if}