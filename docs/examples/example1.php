<?php
    require_once 'XML/FOAF.php';
    // Main FOAF
    $foaf = new XML_FOAF();

    $foaf->newAgent('person');
    $foaf->setName('Davey Shafik');
    $foaf->setTitle('Mr');
    $foaf->setFirstName('Davey');
    $foaf->setSurname('Shafik');
    $foaf->addMbox('mailto:davey@php.net',TRUE); // see also: XML_FOAF::setMboxSha1Sum();
    $foaf->addHomepage('http://pixelated-dreams.com/~davey/');
    $foaf->addWeblog('http://pixelated-dreams.com/blog');
    $foaf->addImg('http://pixelated-dreams.com/~davey/me.jpg');
    $foaf->addPage('http://pixelated-dreams.com/~davey/CV','Curriculum Vitae','Davey Shafiks Curriculum Vitae');
    $foaf->addPage('http://www.php-mag.net/itr/online_artikel/psecom,id,484,nodeid,114.html','Sticking The Fork In','Creating Daemons in PHP');
    $foaf->addPage('http://pawscon.com/', 'PHP and Web Standards Conference UK 2004', 'A Conference dedicated to PHP, Web Standards and the Semantic Web');
    $foaf->addPhone('07776293539');
    $foaf->addJabberID('fractured_realities@jabber.org');
    $foaf->addTheme('http://php.net');
    $foaf->addOnlineAccount('Davey','http://freenode.info','http://xmlns.com/foaf/0.1/OnlineChatAccount');
    $foaf->addOnlineGamingAccount('Davey_S','http://www.there.com');
    $foaf->addWorkplaceHomepage('http://www.pawscon.com');
    $foaf->addSchoolHomepage('http://www.nobel.herts.sch.uk/');
    $foaf->addInterest('http://xmlns.com/foaf/0.1/');
	$foaf->addFundedBy('http://synapticmedia.net');
	$foaf->addLogo('http://paws.davey.is-a-geek.com/images/paws.jpg');
    $foaf->setBasedNear(52.565475,-1.162895);
	$foaf->addDepiction('http://example.org/depiction/');
	$foaf->addDepiction('http://example.org/depiction/2');
	
    // Content of a <foaf:knows><foaf:Person /></foaf:knows>
    $matt = new XML_FOAF();
    $matt->newAgent('person');
    $matt->setName('Matt McClanahan');
    $matt->addNick('mattmcc');
    $matt->addMboxSha1Sum('0cd5f54daf6aa59d1071ea6bf2973e0171ece606',TRUE);
    $matt->addSeeAlso('http://mmcc.cx/foaf.rdf');
    $matt->addJabberID('mattmcc@jabber.com');
	$matt->addOnlineChatAccount('mattmcc','http://freenode.info','http://xmlns.com/foaf/0.1/OnlineChatAccount');
    // Add to Main FOAF
    $foaf->addKnows($matt);

    // Another <foaf:knows><foaf:Person /></foaf:knows>
    /*
      Although we use another instance of XML_FOAF, we could re-use
      the one from above ($matt)
    */
    $libby = new XML_FOAF();
    $libby->newAgent('person');
    $libby->setName('Libby Miller');
    $libby->addMbox('mailto:libby.miller@bristol.ac.uk');
    $libby->addSeeAlso('http://swordfish.rdfweb.org/people/libby/rdfweb/webwho.xrdf');

    // Add to Main FOAF
    $foaf->addKnows($libby);
    
    $mcd = new XML_FOAF();
    $mcd->newAgent('Organization');
    $mcd->setName('McDonalds');
    $mcd->addHomepage('http://www.mcdonalds.com/');
    
    $foaf->addKnows($mcd);

    if (!isset($_GET['xml'])) {
    	echo "<pre>" .htmlentities($foaf->get()). "</pre>";
    	echo "<hr />";
    	show_source(__FILE__);
    } else {
    	header('Content-Type: text/xml');
    	$foaf->dump();
    }

    /* Output
    <rdf:RDF xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:foaf="http://xmlns.com/foaf/0.1/"
     xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
     xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
     xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#">
        <foaf:Person>
            <foaf:name>Davey Shafik</foaf:name>
            <foaf:title>Mr</foaf:title>
            <foaf:firstName>Davey</foaf:firstName>
            <foaf:surname>Shafik</foaf:surname>
            <foaf:mbox_sha1sum>26b2e3834d83a5ca3fc81e5a942862f7a2bcb653</foaf:mbox_sha1sum>
            <foaf:homepage rdf:resource="http://pixelated-dreams.com/~davey/" />
            <foaf:img rdf:resource="http://pixelated-dreams.com/~davey/me.jpg" />
            <foaf:page>
                <foaf:Document rdf:about="http://pixelated-dreams.com/~davey/CV/">
                    <dc:title>Curriculum Vitae</dc:title>
                </foaf:Document>
            </foaf:page>
            <foaf:phone rdf:resource="tel:07776293539" />
            <foaf:workplaceHomepage rdf:resource="http://www.pawscon.com" />
            <foaf:schoolHomepage rdf:resource="http://www.nobel.herts.sch.uk/" />
            <foaf:interest rdf:resource="http://xmlns.com/foaf/0.1/" />
            <foaf:based_near>
                <geo:Point geo:lat="52.565475" geo:long="-1.162895" />
            </foaf:based_near>
            <foaf:knows>
                <foaf:Person>
                    <foaf:name>Matt McClanahan</foaf:name>
                    <foaf:nick>mattmcc</foaf:nick>
                    <foaf:mbox_sha1sum>0cd5f54daf6aa59d1071ea6bf2973e0171ece606</foaf:mbox_sha1sum>
                    <rdfs:seeAlso rdf:resource="http://mmcc.cx/foaf.rdf" />
                </foaf:Person>
            </foaf:knows>
            <foaf:knows>
                <foaf:Person>
                    <foaf:name>Libby Miller</foaf:name>
                    <foaf:mbox rdf:resource="mailto:libby.miller@bristol.ac.uk" />
                    <rdfs:seeAlso rdf:resource="http://swordfish.rdfweb.org/people/libby/rdfweb/webwho.xrdf" />
                </foaf:Person>
            </foaf:knows>
        </foaf:Person>
    </rdf:RDF>
    */
?>