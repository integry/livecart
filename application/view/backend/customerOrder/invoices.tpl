{activeGrid
	prefix="recurringOrdersWithParent"
	id=$parentID
	role="order.mass"
	controller="backend.customerOrder" action="listInvoices"
	displayedColumns=$displayedColumns
	availableColumns=$availableColumns
	totalCount=$totalCount
	rowCount=15
	showID=true
	container="tabPageContainer"
	filters=$filters
	dataFormatter=$dataFormatter
	count="backend/customerOrder/count.tpl"
	massAction="backend/customerOrder/massAction.tpl"
	advancedSearch=false
}