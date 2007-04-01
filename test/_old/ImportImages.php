<style>
    .singleSelect
    {
        background-color: #DDFFFF;
    }
    .multiSelect
    {
        background-color: #FFDDFF;
    }
    .numeric
    {
        background-color: #FFFFDD;
    }
</style>
<?php

set_time_limit(0);

echo "<pre>";
include("../Initialize.php");

if (!function_exists('googleimage_query'))
{
	function googleimage_query($query) 
	{
		return 'http://www.google.com/images?imgsz=xxlarge|xlarge|large|medium&q='.urlencode($query);
	}
	
	function googleimage_rank($s) 
	{
	    global $cache;
	    $head = 'dyn.initialize';
	
	    //remove page header and footer
	    $s = substr($s,strpos($s,$head) + strlen($head));
	
	    $res = explode('dyn.Img',$s);
	    array_shift($res);
		
		$out = array();
	
	    foreach ($res as $key => $rez) 
		{
	        $parts = explode('","', $rez);
	        
	        list($w, $h, $foo) = preg_split('/ x | \- /', $parts[9]);
			$size = $w * $h;
	
			if ($size > 400000)
			{
				continue;
			}
	
	        if (substr($parts[3], 0, 5) == 'https')
	        {
				continue;
			}
	        
			$out[$size] = $parts[3];
	    }
	    
	    krsort($out);
	
	    return $out;
	}
	    
	function getGoogleImageResults($query)
	{
		$context = stream_context_create(array('http' => array ('header'=> 'Cookie: PREF=ID=94c697df959db614:FF=4:LD=en:NR=10:TM=1163874661:LM=1164237577:S=QrKg1hHadXS7yqLf')));
		
		return googleimage_rank(file_get_contents(googleimage_query($query), false, $context));
	}	
	
	function getTrustRank($url)
	{
		global $trusted;
		
		$url = parse_url($url);
		$host = strtolower($url['host']);
		
		if (isset($trusted[$host]))
		{
			return $trusted[$host];
		}	
		else
		{
			return 0;
		}
	}
	
	function trustSort($a, $b)
	{
		global $results;
		
		$ar = getTrustRank($results[$a]);
		$br = getTrustRank($results[$b]);
		
		if ($ar == $br)
		{
			return $a > $b ? -1 : 1;
		}
		else
		{
			return $ar > $br ? -1 : 1;
		}
		
	}
}

ClassLoader::import("application.model.category.*");
ClassLoader::import("application.model.product.*");

ActiveRecordModel::beginTransaction();
ActiveRecordModel::rollback();

include 'trusted_sites.php';
include 'ignored_urls.php';

if (!isset($trusted))
{
	$trusted = array();
}

if (!isset($ignored))
{
	$ignored = array();
}

$cd = getcwd();
if ($_POST)
{
	chdir('..');
	chdir('..');
	print_r($_POST['image']);
	foreach ($_POST['image'] as $productID => $urls)
	{
		//if ($productID < 3466) continue;
		
		$allUrls = unserialize(base64_decode($_POST['urls'][$productID]));
		$allUrls = array_flip($allUrls);
		
		foreach ($urls as $url)
		{
			unset($allUrls[$url]);
			
			$host = parse_url($url);
			$host = strtolower($host['host']);
			
			if (isset($trusted[$host]))
			{
				$trusted[$host]++;
			}	
			else
			{
				$trusted[$host] = 1;
			}					
		
			try
			{
				$image = ProductImage::getNewInstance(Product::getInstanceByID($productID));
				$image->save();
			
				$image->setFile($url);		
			}
			catch (Exception $e)
			{
				$image->delete();
			}
		}
		
		foreach ($allUrls as $url => $key)
		{
			if (isset($ignored[$url]))
			{
				$ignored[$url]++;
			}	
			else
			{
				$ignored[$url] = 1;
			}	
		}
		
			
	}
	
}

chdir($cd);

include 'processed_products.php';

if (!isset($processed))
{
	$processed = array(6666666);
}

$f = new ARSelectFilter();
$f->setCondition(new IsCond(new ARFieldHandle('Product', 'defaultImageID'), 'NULL'));
$f->mergeCondition(new NotInCond(new ARFieldHandle('Product', 'ID'), $processed));
$f->setLimit(50);

$products = ActiveRecordModel::getRecordSet('Product', $f, array('Category'));

echo '<form action="" method="POST">';

foreach ($products as $prod)
{		
	$product = $prod->toArray();
		
	$processed[] = $product['ID'];
	$results = getGoogleImageResults($product['name']);
	
	echo '<h1>' . $product['Category']['name'] . ' / ' . $product['name'] . '</h1>';
			
	$k = 0;
	
	krsort($results);
	uksort($results, 'trustSort');
	
	foreach ($results as $url)
	{	 
	 	if (isset($ignored[$url]) && $ignored[$url] > 10)
	 	{
			continue;
		}
		 
		$k++;
	 	
		echo '<label for="'.$product['ID'] . '_' .$url.'"><img id="' . $product['ID'] . '_' . $k . '" src="' . $url . '"  /></label><input type="checkbox" name="image['.$product['ID'].'][]" value="'.$url.'" id="' . $product['ID'] . '_' .$url.'" style="margin-right: 30px;"  onchange="document.getElementById(\'' . $product['ID'] . '_' . $k . '\').style.border = (this.checked * 5) + \'px solid red\';" />';
	}
	
	echo '<input type="hidden" name="urls['.$product['ID'].']" value="'.base64_encode(serialize($results)).'" />';
		
}

echo '<Br><br><input type="submit" value="Set Images" name="sm" /></form>';

file_put_contents('processed_products.php', '<?php $processed = ' . var_export($processed, true) . '; ?>');
file_put_contents('trusted_sites.php', '<?php $trusted = ' . var_export($trusted, true) . '; ?>');
file_put_contents('ignored_urls.php', '<?php $ignored = ' . var_export($ignored, true) . '; ?>');

//print_r($products);

exit;

?>