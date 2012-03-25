<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
XML_FOAF Allows advanced creation and simple parsing of FOAF RDF/XML files.
The FOAF Project can be found at http://www.foaf-project.org -
EOT;

$version = '0.4.0';
$apiver  = '0.4.0';
$state   = 'beta';

$notes = <<<EOT
QA release
PHP5 only
Remove assign-by-ref errors
EOT;

$package = PEAR_PackageFileManager2::importOptions(
    'package.xml',
    array(
    'filelistgenerator' => 'git',
    'changelogoldtonew' => false,
    'simpleoutput'	=> true,
    'baseinstalldir'    => '/',
    'packagefile'       => 'package.xml',
    'packagedirectory'  => '.'));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->clearDeps();

$package->setPackage('XML_FOAF');
$package->setPackageType('php');
$package->setSummary('Provides the ability to manipulate FOAF RDF/XML');
$package->setDescription($desc);
$package->setChannel('pear.php.net');
$package->setLicense('BSD License', 'http://opensource.org/licenses/bsd-license');
$package->setAPIVersion($apiver);
$package->setAPIStability($state);
$package->setReleaseVersion($version);
$package->setReleaseStability($state);
$package->setNotes($notes);
$package->setPhpDep('5.0.0');
$package->setPearinstallerDep('1.0b1');
$package->addIgnore(array('package.php', 'package.xml'));
$package->addPackageDepWithChannel('required', 'RDF', 'pear.php.net', '0.1.0alpha1');
$package->addPackageDepWithChannel('required', 'XML_Beautifier', 'pear.php.net', '0.2.2');
$package->addPackageDepWithChannel('required', 'XML_Tree', 'pear.php.net', '1.1');
$package->addReplacement('XML/FOAF.php', 'package-info', '@package_version@', 'version');
$package->addReplacement('XML/FOAF/Common.php', 'package-info', '@package_version@', 'version');
$package->addReplacement('XML/FOAF/Parser.php', 'package-info', '@package_version@', 'version');
$package->generateContents();

if ($_SERVER['argv'][1] == 'make') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
