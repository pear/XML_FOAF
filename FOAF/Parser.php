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

require_once 'XML/FOAF/Common.php';
 
define('RDFAPI_INCLUDE_DIR', '../../RAP/');
define('XML_FOAF_PERSON', 1);
define('XML_FOAF_GROUP', 2);
define('XML_FOAF_ORGANIZATION', 3);
define('XML_FOAF_AGENT', 4);

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

class XML_FOAF_Parser extends XML_FOAF_Common
{
	
	var $foaf;
	
	var $rdf_parser;
	
	var $rdf_model;
	
	function XML_FOAF_Parser()
	{
    	require_once RDFAPI_INCLUDE_DIR . 'RdfAPI.php';
    	$this->rdf_parser =& new RdfParser;
	}
	
	function parseFromURI($uri)
	{
		$this->parseFromFile($uri);
	}
	
	function parseFromFile($file)
	{
		$this->foaf = file_get_contents($file);
		$this->_parse();
	}
	
	function parseFromMem($mem)
	{
		$this->foaf = $mem;
		$this->_parse();
	}
	
	function _parse() 
	{
		$this->rdf_model =& $this->rdf_parser->generateModel($this->foaf);
		if ($this->isAgent('Person')) {
			$this->agent = XML_FOAF_PERSON;
		} elseif ($this->isAgent('Group')) {
			$this->agent = XML_FOAF_GROUP;
		} elseif ($this->isAgent('Organization')) {
			$this->agent = XML_FOAF_ORGANIZATION;
		} else {
			$this->agent = XML_FOAF_PERSON;
		}
		$this->foaf_data['agent'] = $this->getAgent();
		$this->foaf_data['name'] = $this->getName();
		$this->foaf_data['depiction'] = $this->fetchDepiction();
		$this->foaf_data['fundedby'] = $this->fetchFundedBy();
		$this->foaf_data['logo'] = $this->fetchLogo();
		$this->foaf_data['page'] = $this->fetchPage();
		$this->foaf_data['theme'] = $this->fetchTheme();
		$this->foaf_data['title'] = $this->getTitle();
		//echo $this->rdf_model->writeAsHTML();
	}
	
	function isAgent($type)
	{
		$rdql = 'SELECT ?foaf WHERE '
				.'(?foaf <rdf:type> <foaf:' .ucwords($type). '>) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQuery($rdql);
		$rdql = 'SELECT ?foaf ?foaf_knows ?foaf_agent WHERE '
				.'(?foaf <rdf:type> <foaf:' .ucwords($type). '>) '
				.'(?foaf <foaf:knows> ?foaf_knows) '
				.'(?foaf_knows <foaf:name> ?foaf_agent) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result2 = $this->rdf_model->rdqlQuery($rdql);
		echo "<h2>" .htmlentities($rdql). "</h2>";
		RdqlEngine::writeQueryResultAsHtmlTable($result2);
		if (($result[0]['?foaf']->uri != 'bNode1') || (substr($result[0]['?foaf']->uri,0,4) != 'bNode')) {
			return false;
		} else {
			return true;
		}
	}
	
	function getAgent()
	{
		if (is_null($this->agent)) {
			if ($this->isAgent('Person')) {
				$this->agent = XML_FOAF_PERSON;
			} elseif ($this->isAgent('Group')) {
				$this->agent = XML_FOAF_GROUP;
			} elseif ($this->isAgent('Organization')) {
				$this->agent = XML_FOAF_ORGANIZATION;
			} else {
				$this->agent = XML_FOAF_PERSON;
			}
		}
		
		switch ($this->agent) {
			case XML_FOAF_PERSON:
				return 'Person';
				break;
			case XML_FOAF_GROUP:
				return 'Group';
				break;
			case XML_FOAF_ORGANIZATION:
				return 'Organization';
				break;
			default:
				return 'Person';
		}
	}
	
    function getName($fallback = true)
    {
		$rdql = 'SELECT ?foaf ?name WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
				.'(?foaf <foaf:name> ?name) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQuery($rdql);
		if (!is_null($result[0]['?name']) && ($result[0]['?foaf']->uri == 'bNode1') || (substr($result[0]['?foaf']->uri,0,4) != 'bNode')) {
			return $result[0]['?name']->label;
		} elseif ($fallback == true) {
			$rdql = 'SELECT ?foaf ?first_name WHERE '
					.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
					.'(?foaf <foaf:firstName> ?first_name) '
					.'USING '
					.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
					.', foaf FOR <http://xmlns.com/foaf/0.1/>';
			$result = $this->rdf_model->rdqlQuery($rdql);
			if (!is_null($result[0]['?first_name']) && ($result[0]['?foaf']->uri == 'bNode1') || (substr($result[0]['?foaf']->uri,0,4) != 'bNode')) {
				$first_name = $result[0]['?first_name']->label;
				$rdql = 'SELECT ?foaf ?surname WHERE '
						.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
						.'(?foaf <foaf:surname> ?surname) '
						.'USING '
						.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
						.', foaf FOR <http://xmlns.com/foaf/0.1/>';
				$result = $this->rdf_model->rdqlQuery($rdql);
				if (!is_null($result[0]['?surname']) && ($result[0]['?foaf']->uri == 'bNode1') || (substr($result[0]['?foaf']->uri,0,4) != 'bNode')) {
					$surname = ' ' .$result[0]['?surname']->label;
				} else {
					$surname = '';
				}
				return $first_name . $surname;
			} else {
				return null;
			}
		} else {
			return null;
		}
    }

    function fetchDepiction()
    {
		$rdql = 'SELECT ?foaf ?depictions WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
				.'(?foaf <foaf:depiction> ?depictions) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);			
		while ($result->hasNext()) {
			$depiction = $result->next();
			if (($depiction['?foaf']->uri == 'bNode1') || (substr($depiction['?foaf']->uri,0,4) != 'bNode')) {
				$depictions[] = $depiction['?depictions']->uri;
			}
		}
		return $depictions;
    }

    function fetchFundedBy()
    {
		$rdql = 'SELECT ?foaf ?funded_by WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
				.'(?foaf <foaf:fundedBy> ?funded_by) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$funded_by = $result->next();
			if (($funded_by['?foaf']->uri == 'bNode1') || (substr($funded_by['?foaf']->uri,0,4) != 'bNode')) {
				$funders[] = $funded_by['?funded_by']->uri;
			}
		}
		return $funders;
    }

    function fetchLogo()
    {
		$rdql = 'SELECT ?foaf ?logo WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent(). '>) '
				.'(?foaf <foaf:logo> ?logo) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$logo = $result->next();
			if (($logo['?foaf']->uri == 'bNode1') || (substr($logo['?foaf']->uri,0,4) != 'bNode')) {
				$logos[] = $logo['?logo']->uri;
			}
		}
		return $logos;
    }

    function fetchPage()
    {
		$rdql = 'SELECT ?foaf ?document WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:page> ?document) '
				.'(?document <rdf:type> <foaf:Document>) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$page = $result->next();
			if (($page['?foaf']->uri == 'bNode1') || (substr($page['?foaf']->uri,0,4) != 'bNode')) {
				$uri = $page['?document']->uri;
				$pages[$uri] = array();
			}
		}
		$rdql = 'SELECT ?foaf ?document ?title WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:page> ?document) '
				.'(?document <rdf:type> <foaf:Document>) '
				.'(?document <dc:title> ?title) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>'
				.', dc FOR <http://purl.org/dc/elements/1.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$page = $result->next();
			if (($page['?foaf']->uri == 'bNode1') || (substr($page['?foaf']->uri,0,4) != 'bNode')) {
				$uri = $page['?document']->uri;
				$pages[$uri]['title'] = $page['?title']->label;
			}
		}
		$rdql = 'SELECT ?foaf ?document ?description WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:page> ?document) '
				.'(?document <rdf:type> <foaf:Document>) '
				.'(?document <dc:description> ?description) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>'
				.', dc FOR <http://purl.org/dc/elements/1.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$page = $result->next();
			if (($page['?foaf']->uri == 'bNode1') || (substr($page['?foaf']->uri,0,4) != 'bNode')) {
				$uri = $page['?document']->uri;
				$pages[$uri]['description'] = $page['?description']->label;
			}
		}
		
		$i = 0;
		foreach ($pages as $uri=>$values) {
			$pages[$i]['uri'] = $uri;
			foreach ($values as $param=>$value) {
				$pages[$i][$param] = $value;
			}
			unset($pages[$uri]);
			$i += 1;
		}
		return $pages;
    }

    function fetchTheme()
    {
		$rdql = 'SELECT ?foaf ?theme WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:theme> ?theme) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQueryAsIterator($rdql);
		while ($result->hasNext()) {
			$theme = $result->next();
			if (($theme['?foaf']->uri == 'bNode1') || (substr($theme['?foaf']->uri,0,4) != 'bNode')) {
				$themes[] = $theme['?theme']->uri;
			}
		}
		return $themes;
    }


    function getTitle()
    {
		$rdql = 'SELECT ?foaf ?title WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:title> ?title) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		$result = $this->rdf_model->rdqlQuery($rdql);
		if (($result[0]['?foaf']->uri == 'bNode1') || (substr($result[0]['?foaf']->uri,0,4) != 'bNode')) {
			return $result[0]['?title']->label;
		}
    }

    function fetchNick()
    {
		$rdql = 'SELECT ?foaf ?nick WHERE '
				.'(?foaf <rdf:type> <foaf:' .$this->getAgent() .'>) '
				.'(?foaf <foaf:nick> ?nick) '
				.'USING '
				.'rdf FOR <http://www.w3.org/1999/02/22-rdf-syntax-ns#> '
				.', foaf FOR <http://xmlns.com/foaf/0.1/>';
		while ($result->hasNext()) {
			$nick = $result->next();
			if (($nick['?foaf']->uri == 'bNode1') || (substr($nick['?foaf']->uri,0,4) != 'bNode')) {
				$nicks[] = $nick['?nick']->label;
			}
		}
		return $nicks;
    }

    function setGivenName($given_name)
    {

    }

    function addPhone($phone)
    {

    }

    function addMbox($mbox,$sha1 = false,$is_sha1_hash = false)
    {

    }

    function addMboxSha1Sum($mbox,$is_sha1_sum = false)
    {

    }

    function setGender($gender)
    {

    }

    function addJabberID($jabber_id)
    {

    }

    function addAimChatID($aim_chat_id)
    {

    }

    function addIcqChatID($icq_chat_id)
    {

    }

    function addYahooChatID($yahoo_chat_id)
    {

    }

    function addMsnChatID($msn_chat_id)
    {

    }

    function addOnlineAccount($account_name,$account_service_homepage = null,$account_type = null)
    {

    }

    function addOnlineChatAccount($account_name,$account_service_homepage)
    {

    }

    function addOnlineGamingAccount($account_name,$account_service_homepage)
    {

    }

    function addOnlineEcommerceAccount($account_name,$account_service_homepage)
    {

    }

    function addHomepage($uri)
    {

    }

    function addWeblog($uri)
    {

    }

    function addMade($uri)
    {

    }

    /* foaf:Person */

    function setGeekcode($geek_code)
    {

    }

    function setFirstName($first_name)
    {

    }

    function setSurname($surname)
    {

    }

    function setFamilyName($family_name)
    {

    }

    function setPlan($plan)
    {

    }

    function addImg($uri)
    {

    }

    function addMyersBriggs($myers_briggs)
    {

    }

    function addWorkplaceHomepage($uri)
    {

    }

    function addWorkInfoHomepage($uri)
    {

    }

    function addSchoolHomepage($uri)
    {

    }

    function addPublications($uri)
    {

    }

    function addCurrentProject($uri)
    {

    }

    function addPastProject($uri)
    {

    }

    function setBasedNear($geo_lat,$geo_long)
    {

    }

    /* foaf:Person && foaf:Group */

    function addInterest($uri)
    {

    }

    /* foaf:Group */

    function &addMember(&$foaf_agent)
    {

    }

    function setMembershipClass(&$membership_class)
    {

    }

    /* end of Agent only methods */

    function addSeeAlso($uri)
    {

    }

    function &addKnows(&$foaf_agent)
    {

    }

	function toObject()
	{
		$foaf_object = new stdClass();
		foreach ($this->foaf_data as $key=>$value) {
			$foaf_object->$key = $value;
		}
		return $foaf_object;
	}
	
	function toArray()
	{
		return $this->foaf_data;
	}
	
	function toHTML()
	{
		
	}
}

?>