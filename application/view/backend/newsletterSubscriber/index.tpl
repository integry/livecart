<fieldset class="container activeGridControls">

	<span id="newsletterSubscriberMass_0" class="activeGridMass">

		{form action="controller=backend.newsletterSubscriber action=processMass" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select">
			<option value="enable_isEnabled">{t _enable}</option>
			<option value="disable_isEnabled">{t _disable}</option>
			<option value="delete">{t _delete}</option>
		</select>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="massIndicator progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span id="newsletterCount_0">
			<span class="rangeCount">{t _listing_subscribers}</span>
			<span class="notFound">{t _no_subscribers_found}</span>
		</span>
	</span>

</fieldset>
{activeGrid
	prefix="newsletterSubscriber"
	id=0
	controller="backend.newsletterSubscriber" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=0
	filters=$filters
	container="tabSubscribers"
}


{literal}
<script type="text/javascript">
{/literal}

	var massHandler = new ActiveGrid.MassActionHandler(
						$('newsletterSubscriberMass_0'),
						window.activeGrids['newsletterSubscriber_0']
						);
	massHandler.deleteConfirmMessage = '{t _newsletter_delete_confirm|addslashes}' ;
	massHandler.nothingSelectedMessage = '{t _nothing_selected|addslashes}' ;
{literal}
</script>
{/literal}