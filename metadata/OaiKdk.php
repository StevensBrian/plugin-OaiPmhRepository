<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author Matti Lassila, John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2012 Matti Lassila, John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

require_once HELPERS;

/**
 * Class implmenting metadata output for the required oai_dc metadata format.
 * oai_kdk is output of the full Dublin Core fieldset implemented in
 * Dublin Core Extended -plugin.
 *
 * @package OaiPmhRepository
 * @subpackage Metadata Formats
 */
class OaiPmhRepository_Metadata_OaiKdk extends OaiPmhRepository_Metadata_Abstract
{
    /** OAI-PMH metadata prefix */
    const METADATA_PREFIX = 'oai_kdk';
    
    /** XML namespace for output format */
    const METADATA_NAMESPACE = 'http://www.openarchives.org/OAI/2.0/oai_dc/';
    
    /** XML schema for output format */
    const METADATA_SCHEMA = 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd';
    
    /** XML namespace for unqualified Dublin Core */
    const DC_NAMESPACE_URI = 'http://purl.org/dc/elements/1.1/';

    /** XML namepace for DC element refinements*/
    const DC_TERM_NAMESPACE_URI = 'http://purl.org/dc/terms/';
    
    /**
     * Appends Dublin Core metadata. 
     *
     * Appends a metadata element, an child element with the required format,
     * and further children for each of the Dublin Core fields present in the
     * item.
     */
    public function appendMetadata($metadataElement) 
    {
        $oai_dc = $this->document->createElementNS(
            self::METADATA_NAMESPACE, 'oai_dc:dc');
        $metadataElement->appendChild($oai_dc);

        /* Must manually specify XML schema uri per spec, but DOM won't include
         * a redundant xmlns:xsi attribute, so we just set the attribute
         */
        $oai_dc->setAttribute('xmlns:dc', self::DC_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:dcterms', self::DC_TERM_NAMESPACE_URI);
        $oai_dc->setAttribute('xmlns:xsi', parent::XML_SCHEMA_NAMESPACE_URI);
        $oai_dc->setAttribute('xsi:schemaLocation', self::METADATA_NAMESPACE.' '.
            self::METADATA_SCHEMA);

        /* Each of the 16 unqualified Dublin Core elements, in the order
         * specified by the oai_dc XML schema. 
         * Omit 'subject' and 'identifier' because they are handled separately 
         * for type includement.
         */
        $dcElementNames = array( 'title', 'creator', 'description',
                                 'publisher', 'contributor', 'date', 'type',
                                 'format', 'source', 'language',
                                 'relation', 'coverage', 'rights' );

        /* DC Terds
         * specified by the oai_dc XML schema
         */
        $dcTermElementNames = array( 'alternative', 
                                     'tableOfContents', 
                                     'abstract', 
                                     'created',
                                     'valid',
                                     'available',
                                     'issued',
                                     'modified',
                                     'dateAccepted',
                                     'dateCopyrighted',
                                     'dateSubmitted',
                                     'extent',
                                     'medium',
                                     'isVersionOf',
                                     'hasVersion',
                                     'isReplacedBy',
                                     'replaces',
                                     'isRequiredBy',
                                     'requires',
                                     'isPartOf',
                                     'hasPart',
                                     'isReferencedBy',
                                     'references',
                                     'isFormatOf',
                                     'hasFormat',
                                     'conformsTo',
                                     'spatial',
                                     'temporal',
                                     'audience',
                                     'accuralMethod',
                                     'accuralPeriodicity',
                                     'accuralPolicy',
                                     'instructionalMethod',
                                     'provenance',
                                     'rightsHolder',
                                     'mediator',
                                     'educationLevel',
                                     'accessRights',
                                     'license',
                                     'bibliographicCitation');

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        foreach($dcElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);
            $dcElements = $this->item->getElementTextsByElementNameAndSetName(
                $upperName, 'Dublin Core');
            foreach($dcElements as $elementText)
            {
                if ($elementText->text != ' ')
                {
                    $this->appendNewElement($oai_dc, 
                        'dc:'.$elementName, $elementText->text);
                }
            }
            // Append the browse URI to all results
            if($elementName == 'identifier') 
            {
                
                // Also append an identifier for each file
                if(get_option('oaipmh_repository_expose_files')) {
                    $files = $this->item->getFiles();
                    foreach($files as $file) 
                    {
                        $this->appendNewElement($oai_dc, 
                            'dc:identifier', $file->getWebPath('archive'));
                    }
                }
            }

            
        }
        /* 
         * Retrieve subject headings which are saved as tags.
         */

        $tags = $this->item->getTags();
        foreach($tags as $tag)
        {
        
        $this->appendNewElement($oai_dc, 
                            'dc:subject', $tag->name, 'dcterms:DDC');
        }

        /* Handle UDC entries separately */

        $dcSubjects = $this->item->getElementTextsByElementNameAndSetName(
                'Subject', 'Dublin Core');
            foreach($dcSubjects as $dcSubject)
            {
                if (is_numeric(substr($dcSubject->text, 0, 1)))
                {
                    $this->appendNewElement($oai_dc, 
                        'dc:subject', $dcSubject->text, 'dcterms:UDC');
                }
                else {
                    
                    if ($dcSubject->text != ' ') {
                    $this->appendNewElement($oai_dc, 
                        'dc:subject', $dcSubject->text);
                    }
                }
            }

        /* Handle URNs */

        $dcIdentifiers = $this->item->getElementTextsByElementNameAndSetName(
                'Identifier', 'Dublin Core');
            foreach($dcIdentifiers as $dcIdentifier)
            {
                if (substr($dcIdentifier->text, 0, 3) == 'URN') {
                   $this->appendNewElement($oai_dc, 
                    'dc:identifier', $dcIdentifier->text, 'URI'); 
                } else {

                $this->appendNewElement($oai_dc, 
                    'dc:identifier', abs_item_uri($this->item));
                }
            }



        /* Create metadata entries for dc:terms
         */
        foreach($dcTermElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);

            /* Handle nonstandard DC element names used in DC Extended module */

            if ($upperName = 'alternative') {
                $upperName = 'Alternative Title';
            }
            $dcElements = $this->item->getElementTextsByElementNameAndSetName(
                $upperName, 'Dublin Core');
            foreach($dcElements as $elementText)
            {
                if ($elementText->text != ' ')
                {
                    $this->appendNewElement($oai_dc, 
                        'dcterms:'.$elementName, $elementText->text);
                }
            }
        }
    }
    
    /**
     * Returns the OAI-PMH metadata prefix for the output format.
     *
     * @return string Metadata prefix
     */
    public function getMetadataPrefix()
    {
        return self::METADATA_PREFIX;
    }
    
    /**
     * Returns the XML schema for the output format.
     *
     * @return string XML schema URI
     */
    public function getMetadataSchema()
    {
        return self::METADATA_SCHEMA;
    }
    
    /**
     * Returns the XML namespace for the output format.
     *
     * @return string XML namespace URI
     */
    public function getMetadataNamespace()
    {
        return self::METADATA_NAMESPACE;
    }

    /**
     * Creates a parent element with the given name, with text as given.  
     *
     * Adds the resulting element as a child of the given parent node.
     *
     * @param DomElement $parent Existing parent of all the new nodes.
     * @param string $name Name of the new parent element.
     * @param string $text Text of the new element.
     * @return DomElement The new element.
     */
    protected function appendNewElement($parent, $name, $text = null, $type = null)
    {
        $document = $this->document;
        $newElement = $document->createElement($name);
        // Use a TextNode, causes escaping of input text
        if($text) {
            $text = $document->createTextNode($text);
            $newElement->appendChild($text);

        }

        if($type) {
            $newElement->setAttribute('xsi:type', $type);
        }
        $parent->appendChild($newElement);
        return $newElement;
     }
}
