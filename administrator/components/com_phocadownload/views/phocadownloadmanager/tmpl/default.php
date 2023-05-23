<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
 defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\Path;
/*
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('dropdown.init');
JHtml::_('formbehavior.chosen', 'select');*/

$r 			=  new PhocaDownloadRenderAdminView();

if ($this->manager == 'filemultiple') {

    Factory::getDocument()->addScriptDeclaration('
	
	Joomla.submitbutton = function(task)
	{
		if (task == \'phocadownloadm.cancel\') {
			Joomla.submitform(task);
		}

		if (task == \'phocadownloadm.save\') {
			var phocadownloadmform = document.getElementById(\'adminForm\');
			if (phocadownloadmform.boxchecked.value==0) {
				alert( "'. Text::_( 'COM_PHOCADOWNLOAD_WARNING_SELECT_FILENAME_OR_FOLDER', true ).'" );
			} else  {
				var f = phocadownloadmform;
				var nSelectedImages = 0;
				var nSelectedFolders = 0;
				var i=0;
				cb = eval( \'f.cb\' + i );
				while (cb) {
					if (cb.checked == false) {
						// Do nothing
					}
					else if (cb.name == "cid[]") {
						nSelectedImages++;
					}
					else {
						nSelectedFolders++;
					}
					// Get next
					i++;
					cb = eval( \'f.cb\' + i );
				}

				if (phocadownloadmform.jform_catid.value == "" && nSelectedImages > 0){
					alert( "'. Text::_( 'COM_PHOCADOWNLOAD_WARNING_FILE_SELECTED_SELECT_CATEGORY', true ).'" );
				} else {
					Joomla.submitform(task);
				}
			}
		}
		//Joomla.submitform(task);
	}');

}

echo '<div id="phocadownloadmanager">';

if ($this->manager == 'filemultiple') {
	echo $r->startForm($this->t['o'], $this->t['task'], 'adminForm', 'adminForm');
	echo '<div class="col-sm-4 form-horizontal" style="border-right: 1px solid #d3d3d3;padding-right: 5px;">';
	echo '<h4>'. Text::_('COM_PHOCADOWNLOAD_MULTIPLE_ADD').'</h4>';

	echo '<div>'."\n";
	$formArray = array ('title', 'alias','published', 'approved', 'ordering', 'catid', 'language', 'pap_copy_m');
	echo $r->group($this->form, $formArray);
	echo '</div>'. "\n";

	echo '</div>'. "\n";
}

if ($this->manager == 'filemultiple') {
	echo '<div class="col-sm-8 form-horizontal">';
} else {
	echo '<div class="span12 form-horizontal">';
}

echo '<div class="pd-admin-path">' . Text::_('COM_PHOCADOWNLOAD_PATH'). ': '.Path::clean($this->t['path']['orig_abs_ds']. $this->folderstate->folder) .'</div>';

//$countFaF =  count($this->images) + count($this->folders);
echo '<table class="table table-hover table-condensed ph-multiple-table">'
.'<thead>'
.'<tr>';
echo '<th class="hidden-phone ph-check">'. "\n";
if ($this->manager == 'filemultiple') {
	echo '<input type="checkbox" name="checkall-toggle" value="" title="'.Text::_('JGLOBAL_CHECK_ALL').'" onclick="Joomla.checkAll(this)" />'. "\n";
} else {
	echo '';
}
echo '</th>'. "\n";

echo '<th width="20">&nbsp;</th>'
.'<th width="95%">'.Text::_( $this->t['l'].'_FILENAME' ).'</th>'
.'</tr>'
.'</thead>';




/*
echo '<div class="pd-admin-files">';

if ($this->manager == 'filemultiple' && (count($this->files) > 0 || count($this->folders) > 0)) {
	echo '<div class="pd-admin-file-checkbox">';
	$fileFolders = count($this->files) + count($this->folders);
	echo '<input type="checkbox" name="toggle" value="" onclick="checkAll('.$fileFolders.');" />';
	echo '&nbsp;&nbsp;'. Text::_('COM_PHOCADOWNLOAD_CHECK_ALL');
	echo '</div>';
}*/
echo '<tbody>';
echo $this->loadTemplate('up');
if (count($this->files) > 0 || count($this->folders) > 0) { ?>
<div>

	<?php for ($i=0,$n=count($this->folders); $i<$n; $i++) :
		$this->setFolder($i);
		$this->folderi = $i;
		echo $this->loadTemplate('folder');
	endfor; ?>

	<?php for ($i=0,$n=count($this->files); $i<$n; $i++) :
		$this->setFile($i);
		$this->filei = $i;
		echo $this->loadTemplate('file');
	endfor; ?>

</div>
<?php } else {
	echo '<tr>'
	.'<td>&nbsp;</td>'
	.'<td>&nbsp;</td>'
	.'<td>'.Text::_( $this->t['l'].'_THERE_IS_NO_FILE' ).'</td>'
	.'</tr>';
}
echo '</tbody>'
.'</table>';

if ($this->manager == 'filemultiple') {

	echo '<input type="hidden" name="task" value="" />'. "\n";
	echo '<input type="hidden" name="boxchecked" value="0" />'. "\n";
	echo '<input type="hidden" name="layout" value="edit" />'. "\n";
	echo HTMLHelper::_('form.token');
	echo $r->endForm();

	echo '</div>';
	echo '<div class="clearfix"></div>';

} ?>

<div style="border-bottom:1px solid #cccccc;margin-bottom: 10px">&nbsp;</div>

<?php
if ($this->t['displaytabs'] > 0) {

	/*echo '<ul class="nav nav-tabs" id="configTabs">';

	$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-upload.png','') . '&nbsp;'.Text::_($this->t['l'].'_UPLOAD');
	echo '<li><a href="#upload" data-toggle="tab">'.$label.'</a></li>';

	if((int)$this->t['enablemultiple']  > 0) {
		$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-upload-multiple.png','') . '&nbsp;'.Text::_($this->t['l'].'_MULTIPLE_UPLOAD');
		echo '<li><a href="#multipleupload" data-toggle="tab">'.$label.'</a></li>';
	}

	$label = HTMLHelper::_( 'image', $this->t['i'].'icon-16-folder.png','') . '&nbsp;'.Text::_($this->t['l'].'_CREATE_FOLDER');
	echo '<li><a href="#createfolder" data-toggle="tab">'.$label.'</a></li>';

	echo '</ul>';*/

	$activeTab = '';
	if (isset($this->t['tab']) && $this->t['tab'] != '') {
	    $activeTab = $this->t['tab'];
    } else  {
		$activeTab = 'multipleupload';
	}

	echo $r->startTabs($activeTab);

	$tabs = array();
	$tabs['multipleupload'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-bl duotone icon-upload"></i></span>' . '&nbsp;'.Text::_('COM_PHOCADOWNLOAD_MULTIPLE_UPLOAD');
	$tabs['upload'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-bd duotone icon-upload"></i></span>' . '&nbsp;'.Text::_('COM_PHOCADOWNLOAD_UPLOAD');

	if (!empty($this->t['javaupload'])) {
	    $tabs['javaupload'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-rl duotone icon-upload"></i></span>' . '&nbsp;'.Text::_('COM_PHOCADOWNLOAD_JAVA_UPLOAD');
    }

	$tabs['createfolder'] = '<span class="ph-cp-item"><i class="phi phi-fs-s phi-fc-brd duotone icon-folder"></i></span>' . '&nbsp;'.Text::_('COM_PHOCADOWNLOAD_CREATE_FOLDER');

	echo $r->navigation($tabs, $activeTab);

	echo $r->startTab('multipleupload', $tabs['multipleupload'], $activeTab == 'multipleupload' ? 'active' : '');
	echo $this->loadTemplate('multipleupload');
	echo $r->endTab();

	echo $r->startTab('upload', $tabs['upload'], $activeTab == 'upload' ? 'active' : '');
	echo $this->loadTemplate('upload');
	echo $r->endTab();

	echo $r->startTab('createfolder', $tabs['createfolder'], $activeTab == 'createfolder' ? 'active' : '');


	echo PhocaDownloadFileUpload::renderCreateFolder($this->session->getName(), $this->session->getId(), $this->currentFolder, 'phocadownloadmanager', 'manager='.PhocaDownloadUtils::filterValue($this->manager, 'alphanumeric').'&amp;tab=createfolder&amp;field='. PhocaDownloadUtils::filterValue($this->field, 'alphanumeric2') );
	echo $r->endTab();

	echo $r->endTabs();
}
echo '</div>';

/*
if ($this->t['tab'] != '') {$jsCt = 'a[href=#'.PhocaDownloadUtils::filterValue($this->t['tab'], 'alphanumeric') .']';} else {$jsCt = 'a:first';}
echo '<script type="text/javascript">';
echo '   jQuery(\'#configTabs '.$jsCt.'\').tab(\'show\');'; // Select first tab
echo '</script>';
*/
?>
