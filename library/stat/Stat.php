<?php

/*
if( !function_exists('memory_get_usage') )
{
   function memory_get_usage()
   {
	   //If its Windows
	   //Tested on Win XP Pro SP2. Should work on Win 2003 Server too
	   //Doesn't work for 2000
	   //If you need it to work for 2000 look at http://us2.php.net/manual/en/function.memory-get-usage.php#54642
	   if ( substr(PHP_OS,0,3) == 'WIN')
	   {
			   if ( substr( PHP_OS, 0, 3 ) == 'WIN' )
			   {
				   $output = array();
				   exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );

				   return preg_replace( '/[\D]/', '', $output[5] ) * 1024;
			   }
	   }else
	   {
		   //We now assume the OS is UNIX
		   //Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
		   //This should work on most UNIX systems
		   $pid = getmypid();
		   exec("ps -eo%mem,rss,pid | grep $pid", $output);
		   $output = explode("  ", $output[0]);
		   //rss is given in 1024 byte units
		   return $output[1] * 1024;
	   }
   }
}
*/

/**
 * Helper class for representing stats about resources used during a runtime
 *
 * @package library.stat
 * @author Integry Systems
 */
class Stat {

	private $startTime = null;
	private $lastStep = null;
	private $steps = array();

	public function __construct($startCollectingStats = true) {
		if ($startCollectingStats) {
			$this->start();
		}
	}

	/**
	 * Starts collecting statistical information
	 *
	 */
	public function start() {
		$this->startTime = microtime(true);
		$this->lastStep = $this->startTime;
	}

	public function logStep($name)
	{
		$time = microtime(true);
		$elapsedTime = $time - $this->lastStep;
		$elapsedTime = round($elapsedTime, 3);
		if ($elapsedTime < 0.005)
		{
			$elapsedTime = 0;
		}
		$this->steps[$name] = $elapsedTime;
		$this->lastStep = $time;
	}

	/**
	 * Gets amemory usage is memory_limit is enabled
	 *
	 * @return string
	 */
	public function getMemoryUsage() {
		if (function_exists('memory_get_usage')) {
			$memUsageInfo = "";
			if (memory_get_usage() > 1024) {
				$memUsageInfo .= round(memory_get_usage() / 1024, 1). "KB";
				if(memory_get_usage() > 1024*1024) {
					$memUsageInfo .= " | " . round(memory_get_usage() / (1024*1024), 1) . "MB";
				}
			}
			return '<strong>' . memory_get_usage() . "</strong> (bytes) [".$memUsageInfo."]";
		}
		return "not enabled";
	}

	/**
	 * Outputs statistical information (html+js)
	 *
	 */
	public function display() {

		$endTime = microtime(true) - $this->startTime;
		$output ='
		<div id="stat">
			<div id="statButton">
				<a href="#" id="toggler" onclick="if(document.getElementById(\'statData\').style.display==\'block\') { document.getElementById(\'statData\').style.display=\'none\'; document.getElementById(\'toggler\').innerHTML=\'View stats\'; } else { document.getElementById(\'statData\').style.display=\'block\'; document.getElementById(\'toggler\').innerHTML=\'Hide stats\'; } return false; ">
					View stats
				</a>
			</div>
			<div style="display: none;" id="statData">';

		$dbTime = 0;
		$queryCnt = array();
		foreach (ARLogger::$queryTimes as $query)
		{
			if (!isset($queryCnt[$query[0]]))
			{
				$queryCnt[$query[0]] = 0;
			}
			$queryCnt[$query[0]]++;
			$dbTime += $query[1];
		}
		asort($queryCnt);

		$output .= '
			<table>
				<tr>
					<td class="label">Execution time: </td>
					<td><strong>' . $endTime . '</strong> (seconds)</td>
				</tr>
				<tr>
					<td class="label">Memory usage: </td>
					<td>'.$this->getMemoryUsage().'</td>
				</tr>
				<tr>
					<td class="label">Server load: </td>
					<td>' . $this->getServerLoadInfo() . $this->getCpuInfo() . '</td>
				</tr>
				<tr>
					<td class="label">Database queries: </td>
					<td>'.count(ARLogger::$queryTimes).' (' . $dbTime . ')</td>
				</tr>
				<tr>
					<td class="label">ClassLoader time: </td>
					<td>'.$GLOBALS['ClassLoaderTime'].' (' . $GLOBALS['ClassLoaderCount'] . ' calls)</td>
				</tr>
				<tr>
					<td class="label">Execution steps: </td>
					<td>' . $this->getExecutionStepHtml() . '</td>
				</tr>
				<tr>
					<td class="label">Total Includes: </td>
					<td>' . count(get_included_files()) . '</td>
				</tr>
				<tr>
					<td class="label" valign="top">File list:</td>
					<td>
						<ol>';
		foreach (get_included_files() as $value) {
			$output .= "<li>".$value."</li>\n";
		}
		$output .= '
						</ol>
					</td>
				</tr>
			</table>
			</div>
		</div>';
		echo $output;

		echo '<h1>Database Queries</h1><ul id="statQueries">';
		foreach (ARLogger::$queryTimes as $key => $query)
		{
			$q = $query[0];
			$id = md5($q) . '_' . $key;
			echo '<li><strong style="' . ($query[1] > 0.10 ? 'color: red;' : '') . '">' . $query[1] . '</strong> - <span onclick="document.getElementById(\''. $id .'\').style.display = \'\';">' . $query[0] . '</span><div id="' . $id . '" style="display: none;">' . $query[2] . '</div></li>';
		}
		echo '</ul>';
	}

	private function getExecutionStepHtml()
	{
		$output = array('<table>');

		foreach ($this->steps as $name => $time)
		{
			$output[] = '<tr><td>' . $name . '</td><td>' . $time . '</td></tr>';
		}

		$output[] = '</table>';

		return implode("\n", $output);
	}

	private function getServerLoadInfo()
	{
		$uptime = exec('uptime');
		if (preg_match('/load average:.+$/', $uptime, $matches) > 0) {
			return $matches[0];
		}
		else
		{
			return 'unknown';
		}
	}

	private function getCpuInfo()
	{
		// Linux only
		$cnt = 0;
		foreach (explode("\n", shell_exec('cat /proc/cpuinfo')) as $line)
		{
			if (substr($line, 0, 9) == 'processor')
			{
				$cnt++;
			}
		}

		return $cnt ? (' (' . $cnt . ' processor' . ($cnt > 1 ? 's' : '')) . ')' : '';
	}
}

?>