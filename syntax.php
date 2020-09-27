<?php
/**
 * Variable Plugin: allows to insert dynamic variables
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 */

class syntax_plugin_var extends DokuWiki_Syntax_Plugin {
    protected $aliasSeps = '._-';

    /// Added in 2015-06 release to implement 
    static $wikiVERSION;

    function getType() { return 'substition'; }
    function getSort() { return 99; }
    function connectTo($mode) { $this->Lexer->addSpecialPattern('@\w{2,6}['.$this->aliasSeps.']?@', $mode, 'plugin_var'); }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match, 1, -1); // strip markup
        return array($match);
    }

    function wikiLink($id, $namespace = false)
    {
        if ($namespace)
            $id = getNS($id);
        $link = wl($id, '', true);
        if ($namespace)
            $link .= '/';
        return $link;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        global $INFO;
        global $conf;

        $meta = $data[0];
        $length = strlen($meta);
        $aliasSep = '.';
        $part = substr($meta, 0, -1);
        if ($part == 'ALIAS' && strpos ($this->aliasSeps, $meta[$length-1]) !== false) {
            $aliasSep = $meta[$length-1];
            $meta = $part;
        }
        $metadata = $mode == 'metadata';
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
            case 'NSL':
                $meta  = $this->wikiLink($ID, true);
                $nocache = true;
                if (!$metadata)
                    $renderer->externallink($meta);
                break;
            case 'PAGE':
                $xhtml = strtr(noNS($ID),'_',' ');
                $meta  = $xhtml;
                break;
            case 'PAGEL':
                $meta  = $this->wikiLink($ID);
                $nocache = true;
                if (!$metadata)
                    $renderer->externallink($meta);
                break;
            case 'USER':
                $xhtml   = $_SERVER['REMOTE_USER'];
                $nocache = true;
                break;
            case 'ALIAS':
                $xhtml   = ($_SERVER['REMOTE_USER'] ? str_replace(' ', $aliasSep, $INFO['userinfo']['name']) : $_SERVER['REMOTE_USER']);
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
                $nocache = true;
                break;
            case 'SMONTH':
                $xhtml = date('n');
                $nocache = true;
                break;
            case 'DAY':
                $xhtml   = date('d');
                $nocache = true;
                break;
            case 'SDAY':
                $xhtml   = date('j');
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

        if ($metadata) {
            $renderer->cdata($meta);
        } else {
            if ($xhtml != null) {
                $renderer->cdata($xhtml);
            }
            if ($nocache) {
                $renderer->nocache();
            }
        }

        return true;
    }
}
syntax_plugin_var::$wikiVERSION = getVersion();

// vim:ts=4:sw=4:et:enc=utf-8:
