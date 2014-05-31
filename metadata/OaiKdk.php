<?php
/**
 * @package OaiPmhRepository
 * @subpackage MetadataFormats
 * @author Matti Lassila, John Flatness, Yu-Hsun Lin
 * @copyright Copyright 2012 Matti Lassila, John Flatness, Yu-Hsun Lin
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**require_once HELPERS;**/

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
                                 'publisher', 'contributor', 'date',
                                 'format', 'source','relation', 'coverage', 
                                 'rights' );

        /* DC Terms specified in http://dublincore.org/schemas/xmls/qdc/dcterms.xsd
         * Array maps element names to labels used in DC Extented plugin
         */
        
        $dcTermElements = array(
                 'alternative' => 'Alternative Title', 
                 'tableOfContents' => 'Table Of Contents', 
                 'abstract' => 'Abstract', 
                 'created' => 'Date Created',
                 'valid' => 'Date Valid',
                 'available' => 'Date Available',
                 'issued' => 'Date Issued',
                 'modified' => 'Date Modified',
                 'dateAccepted' => 'Date Accepted',
                 'dateCopyrighted' => 'Date Copyrighted',
                 'dateSubmitted' => 'Date Submitted',
                 'extent' => 'Extent',
                 'medium' => 'Medium',
                 'isVersionOf' => 'Is Version Of',
                 'hasVersion' => 'Has Version',
                 'isReplacedBy' => 'Is Replaced By',
                 'replaces' => 'Replaces',
                 'isRequiredBy' => 'Is Required By',
                 'requires' => 'Requires',
                 'isPartOf' => 'Is Part Of',
                 'hasPart' => 'Has Part',
                 'isReferencedBy' => 'Is Referenced By',
                 'references' => 'References',
                 'isFormatOf' => 'Is Format Of',
                 'hasFormat' => 'Has Format',
                 'conformsTo' => 'Conforms To',
                 'spatial' => 'Spatial Coverage',
                 'temporal' => 'Temporal Coverage',
                 'audience' => 'Audience',
                 'accuralMethod' => 'Accrual Method',
                 'accuralPeriodicity' => 'Accrual Periodicity',
                 'accuralPolicy' => 'Accrual Policy',
                 'instructionalMethod' => 'Instructional Method',
                 'provenance' => 'Provenance',
                 'rightsHolder' => 'Rights Holder',
                 'mediator' => 'Mediator',
                 'educationLevel' => 'Audience Education Level',
                 'accessRights' => 'Access Rights',
                 'license' => 'License',
                 'bibliographicCitation' => 'Bibliographic Citation');

        /* Must create elements using createElement to make DOM allow a
         * top-level xmlns declaration instead of wasteful and non-
         * compliant per-node declarations.
         */
        foreach($dcElementNames as $elementName)
        {   
            $upperName = Inflector::camelize($elementName);
            $dcElements = $this->item->getElementTexts(
                 'Dublin Core',$upperName);
            foreach($dcElements as $elementText)
            {
                if ($elementText->text != ' ')
                {
                    $this->appendNewElement($oai_dc, 
                        'dc:'.$elementName, $elementText->text);
                }
            }
        }

        /* Handle UDC/YKL entries separately */

        $dcSubjects = $this->item->getElementTexts(
                'Dublin Core','Subject');
            foreach($dcSubjects as $dcSubject)
            {
                if (is_numeric(substr(trim($dcSubject->text), 0, 1)) & !preg_match('/[A-Za-z]/', $dcSubject->text))
                {
                    $this->appendNewElement($oai_dc, 
                        'dc:subject', trim($dcSubject->text), 'dcterms:YKL');
                }
                else {
                    
                    if ($dcSubject->text != ' ') {
                    $this->appendNewElement($oai_dc, 
                        'dc:subject', trim($dcSubject->text));
                    }
                }
            }
            
        /* Handle language entries as used in some Finnish Omeka installations */

        $dcLanguages = $this->item->getElementTexts(
                'Dublin Core','Language');
            
            foreach($dcLanguages as $dcLanguage)
            {
                // If language code is exactly two characters long
                // it is probably standard ISO639 code and we'll use
                // it as it is.
                
                $language= $dcLanguage->text;
                
                if (strlen($language) != 2) 
                {
                
                    switch($dcLanguage->text)
                    {
                        case 'suomi':
                            $language = 'fi';
                            break;
                        case 'englanti':
                            $language = 'en';
                            break;
                        case 'ruotsi':
                            $language = 'sv';
                            break;
                        case 'espanja':
                            $language = 'es';
                            break;
                        case 'hollanti':
                            $language = 'nl';
                            break;
                        case 'italia':
                            $language = 'it';
                            break;
                        case 'latina':
                            $language = 'la';
                            break;
                        case 'venäjä':
                            $language = 'ru';
                            break;
                        case 'ranska':
                            $language = 'fr';
                            break;
                        case 'saksa':
                            $language = 'de';
                            break;
                        case 'viro':
                            $language = 'et';
                            break;
                    
                        default:
                            $language = 'fi';
                    }
                }
  
                    $this->appendNewElement($oai_dc, 
                        'dc:language', trim($language));
               
            }
        
        
        
        /* Handle URNs and local URIs. This is for URN resolving in NLF*/

        $dcIdentifiers = $this->item->getElementTexts(
                'Dublin Core','Identifier');
            foreach($dcIdentifiers as $dcIdentifier)
            {
                if (substr($dcIdentifier->text, 0, 3) == 'URN') {
                   $this->appendNewElement($oai_dc, 
                    'dc:identifier', trim($dcIdentifier->text), 'URI'); 
                } 
                
            }
         $this->appendNewElement($oai_dc, 
                    'dc:identifier', absolute_url(record_url($this->item)), 'coolUri');

        /* Handle itemtype if empty in metadata */
        
        $dcTypes = $this->item->getElementTexts('Dublin Core', 'Type');
        
         if(empty($dcTypes))
             {
                 $it = $this->item->getItemType();
                 $itemtype = $it->name;
                 $this->appendNewElement($oai_dc, 
                            'dc:type', $this->translateItemType(strtolower(trim($itemtype)))); 
                              }
        else
        {   
         foreach($dcTypes as $dcType)
         {
                 if($dcType->text)
                 {
                     $itemtype = $dcType->text;    
                 }
         
         $this->appendNewElement($oai_dc, 
                            'dc:type', $this->translateItemType(strtolower(trim($itemtype)))); 
         }
        }
            
        

        /* Create metadata entries for dc:terms
         */
        foreach($dcTermElements as $key => $value)
        {   
            
            $dcElements = $this->item->getElementTexts(
                'Dublin Core',$dcTermElements[$key]);
            foreach($dcElements as $elementText)
            {
                if ($elementText->text != ' ')
                {
                    $this->appendNewElement($oai_dc, 
                        'dcterms:' . $key, $elementText->text);
                }
            }
        }


        // Expose thumbnails if requested
        if(get_option('oaipmh_repository_expose_files')) {
            $files = $this->item->getFiles();
            
            foreach($files as $file) 
            {
                if($file->hasThumbnail()) {
                $this->appendNewElement($oai_dc, 
                    'dc:identifier', $file->getWebPath('fullsize'),'file');
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
     
     protected function translateItemType($itemtype)
     {
         switch($itemtype)
                    {
                        case 'document':
                            $itemtype = 'Text';
                            break;
                        case 'still image':
                            $itemtype = 'Image';
                            break;
                        case 'artikkeli':
                            break;
                            $itemtype = 'Text';
                        case 'artikkeliviite':
                            break;
                            $itemtype = 'Text';
                        case 'website':
                            $itemtype = 'Text';
                            break;
                        case 'linkki':
                            $itemtype = 'Text';
                            break;
                        case 'kirje':
                            $itemtype = 'Text';
                            break;
                        case 'käsikirjoitus':
                            $itemtype = 'Text';
                            break;
                        default:
                           $itemtype = $itemtype;
                    }
            return $itemtype;
    }
}

