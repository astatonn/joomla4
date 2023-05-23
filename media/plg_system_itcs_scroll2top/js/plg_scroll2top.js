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
 
document.addEventListener('DOMContentLoaded', function (event) {
 
	var backToTop = document.getElementById('scroll2top');
 
	if (backToTop) {
 
		function checkScrollPos() {
			if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
				backToTop.classList.add('visible');
			} else {
				backToTop.classList.remove('visible')
			}
		}
 
		checkScrollPos();
 
		window.onscroll = function() {
			checkScrollPos();
		};
 
		backToTop.addEventListener('click', function(event) {
			event.preventDefault();
			window.scrollTo({top:0, left:0, behavior: 'smooth'});
		});
	}
 
});