<?php
require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '0.2';
$notes = <<<EOT
	* Added XML_FOAF_Parser.
    * Fixed small bugs in XML_FOAF
    * Moved common methods to XML_FOAF_Common
EOT;

$description =<<<EOT
	XML_FOAF Allows advanced creation and simple parsing of FOAF RDF/XML files.
    The FOAF Project can be found at http://www.foaf-project.org -
    XML_FOAF_Lite will soon follow for simple creation of FOAFs.
EOT;

$package = new PEAR_PackageFileManager();

$result = $package->setOptions(array(
    'package'           => 'XML_FOAF',
    'summary'           => 'Provides the ability to manipulate FOAF RDF/XML',
    'description'       => $description,
    'version'           => $version,
    'state'             => 'alpha',
    'license'           => 'PHP License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'package.xml','*test*','*RAP.bak*'),
    'notes'             => $notes,
    'changelogoldtonew' => false,
    'baseinstalldir'    => 'XML',
    'packagedirectory'  => '',
    'dir_roles'         => array('docs' => 'doc',
                                 'docs/examples' => 'doc')
    ));

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}

$package->addDependency('php', '4.3.0', 'ge', 'php', false);
$package->addDependency('XML_Tree', '1.1', 'ge', 'pkg', false);
$package->addDependency('XML_Beautifier', '0.2.2', 'ge', 'pkg', false);

if ($_SERVER['argv'][1] == 'commit') {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
?>