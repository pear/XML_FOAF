<?php
require_once 'PEAR/PackageFileManager.php';
require_once 'Console/Getopt.php';

$version = '0.3.0';
$notes = <<<EOT
- switch to BSD license
- add package.xml v2 (while retaining package.xml v1)
- PEAR CS cleanup
- Moved to PEAR::RDF as the RDF backend
- Fixed Bug #2991:  URI Validation with Net_URL [davey]
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
    'license'           => 'BSD License',
    'filelistgenerator' => 'cvs',
    'ignore'            => array('package.php', 'package.xml'),
    'notes'             => $notes,
    'changelogoldtonew' => false,
    'baseinstalldir'    => 'XML',
    'packagedirectory'  => '',
    'dir_roles'         => array('docs/examples' => 'doc')
    ));

$package->addDependency('php', '4.3.0', 'ge', 'php', false);
$package->addDependency('PEAR', '1.0b1', 'ge', 'pkg', false);
$package->addDependency('RDF', true, 'has', 'pkg', false);
$package->addDependency('XML_Tree', '1.1', 'ge', 'pkg', false);
$package->addDependency('XML_Beautifier', '0.2.2', 'ge', 'pkg', false);

if (isset($_GET['make']) || (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'make')) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    echo $result->getMessage();
    die();
}
?>
