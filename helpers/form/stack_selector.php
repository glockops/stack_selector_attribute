<?php

/**
 * Special form elements for choosing a stack.
 * @author Daniel Mitchell <glockops@gmail.com>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/     Creative Commons Attribution-ShareAlike 3.0 Unported License
 *
 * Helper is based of Andrew Embler's work on the page_selector helper.
 */

defined('C5_EXECUTE') or die("Access Denied.");
class FormStackSelectorHelper {
	
	
	protected $package  = 'stack_selector_attribute';
	
	/** 
	 * Creates form fields and JavaScript stack chooser for choosing a stack. For use with inclusion in blocks.
	 * <code>
	 *     $sh->select_stack('stackID', 1); // prints out the stack with the stack ID of 1. 
	 * </code>
	 * @param int $stID
	 */
	 
	 public function select_stack($fieldName, $stID = false) {
		
		// Get Collection object for JS comparison 
		global $c; 
		 
		// Build URL to popup dialog
		$uh = Loader::helper('concrete/urls');
		$tools = $uh->getToolsURL('stack_search_selector',$this->package); 

		// Set selected stack if passed
		$selectedStID = 0;
		if (isset($_REQUEST[$fieldName])) {
			$selectedStID = $_REQUEST[$fieldName];
		} else if ($stID > 0) {
			$selectedStID = $stID;
		}
		
		// Build Form
		$html = '';
		$clearStyle = 'display: none';
		$html .= '<div class="ccm-summary-selected-item"  id="'.$c->getCollectionID().'"><div class="ccm-summary-selected-item-inner"><strong class="ccm-summary-selected-item-label">';
		
		// Displays the stack name if selected.
		if ($selectedStID > 0) {
			$stack = Stack::getByID($selectedStID);
			if(is_object($stack)) {
				$html .= $stack->getStackName();
			} else {
				$selectedStID = 0;
			}
			$clearStyle = '';
		}
		$html .= '</strong></div>';
		
		// Displays the stack selection link		
		$html .= '<a class="ccm-select-stack" dialog-sender="' . $fieldName . '" dialog-width="90%" dialog-height="70%" dialog-append-buttons="true" dialog-modal="false" dialog-title="' . t('Choose Stack') . '" href="' . $tools . '">' . t('Select Stack') . '</a>';
		
		// Displays removal link
		$html .= '&nbsp;<a href="javascript:void(0)" dialog-sender="' . $fieldName . '" class="ccm-clear-selected-stack" style="float: right; margin-top: -8px;' . $clearStyle . '"><img src="' . ASSETS_URL_IMAGES . '/icons/remove.png" style="vertical-align: middle; margin-left: 3px" /></a>';
		
		// Hidden form field that contains the selected stack ID
		$html .= '<input type="hidden" name="' . $fieldName . '" value="' . $selectedStID . '"/>';
		$html .= '</div>'; 
		
		// Build the Javascript that is included
		$html .= 
	   '<script type="text/javascript"> 
	    var ccmActiveStackField;
		function ccm_initSelectStack() {
			$("a.ccm-select-stack").unbind().dialog().click(function(){
				ccmActiveStackField = this;
			});
			$("a.ccm-clear-selected-stack").unbind().click(function(){
				ccmActiveStackField = this;
				clearStackSelection();
			});
		};
		function clearStackSelection() {
			// Finds the field sending this request
			var fieldName = $(ccmActiveStackField).attr("dialog-sender");
			var par = $(ccmActiveStackField).parent().find(".ccm-summary-selected-item-label");
			$(ccmActiveStackField).parent().find(".ccm-clear-selected-stack").hide();
			var pari = $(ccmActiveStackField).parent().find("[name=\'"+fieldName+"\']");
			console.log(pari);
			// Removes stack name
			par.html("");
			// Resets hidden form field
			pari.val("0");
		}
		$(ccm_initSelectStack);
		ccm_selectStackNode = function(stID, sName) {
			var fieldName = $(ccmActiveStackField).attr("dialog-sender");
			var cobj = $(ccmActiveStackField).parent().attr("id");
			if(cobj == stID) {
				alert("' . t('You cannot insert a stack into itself.') . '");
				return false;
			}
			var img_url = "' . ASSETS_URL_IMAGES . '";
			if($(ccmActiveStackField).hasClass("ccm-select-stacks")) {
				// This is for a multi-select field
				
				// Build the HTML for a new stack selection
				var html = \'<div class="ccm-summary-selected-item"><div class="ccm-summary-selected-item-inner"><strong class="ccm-summary-selected-item-label" style="float:left;">\';
				html += sName;
				html += "</strong></div>";
				html += \'&nbsp;<a href="javascript:void(0)" dialog-sender=\'+ stID +\' class="ccm-remove-selected-stack" style="float: right;"><img src="\'+ img_url +\'/icons/remove.png" style="vertical-align: middle; margin-left: 3px" /></a>\';
				html += \'<input type="hidden" name="\'+fieldName+\'[]" value="\'+ stID + \'" />\';
				html += "</div>";
				
				$(ccmActiveStackField).parent().append(html);
				
			} else {
				// This is for a single select field
				var par = $(ccmActiveStackField).parent().find(".ccm-summary-selected-item-label");
				$(ccmActiveStackField).parent().find(".ccm-clear-selected-stack").show();
				var pari = $(ccmActiveStackField).parent().find("[name=\'"+fieldName+"\']");
				par.html(sName);
				pari.val(stID);
				
			}	
			return true;		
			
		}
		
		</script>';
		
		return $html;
		
	 }
	
	/** 
	 * Creates form fields and JavaScript stack chooser for choosing multiple stacks. For use with inclusion in blocks.
	 * REQUIRES a special save argument in the controller save() function.
	 *
	 * Code to include in block:
	 * <code>
	 *     $sh->select_multi_stack('stackIDs', '1,2,3'); // prints out the stacks with stack IDs of 1, 2 and 3. 
	 * </code>
	 *
	 * Code to include in save() function in controller
	 * <code>
	 * 		$args['field_name'] = (empty($args['field_name'])) ? 0 : implode(',',$args['field_name']);
	 * </code>
	 * - Change 'field_name' to the field name you gave the select_multi_stack form field.  
	 * You need this line for each multi-select field you've included in your block
	 *
	 * @param int $stIDs, comma separated string of selected stIDs
	 */ 
	 public function select_multi_stack($fieldName, $stIDs) {
		// Get Collection object for JS comparison  
		global $c;
		
		// Build URL to popup dialog
		$uh = Loader::helper('concrete/urls');
		$tools = $uh->getToolsURL('stack_search_selector',$this->package);
		
		// Get the helper for making buttons
		$ih = Loader::helper('concrete/interface');
		
		// Retrieve passed values
		$selectedStIDs = 0;
		if (isset($_REQUEST[$fieldName])) {
			$selectedStIDs = $_REQUEST[$fieldName];
		} else if ($stIDs > 0) {
			$selectedStIDs = $stIDs;
		}
		
		// Convert string into array
		if($selectedStIDs != 0) {
			$selectedStIDs = explode(',',$selectedStIDs);
		}
		
		$html = '';
		
		// Create the wrapper for fields.
		$html .= '<div class="ccm-summary-selected-items" id="'.$c->getCollectionID().'">';
		
		// Create the "Add Stack" button
		$html .= $ih->button(t('Add Stack'),$tools,'left','ccm-select-stacks',array('dialog-sender'=>$fieldName,'dialog-width'=>'90%','dialog-height'=>'70%','dialog-append-buttons'=>'true','dialog-modal'=>'false','dialog-title'=>t('Choose Stack')));
		
		// Insert the Stacks that are selected
		if(is_array($selectedStIDs)) {
			foreach($selectedStIDs as $s) {
				$stack = Stack::getByID($s);
				if(is_object($stack)) {
					$html .= '<div class="ccm-summary-selected-item"><div class="ccm-summary-selected-item-inner"><strong class="ccm-summary-selected-item-label" style="float:left;">' . $stack->getStackName() . '</strong></div>' . 
					'&nbsp;<a href="javascript:void(0)" class="ccm-remove-selected-stack" style="float: right;"><img src="'. ASSETS_URL_IMAGES .'/icons/remove.png" style="vertical-align: middle; margin-left: 3px" /></a>' .
					'<input type="hidden" name="'. $fieldName.'[]" value="'. $stack->cID . '" />' .
					'</div>';
				}
			}
		}
		// Close the wrapper
		$html .= '</div>';
		
		// Include Javascript
		$html .= 
	   '<script type="text/javascript"> 
	    var ccmActiveStackField;
		function ccm_initSelectStacks() {
			$("a.ccm-select-stacks").unbind().dialog().click(function(){
				ccmActiveStackField = this;
			});
			$("a.ccm-remove-selected-stack").live(\'click\', function() {
				ccmActiveStackField = this;
				removeStackSelection();
			});
			
		};
		function removeStackSelection() {
			// Removes the entire parent containing the remove button (including hidden field)
			$(ccmActiveStackField).parent().remove();
		}
		$(ccm_initSelectStacks);
		ccm_selectStackNode = function(stID, sName) {
			var fieldName = $(ccmActiveStackField).attr("dialog-sender");
			var cobj = $(ccmActiveStackField).parent().attr("id");
			if(cobj == stID) {
				alert("' . t('You cannot insert a stack into itself.') . '");
				return false;
			}
			var img_url = "' . ASSETS_URL_IMAGES . '";
			if($(ccmActiveStackField).hasClass("ccm-select-stacks")) {
				// This is for a multi-select field
				
				// Build the HTML for a new stack selection
				var html = \'<div class="ccm-summary-selected-item"><div class="ccm-summary-selected-item-inner"><strong class="ccm-summary-selected-item-label" style="float:left;">\';
				html += sName;
				html += "</strong></div>";
				html += \'&nbsp;<a href="javascript:void(0)" class="ccm-remove-selected-stack" style="float: right;"><img src="\'+ img_url +\'/icons/remove.png" style="vertical-align: middle; margin-left: 3px" /></a>\';
				html += \'<input type="hidden" name="\'+fieldName+\'[]" value="\'+ stID + \'" />\';
				html += "</div>";
				
				$(ccmActiveStackField).parent().append(html);				
				
			} else {
				// This is for a single select field
				var par = $(ccmActiveStackField).parent().find(".ccm-summary-selected-item-label");
				$(ccmActiveStackField).parent().find(".ccm-clear-selected-stack").show();
				var pari = $(ccmActiveStackField).parent().find("[name=\'"+fieldName+"\']");
				par.html(sName);
				pari.val(stID);
				
			}
			return true;			
			
		}
		</script>';
		
		return $html;
		
		
	 }
	
}