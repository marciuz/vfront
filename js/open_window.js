/**
 * Funzione che apre una finestra popup
 *
 * @package VFront
 * @author Mario Marcello Verona <marcelloverona@gmail.com>
 * @copyright 2007 Mario Marcello Verona
 * @version 0.90
 * @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/
function openWindow(url, name, percent) {
	    var w = 630, h = 440; // default sizes
	    if (window.screen) {
	        w = window.screen.availWidth * percent / 100;
	        h = window.screen.availHeight * percent / 100;
	    }
	   winRef= window.open(url,name,'width='+w+',height='+h+' ,toolbar=yes, location=no,status=yes,menubar=no,scrollbars=yes,resizable=yes');
	   winRef.focus();
}