/**
 *	Requires rico.js
 *
 */
ActiveGrid = Class.create();

ActiveGrid.prototype = 
{
  	tableInstance: null,
  	
  	dataUrl: null,
  	
	initialize: function(tableInstance, dataUrl, options)
  	{
		this.tableInstance = tableInstance;
		this.dataUrl = dataUrl;

		this.ricoGrid = new Rico.LiveGrid(this.tableInstance.id, 5, 100, dataUrl, 
								{
								  prefetchBuffer: true, 
								  onscroll: updateHeader,  
								  sortAscendImg: 'http://openrico.org/images/sort_asc.gif',
						          sortDescendImg: 'http://openrico.org/images/sort_desc.gif' 
								}
							);		
console.log(this.tableInstance.id);
	}
}