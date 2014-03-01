{% if chartType < 2 %}
	<div class="intervalSelector">
		<span>{t _interval}:</span>
		<select class="intervalSelect">
			<option value="day">{t _daily}</option>
			<option value="month">{t _monthly}</option>
			<option value="year">{t _yearly}</option>
			<option value="hour">{t _hourly}</option>
			<option value="week">{t _weekly}</option>
		</select>
	</div>
{% endif %}