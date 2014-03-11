<?php  
defined('C5_EXECUTE') or die("Access Denied.");

class StackSelectorAttributeTypeController extends AttributeTypeController  {

	protected $searchIndexFieldDefinition = 'I 11 DEFAULT 0 NULL';

	public function getValue() {
		$db = Loader::db();
		$value = $db->GetOne("select value from atStackSelector where avID = ?", array($this->getAttributeValueID()));
		return $value;	
	}
	
	public function searchForm($list) {
		$PagecID = $this->request('value');
		$list->filterByAttribute($this->attributeKey->getAttributeKeyHandle(), $PagecID, '=');
		return $list;
	}
	
	public function search() {
		$form_selector = Loader::helper('form/stack_selector','stack_selector_attribute');
		print $form_selector->select_stack($this->field('value'), $this->request('value'));
	}
	
	public function form() {
		if (is_object($this->attributeValue)) {
			$value = $this->getAttributeValue()->getValue();
		}
		$form_selector = Loader::helper('form/stack_selector','stack_selector_attribute');
		print $form_selector->select_stack($this->field('value'), $value);
	}
	
	/*
	public function validateForm($p) {
		return $p['value'] != 0;
	}
	*/

	public function saveValue($value) {
		$db = Loader::db();
		$db->Replace('atStackSelector', array('avID' => $this->getAttributeValueID(), 'value' => $value), 'avID', true);
	}
	
	public function deleteKey() {
		$db = Loader::db();
		$arr = $this->attributeKey->getAttributeValueIDList();
		foreach($arr as $id) {
			$db->Execute('delete from atStackSelector where avID = ?', array($id));
		}
	}
	
	public function saveForm($data) {
		$db = Loader::db();
		$this->saveValue($data['value']);
	}
	
	public function deleteValue() {
		$db = Loader::db();
		$db->Execute('delete from atStackSelector where avID = ?', array($this->getAttributeValueID()));
	}
	
}