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
 * @author      Sascha Szott <opus-development@saschaszott.de>
 * @copyright   Copyright (c) 2013-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for editing enrichments.
 *
 * This form filters enrichments, so that some values cannot be edited directly.
 *
 * TODO rename to Enrichments
 * TODO generic mechanism for excluding enrichments from editing
 * TODO use custom elements/subforms for different enrichment types
 */
class Admin_Form_Document_MultiEnrichmentSubForm extends Admin_Form_Document_MultiSubForm
{

    /**
     * Es wurde ein neuer Enrichmentkey im Select-Formularfeld ausgew??hlt.
     * Dieser Klick l??st einen Formular-Submit aus (mittels JavaScript umgesetzt).
     */
    const ELEMENT_SELECTION_CHANGED = "SelectionChanged";

    public function getFieldValues($document)
    {
        $value = parent::getFieldValues($document);
        if (! is_null($value)) {
            $value = $this->filterEnrichments($value);
        }
        return $value;
    }

    /**
     * Besondere Behandlung der beiden AutoCreate-Enrichments f??r DOIs und URNs
     * diese Enrichments sollen indirekt ??ber Checkboxen im Abschnitt DOI / URN
     * verwaltet werden und nicht im Enrichments-Block angezeigt werden (somit
     * werden bei den DOI/URN-Enrichments auch konfligierende Eintragungen
     * zwischen Enrichment-Wert und Checkbox-Zustand vermieden)
     *
     * @param $enrichments
     *
     * @return array
     */
    private function filterEnrichments($enrichments)
    {
        $result = [];
        foreach ($enrichments as $enrichment) {
            $keyName = $enrichment->getKeyName();
            if ($keyName == 'opus.doi.autoCreate' || $keyName == 'opus.urn.autoCreate') {
                continue;
            }
            $result[] = $enrichment;
        }
        return $result;
    }

    /**
     * Spezialbehandlung f??r Enrichments erforderlich, weil dort Typ des
     * Eingabeformularelements f??r den Enrichmentwert vom ausgew??hlten
     * Enrichment-Key (und dessen Enrichment-Type) abh??ngig ist.
     *
     * @param array $data
     * @param array $context
     *
     * @return array|string|null
     */
    public function processPost($data, $context)
    {
        // es wurde f??r ein bereits bestehendes Enrichment der zugeh??rige EnrichmentKey
        // im Eingabeformular ver??ndert
        if (array_key_exists(self::ELEMENT_SELECTION_CHANGED, $data)) {
            $this->processPostSelectionChanged();
            $result = Admin_Form_Document::RESULT_SHOW;
        } else {
            $result = parent::processPost($data, $context);

            // es wurde der Add-Button gedr??ckt und ein neues Enrichment-Subformular
            // zum bestehenden Metadatenformular hinzugef??gt: nun muss noch das zum
            // vorausgew??hlten (ersten) Enrichmentkey passende Eingabeformularfeld
            // angezeigt werden (dieses wird durch den zugeh??rigen Enrichmenttyp
            // bestimmt bzw. es ist ein einfaches Textfeld, wenn kein Enrichmenttyp
            // zugeordnet wurde)
            if (array_key_exists(self::ELEMENT_ADD, $data)) {
                $subForms = $this->getSubForms();
                if (! empty($subForms)) {
                    // das gerade neu hinzugef??gte Subformular (noch ohne Feld f??r
                    // die Eingabe des Enrichmentwerts) ausw??hlen und behandeln
                    $newSubForm = end($subForms);
                    if ($newSubForm instanceof Admin_Form_Document_Enrichment) {
                        $newSubForm->initEnrichmentValueElement();
                        $this->prepareSubFormDecorators($newSubForm);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * ??ndert den Enrichment-Key und das zugeh??rige Eingabefeld f??r den Enrichment-Wert
     * auf Basis des zugeordneten Enrichment-Types. Ist f??r den Enrichment-Key
     * kein Enrichment-Type angegeben, so wird ein einfaches Textfeld verwendet.
     *
     * @return string
     */
    protected function processPostSelectionChanged()
    {
        $subForms = $this->getSubForms();
        if (! empty($subForms)) {
            $subForm = reset($subForms);
            // das erste Unterformular ausw??hlen als Sprungziel nach dem Neuladen
            // des Metadatenformulars
            $this->_addAnchor($subForm);
        }
    }

    /**
     * Erzeugt und f??llt die Enrichment-Unterformular mit Werten auf Basis des
     * ??bergebenen Opus_Documents. Diese Methode wird immer dann aufgerufen,
     * wenn das Metadatenformular erstmalig (per GET) aufgerufen wird.
     *
     * @param Opus_Document $document
     */
    public function populateFromModel($document)
    {
        parent::populateFromModel($document);

        // Zusatzschritt erforderlich (deswegen wurde diese Methode ??berschrieben):
        // Dekoratoren auf den einzelnen Enrichment-Unterformularen setzen,
        // damit zeilenweise Ausgabe (innerhalb einer Tabelle) sauber funktioniert
        foreach ($this->getSubForms() as $subForm) {
            $this->prepareSubFormDecorators($subForm);
        }
    }

    /**
     * Initialisiert die Eingabeformularelemente f??r die Enrichment-Werte aus dem
     * POST-Request. Hierbei muss zus??tzlich aus dem ausgew??hlten Enrichment-Key
     * der zugeh??rige Enrichment-Type abgeleitet werden. Daher musste die Methode
     * ??berschrieben werden.
     *
     * @param      $post Variablen aus dem POST-Request
     * @param null $document
     */
    public function constructFromPost($post, $document = null)
    {
        parent::constructFromPost($post, $document);

        foreach ($this->getSubForms() as $subForm) {
            if ($subForm instanceof Admin_Form_Document_Enrichment) {
                $subFormName = $subForm->getName();
                $enrichmentKeyName = null;
                if (array_key_exists($subFormName, $post)) {
                    $enrichmentKeyName = $post[$subFormName][Admin_Form_Document_Enrichment::ELEMENT_KEY_NAME];
                }

                // es ist zu pr??fen, ob das Enrichment einen Wert verwendet, der in der
                // Typkonfiguration nicht angegeben ist
                $enrichmentId = null;
                if (array_key_exists(Admin_Form_Document_Enrichment::ELEMENT_ID, $post[$subFormName])) {
                    $enrichmentId = $post[$subFormName][Admin_Form_Document_Enrichment::ELEMENT_ID];
                    if ($enrichmentId == '') {
                        $enrichmentId = null;
                    }
                }

                $subForm->initEnrichmentValueElement($enrichmentKeyName, $enrichmentId);
            }
            $this->prepareSubFormDecorators($subForm);
        }
    }
}
