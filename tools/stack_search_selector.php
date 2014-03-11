<?php  
/**
 * Popup dialog to support the Form Helper for selecting stacks
 * @author Daniel Mitchell <glockops@gmail.com>
 * @license    http://creativecommons.org/licenses/by-sa/3.0/     Creative Commons Attribution-ShareAlike 3.0 Unported License
 *
 * Dialog is based heavily off of the elements/block_area_add_stack.php in the core
 */

defined('C5_EXECUTE') or die("Access Denied.");
Loader::model('stack/list');
$sl = new StackList();
$sl->filterByUserAdded();
$stacks = $sl->get();

$form = Loader::helper('form');

?>

<script type="text/javascript">

$('input[name=ccmStackSearch]').focus(function() {
	if ($(this).val() == '<?php echo t("Search")?>') {
		$(this).val('');
	}
	$(this).css('color', '#000');

	if (!ccmLiveSearchActive) {
		$('#ccmStackSearch').liveUpdate('ccm-stack-list', 'stacks');
		ccmLiveSearchActive = true;
	}
});

ccmStackSearchFormCheckResults = function() {
	return false;
}


var ccmLiveSearchActive = false;
ccmStackSearchResultsSelect = function(which, e) {

	e.preventDefault();
	e.stopPropagation();

	// find the currently selected item
	var obj = $("li.ccm-item-selected");
	var foundblock = false;
	if (obj.length == 0) {
		$($("#ccm-stack-list li.ccm-stack-available")[0]).addClass('ccm-item-selected');
	} else {
		if (which == 'next') {
			var nextObj = obj.nextAll('li.ccm-stack-available');
			if (nextObj.length > 0) {
				obj.removeClass('ccm-item-selected');
				$(nextObj[0]).addClass('ccm-item-selected');
			}
		} else if (which == 'previous') {
			var prevObj = obj.prevAll('li.ccm-stack-available');
			if (prevObj.length > 0) {
				obj.removeClass('ccm-item-selected');
				$(prevObj[0]).addClass('ccm-item-selected');
			}
		}
		
	}	

	var currObj = $("li.ccm-item-selected");
	// handle scrolling
	// this is buggy. needs fixing

	var currPos = currObj.position();
	var currDialog = currObj.parents('div.ui-dialog-content');
	var docViewTop = currDialog.scrollTop();
	var docViewBottom = docViewTop + currDialog.innerHeight();

	var elemTop = currObj.position().top;
	var elemBottom = elemTop + docViewTop + currObj.innerHeight();

	if ((docViewBottom - elemBottom) < 0) {
		currDialog.get(0).scrollTop += currDialog.get(0).scrollTop + currObj.height();
	} else if (elemTop < 0) {
		currDialog.get(0).scrollTop -= currDialog.get(0).scrollTop + currObj.height();
	}


	return true;
	
}
ccmStackSearchDoMapKeys = function(e) {

	if (e.keyCode == 40) {
		ccmStackSearchResultsSelect('next', e);
	} else if (e.keyCode == 38) {
		ccmStackSearchResultsSelect('previous', e);
	} else if (e.keyCode == 13) {
		var obj = $("li.ccm-item-selected");
		if (obj.length > 0) {
			obj.find('a').click();
		}
	}
}
ccmStackSearchMapKeys = function() {
	$(window).bind('keydown.blocktypes', ccmStackSearchDoMapKeys);
}
ccmStackSearchResetKeys = function() {
	$(window).unbind('keydown.blocktypes');
}

$(function() {
	$(window).css('overflow', 'hidden');
	$(window).unbind('keydown.blocktypes');
	ccmStackSearchMapKeys();
	$("#ccmStackSearch").get(0).focus();

});

ccmStackAddToField = function(stID, sName) {
	ccm_selectStackNode(stID,sName);
	$.fn.dialog.closeTop();	
}

</script>


<div id="ccm-add-tab">
	<div class="ccm-block-type-search-wrapper ">

		<form onsubmit="return ccmStackSearchFormCheckResults()">
		<div class="ccm-block-type-search">
		<?php echo $form->text('ccmStackSearch', array('tabindex' => 1, 'autocomplete' => 'off', 'style' => 'width: 168px'))?>
		</div>
		
		</form>
		
	</div>
	
	<ul id="ccm-stack-list" class="icon-select-list icon-select-list-groups">
	<?php  if (count($stacks) > 0) { 
		foreach($stacks as $s) { ?>	
			<li class="ccm-stack-available">
				<a onclick="ccmStackAddToField(<?php echo $s->getCollectionID()?>, '<?php echo $s->getCollectionName()?>')" href="javascript:void(0)"><?php echo $s->getCollectionName()?></a>
			</li>
			
		<?php  }
	} else { ?>
		<br/>
		<p><?php echo t('No stacks can be added to this area.')?></p>
	<?php  } ?>
	</ul>
</div>

