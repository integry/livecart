{literal}
<style>
.datagrid_table {
  
  	border-collapse: collapse;  	  	
}
.datagrid_head {
  
  	background-color: #ffeeee;  
}
.datagrid_row {
  
  	background-color: #eeffee;  
}
.datagrid_head_col {
  
 	border: inset 1pt; 	 
 	text-decoration: underline; 
}
a.datagrid_hrefs {
  	
	color: #DD8800;
}
.datagrid_row_col {

 	border: inset 1pt; 	
}
/* Tree menu part*/	
.treeMenuNode {      	                    
    font-size: 10pt; 	   	    
}            
.treeMenuNodeSelected {      	              
   
	font-size: 10pt;         	
}
.treeMenuNodeSelected a {
   background-color:#aaaaaa; 		  	
}
/* Tab control */
.tabcontrol {
	height: 235px; width: 500px; border: 1px solid #000000;  
}
.tabpage {
	border: inset 1pt; background-color: #66BB66; padding: 3px;
}
.tabpageselected {
	border: inset 1pt; cursor: pointer; background-color: #FFDDDD; padding: 3px;
}
</style>
{/literal}
{$filter}
{$grid}
{$paging}
