<?php
/**
 * @package     Joomla.Field
 * @author      it-conserv.de
 * @copyright   2020 it-conserv.de
 * @license     GNU/GPLv3 <http://www.gnu.org/licenses/gpl-3.0.de.html>
 * @link        https://it-conserv.de
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;
use Joomla\CMS\Form\FormField;

/* ******************* 
 Example in the xml-file
 copy this file in the folder elements:
 <fields name="params" addfieldpath="/modules/mod_j51inlineicons/elements">
 <field name="myfield" type="textpx" default="42" label="Size" description="Size in px" />
 ********************* */
 
class JFormFieldtextpx extends FormField {
 
        protected $type = 'textpx';
 
		protected function getLabel(){
			
			return parent::getLabel();
		}
 
        public function getInput() {

            return 	'<div class="input-append input-group">'.
					'<input class="input-medium form-control" type="text" validate="integer" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
					. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"/>'.
					'<div class="input-group-text">px</div>'.
					'</div>';
        }
}
