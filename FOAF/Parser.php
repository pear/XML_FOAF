<?php
/* vim: get expandtab tabstop=4 softtabstop=4 shiftwidth=4: */
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

define('RDFAPI_INCLUDE_DIR', 'XML/FOAF/RAP/');
define('XML_FOAF_NS','http://xmlns.com/foaf/0.1/');
define('XML_FOAF_DC_NS','http://purl.org/dc/elements/1.1/');
define('XML_FOAF_RDF_NS','http://www.w3.org/1999/02/22-rdf-syntax-ns#');
define('XML_FOAF_RDF_SCHEMA_NS', 'http://www.w3.org/2000/01/rdf-schema#');
define('XML_FOAF_PERSON', 1);
define('XML_FOAF_GROUP', 2);
define('XML_FOAF_ORGANIZATION', 3);
define('XML_FOAF_AGENT', 4);

/**
 * FOAF Parser
 *
 * Individual element parsers that start with _fetch will return multiple elements
 * into the result Array, those that start with _get will return only a single element
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
    /**
     * @var string Original FOAF file
     */

    var $foaf_xml = '';

    /**
     * @var array FOAF data as Array
     */

    var $foaf_data;

    /**
     * @var object MemModel of FOAF
     */

    var $foaf;

    /**
     * @var object Instance of the RAP RDF_Parser
     */

    var $rdf_parser;

    /**
     * @var array Nodes assumed to be primary foaf:Agents
     */

    var $agent_nodes;

    /**
     * @var array Nodes found in <foaf:knows>
     */

    var $known_nodes;

    /**
     * XML_FOAF_Parser Constructor
     */

    function __construct() {
        require_once RDFAPI_INCLUDE_DIR . 'RdfAPI.php';
        $this->rdf_parser =& new RdfParser;
    }

    /**
     * XML_FOAF_Parser PHP4 Compatible Constructor
     */

    function XML_FOAF_Parser()
    {
        $this->__construct();
    }

    /**
     * Parse a FOAF at the specified URI
     *
     * @param $uri string URI for a FOAF file
     * @access public
     * @return void
     */

    function parseFromURI($uri)
    {
        $this->parseFromFile($uri);
    }

    /**
     * Parse a FOAF in the specified File
     *
     * @param $file string File Path for a FOAF file
     * @param $use_include_path Whether to look for the file in the php include_path
     * @access public
     * @return void
     */

    function parseFromFile($file,$use_include_path = false)
    {
        $this->foaf = file_get_contents($file,$use_include_path);
        $this->_parse();
    }

    /**
     * Parse a FOAF contained in the specified variable
     *
     * @param $mem string Variable holding a FOAF file's XML
     * @access public
     * @return void
     */

    function parseFromMem($mem)
    {
        $this->foaf = $mem;
        $this->_parse();
    }

    /**#@+
     * @access private
     * @return void
     */

    /**
     * Calls all the seperate property parsers
     */

    function _parse()
    {
        $this->foaf =& $this->rdf_parser->generateModel($this->foaf);
        if (isset($_GET['dev'])) { $this->foaf->writeAsHTMLTable(); }
        $this->foaf_data = $this->_fetchAgent();
        $this->_fetchAimChatID();
        $this->_fetchCurrentProject();
        $this->_fetchDcDescription();
        $this->_fetchDcTitle();
        $this->_fetchDepiction();
        $this->_fetchFundedBy();
        $this->_fetchHoldsAccount();
        $this->_fetchHomepage();
        $this->_fetchIcqChatID();
        $this->_fetchImg();
        $this->_fetchInterest();
        $this->_fetchJabberID();
        $this->_fetchLogo();
        $this->_fetchMade();
        $this->_fetchMbox();
        $this->_fetchMboxSha1Sum();
        $this->_fetchMember();
        $this->_fetchMsnChatID();
        $this->_fetchMyersBriggs();
        $this->_fetchNick();
        $this->_fetchPage();
        $this->_fetchPastProject();
        $this->_fetchPhone();
        $this->_fetchPublication();
        $this->_fetchSchoolHomepage();
        $this->_fetchSeeAlso();
        $this->_fetchTheme();
        $this->_fetchWeblog();
        $this->_fetchWorkInfoHomepage();
        $this->_fetchWorkplaceHomepage();
        $this->_fetchYahooChatID();
        $this->_getBasedNear();
        $this->_getFamilyName();
        $this->_getFirstName();
        $this->_getGeekcode();
        $this->_getGender();
        $this->_getGivenName();
        $this->_getMembershipClass();
        $this->_getName();
        $this->_getPlan();
        $this->_getSurname();
        $this->_getTitle();
    }

    /**
     * Parses our the foaf:Agents
     *
     * Looks for all foaf:Agents (foaf:Person,foaf:Group,foaf:Organzation and foaf:Agent)
     * and decides which are the primary agents (who/what the FOAF is about) and
     * which are only known by the primary agents
     *
     * @access private
     * @return void
     */

    function _fetchAgent()
    {
        $person_resource = new Resource(XML_FOAF_NS . 'Person');
        $persons = $this->foaf->find(null,null,$person_resource);
        $group_resource = new Resource(XML_FOAF_NS . 'Group');
        $groups = $this->foaf->find(null,null,$group_resource);
        $organization_resource = new Resource(XML_FOAF_NS . 'Organization');
        $organizations = $this->foaf->find(null,null,$organization_resource);
        $agent_resource = new Resource(XML_FOAF_NS . 'Agent');
        $agents = $this->foaf->find(null,null,$agent_resource);
        $agents->addModel($persons);
        $agents->addModel($groups);
        $agents->addModel($organizations);
        $knows_resource = new Resource(XML_FOAF_NS . 'knows');
        $knows = $this->foaf->find(null,$knows_resource,null);
        $i = 0;
        $agent_nodes = array();
        $known_nodes = array();
        foreach ($agents->triples as $agent) {
            $agent_nodes[$agent->subj->uri] = $agent->obj->uri;
            $i += 1;
            foreach ($knows->triples as $know) {
                if ($agent->subj->uri == $know->obj->uri) {
                    $agent_type = pathinfo($agent->obj->uri);
                    $agent_type = $agent_type['basename'];
                    $known = array('node' => $know->obj->uri, 'type' => $agent_type);
                    $known_nodes["{$know->subj->uri}"][] = $known;
                    $this->known_nodes[] = $know->obj->uri;
                    unset($agent_nodes[$agent->subj->uri]);
                }
            }
        }

        $agents = array();
        $i = 0;
        foreach ($agent_nodes as $node => $agent_type) {
            $agent_type = pathinfo($agent_type);
            $agent_type = $agent_type['basename'];
            $agents[$i] = array ('node' => $node, 'type' => $agent_type);
            $this->agent_nodes[] = $node;
            if (isset($known_nodes[$node])) {
                foreach ($known_nodes[$node] as $knows) {
                    $agents[$i]['knows'][] = $knows;
                }
            }
            $i += 1;
        }
        if (!is_array($this->known_nodes)) {
            $this->known_nodes = array();
        }
        return $agents;
    }

    /**
     * Finds the foaf:name's and inserts them into the result array
     *
     * If more than one foaf:name is found for a single foaf:Agent, the
     * last found will be the one shown in the result
     */

    function _getName()
    {
        $name_resource = new Resource(XML_FOAF_NS . 'name');
        $names = $this->foaf->find(null,$name_resource,null);
        foreach ($names->triples as $name) {
            if (in_array($name->subj->uri,$this->agent_nodes)) {
                foreach ($this->foaf_data as $key => $agent) {
                    if (isset($agent['node']) && ($agent['node'] == $name->subj->uri)) {
                        $this->foaf_data[$key]['name'] = $name->obj->label;
                        break;
                    }
                }
            } elseif (in_array($name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $knows_key => $knows) {
                        if (isset($knows['node']) && ($knows['node'] == $name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$knows_key]['name'] = $name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:depiction's and inserts them into the result Array
     */

    function _fetchDepiction()
    {
        $depictions_resource = new Resource(XML_FOAF_NS . 'depiction');
        $depictions = $this->foaf->find(null,$depictions_resource,null);
        foreach ($depictions->triples as $depiction) {
            if (in_array($depiction->subj->uri,$this->agent_nodes)) {
                foreach ($this->foaf_data as $key => $agent) {
                    if (isset($agent['node']) && ($agent['node'] == $depiction->subj->uri)) {
                        $this->foaf_data[$key]['depiction'][] = $depiction->obj->uri;
                        break;
                    }
                }
            } elseif (in_array($depiction->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $depiction_key => $depiction_array) {
                        if (isset($depiction_array['node']) && ($depiction_array['node'] == $depiction->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$depiction_key]['depiction'][] = $depiction->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:fundedBy's and inserts them into the result Array
     */

    function _fetchFundedBy()
    {
        $funded_by_resource = new Resource(XML_FOAF_NS . 'funded_by');
        $funded_bys = $this->foaf->find(null,$funded_by_resource,null);
        foreach ($funded_bys->triples as $funded_by) {
            if (in_array($funded_by->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $agent) {
                    if ($agent == $funded_by->subj->uri) {
                        $this->foaf_data[$key]['fundedby'][] = $funded_by->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($funded_by->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $funded_by_key => $funded_by_array) {
                        if (isset($funded_by_array['node']) && ($funded_by_array['node'] == $funded_by->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$funded_by_key]['fundedby'][] = $funded_by->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:logo's and inserts them into the result Array
     */

    function _fetchLogo()
    {
        $logo_resource = new Resource(XML_FOAF_NS . 'logo');
        $logos = $this->foaf->find(null,$logo_resource,null);
        foreach ($logos->triples as $logo) {
            if (in_array($logo->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $agent) {
                    if ($agent == $logo->subj->uri) {
                        $this->foaf_data[$key]['logo'][] = $logo->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($logo->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $logo_key => $logo_array) {
                        if (isset($logo_array['node']) && ($logo_array['node'] == $logo->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$logo_key]['logo'][] = $logo->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:page's and inserts them into the result Array
     */

    function _fetchPage()
    {
        $page_resource = new Resource(XML_FOAF_NS . 'page');
        $pages = $this->foaf->find(null,$page_resource,null);
        foreach ($pages->triples as $page) {
            if (in_array($page->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $page->subj->uri) {
                        $this->foaf_data[$key]['page'][] = $page->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($page->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $page_key => $page_array) {
                        if (isset($page_array['node']) && ($page_array['node'] == $page->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$page_key]['page'][] = $page->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:theme's and inserts them into the result Array
     */

    function _fetchTheme()
    {
        $theme_resource = new Resource(XML_FOAF_NS . 'theme');
        $themes = $this->foaf->find(null,$theme_resource,null);
        foreach ($themes->triples as $theme) {
            if (in_array($theme->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $theme->subj->uri) {
                        $this->foaf_data[$key]['theme'][] = $theme->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($theme->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $theme_key => $theme_array) {
                        if (isset($theme_array['node']) && ($theme_array['node'] == $theme->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$theme_key]['theme'][] = $theme->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all the foaf:title and inserts them into the result Array
     *
     * If more than one foaf:title is found for one foaf:Agent the
     * last one found is insert into the result
     */

    function _getTitle()
    {
        $title_resource = new Resource(XML_FOAF_NS . 'title');
        $titles = $this->foaf->find(null,$title_resource,null);
        foreach ($titles->triples as $title) {
            if (in_array($title->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $title->subj->uri) {
                        $this->foaf_data[$key]['title'] = $title->obj->label;
                    }
                    break;
                }
            } elseif (in_array($title->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $title_key => $title_array) {
                        if (isset($title_array['node']) && ($title_array['node'] == $title->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$title_key]['title'] = $title->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:nick's and inserts them into the result Array
     */

    function _fetchNick()
    {
        $nick_resource = new Resource(XML_FOAF_NS . 'nick');
        $nicks = $this->foaf->find(null,$nick_resource,null);
        foreach ($nicks->triples as $nick) {
            if (in_array($nick->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $nick->subj->uri) {
                        $this->foaf_data[$key]['nick'][] = $nick->obj->label;
                    }
                    break;
                }
            } elseif (in_array($nick->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $nick_key => $nick_array) {
                        if (isset($nick_array['node']) && ($nick_array['node'] == $nick->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$nick_key]['nick'][] = $nick->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:givenName's and inserts them into the result Array
     *
     * If more than one foaf:givenName is found for a single foaf:Agent, the
     * last one found is inserted into the result array
     */

    function _getGivenName()
    {
        $given_name_resource = new Resource(XML_FOAF_NS . 'givenName');
        $given_names = $this->foaf->find(null,$given_name_resource,null);
        foreach ($given_names->triples as $given_name) {
            if (in_array($given_name->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $given_name->subj->uri) {
                        $this->foaf_data[$key]['givenname'] = $given_name->obj->label;
                    }
                    break;
                }
            } elseif (in_array($given_name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $given_name_key => $given_name_array) {
                        if (isset($given_name_array['node']) && ($given_name_array['node'] == $given_name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$given_name_key]['givenname'] = $given_name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:phone's and inserts them into the result Array
     */

    function _fetchPhone()
    {
        $phone_resource = new Resource(XML_FOAF_NS . 'phone');
        $phones = $this->foaf->find(null,$phone_resource,null);
        foreach ($phones->triples as $phone) {
            if (in_array($phone->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $phone->subj->uri) {
                        $this->foaf_data[$key]['phone'][] = $phone->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($phone->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $phone_key => $phone_array) {
                        if (isset($phone_array['node']) && ($phone_array['node'] == $phone->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$phone_key]['phone'][] = $phone->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:mbox's and inserts them into the result Array
     */

    function _fetchMbox()
    {
        $mbox_resource = new Resource(XML_FOAF_NS . 'mbox');
        $mboxs = $this->foaf->find(null,$mbox_resource,null);
        foreach ($mboxs->triples as $mbox) {
            if (in_array($mbox->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $mbox->subj->uri) {
                        $this->foaf_data[$key]['mbox'][] = $mbox->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($mbox->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $mbox_key => $mbox_array) {
                        if (isset($mbox_array['node']) && ($mbox_array['node'] == $mbox->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$mbox_key]['mbox'][] = $mbox->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:mbox_sha1sum's and inserts them into the result Array
     */

    function _fetchMboxSha1Sum()
    {
        $mbox_sha1sum_resource = new Resource(XML_FOAF_NS . 'mbox_sha1sum');
        $mbox_sha1sums = $this->foaf->find(null,$mbox_sha1sum_resource,null);
        foreach ($mbox_sha1sums->triples as $mbox_sha1sum) {
            if (in_array($mbox_sha1sum->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $mbox_sha1sum->subj->uri) {
                        $this->foaf_data[$key]['mbox_sha1sum'][] = $mbox_sha1sum->obj->label;
                    }
                    break;
                }
            } elseif (in_array($mbox_sha1sum->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $mbox_sha1sum_key => $mbox_sha1sum_array) {
                        if (isset($mbox_sha1sum_array['node']) && ($mbox_sha1sum_array['node'] == $mbox_sha1sum->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$mbox_sha1sum_key]['mbox_sha1sum'][] = $mbox_sha1sum->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:gender's and inserts them into the result Array
     *
     * If more than one foaf:gender is found for a single foaf:Agent, the
     * last found is inserted into the result Array.
     */

    function _getGender()
    {
        $gender_resource = new Resource(XML_FOAF_NS . 'gender');
        $genders = $this->foaf->find(null,$gender_resource,null);
        foreach ($genders->triples as $gender) {
            if (in_array($gender->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $gender->subj->uri) {
                        $this->foaf_data[$key]['gender'] = $gender->obj->label;
                    }
                    break;
                }
            } elseif (in_array($gender->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $gender_key => $gender_array) {
                        if (isset($gender_array['node']) && ($gender_array['node'] == $gender->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$gender_key]['gender'][] = $gender->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:jabberID's and inserts them into the result Array
     */

    function _fetchJabberID()
    {
        $jabber_id_resource = new Resource(XML_FOAF_NS . 'jabberID');
        $jabber_ids = $this->foaf->find(null,$jabber_id_resource,null);
        foreach ($jabber_ids->triples as $jabber_id) {
            if (in_array($jabber_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $jabber_id->subj->uri) {
                        $this->foaf_data[$key]['jabberid'][] = $jabber_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($jabber_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $jabber_id_key => $jabber_id_array) {
                        if (isset($jabber_id_array['node']) && ($jabber_id_array['node'] == $jabber_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$jabber_id_key]['jabberid'][] = $jabber_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:aimChatID's and inserts them into the result Array
     */

    function _fetchAimChatID()
    {
        $aim_chat_id_resource = new Resource(XML_FOAF_NS . 'aimChatID');
        $aim_chat_ids = $this->foaf->find(null,$aim_chat_id_resource,null);
        foreach ($aim_chat_ids->triples as $aim_chat_id) {
            if (in_array($aim_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $aim_chat_id->subj->uri) {
                        $this->foaf_data[$key]['aimchatid'][] = $aim_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($aim_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $aim_chat_id_key => $aim_chat_id_array) {
                        if (isset($aim_chat_id_array['node']) && ($aim_chat_id_array['node'] == $aim_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$aim_chat_id_key]['aimchatid'][] = $aim_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:icqChatID's and inserts them into the result Array
     */

    function _fetchIcqChatID()
    {
        $icq_chat_id_resource = new Resource(XML_FOAF_NS . 'icqChatID');
        $icq_chat_ids = $this->foaf->find(null,$icq_chat_id_resource,null);
        foreach ($icq_chat_ids->triples as $icq_chat_id) {
            if (in_array($icq_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $icq_chat_id->subj->uri) {
                        $this->foaf_data[$key]['icqchatid'][] = $icq_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($icq_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $icq_chat_id_key => $icq_chat_id_array) {
                        if (isset($icq_chat_id_array['node']) && ($icq_chat_id_array['node'] == $icq_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$icq_chat_id_key]['icqchatid'][] = $icq_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:yahooChatID's and inserts them into the result Array
     */

    function _fetchYahooChatID()
    {
        $yahoo_chat_id_resource = new Resource(XML_FOAF_NS . 'yahooChatID');
        $yahoo_chat_ids = $this->foaf->find(null,$yahoo_chat_id_resource,null);
        foreach ($yahoo_chat_ids->triples as $yahoo_chat_id) {
            if (in_array($yahoo_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $yahoo_chat_id->subj->uri) {
                        $this->foaf_data[$key]['yahoochatid'][] = $yahoo_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($yahoo_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $yahoo_chat_id_key => $yahoo_chat_id_array) {
                        if (isset($yahoo_chat_id_array['node']) && ($yahoo_chat_id_array['node'] == $yahoo_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$yahoo_chat_id_key]['yahoochatid'][] = $yahoo_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:msnChatID's and inserts them into the result Array
     */

    function _fetchMsnChatID()
    {
        $msn_chat_id_resource = new Resource(XML_FOAF_NS . 'msnChatID');
        $msn_chat_ids = $this->foaf->find(null,$msn_chat_id_resource,null);
        foreach ($msn_chat_ids->triples as $msn_chat_id) {
            if (in_array($msn_chat_id->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $msn_chat_id->subj->uri) {
                        $this->foaf_data[$key]['msnchatid'][] = $msn_chat_id->obj->label;
                    }
                    break;
                }
            } elseif (in_array($msn_chat_id->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $msn_chat_id_key => $msn_chat_id_array) {
                        if (isset($msn_chat_id_array['node']) && ($msn_chat_id_array['node'] == $msn_chat_id->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$msn_chat_id_key]['msnchatid'][] = $msn_chat_id->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:onlineAccount's and inserts them into the result Array
     */

    function _fetchHoldsAccount()
    {
        $holds_account_resource = new Resource(XML_FOAF_NS . 'holdsAccount');
        $holds_accounts = $this->foaf->find(null,$holds_account_resource,null);
        $account_name_resource = new Resource(XML_FOAF_NS . 'accountName');
        $account_names = $this->foaf->find(null,$account_name_resource,null);
        $account_service_homepage_resource = new Resource(XML_FOAF_NS . 'accountServiceHomepage');
        $account_service_homepages = $this->foaf->find(null,$account_service_homepage_resource,null);
        $rdf_type_resource = new Resource(XML_FOAF_RDF_NS . 'type');
        $rdf_types = $this->foaf->find(null,$rdf_type_resource,null);
        foreach ($holds_accounts->triples as $holds_account) {
            foreach ($account_names->triples as $account_name) {
                if ($account_name->subj->uri == $holds_account->obj->uri) {
                    $accounts[$account_name->subj->uri]['accountname'] = $account_name->obj->label;
                }
            }
            foreach ($account_service_homepages->triples as $account_service_homepage) {
                if ($account_service_homepage->subj->uri == $holds_account->obj->uri) {
                    $accounts[$account_service_homepage->subj->uri]['accountservicehompage'] = $account_service_homepage->obj->uri;
                }
            }
            foreach ($rdf_types->triples as $rdf_type) {
                if ($rdf_type->subj->uri == $holds_account->obj->uri) {
                    $account_type = pathinfo($rdf_type->obj->uri);
                    $accounts[$rdf_type->subj->uri]['type'] = $account_type['basename'];
                }
            }
        }

        $online_account_resource = new Resource(XML_FOAF_NS . 'OnlineAccount');
        $online_accounts = $this->foaf->find(null,null,$online_account_resource);
        $online_chat_account_resource = new Resource(XML_FOAF_NS .'OnlineChatAccount');
        $online_chat_accounts = $this->foaf->find(null,null,$online_chat_account_resource);
        $online_gaming_account_resource = new Resource(XML_FOAF_NS . 'OnlineGamingAccount');
        $online_gaming_accounts = $this->foaf->find(null,null,$online_gaming_account_resource);
        $online_ecommerce_account_resource = new Resource(XML_FOAF_NS . 'OnlineEcommerceAccount');
        $online_ecommerce_accounts = $this->foaf->find(null,null,$online_ecommerce_account_resource);

        foreach ($online_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineAccount';
            }
        }

        foreach ($online_chat_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineChatAccount';
            }
        }

        foreach ($online_gaming_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineGamingAccount';
            }
        }

        foreach ($online_ecommerce_accounts->triples as $account_type) {
            if (!isset($accounts[$account_type->subj->uri]['type'])) {
                $accounts[$account_type->subj->uri]['type'] = 'OnlineEcommerceAccount';
            }
        }

        foreach ($holds_accounts->triples as $holds_account) {
            $agent_accounts[$holds_account->subj->uri][] = $accounts[$holds_account->obj->uri];
        }

        if (isset($agent_accounts)) {
            foreach ($agent_accounts as $node => $accounts) {
                if (in_array($node,$this->agent_nodes)) {
                    foreach ($this->foaf_data as $key => $value) {
                        if ($value['node'] == $node) {
                            $this->foaf_data[$key]['holdsaccount'] = $agent_accounts[$node];
                        }
                        break;
                    }
                } elseif (in_array($node,$this->known_nodes)) {
                    foreach ($this->foaf_data as $agent_key => $agent) {
                        foreach ($agent['knows'] as $holds_account_key => $holds_account_array) {
                            if (isset($holds_account_array['node']) && ($holds_account_array['node'] == $node)) {
                                $this->foaf_data[$agent_key]['knows'][$holds_account_key]['holdsaccount'] = $agent_accounts[$node];
                                break 2;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:homepage's and inserts them into the result Array
     */

    function _fetchHomepage()
    {
        $homepage_resource = new Resource(XML_FOAF_NS . 'homepage');
        $homepages = $this->foaf->find(null,$homepage_resource,null);
        foreach ($homepages->triples as $homepage) {
            if (in_array($homepage->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $homepage->subj->uri) {
                        $this->foaf_data[$key]['homepage'][] = $homepage->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($homepage->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $homepage_key => $homepage_array) {
                        if (isset($homepage_array['node']) && ($homepage_array['node'] == $homepage->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$homepage_key]['homepage'][] = $homepage->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:weblog's and inserts them into the result Array
     */

    function _fetchWeblog()
    {
        $weblog_resource = new Resource(XML_FOAF_NS . 'weblog');
        $weblogs = $this->foaf->find(null,$weblog_resource,null);
        foreach ($weblogs->triples as $weblog) {
            if (in_array($weblog->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $weblog->subj->uri) {
                        $this->foaf_data[$key]['weblog'][] = $weblog->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($weblog->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $weblog_key => $weblog_array) {
                        if (isset($weblog_array['node']) && ($weblog_array['node'] == $weblog->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$weblog_key]['weblog'][] = $weblog->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:made's and inserts them into the result Array
     */

    function _fetchMade()
    {
        $made_resource = new Resource(XML_FOAF_NS . 'made');
        $mades = $this->foaf->find(null,$made_resource,null);
        foreach ($mades->triples as $made) {
            if (in_array($made->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $made->subj->uri) {
                        $this->foaf_data[$key]['made'][] = $made->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($made->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $made_key => $made_array) {
                        if (isset($made_array['node']) && ($made_array['node'] == $made->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$made_key]['made'][] = $made->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /* foaf:Person */

    /**
     * Finds all foaf:geekcode's and inserts them into the result Array
     *
     * If more than one foaf:geekcode is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getGeekcode()
    {
        $geekcode_resource = new Resource(XML_FOAF_NS . 'geekcode');
        $geekcodes = $this->foaf->find(null,$geekcode_resource,null);
        foreach ($geekcodes->triples as $geekcode) {
            if (in_array($geekcode->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $geekcode->subj->uri) {
                        $this->foaf_data[$key]['geekcode'] = $geekcode->obj->label;
                    }
                    break;
                }
            } elseif (in_array($geekcode->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $geekcode_key => $geekcode_array) {
                        if (isset($geekcode_array['node']) && ($geekcode_array['node'] == $geekcode->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$geekcode_key]['geekcode'] = $geekcode->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:firstName's and inserts them into the result Array
     *
     * If more than one foaf:firstName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFirstName()
    {
        $first_name_resource = new Resource(XML_FOAF_NS . 'firstName');
        $first_names = $this->foaf->find(null,$first_name_resource,null);
        foreach ($first_names->triples as $first_name) {
            if (in_array($first_name->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $first_name->subj->uri) {
                        $this->foaf_data[$key]['firstname'] = $first_name->obj->label;
                    }
                    break;
                }
            } elseif (in_array($first_name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $first_name_key => $first_name_array) {
                        if (isset($first_name_array['node']) && ($first_name_array['node'] == $first_name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$first_name_key]['firstname'] = $first_name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:surname's and inserts them into the result Array
     *
     * If more than one foaf:surname is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getSurname()
    {
        $surname_resource = new Resource(XML_FOAF_NS . 'surname');
        $surnames = $this->foaf->find(null,$surname_resource,null);
        foreach ($surnames->triples as $surname) {
            if (in_array($surname->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $surname->subj->uri) {
                        $this->foaf_data[$key]['surname'] = $surname->obj->label;
                    }
                    break;
                }
            } elseif (in_array($surname->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $surname_key => $surname_array) {
                        if (isset($surname_array['node']) && ($surname_array['node'] == $surname->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$surname_key]['surname'] = $surname->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:familyName's and inserts them into the result Array
     *
     * If more than one foaf:familyName is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getFamilyName()
    {
        $family_name_resource = new Resource(XML_FOAF_NS . 'familyName');
        $family_names = $this->foaf->find(null,$family_name_resource,null);
        foreach ($family_names->triples as $family_name) {
            if (in_array($family_name->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $family_name->subj->uri) {
                        $this->foaf_data[$key]['familyname'] = $family_name->obj->label;
                    }
                    break;
                }
            } elseif (in_array($family_name->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $family_name_key => $family_name_array) {
                        if (isset($family_name_array['node']) && ($family_name_array['node'] == $family_name->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$family_name_key]['familyname'] = $family_name->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:plan's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     */

    function _getPlan()
    {
        $plan_resource = new Resource(XML_FOAF_NS . 'plan');
        $plans = $this->foaf->find(null,$plan_resource,null);
        foreach ($plans->triples as $plan) {
            if (in_array($plan->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $plan->subj->uri) {
                        $this->foaf_data[$key]['plan'] = $plan->obj->label;
                    }
                    break;
                }
            } elseif (in_array($plan->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $plan_key => $plan_array) {
                        if (isset($plan_array['node']) && ($plan_array['node'] == $plan->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$plan_key]['plan'] = $plan->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:img's and inserts them into the result Array
     */

    function _fetchImg()
    {
        $img_resource = new Resource(XML_FOAF_NS . 'img');
        $imgs = $this->foaf->find(null,$img_resource,null);
        foreach ($imgs->triples as $img) {
            if (in_array($img->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $img->subj->uri) {
                        $this->foaf_data[$key]['img'][] = $img->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($img->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $img_key => $img_array) {
                        if (isset($img_array['node']) && ($img_array['node'] == $img->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$img_key]['img'][] = $img->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:myersBriggs's and inserts them into the result Array
     */

    function _fetchMyersBriggs()
    {
        $meyers_briggs_resource = new Resource(XML_FOAF_NS . 'meyersBriggs');
        $meyers_briggss = $this->foaf->find(null,$meyers_briggs_resource,null);
        foreach ($meyers_briggss->triples as $meyers_briggs) {
            if (in_array($meyers_briggs->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $meyers_briggs->subj->uri) {
                        $this->foaf_data[$key]['meyersbriggs'][] = $meyers_briggs->obj->label;
                    }
                    break;
                }
            } elseif (in_array($meyers_briggs->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $meyers_briggs_key => $meyers_briggs_array) {
                        if (isset($meyers_briggs_array['node']) && ($meyers_briggs_array['node'] == $meyers_briggs->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$meyers_briggs_key]['meyersbriggs'][] = $meyers_briggs->obj->label;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:workplaceHompage's and inserts them into the result Array
     */

    function _fetchWorkplaceHomepage()
    {
        $workplace_homepage_resource = new Resource(XML_FOAF_NS . 'workplaceHomepage');
        $workplace_homepages = $this->foaf->find(null,$workplace_homepage_resource,null);
        foreach ($workplace_homepages->triples as $workplace_homepage) {
            if (in_array($workplace_homepage->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $workplace_homepage->subj->uri) {
                        $this->foaf_data[$key]['workplacehomepage'][] = $workplace_homepage->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($workplace_homepage->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $workplace_homepage_key => $workplace_homepage_array) {
                        if (isset($workplace_homepage_array['node']) && ($workplace_homepage_array['node'] == $workplace_homepage->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$workplace_homepage_key]['workplacehomepage'][] = $workplace_homepage->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:workInfoHomepage's and inserts them into the result Array
     */

    function _fetchWorkInfoHomepage()
    {
        $work_info_homepage_resource = new Resource(XML_FOAF_NS . 'workInfoHomepage');
        $work_info_homepages = $this->foaf->find(null,$work_info_homepage_resource,null);
        foreach ($work_info_homepages->triples as $work_info_homepage) {
            if (in_array($work_info_homepage->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $work_info_homepage->subj->uri) {
                        $this->foaf_data[$key]['workinfohomepage'][] = $work_info_homepage->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($work_info_homepage->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $work_info_homepage_key => $work_info_homepage_array) {
                        if (isset($work_info_homepage_array['node']) && ($work_info_homepage_array['node'] == $work_info_homepage->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$work_info_homepage_key]['workinfohomepage'][] = $work_info_homepage->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:schoolHomepage's and inserts them into the result Array
     */

    function _fetchSchoolHomepage()
    {
        $school_homepage_resource = new Resource(XML_FOAF_NS . 'schoolHomepage');
        $school_homepages = $this->foaf->find(null,$school_homepage_resource,null);
        foreach ($school_homepages->triples as $school_homepage) {
            if (in_array($school_homepage->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $school_homepage->subj->uri) {
                        $this->foaf_data[$key]['schoolhomepage'][] = $school_homepage->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($school_homepage->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $school_homepage_key => $school_homepage_array) {
                        if (isset($school_homepage_array['node']) && ($school_homepage_array['node'] == $school_homepage->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$school_homepage_key]['schoolhomepage'][] = $school_homepage->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:publication's and inserts them into the result Array
     */

    function _fetchPublication()
    {
        $publication_resource = new Resource(XML_FOAF_NS . 'publication');
        $publications = $this->foaf->find(null,$publication_resource,null);
        foreach ($publications->triples as $publication) {
            if (in_array($publication->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $publication->subj->uri) {
                        $this->foaf_data[$key]['publication'][] = $publication->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($publication->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $publication_key => $publication_array) {
                        if (isset($publication_array['node']) && ($publication_array['node'] == $publication->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$publication_key]['publication'][] = $publication->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:currentProject's and inserts them into the result Array
     */

    function _fetchCurrentProject()
    {
        $current_project_resource = new Resource(XML_FOAF_NS . 'currentProject');
        $current_projects = $this->foaf->find(null,$current_project_resource,null);
        foreach ($current_projects->triples as $current_project) {
            if (in_array($current_project->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $current_project->subj->uri) {
                        $this->foaf_data[$key]['currentproject'][] = $current_project->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($current_project->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $current_project_key => $current_project_array) {
                        if (isset($current_project_array['node']) && ($current_project_array['node'] == $current_project->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$current_project_key]['currentproject'][] = $current_project->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:pastProject's and inserts them into the result Array
     */

    function _fetchPastProject()
    {
        $past_project_resource = new Resource(XML_FOAF_NS . 'pastProject');
        $past_projects = $this->foaf->find(null,$past_project_resource,null);
        foreach ($past_projects->triples as $past_project) {
            if (in_array($past_project->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $past_project->subj->uri) {
                        $this->foaf_data[$key]['pastproject'][] = $past_project->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($past_project->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $past_project_key => $past_project_array) {
                        if (isset($past_project_array['node']) && ($past_project_array['node'] == $past_project->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$past_project_key]['pastproject'][] = $past_project->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all foaf:basedNear's and inserts them into the result Array
     */

    function _getBasedNear()
    {

    }

    /* foaf:Person && foaf:Group */

    /**
     * Finds all foaf:interest's and inserts them into the result Array
     */

    function _fetchInterest()
    {
        $interest_resource = new Resource(XML_FOAF_NS . 'interest');
        $interests = $this->foaf->find(null,$interest_resource,null);
        foreach ($interests->triples as $interest) {
            if (in_array($interest->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $interest->subj->uri) {
                        $this->foaf_data[$key]['interest'][] = $interest->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($interest->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $interest_key => $interest_array) {
                        if (isset($interest_array['node']) && ($interest_array['node'] == $interest->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$interest_key]['interest'][] = $interest->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /* foaf:Group */

    /**
     * Finds all foaf:member's and inserts them into the result Array
     *
     * @todo Need to figure out how to point to an agent in the foaf_data :)
     */

    function _fetchMember()
    {

    }

    /**
     * Finds all foaf:membershipClass's and inserts them into the result Array
     *
     * If more than one foaf:plan is found for a single foaf:Agent, the
     * last found will be inserted into the result Array
     *
     * @todo Use http://xmlns.com/foaf/0.1/#term_Group for reference (second example)
     * @todo figure out how to point to an agent in the foaf_data
     */

    function _getMembershipClass()
    {

    }

    /* end of Agent only methods */

    /**
     * Finds all rdf:seeAlso's and inserts them into the result Array
     */

    function _fetchSeeAlso()
    {
        $see_also_resource = new Resource(XML_FOAF_RDF_SCHEMA_NS . 'seeAlso');
        $see_alsos = $this->foaf->find(null,$see_also_resource,null);
        foreach ($see_alsos->triples as $see_also) {
            if (in_array($see_also->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $key => $value) {
                    if ($value == $see_also->subj->uri) {
                        $this->foaf_data[$key]['seealso'][] = $see_also->obj->uri;
                    }
                    break;
                }
            } elseif (in_array($see_also->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $see_also_key => $see_also_array) {
                        if (isset($see_also_array['node']) && ($see_also_array['node'] == $see_also->subj->uri)) {
                            $this->foaf_data[$agent_key]['knows'][$see_also_key]['seealso'][] = $see_also->obj->uri;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Finds all dc:title's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['title'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcTitle()
    {
        $dc_title_resource = new Resource(XML_FOAF_DC_NS . 'title');
        $dc_titles = $this->foaf->find(null,$dc_title_resource,null);
        foreach ($dc_titles->triples as $title) {
            $this->foaf_data['dc']['title'][$title->subj->uri] = $title->obj->label;
        }
    }


    /**
     * Finds all dc:description's and inserts them into the result Array
     *
     * These are inserted at $result['dc']['description'][$uri] where $uri is the URI
     * they are for. You will need to check this for titles and descriptions upon output
     * for any element you want them for.
     */

    function _fetchDcDescription()
    {
        $dc_description_resource = new Resource(XML_FOAF_DC_NS . 'description');
        $dc_descriptions = $this->foaf->find(null,$dc_description_resource,null);
        foreach ($dc_descriptions->triples as $description) {
            $this->foaf_data['dc']['description'][$description->subj->uri] = $description->obj->label;
        }
    }

    /**#@-*/

    /**
     * Fetch a FOAF Property with multiple values
     *
     * @param $xmlns string XML Namespace URI
     * @param $property string XML Element name
     * @param $obj_value string Triple's "Object" value (label or uri typically)
     * @access private
     * @return void
     */

    function _fetchProperty($xmlns,$property,$obj_value)
    {
        $obj_value = str_lower($obj_value);
        $property_resource = new Resource($xmlns . $property);
        $properties = $this->foaf->find(null,$property_resource,null);
        foreach ($properties->triples as $triple) {
            if (in_array($triple->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $node_uri => $node_data) {
                    if ($node_data == $triple->subj->uri) {
                        $property = strtolower(str_replace('_','',$property));
                        $this->foaf_data[$node_uri][$property][] = $triple->obj->{$obj_value};
                    }
                    break;
                }
            } elseif (in_array($triple->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $node_uri => $node_data) {
                        if (isset($node_data['node']) && ($node_data['node'] == $triple->subj->uri)) {
                            $property = strtolower(str_replace('_','',$property));
                            $this->foaf_data[$agent_key]['knows'][$node_uri][$property][] = $triple->obj->{$obj_value};
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Fetch a FOAF Property with a single value
     *
     * @param $xmlns string XML Namespace URI
     * @param $property string XML Element name
     * @param $obj_value string Triple's "Object" value (label or uri typically)
     * @access private
     * @return void
     */

    function _getProperty($xmlns,$property,$obj_value)
    {
        $obj_value = strtolower($obj_value);
        $property_resource = new Resource($xmlns . $property);
        $properties = $this->foaf->find(null,$property_resource,null);
        foreach ($properties->triples as $triple) {
            if (in_array($triple->subj->uri,$this->agent_nodes)) {
                foreach ($this->agent_nodes as $node_uri => $node_data) {
                    if ($node_data == $triple->subj->uri) {
                        $property = strtolower(str_replace('_','',$property));
                        $this->foaf_data[$node_uri][$property] = $triple->obj->{$obj_value};
                    }
                    break;
                }
            } elseif (in_array($triple->subj->uri,$this->known_nodes)) {
                foreach ($this->foaf_data as $agent_key => $agent) {
                    foreach ($agent['knows'] as $node_uri => $node_data) {
                        if (isset($node_data['node']) && ($node_data['node'] == $triple->subj->uri)) {
                            $property = strtolower(str_replace('_','',$property));
                            $this->foaf_data[$agent_key]['knows'][$node_uri][$property] = $triple->obj->{$obj_value};
                            break 2;
                        }
                    }
                }
            }
        }
    }

    /**
     * Return parsed FOAF data as an Object
     *
     * @todo Make it work!
     * @access public
     * @return object
     */

    function toObject()
    {
        $foaf_object = new stdClass();
        foreach ($this->foaf_data as $key=>$value) {
            $foaf_object->$key = $value;
        }
        return $foaf_object;
    }

    /**
     * Return parsed FOAF data as an Array
     *
     * @access public
     * @return array
     */

    function toArray()
    {
        return $this->foaf_data;
    }

    /**
     * Return parsed FOAF data pretty HTML
     *
     * @todo Write code to return an HTML table
     * @access public
     * @return string
     */

    function toHTML(&$foaf_data)
    {
        require_once 'Validate.php';
        $table = '<table>';
        foreach ($foaf_data as $key => $agent) {
            if (isset ($agent['type'])) {
                $table .= '<tr><th colspan="2" class="xml_foaf_' .strtolower($agent['type']). '><h1 class="xml_foaf">';
                if (isset($agent['name'])) {
                    $table .= $agent['name'];
                } else {
                    $name = NULL;
                    if (isset($agent['firstname'])) {
                        $name .= $agent['firstname'];
                    } elseif (isset($agent['givenname'])) {
                        $name .= $agent['givenname'];
                    }
                    if (isset($agent['surname'])) {
                        $name .= ' ' .$agent['surname'];
                    } elseif (isset($agent['familyname'])) {
                        $name .= ' ' .$agent['familyname'];
                    }
                    if (is_null($name)) {
                        $name = $agent['node'];
                    }
                    $table .= $name;
                }
                $table .= '</h1></th></tr>';
                unset($agent['node']);
                foreach ($agent as $key => $property) {
                    $table .= '<tr><th>' .$key. '</th><td>';
                    if (!is_array($property)) {
                        if (Validate::uri($property,array('allowed_schemes' => array('http','ftp')))) {
                            $table .= '<a href="' .$property. '">' .$property. '</a></td></tr>';
                        } else {
                            $table .= $property. '</td></tr>';
                        }
                    } else {
                        if ($key == 'knows') {
                            $property =  array ($property);
                            $table .= $this->toHTML($property);
                        } elseif ($key != 'holdsaccount') {
                            $table .= '<ul>';
                            foreach ($property as $child) {
                                if (Validate::uri($child,array('allowed_schemes' => array('http','ftp')))) {
                                    $table .= '<li>';
                                    if (isset($this->foaf_data['dc']['title'][$child])) {
                                        $table .= '<h2 class="xml_foaf"><a href="' .$child. '">';
                                        $table .= $this->foaf_data['dc']['title'][$child];
                                        $table .= '</a></h2>';
                                    } else {
                                        $table .= '<a href="' .$child. '">' .$child. '</a>';
                                    }
                                    if (isset($this->foaf_data['dc']['description'][$child])) {
                                        $table .= '<p class="xml_foaf">' .$this->foaf_data['dc']['description'][$child]. '</p>';
                                    }
                                    $table .= '</li>';
                                } else {
                                    $table .= '<li>' .$child. '</li>';
                                }
                            }
                            $table .= '</ul>';
                        } else {
                            foreach ($property as $account) {
                                $table .= '<table>';
                                foreach ($account as $key => $data) {
                                    $table .= '<tr><th>' .$key. '</th><td>' .$data. '</td></tr>';
                                }
                                $table .= '</table>';
                            }
                        }
                    }
                }
            } else {
                continue;
            }
        }
        $table .= '</table>';
        return $table;
    }
}

?>