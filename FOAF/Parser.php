<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2002 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 3.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/3_0.txt                                   |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Davey Shafik <davey@php.net>                                |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * FOAF Parser
 * @package XML_FOAF
 * @category XML
 */

/**
 * FOAF Parser
 *
 * @package XML_FOAF
 * @author Davey <davey@php.net>
 * @version 0.1
 * @copyright Copyright 2003 Davey Shafik and Synaptic Media. All Rights Reserved.
 * @example docs/examples/example2.php Basic Usage of XML_FOAF_Parser
 * @todo Implement PEAR_Error handling
 */

define('RDFAPI_INCLUDE_DIR', './RAP/');

class XML_FOAF_Parser {
	
		function XML_FOAF_Parser() {
        	require_once RDFAPI_INCLUDE_DIR . 'RdfAPI.php';
        	$this->rdf_parser =& new RdfParser;
        	$this->rdf_model =& $this->rdf_parser->generateModel($this->annotation);
        	$rdql = 'SELECT ?a ?body WHERE (?a <rdf:type> <a:Annotation>), (?a <a:body> ?body) USING a FOR <http://www.w3.org/2000/10/annotation-ns#>, rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#>';
        	$result = $this->rdf_model->rdqlquery($rdql,TRUE);
        	RdqlEngine::writeQueryResultAsHtmlTable($result);
		}
		
		function parseFromURI($uri) {
			
		}
		
		function parseFromFile($file) {
			
		}
		
		function parseFromMem($mem) {
			
		}
		
		function _parse() {
			
		}
		
		function toObject() {
			
		}
		
		function toArray() {
			
		}
		
		function toHTML() {
			
		}		
}

?>