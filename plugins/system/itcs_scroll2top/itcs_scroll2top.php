<?php
/**
* Joomla.Plugin - itcs Scroll to Top Button
* ------------------------------------------------------------------------
* @package     itcs Scroll to Top Button
* @author      it-conserv.de
* @copyright   2020 it-conserv.de
* @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
* @link        https://it-conserv.de
* ------------------------------------------------------------------------
*/

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * plg_itcs_scroll2top
 */
class PlgSystemItcs_scroll2top extends CMSPlugin
{

	/**
	 * Application object
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object	$subject The object to observe
	 * @param 	array   $config  An array that holds the plugin configuration
	 * @since	1.0
	 */
	public function onAfterDispatch()	
	{

		//check client
		if ( ! $this->app->isClient('site')) return;		

		//size
		$this->size = $this->params->get('size','medium');

		//color
		$cust_color = $this->params->get('s2t_color');
		$this->color = $this->params->get('color','blue');
		
		$bg_color = $this->params->get('bg_color','');
		$bg_color = ($bg_color == '') ? 'transparent' : $bg_color;

		//icon
		$this->icon = $this->params->get('icon','ion-chevron-up');
		$this->img = $this->params->get('s2t_image','');

		// Load CSS/JS
		$document = $this->app->getDocument();

		if (!($document instanceof \Joomla\CMS\Document\HtmlDocument))
		{
		   return;
		}

		$wa = $document->getWebAssetManager();
		$wa->getRegistry()->addRegistryFile('media/plg_system_itcs_scroll2top/joomla.asset.json');
		
		$wa->useStyle('plg_system_itcs_scroll2top.scroll2top')
			->useScript('plg_system_itcs_scroll2top.scroll2top');

		if (stripos($this->icon,"ion") !== false & $this->img == ''){
			$wa->useStyle('plg_system_itcs_scroll2top.ion');
		}
		if (stripos($this->icon,"fa-") !== false & $this->img == ''){
			$wa->useStyle('plg_system_itcs_scroll2top.fa');
		}

		// custom Styles
		// margins
		$mr	=	$this->params->get('s2t_right', 20);
		$mb	=	$this->params->get('s2t_bottom', 20);

		// add custom style
		$wa->addInlineStyle('
		.snip1452.custom:hover,.scrollToTop.snip1452.custom:hover [class^="fa-"]::before,.scrollToTop.snip1452.custom:hover [class*="fa-"]::before{color: '.$cust_color.';}
		.snip1452.custom:hover:after{border-color: '.$cust_color.';}
		.scrollToTop{right: '.$mr.'px;bottom: '.$mb.'px;}
		.scrollToTop.snip1452::after{background-color: ' . $bg_color . ';}		
		');
	}
	
	/**
	 * Do something onAfterRender
	 */
	public function onAfterRender()
	{

		//check client
		if ( ! $this->app->isClient('site')) return;

		$body = $this->app->getBody();
		
		$Scroll2top = "\n";
		
		// create Scroll2Top Button
		$Scroll2top .= '<div id="scroll2top" class="scrollToTop snip1452 '.$this->size.' '.$this->color.'" data-scroll="top">';
			if ( $this->img !='' ){
				$Scroll2top .= '<img src="' . $this->img . '" alt="top" />';
			}
			else{
				$Scroll2top .= '<i class="' . $this->icon . '"></i>';
			}
		$Scroll2top .= '</div>'."\n";

		$pos = strrpos($body, "</body>");

		if($pos > 0)
		{
			$body = substr($body, 0, $pos)."\n".'<!-- Scroll to Top -->'.$Scroll2top.'<!-- End Scroll to Top -->'."\n".substr($body, $pos);
			$this->app->setBody($body);
		}

		return true;
	
	}

}