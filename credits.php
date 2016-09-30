<?php
/**
* Lista di Credits dell'applicazione VFront. 
* Nel file vengono mostrate le applicazione di terze parte utilizzate e le loro tipologie di licenza.
* 
* @package VFront
* @author Mario Marcello Verona <marcelloverona@gmail.com>
* @copyright 2007-2010 M.Marcello Verona
* @version 0.96 $Id: credits.php 1076 2014-06-13 13:03:44Z marciuz $
* @license http://www.gnu.org/licenses/gpl.html GNU Public License
*/

require("./inc/conn.php");
require("./inc/layouts.php");

echo openLayout1(_("Credits and third party software and libraries"));

echo breadcrumbs(array("javascript:history.back();"=>_('go back'),""));

echo "<h1>"._("Credits and third party software")."</h1>\n";

echo "<p>VFront &egrave; un software scritto da Marcello Verona &lt;marcelloverona@gmail.com&gt; e rilasciato sotto License: <acronym title=\"GNU Public Licence\">GPL</acronym> 2.0<br />

Il <a href=\"http://www.gnu.org/licenses/gpl.html\">testo completo</a> della License: &egrave; leggibile dal sito GNU</p>\n";

echo "
<p>Sono qui citate le applicazioni sviluppate da terze parti attualmente in uso in VFront. Alcune applicazioni sono opzionali. E' riportata anche la tipologia di License: delle applicazioni suddette.</p>

<hr />

<p><b>Prototype</b>  <a href=\"http://prototype.conio.net/\" target=\"_blank\">http://prototype.conio.net/</a><br />  
Libreria di funzioni javascript<br />
License: MIT  - http://www.opensource.org/licenses/mit-license.php </p>

<p><b>Scriptaculous</b>  <a href=\"http://script.aculo.us/\" target=\"_blank\">http://script.aculo.us/</a><br />
Libreria di funzioni javascript<br />
License: MIT  - http://www.opensource.org/licenses/mit-license.php </p>

<p><b>PHPMailer</b>  <a href=\"http://phpmailer.sourceforge.net/\" target=\"_blank\">http://phpmailer.sourceforge.net/</a><br />
Libreria per l'invio di email tramite PHP<br />
License: LGPL - http://www.opensource.org/licenses/lgpl-license.php</p>

<p><b>YAV</b>  <a href=\"http://yav.sourceforge.net\" target=\"_blank\">http://yav.sourceforge.net/</a><br />
Tool di validazione Javascript<br />
License: LGPL - http://www.opensource.org/licenses/lgpl-license.php</p>

<p><b>Apache FOP</b>  <a href=\"http://xmlgraphics.apache.org/fop/\" target=\"_blank\">http://xmlgraphics.apache.org/fop/</a><br />
Generazione di report PDF ed altri formati mediante trasformazione XSL-FO<br />
License: Apache 2.0 - http://www.apache.org/licenses/</p>

<p><b>DHTMLxGrid</b>  <a href=\"http://www.scbr.com/docs/products/dhtmlxGrid/\" target=\"_blank\">http://www.scbr.com/docs/products/dhtmlxGrid/</a><br />
Generatore di tabelle dinamiche in javascript nella vista tabella<br />
License: GPL - http://www.gnu.org/licenses/gpl.html</p>

<p><b>Algoritmo MD5 per Javascript</b>  <a href=\"http://pajhome.org.uk/crypt/md5\" target=\"_blank\">http://pajhome.org.uk/crypt/md5</a><br />
Generatore di hash md5 in javascript nei campi password delle maschere<br />
License: BSD - http://www.opensource.org/licenses/bsd-license.php</p>

<p><b>FCKeditor</b> - <a href=\"http://www.fckeditor.net/\" target=\"_blank\">http://www.fckeditor.net/</a><br />
Rich text editor in javascript<br />
License: LGPL - http://www.opensource.org/licenses/lgpl-license.php</p>

<p><b>JsCalendar</b>  <a href=\"http://sourceforge.net/projects/jscalendar/\" target=\"_blank\">http://sourceforge.net/projects/jscalendar/</a><br />
Generatore di calendari in javascript<br />
License: GPL  - http://www.gnu.org/licenses/gpl.html</p>

<p><b>Adminer</b>  <a href=\"http://www.adminer.org\" target=\"_blank\">http://www.adminer.org</a><br />
Database administration<br />
License: GPL  - http://www.gnu.org/licenses/gpl.html</p>


<p>Le <b>icone</b> utilizzate fanno parte del progetto Crystal <a href=\"http://www.everaldo.com/crystal/\" target=\"_blank\">http://www.everaldo.com/crystal/</a><br />
License: LGPL  - http://www.everaldo.com/crystal/?action=license</p>
\n";



echo closeLayout1();


?>