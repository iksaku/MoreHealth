<?php

$pharName = "MoreHealth.phar";

echo "Creating $pharName...\n";
startTiming("process");
startTiming("makePhar");

if(is_file($pharName)){
	unlink($pharName);
}

$phar = new Phar($pharName);
$phar->setStub('<?php __HALT_COMPILER();');
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->startBuffering();

echo "Adding files...\n";
startTiming("addFiles");
addDir($phar, "src/", "src");
$phar->addFile("plugin.yml", "plugin.yml");
$phar->addFile("LICENSE", "LICENSE");
echo "Done adding files! (" . stopTiming("addFiles") . " s)\n";

echo "Compressing... ";
startTiming("compressPhar");
$phar->compressFiles(\Phar::GZ);
echo "Done! (" . stopTiming("compressPhar") . " s)\n";

$phar->stopBuffering();
echo "Phar creation completed! (" . stopTiming("makePhar") . " s)\n";

echo "Staging phar to Git index... ";
startTiming("stagePhar");
exec("git add $pharName");
echo "Done! (" . stopTiming("stagePhar") . " s)\n";

echo "Phar export process completed! (" . stopTiming("process") . " s)";
exec("exit");

function addDir(\Phar $phar, $rel, $dir){
	$dirCanon = rtrim(str_replace("\\", "/", realpath($dir)), "/\\");
	foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $file){
		if(!is_file($file)){
			continue;
		}
		$relative = str_replace("//", "/", $rel . substr(str_replace("\\", "/", realpath($file)), strlen($dirCanon)));
		$phar->addFile($file, $relative);
	}
}

function startTiming($key){
	global $timings;
	$timings[$key] = -microtime(true);
}
function stopTiming($key){
	global $timings;
	$timings[$key] += microtime(true);
	$ret = $timings[$key];
	unset($timings[$key]);
	return $ret;
}
