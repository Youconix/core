<?php
namespace youconix\core\helpers;

/**
 * Helper for generating and parsing UBB-code
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class UBB extends \youconix\core\helpers\Helper
{

    /**
     *
     * @var \Builder
     */
    protected $builder;

    protected $a_smileys;

    /**
     * PHP5 constructor
     *
     * @param \Builder $builder            
     */
    public function __construct(\Builder $builder)
    {
        $this->builder = $builder;
        
        $this->loadSmileys();
    }

    /**
     * Loads the smileys from the database
     */
    protected function loadSmileys()
    {
        $this->a_smileys = array();
        
        try {
            $this->builder->select('smileys', 'code,url');
            $service_Database = $this->builder->getResult();
            
            if ($service_Database->num_rows() > 0) {
                $a_smileys = $service_Database->fetch_assoc();
                
                foreach ($a_smileys as $a_smiley) {
                    $this->a_smileys[] = array(
                        'code' => $a_smiley['code'],
                        'url' => NIV . 'images/smileys/' . $a_smiley['url']
                    );
                }
            }
        } catch (DBException $ex) {}
    }

    /**
     * Changes the UBB-code into HTML and writes the smileys
     *
     * @param string $s_text
     *            The message that need to be transformed
     * @return string The transformed message
     */
    public function parse($s_text)
    {
        $s_text = $this->fromUBB($s_text);
        
        foreach ($this->a_smileys as $a_smiley) {
            $s_text = str_ireplace($a_smiley['code'], '<img src="' . $a_smiley['url'] . '" alt="' . $a_smiley['code'] . '"/>', $s_text);
        }
        
        return $s_text;
    }

    /**
     * Changes the XHTML-code into UBB code and reverses the smileys
     *
     * @param string $s_text
     *            The message that need to be transformed
     * @return string The transformed message
     */
    public function revert($s_text)
    {
        $s_text = $this->toUBB($s_text);
        
        foreach ($this->a_smileys as $a_smiley) {
            $s_text = str_ireplace('<img src="' . $a_smiley['url'] . '" alt="' . $a_smiley['code'] . '"/>', $a_smiley['code'], $s_text);
        }
        
        return $s_text;
    }

    /**
     * Parses the UBB-code into XHTML-code
     *
     * @param string $s_text
     *            The text to parse
     * @return string The given string with XHTML-code
     */
    private function fromUBB($s_text)
    {
        /* Pars UBB */
        $a_ubb = array(
            '#\[b\]#si',
            '#\[/b\]#si',
            '#\[i\]#si',
            '#\[/i\]#si',
            '#\[u\]#si',
            '#\[/u\]#si',
            '#\[s\]#si',
            '#\[/s\]#si',
            '#\[p\]#si',
            '#\[/p\]#si',
            '#\[h1\]#si',
            '#\[/h1\]#si',
            '#\[h2\]#si',
            '#\[/h2\]#si',
            '#\[h3\]#si',
            '#\[/h3\]#si',
            '#\[h4\]#si',
            '#\[/h4\]#si',
            '#\[h5\]#si',
            '#\[/h5\]#si',
            '#\[br/?\]#si',
            '#\[ul\]#si',
            '#\[/ul\]#si',
            '#\[ol\]#si',
            '#\[/ol\]#si',
            '#\[li\]#si',
            '#\[/li\]#si',
            '#\[center\]#si',
            '#\[/center\]#si',
            '#\[/right\]#si',
            '#\[/right\]#si'
        );
        $a_html = array(
            '<strong>',
            '</strong>',
            '<em>',
            '</em>',
            '<u>',
            '</u>',
            '<s>',
            '</s>',
            '<p>',
            '</p>',
            '<h1>',
            '</h1>',
            '<h2>',
            '</h2>',
            '<h3>',
            '</h3>',
            '<h4>',
            '</h4>',
            '<h5>',
            '</h5>',
            '<br/>',
            '<ul>',
            '</ul>',
            '<ol>',
            '</ol>',
            '<li>',
            '</li>',
            '<div class="textCenter">',
            '</div>',
            '<div class="textRight">',
            '</div>'
        );
        
        /* Check closure */
        $i_number = count($a_ubb);
        $i = 0;
        while ($i < $i_number) {
            if ($a_ubb[$i] == '#\[br/?\]#si') {
                $i ++;
                continue;
            }
            
            $out1 = preg_match_all($a_ubb[$i], $s_text, $out);
            $out2 = preg_match_all($a_ubb[($i + 1)], $s_text, $out2);
            
            if ($out1 > $out2) {
                $s_text .= $a_ubb[($i + 1)];
            } else 
                if ($out1 < $out2) {
                    $s_text = $a_ubb[$i] . $input;
                }
            
            $i = $i + 2;
        }
        
        /* Set UBB-code */
        $s_text = preg_replace("#\[img\][[:space:]]*([^\\[]*)[[:space:]]*\[/img\]#", "<img src=\"\\1\" alt=\"\"/>", $s_text);
        $s_text = preg_replace("#\[img=([:;/a-zA-Z0-9_\-+.\&\?=\[\]]+)\][[:space:]]*([^\\[]*)[[:space:]]*\[/img\]#", "<img src=\"\\1\" alt=\"\\2\"/>", $s_text);
        $s_text = preg_replace("#\[url\][[:space:]]*([a-zA-Z0-9_\-+\.!\?<>=/\"'\s\<\>]+)[[:space:]]*\[/url\]#", "<a href=\"\\1\">\\1</a>", $s_text);
        $s_text = preg_replace("#\[url=([:;/a-zA-Z0-9_\-+.\&\?=\[\]]+)\]([a-zA-Z0-9_\-+\.!\?<>=/\"'\s\<\>]+)\[/url\]#", '<a href="\\1">\\2</a>', $s_text);
        $s_text = preg_replace("#\[email=([a-zA-Z0-9_@+\-\.\]+)\]([a-zA-Z0-9_\-+.\s]+)\[/email\]#", '<a href="mailto:\\1">\\2</a>', $s_text);
        
        while (preg_match("#\[quote\][[:space:]]*([^\\[]*)[[:space:]]*\[/quote\]#", $s_text)) {
            $s_text = preg_replace("#\[quote\][[:space:]]*([^\\[]*)[[:space:]]*\[/quote\]#", '<blockquote>\\1</blockquote>', $s_text);
        }
        
        $s_text = preg_replace($a_ubb, $a_html, $s_text);
        
        return $s_text;
    }

    /**
     * Parses the XHTML-code into UBB-code
     *
     * @param string $s_text
     *            The text to parse
     * @return string The given string with UBB-code
     */
    private function toUBB($s_text)
    {
        /* Parse HTML */
        $a_html = array(
            '#<strong>#si',
            '#</strong>#si',
            '#<em>#si',
            '#</em>#si',
            '#<u>#si',
            '#</u>#si',
            '#<s>#si',
            '#</s>#si',
            '#<p>#si',
            '#</p>#si',
            '#<h1>#si',
            '#</h1>#si',
            '#<h2>#si',
            '#</h2>#si',
            '#<h3>#si',
            '#</h3>#si',
            '#<h4>#si',
            '#</h4>#si',
            '#<h5>#si',
            '#</h5>#si',
            '#<br/>#si',
            '#<br>#si',
            '#<ul>#si',
            '#</ul>#si',
            '#<ol>#si',
            '#</ol>#si',
            '#<li>#si',
            '#</li>#si'
        );
        
        $a_ubb = array(
            '[b]',
            '[/b]',
            '[i]',
            '[/i]',
            '[u]',
            '[/u]',
            '[s]',
            '[/s]',
            '[p]',
            '[/p]',
            '[h1]',
            '[/h1]',
            '[h2]',
            '[/h2]',
            '[h3]',
            '[/h3]',
            '[h4]',
            '[/h4]',
            '[h5]',
            '[/h5]',
            '[br/]',
            '[br/]',
            '[ul]',
            '[/ul]',
            '[ol]',
            '[/ol]',
            '[li]',
            '[/li]'
        );
        
        $s_text = preg_replace($a_html, $a_ubb, $s_text);
        
        /* Set UBB-code */
        $s_text = preg_replace("#<img src=\"([[0-9a-zA-Z:/\.\-_]*)\" alt=\"([[0-9a-zA-Z:/\.\-_]*)\"/?>#si", "[img=\\1]\\2[/img]", $s_text);
        $s_text = preg_replace("#<img src=\"([[0-9a-zA-Z:/\.\-_]*)\"/?>#si", "[img]\\1[/img]", $s_text);
        $s_text = preg_replace("#<a href=\"([:;/a-zA-Z0-9_\-+.\&\?=\'\[\]]+)\">([a-zA-Z0-9_\-+.!?=/\"'\[\]\s]+)</a>#", '[url=\\1]\\2[/url]', $s_text);
        $s_text = preg_replace("#<a href=\"mailto:([a-zA-Z0-9_@+\-\.\]+)\">([a-zA-Z0-9_\-+.\s]+)</a>#", '[email=\\1]\\2[/email]', $s_text);
        
        while (preg_match("#<blockquote>[[:space:]]*([^\\[]*)[[:space:]]*</blockquote>#", $s_text)) {
            $s_text = preg_replace("#<blockquote>[[:space:]]*([^\\[]*)[[:space:]]*</blockquote>#", '[quote]\\1[/quote]', $s_text);
        }
        
        $a_html = array(
            '#<div class="textCenter">#si',
            '#</div>#si',
            '#<div class="textRight">#si',
            '#</div>#si'
        );
        $a_ubb = array(
            '[center]',
            '[/center]',
            '[/right]',
            '[/right]'
        );
        $s_text = preg_replace($a_html, $a_ubb, $s_text);
        
        return $s_text;
    }
}