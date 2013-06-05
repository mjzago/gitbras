<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Formular fuer das Editieren der Lizenzen eines Dokuments.
 * 
 * Es werden die aktiven Lizenzen mit Checkboxen angezeigt, so daß man schnell die Lizenzen des Dokuments auswählen 
 * kann.
 * 
 * Das Metadaten-Formular in der Administration zeigt alle Lizenzen, unabhängig davon ob sie aktiv sind, da bei 
 * nachträglicher Deaktivierung einer Lizenz, immer noch Dokumente damit verknüpft sein können.
 */
class Admin_Form_DocumentLicences extends Admin_Form_AbstractDocumentSubForm {
    
    /**
     * Name für Formularelement für ID der Lizenz.
     */
    const ELEMENT_NAME_PREFIX = 'licence';
    
    /**
     * CSS Klasse für aktive Lizenzen.
     */
    const ACTIVE_CSS_CLASS = 'active';
    
    /**
     * CSS Klasse für inaktive Lizenzen.
     */
    const INACTIVE_CSS_CLASS = 'disabled';
    
    /**
     * Erzeugt Checkbox Formularelemente für alle Lizenzen.
     */
    public function init() {
        parent::init();
        
        $licences = Opus_Licence::getAll();

        foreach ($licences as $licence) {
            $element = new Zend_Form_Element_Checkbox(self::ELEMENT_NAME_PREFIX . $licence->getId());
            $element->setLabel($licence->getNameLong());
            $cssClass = ($licence->getActive()) ? self::ACTIVE_CSS_CLASS : self::INACTIVE_CSS_CLASS;
            $element->addDecorator('Label', array('class' => $cssClass));
            $this->addElement($element);
        }
        
        $this->setLegend('admin_document_section_licences');
    }
    
    /**
     * Setzt die dem Dokument zugewiesenen Lizenzen als ausgewählt im Formular.
     * @param Opus_Document $document
     */
    public function populateFromModel($document) {
        $allLicences = Opus_Licence::getAll();
        
        foreach ($allLicences as $licence) {
            $element = $this->getElement(self::ELEMENT_NAME_PREFIX . $licence->getId());

            $element->setChecked($this->hasLicence($document, $licence));
        }
    }
    
    /**
     * Aktualisiert die Liste der Lizenzen fuer ein Dokument.
     * @param Opus_Document $document
     */
    public function updateModel($document) {
        $licences = Opus_Licence::getAll();
        
        $docLicences = array();
        
        foreach ($licences as $licence) {
            $element = $this->getElement(self::ELEMENT_NAME_PREFIX . $licence->getId());
            if (!is_null($element) && $element->getValue()) {
                $docLicences[] = $licence;
            }
        }
        
        $document->setLicence($docLicences);
    }
    
    /**
     * Prueft, ob eine Lizenz einem Dokument zugewiesen ist.
     * 
     * @param Opus_Document $document
     * @param Opus_Licence $licence
     * @return boolean true - Lizenz zugewiesen; false - Lizenz nicht zugewiesen
     */
    public function hasLicence($document, $licence) {
        $licences = $document->getLicence();
        
        foreach ($licences as $docLicence) {
            if ($docLicence->getModel()->getId() == $licence->getId()) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Meldet, ob mindestens eine Lizenz ausgewählt ist.
     * 
     * Die Funktion wird für die Ausgabe des Metadaten-Formulars als Metadaten-Übersicht verwendet, um zu entscheiden,
     * ob das Unterformular für Lizenzen angezeigt werden soll oder nicht.
     * 
     * @return boolean
     */
    public function isEmpty() {
        $elements = $this->getElements();
        
        foreach ($elements as $element) {
            if ($element->getValue() == 1) {
                return false;
            }
        }
        
        return true;
    }
    
}