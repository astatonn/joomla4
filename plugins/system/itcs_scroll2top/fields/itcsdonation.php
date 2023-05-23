<?php
/**
* Joomla.Plugin - itcs donation field
* ------------------------------------------------------------------------
* @package     itcs donation field
* @author      it-conserv.de
* @copyright   2020 it-conserv.de
* @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
* @link        https://it-conserv.de
* ------------------------------------------------------------------------
*/ 

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for itcs Extensions.
 * Provides a donation code check.
 */
class JFormFieldItcsDonation extends FormField
{
	protected $type = 'itcsdonation';

	protected function getInput()
	{
		$html = '
		<button class="btn btn-success" onclick="window.open(\'https://www.paypal.me/peerluks/5EUR\');" type="button"><span class="icon-smiley-2 icon-white" aria-hidden="true"></span> 5 €</button>
		<button class="btn btn-success" onclick="window.open(\'https://www.paypal.me/peerluks/10EUR\');" type="button"><span class="icon-thumbs-up icon-white" aria-hidden="true"></span> 10 €</button>
		<button class="btn btn-success" onclick="window.open(\'https://www.paypal.me/peerluks/15EUR\');" type="button"><span class="icon-heart-2 icon-white" aria-hidden="true"></span> 15 €</button>
		<button class="btn btn-success" onclick="window.open(\'https://www.paypal.me/peerluks/\');" type="button"><span class="icon-star icon-white" aria-hidden="true"></span> # €</button>
		';
		return $html;
	}

	/*
	protected function getLabel()
	{
		return Text::_('ITCSDONATION'); //ITCSDONATION="Projekt unterstützen?"
	}
	*/
}
