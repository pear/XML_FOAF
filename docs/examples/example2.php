<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>XML_FOAF_Parser Example</title>
		<meta name="Author" content="" />
		<meta name="Keywords" content="" />
		<meta name="Description" content="" />
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
		<style type="text/css">
			table,th,td { border: 1px solid black; }
		</style>
	</head>
	<body>
		<?php
			// Get our FOAF File from $_GET['foaf']
			if (!isset($_REQUEST['foaf'])) {
				echo "<strong>Please enter a FOAF file below";
			} else {
				$foaf = file_get_contents($_REQUEST['foaf']);
	
				// Require the XML_FOAF_Parser class
				require_once 'XML/FOAF/Parser.php';
	
				// Create new Parser object
				$parser = new XML_FOAF_Parser;
	
				// Start of output
				echo '<h1>XML_FOAF_Parser Example</h1>';
				if (isset($_REQUEST['xml'])) {
					echo '<pre>' .htmlentities($foaf). '</pre>';
				}
				
				// Parser our FOAF in $foaf
				$parser->parseFromMem($foaf);
				
				if (isset($_REQUEST['table'])) {
					// Show our FOAF as an HTML table
					echo "<h2>FOAF as HTML Table</h2>";
					echo $parser->toHTML($parser->toArray());
				}
				
				if (isset($_REQUEST['array'])) {
					// Show the contents of the FOAF Data array
					echo "<h2>FOAF as Array</h2>";
					echo "<pre>";
					var_dump($parser->toArray());
					echo "</pre>";
				}
			}
		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<p>
				<label>FOAF File URI: <input type="text" name="foaf" value="<?php echo(@$_REQUEST['foaf']) ?>" /></label>
				<br />
				Show XML: <input type="checkbox" name="xml" value="true" />
				<br />
				Show as HTML Table: <input type="checkbox" name="table" value="true" checked="checked" />
				<br />
				Show as Array: <input type="checkbox" name="array" value="true" />
				<br />
				<input type="submit" value="Parse FOAF!" />
			</p>
		</form>
	</body>
</html>