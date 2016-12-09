<?php
/**
 * Date: 2015/3/14
 * Time: 8:14
 *
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN . 'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_wxwrap_wx extends DokuWiki_Syntax_Plugin {
   // protected $special_pattern = '<wx\b[^>\r\n]*?/>';
    protected $entry_pattern   = '<wx\b.*?>(?=.*?</wx>)';
    protected $exit_pattern    = '</wx>';


    public function getType(){ return 'formatting'; }
    public function getAllowedTypes() { return array('formatting', 'substition', 'disabled'); }
    public function getSort(){ return 409; }
    public function connectTo($mode) { 
      $this->Lexer->addEntryPattern($this->entry_pattern,$mode,'plugin_wxwrap_wx'); }
    public function postConnect() { 
      $this->Lexer->addExitPattern($this->exit_pattern,'plugin_wxwrap_wx'); }


    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
        switch ($state) {
            case DOKU_LEXER_ENTER :
                $match = trim(substr($match,3,-1));
                $arg_list = explode(" ",$match);
                $this->pg_count=$this->pg_count+1;
                return array($state, $arg_list,$this->pg_count-1);

            case DOKU_LEXER_UNMATCHED :  return array($state, $match);
            case DOKU_LEXER_EXIT :       return array($state, '');
        }
        return array();
    }

    /**
     * Create output
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        // $data is what the function handle() return'ed.
        if($mode == 'xhtml'){
            /** @var Doku_Renderer_xhtml $renderer */
            list($state,$match,$count) = $data;
            switch ($state) {
                case DOKU_LEXER_ENTER :
                    $arg_list = $match;
                    $length = count($arg_list);
                    if ($length<1) {
                      $renderer->doc .= 
                      '<span class="wx_show">微信简介:</span><div class="wx_wechat" id="wx_wechat_'.$count.'">';
                      break;
                    }
                    $subname=$arg_list[0];
                    $args = implode(";",array_slice($arg_list,1));
                    $str2 =<<<MYSTR2
<span class="wx_show">微信简介:</span><div class="wx_wechat" id="wx_wechat_$count" wxargs="$args" wxlength="$length" >
MYSTR2;
                    $renderer->doc .= $str2;
                    break;

                case DOKU_LEXER_UNMATCHED :
                    $lines_un = explode("\n", $match);
                    $lines = [];
                    foreach ( $lines_un as $lu ) {
                      if (strlen($lu) > 2) {
                        $lines []= $lu;
                      }
                    }
                    $l_length = count($lines);
                    if ($l_length<2) {
                      $renderer->doc .= '<div><span>错误的格式:</span>'.$match.'</div>';
                      break;
                    }
                    $inf = implode(";",$lines);
                    $str3 =<<<MYSTR3
<div><span>描述:</span>$lines[0]<br/><span>图片:</span><img src="$lines[1]" alt="$lines[1]" ><hr></div>
MYSTR3;
                    $renderer->doc .= $str3;
                    break;
                case DOKU_LEXER_EXIT :
                    $renderer->doc .= "</div>";
                    break;
            }
            return true;
        }
        return false;
    }

}