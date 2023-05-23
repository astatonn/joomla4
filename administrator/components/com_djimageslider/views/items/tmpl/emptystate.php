<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_DJIMAGESLIDER_ITEMS',
	'formURL'    => 'index.php?option=com_djimageslider&view=items',
	'helpURL'    => 'https://support.dj-extensions.com/portal/en/kb/articles/creating-new-custom-item',
	'icon'       => 'generic',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_djimageslider'))
{
	$displayData['createURL'] = 'index.php?option=com_djimageslider&task=item.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
