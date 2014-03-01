{activeGrid
	prefix="newsletterSubscriber"
	id=0
	controller="backend.newsletterSubscriber" action="lists"
	displayedColumns=displayedColumns
	availableColumns=availableColumns
	totalCount=0
	filters=filters
	container="tabSubscribers"
	count="backend/newsletterSubscriber/count.tpl"
	massAction="backend/newsletterSubscriber/massAction.tpl"
}



<script type="text/javascript">


	var massHandler = new ActiveGrid.MassActionHandler(
						('newsletterSubscriberMass_0'),
						window.activeGrids['newsletterSubscriber_0']
						);
	massHandler.deleteConfirmMessage = '[[ addslashes({t _newsletter_delete_confirm}) ]]' ;
	massHandler.nothingSelectedMessage = '[[ addslashes({t _nothing_selected}) ]]' ;

</script>
