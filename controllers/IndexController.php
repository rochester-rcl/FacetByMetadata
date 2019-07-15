<?php

class FacetByMetadata_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $elementIds = $this->getParam('elements');
        $itemTypes = $this->getParam('itemtypes');
        $itemId = $this->getParam('item_id');
        $db = $this->_helper->db;
        $item = $db->getTable('Item')->find($itemId);
        $advanced = array();
        $e = $db->getTable('Element')->findByElementSetNameAndElementName('Dublin Core', 'Type');
        if (count($elementIds) > 0) {
            foreach ($elementIds as $elementId) {
                $element = $db->getTable('Element')->find($elementId);
                if (count($itemTypes) > 0) {
                    foreach ($itemTypes as $itemType) {
                        if ($element) {
                            $term = metadata($item, array($element->getElementSet()->name, $element->name), array('no_filter' => true));
                            if (!empty($term)) {
                                $advanced[] = array('element_id' => $elementId, 'terms' => $term, 'type' => 'is exactly', 'joiner' => 'or');
                            }
                        }
                        $advanced[] = array('element_id' => $e->id, 'terms' => $itemType, 'type' => 'is exactly');
                    }
                } else {
                    if ($element) {
                        $term = metadata($item, array($element->getElementSet()->name, $element->name), array('no_filter' => true));
                        if (!empty($term)) {
                            $advanced[] = array('element_id' => $elementId, 'terms' => $term, 'type' => 'is exactly', 'joiner' => 'and');
                        }
                    }
                }
            }
        }
        $paramArray = array('search' => '', 'advanced' => $advanced);
        $params = http_build_query($paramArray);
        $this->_helper->redirector->gotoUrl('items/browse?' . $params);
    }
}
