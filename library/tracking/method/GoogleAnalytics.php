<?php

include_once dirname(dirname(__file__)) . '/TrackingMethod.php';

class GoogleAnalytics extends TrackingMethod
{
	public function getHtml()
	{
		return
'<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
var pageTracker = _gat._getTracker("' . $this->getValue('code') . '");
pageTracker._initData();
pageTracker._trackPageview();
</script>';
	}
}

?>