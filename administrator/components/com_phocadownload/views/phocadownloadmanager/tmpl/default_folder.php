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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
$group 	= PhocaDownloadSettings::getManagerGroup($this->manager);

if ($this->manager == 'filemultiple') {

	$checked 	= HTMLHelper::_('grid.id', $this->folderi, $this->folders[$this->folderi]->path_with_name_relative_no, 0, 'foldercid' );
	$link		= 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager'
		 .'&amp;manager='.htmlspecialchars($this->manager)
		 .$group['c']
		 .'&amp;folder='.htmlspecialchars($this->_tmp_folder->path_with_name_relative_no)
		 .'&amp;field='. htmlspecialchars((string)$this->field);

	echo '<tr>'
	.' <td>'. $checked .'</td>'
	.' <td class="ph-img-table"><a href="'. Route::_( $link ).'">'
	. HTMLHelper::_( 'image', $this->t['i'].'icon-16-folder-small.png', '').'</a></td>'
	.' <td><a href="'. Route::_( $link ).'">'. $this->_tmp_folder->name.'</a></td>'
	.'</tr>';

} else {

	$link		= 'index.php?option=com_phocadownload&amp;view=phocadownloadmanager'
		 .'&amp;manager='. $this->manager
		 . $group['c']
		 .'&amp;folder='.$this->_tmp_folder->path_with_name_relative_no
		 .'&amp;field='. $this->field;

	echo '<tr>'
	.' <td></td>'
	.' <td class="ph-img-table"><a href="'. Route::_( $link ).'">'
	. HTMLHelper::_( 'image', $this->t['i'].'icon-16-folder-small.png', Text::_('COM_PHOCADOWNLOAD_OPEN')).'</a></td>'
	.' <td><a href="'. Route::_( $link ).'">'. $this->_tmp_folder->name.'</a></td>'
	.'</tr>';
}
?>
