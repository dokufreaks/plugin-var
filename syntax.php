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

class syntax_plugin_var extends DokuWiki_Syntax_Plugin {

    function getType() { return 'substition'; }
    function getSort() { return 99; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('@\w{2,6}@', $mode, 'plugin_var'); }

    function handle($match, $state, $pos, &$handler) {
        $match = substr($match, 1, -1); // strip markup
        return array($match);
    }

    function render($mode, &$renderer, $data) {
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
            default:
                // for unknown match render original
                $xhtml = "@{$meta}@";
                break;
        }

        // for XHTML output
        if ($mode == 'xhtml') {
            // prevent caching to ensure the included pages are always fresh
            if ($nocache) $renderer->info['cache'] = false;

            $renderer->doc .= hsc($xhtml);
            return true;

        // for metadata renderer
        } elseif ($mode == 'metadata') {
            if ($renderer->capture) $renderer->doc .= $meta;
            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et:enc=utf-8:
