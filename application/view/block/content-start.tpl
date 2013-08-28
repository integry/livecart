<div id="content" class="col col-lg-[[12 - global('layoutspanLeft') - global('layoutspanRight')]]">

{block BREADCRUMB}

{if $title && !$hideTitle}
	<h1>[[ content('title') ]]</h1>
{/if}

[[ partial("block/message.tpl") ]]
