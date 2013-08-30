{activeGrid
	prefix="newsletterSubscriber"
	id=0
	controller="backend.newsletterSubscriber" action="lists"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=0
	filters=$filters
	container="tabSubscribers"
	count="backend/newsletterSubscriber/count.tpl"
	massAction="backend/newsletterSubscriber/massAction.tpl"
}



<script type="text/javascript">


	var massHandler = new ActiveGrid.MassActionHandler(
						$('newsletterSubscriberMass_0'),
						window.activeGrids['newsletterSubscriber_0']
						);
	massHandler.deleteConfirmMessage = '{t _newsletter_delete_confirm|addslashes}' ;
	massHandler.nothingSelectedMessage = '{t _nothing_selected|addslashes}' ;

</script>
