<?php

require_once 'PEAR/PackageFileManager2.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$desc = <<<EOT
XML_FOAF Allows advanced creation and simple parsing of FOAF RDF/XML files.
The FOAF Project can be found at http://www.foaf-project.org -
XML_FOAF_Lite will soon follow for simple creation of FOAFs.
EOT;

$version = '0.3.0';
$apiver  = '0.3.0';
$state   = 'alpha';

$notes = <<<EOT
- switch to BSD license
- add package.xml v2 (while retaining package.xml v1)
- PEAR CS cleanup
- Moved to PEAR::RDF as the RDF backend
- Fixed Bug #2991:  URI Validation with Net_URL [davey]
EOT;

$package = PEAR_PackageFileManager2::importOptions(
    'package2.xml',
    array(
    'filelistgenerator' => 'cvs',
    'changelogoldtonew' => false,
    'simpleoutput'	=> true,
    'baseinstalldir'    => 'XML',
    'packagefile'       => 'package2.xml',
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
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.0b1');
$package->addIgnore(array('package.php', 'package.xml', 'package2.php', 'package2.xml'));
$package->addPackageDepWithChannel('required', 'RDF', 'pear.php.net', '0.1.0alpha1');
$package->addPackageDepWithChannel('required', 'XML_Beautifier', 'pear.php.net', '0.2.2');
$package->addPackageDepWithChannel('required', 'XML_Tree', 'pear.php.net', '1.1');
$package->addReplacement('FOAF.php', 'package-info', '@package_version@', 'version');
$package->addReplacement('FOAF/Common.php', 'package-info', '@package_version@', 'version');
$package->addReplacement('FOAF/Parser.php', 'package-info', '@package_version@', 'version');
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
