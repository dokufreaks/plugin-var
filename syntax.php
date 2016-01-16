<?php
/**
 * Variable Plugin: allows to insert dynamic variables
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
require_once(DOKU_INC.'inc/infoutils.php');

class syntax_plugin_var extends DokuWiki_Syntax_Plugin {

	/// Added in 2015-06 release to implement 
    static $wikiVERSION;

    function getType() { return 'substition'; }
    function getSort() { return 99; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('@\w{2,6}@', $mode, 'plugin_var'); }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match, 1, -1); // strip markup
        return array($match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        global $INFO;
        global $conf;

        $meta    = $data[0];
        $nocache = false;
        switch ($meta) {
            case 'ID':
                $xhtml = $ID;
                $meta  = $xhtml;
                break;
            case 'NS':
                $xhtml = getNS($ID);
                $meta  = $xhtml;
                break;
            case 'PAGE':
                $xhtml = strtr(noNS($ID),'_',' ');
                $meta  = $xhtml;
                break;
            case 'USER':
                $xhtml   = $_SERVER['REMOTE_USER'];
                $nocache = true;
                break;
            case 'NAME':
                $xhtml   = ($_SERVER['REMOTE_USER'] ? $INFO['userinfo']['name'] : clientIP());
                $nocache = true;
                break;
            case 'MAIL':
                $xhtml   = ($_SERVER['REMOTE_USER'] ? $INFO['userinfo']['mail'] : '');
                $nocache = true;
                break;
            case 'DATE':
                $xhtml   = strftime($conf['dformat']);
                $nocache = true;
                break;
            case 'YEAR':
                $xhtml = date('Y');
                break;
            case 'MONTH':
                $xhtml = date('m');
                break;
            case 'DAY':
                $xhtml   = date('d');
                $nocache = true;
                break;
            case 'WIKI':
                $xhtml = $conf['title'];
                break;
            case 'TITLE':
                $xhtml = ($INFO['meta']['title']) ? $INFO['meta']['title'] : $meta;
                $nocache = true;
                break;
            case 'SERVER':
                $xhtml = ($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $meta;
                $nocache = true;
                break;
            case 'VER':
                $xhtml = self::$wikiVERSION;
                break;
            case 'VERR':
                list($vrel,$vdate,$vname) = explode(' ', self::$wikiVERSION);
                $xhtml = trim($vdate);
                break;
            case 'VERN':
                list($vrel,$vdate,$vname) = explode(' ', self::$wikiVERSION);
                $xhtml = trim(substr($vname, 1, -1));
                break;
            default:
                // for unknown match render original
                $xhtml = "@{$meta}@";
                break;
        }

        if ($mode == 'metadata') {
            $renderer->cdata($meta);
        } else {
            $renderer->cdata($xhtml);

            if ($nocache) {
                $renderer->nocache();
            }
        }

        return true;
    }
}
syntax_plugin_var::$wikiVERSION = getVersion();

// vim:ts=4:sw=4:et:enc=utf-8:
