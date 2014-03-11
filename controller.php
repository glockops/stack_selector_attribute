<?php       

defined('C5_EXECUTE') or die(_("Access Denied."));

class StackSelectorAttributePackage extends Package {

	protected $pkgHandle = 'stack_selector_attribute';
	protected $appVersionRequired = '5.5.0';
	protected $pkgVersion = '1.1';
	
	public function getPackageDescription() {
		return t("Allows stacks to be used as attributes and adds form helpers for using stacks in blocks");
	}
	
	public function getPackageName() {
		return t("Stack Selector Attribute");
	}
	
	public function install() {
		$pkg = parent::install();
		$pkgh = Package::getByHandle('stack_selector_attribute'); 
		Loader::model('attribute/categories/collection');
		$col = AttributeKeyCategory::getByHandle('collection');
		$pageselector = AttributeType::add('stack_selector', t('Stack Selector'), $pkgh);
		$col->associateAttributeKeyType(AttributeType::getByHandle('stack_selector'));
	}
}