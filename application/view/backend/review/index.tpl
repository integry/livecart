{literal}
<script type="text/javascript">
	Backend.Product.Editor.prototype.links = {};
	Backend.Product.Editor.prototype.links.countTabsItems = '{/literal}{link controller=backend.product action=countTabsItems}{literal}';
	Backend.Product.GridFormatter.productUrl = '{/literal}{backendProductUrl}{literal}';
</script>
{/literal}

<div>

<fieldset class="container activeGridControls">

	<span {denied role="product.mass"}style="display: none;"{/denied} id="reviewMass_{$id}" class="activeGridMass">

		{form action="controller=backend.review action=processMass query=id=`$id`" method="POST" handle=$massForm onsubmit="return false;"}

		<input type="hidden" name="filters" value="" />
		<input type="hidden" name="selectedIDs" value="" />
		<input type="hidden" name="isInverse" value="" />

		{t _with_selected}:
		<select name="act" class="select" onchange="Backend.Product.massActionChanged(this);">
			<option value="enable_isEnabled">{t _approve}</option>
			<option value="disable_isEnabled">{t _disapprove}</option>
			<option value="delete">{t _delete}</option>
		</select>

		<input type="submit" value="{tn _process}" class="submit" />
		<span class="massIndicator progressIndicator" style="display: none;"></span>

		{/form}

	</span>

	<span class="activeGridItemsCount">
		<span id="productCount_{$id}">
			<span class="rangeCount" style="display: none;">{t _listing_reviews}</span>
			<span class="notFound" style="display: none;">{t _no_reviews_found}</span>
		</span>
	</span>

</fieldset>
{activeGrid
	prefix="reviews"
	id=$id
	role="product.mass"
	controller="backend.review" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	filters=$filters
	container=$container
	dataFormatter="Backend.Review.GridFormatter"
}

</div>

{literal}
<script type="text/javascript">
{/literal}
	var massHandler = new ActiveGrid.MassActionHandler(
						$('reviewMass_{$id}'),
						window.activeGrids['reviews_{$id}'],
{literal}
						{
							'onComplete':
								function()
								{
								}
						}
{/literal}
						);
	massHandler.deleteConfirmMessage = '{t _delete_conf|addslashes}' ;
	massHandler.nothingSelectedMessage = '{t _nothing_selected|addslashes}' ;

{literal}
</script>
{/literal}