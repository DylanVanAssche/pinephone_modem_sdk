<?php 

$version = "";
$changelogfile = "";
$templatefile = "";
$outputdir = "";
$pkgfile = "";
if (isset($argc) && $argc == 6) {
    $version = $argv[1];
    $changelogfile = $argv[2];
    $templatefile = $argv[3];
    $pkgfile = $argv[4];
    $outputdir = $argv[5];
} else {
	echo "Not enough arguments \n";
    echo "Usage: \n";
    echo "\t ".$argv[0]." [VERSION] [CHANGELOGFILE] [TEMPLATEFILE] [PKGFILE] [OUTPUTDIR]\n";
    exit(1);
}

if ($version == "0.0.0") {
    echo "Invalid version number: ".$version."\n";
    exit(1);
}

$outputxml = $outputdir."/package_".$version.".metainfo.xml";
$fwdata  = array(
    'id' => 'org.postmarketos.pinephone.eg25.firmware',
    'name' => 'PinePhone Modem SDK firmware',
    'branch' => 'FOSS-002',
    'summary' => 'Biktorgj\'s firmware for the Quectel EG25-G modem in the Pine64 PinePhone and Pine64 PinePhone Pro',
    'description' => 'Custom firmware for Quectel\'s EG25-G Modem',
    'url' => 'https://github.com/Biktorgj/pinephone_modem_sdk',
    'license' => 'MIT',
);

$subdevs = array(
    'c33a4560-8681-55b6-bbb6-85258f2de149',
    '1a2996cb-f86e-5583-a464-e1b96e1c6ae9',
    '587bf468-6859-5522-93a7-6cce552a0aa3',
    '22ae45db-f68e-5c55-9c02-4557dca238ec',
);

$keywords = array(
    'quectel',
    'eg25-g',
    'pine64',
    'pinephone',
    'pinephonepro',
);

$categories = array(
    'X-NetworkInterface',
    'X-Device',
    'X-System',
);

$relfile = "package_".$version.".zip";
$relname = "PACKAGE_".$version;
$sha1sum =  hash("sha1", file_get_contents($pkgfile));
$sha256sum =  hash("sha256", file_get_contents($pkgfile));
$changelog = file_get_contents($changelogfile);
$template = file_get_contents($templatefile);
$date = date('Y-m-d');

if (empty($changelog)) {
    echo "Changelog file is empty or doesn't exist\n";
    exit(1);
}

if (empty($template)) {
    echo "XML Template is empty or doesn't exist\n";
    exit(1);
}

if (empty($sha1sum)) {
    echo "Couldn't get SHA1 sum of package file. Does file exist?\n";
    exit(1);
}
if (empty($sha256sum)) {
    echo "Couldn't get SHA256 sum of package file. Does file exist?\n";
    exit(1);
}

$output = str_replace("%%ID%%", $fwdata['id'], $template);
$output = str_replace("%%FWNAME%%", $fwdata['name'], $output);
$output = str_replace("%%FWBRANCH%%", $fwdata['branch'], $output);
$output = str_replace("%%FWSUMMARY%%", $fwdata['summary'], $output);
$output = str_replace("%%FWDESCRIPTION%%", $fwdata['description'], $output);
// Category loop
$categorystr = "";
foreach ($categories as $category) {
    $categorystr.= "<category>".$category."</category>\n";
}
$output = str_replace("%%CATEGORIES%%", $categorystr, $output);

// Subdev loop
$subdevstr = "";
foreach ($subdevs as $device) {
    $subdevstr.="<firmware type=\"flashed\">".$device."</firmware>\n";
}
$output = str_replace("%%SUBDEVS%%", $subdevstr, $output);

$output = str_replace("%%URL%%", $fwdata['url'], $output);
$output = str_replace("%%LICENSE%%", $fwdata['license'], $output);

$output = str_replace("%%RELNAME%%", $relname, $output);
$output = str_replace("%%RELFILE%%", $relfile, $output);


$output = str_replace("%%DATE%%", $date, $output);
$output = str_replace("%%SHA1SUM%%", $sha1sum, $output);
$output = str_replace("%%SHA256SUM%%", $sha256sum, $output);
// UL List of commit history
$changelogarray = explode("\n", $changelog);
$changelogstr = "";
foreach ($changelogarray as $logitem) {
    $changelogstr.="<li>".$logitem."</li>\n";
}
$output = str_replace("%%CHANGELOG%%", $changelogstr, $output);

// Keyword loop
$keywordstr = "";
foreach ($keywords as $keyword) {
    $keywordstr.= "<keyword>".$keyword."</keyword>\n";
}
$output = str_replace("%%KEYWORDS%%", $keywordstr, $output);

if (!file_put_contents($outputxml, $output)) {
    echo "Error creating output file\n";
    exit(1);
}
echo "Done! \n";
?>