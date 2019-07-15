<?php

class Facet_By_Metadata_Form extends Omeka_Form
{
    protected $item;

    public function getItemTypes()
    {
        $db = get_db();
        $sql = $db->query("SELECT DISTINCT id, name FROM `{$db->prefix}item_types`");
        $results = $sql->fetchAll();
        $itemTypes = array();
        foreach ($results as $result) {
            $itemTypes[$result['id']] = $result['name'];
        }
        return $itemTypes;
    }

    public function init()
    {
        $this->setAction(url('facet-by-metadata'));
        $elTable = get_db()->getTable('Element');
        $elements = array();
        $elementIds = json_decode(get_option('facet_by_metadata_elements'), true);
        $terms = get_db()->getTable('SimpleVocabTerm');
        $types = array();
        foreach ($elementIds as $elementId) {
            $element = $elTable->find($elementId);
            if ($element->name === 'Type') {
                foreach (explode("\n", $terms->findByElementId($elementId)->terms) as $t) {
                    $types[$t] = $t;
                }
            } else {
                $elementValue = metadata($this->item, array($element->getElementSet()->name, $element->name), array('no_escape' => true, 'no_filter' => true));
                if (!empty($elementValue) || $elementValue !== NULL) {
                    $elements[$elementId] = metadata($element, 'name') . ': ' . $elementValue;
                }
            }
        }

        if (count($types) > 0) {
            $multiselect = new Zend_Form_Element_Multiselect('itemtypes');
            $multiselect->setMultiOptions($types);
            $multiselect->setLabel('Search by Type');
            $this->addElement($multiselect);
        }
        $checkboxes = new Zend_Form_Element_MultiCheckbox('elements');
        $checkboxes->setMultiOptions($elements);
        $this->addElement($checkboxes);
        $this->addElement('hidden', 'item_id', array('value' => $this->item->id));
        $this->addElement('submit', __('Find'));
    }

    protected function setItem($item)
    {
        $this->item = $item;
    }
}
