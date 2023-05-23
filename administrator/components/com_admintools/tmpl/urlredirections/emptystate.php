<?php
/**
 * @package   admintools
 * @copyright Copyright (c)2010-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
	'textPrefix' => 'COM_ADMINTOOLS_URLREDIRECTIONS',
	'formURL'    => 'index.php?option=com_admintools&view=urlredirections',
	//'helpURL'    => 'https://docs.joomla.org/Special:MyLanguage/Adding_a_new_article',
	'icon'       => 'fa fa-random',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_admintools'))
{
	$displayData['createURL'] = 'index.php?option=com_admintools&task=urlredirection.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
