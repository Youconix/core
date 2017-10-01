<?php

namespace youconix\core\templating;

/**
 * Template parser for joining the templates and the PHP code
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @version 1.0
 * @since 1.0
 */
class TemplateTpl extends \youconix\core\templating\TemplateParent {

  /**
   * Loads the templates
   */
  protected function loadTemplates($s_file) {
    $file = $this->fileHandler->getFile($s_file);
    $s_template = $this->fileHandler->readFileObject($file);
    $i_changed = $file->getCTime();

    $this->a_templates[$s_file] = ['changed' => $i_changed, 'template' => $s_template];

    $a_includes = null;
    if (preg_match_all('/<include\s+src="([a-z0-9_\-\.\/]+)"\/>/si', $s_template, $a_includes)) {
      if (is_array($a_includes[1])) {
	$a_includes = $a_includes[1];
      } else {
	$a_includes = [$a_includes[1]];
      }

      foreach ($a_includes AS $s_name) {
	$s_include = $s_name;
	if (substr($s_include, 0, 1) !== '/') {
	  $s_include = $this->s_templateDir . $s_include;
	} else {
	  $s_include = $_SERVER['DOCUMENT_ROOT'] . $s_include;
	}

	$include = $this->fileHandler->getFile($s_include);
	$s_includeContent = $this->fileHandler->readFileObject($include);
	$i_changed = $include->getCTime();
	$this->a_includes[$s_include] = ['name' => $s_name, 'changed' => $i_changed, 'template' => $s_includeContent];
      }
    }

    if (strpos($s_template, '<extends') !== false) {
      $a_matches = null;

      preg_match('/<extends\s+src="([a-z0-9_\-\.\/]+)"\s+target="([a-z0-9_\-]+)"\/>/si', $s_template, $a_matches);

      $s_template = str_replace($a_matches[0], '', $s_template);
      $this->a_templates[$s_file]['template'] = $s_template;

      $s_parent = $a_matches[1];
      $s_target = $a_matches[2];

      $this->a_templates[$s_file]['target'] = $s_target;

      if (substr($s_parent, 0, 1) !== '/') {
	$s_parent = $this->s_templateDir . $s_parent;
      } else {
	$s_parent = $_SERVER['DOCUMENT_ROOT'] . $s_parent;
      }

      $this->loadTemplates($s_parent);
    }
  }

  /**
   * Parses the template
   *
   * @throws \TemplateException If the included templates could not be found
   */
  protected function parse() {
    $a_files = array_keys($this->a_templates);
    $i_amount = (count($a_files) - 1);

    for ($i = ($i_amount - 1); $i >= 0; $i--) {
      $this->importChildTemplate($a_files[$i_amount], $this->a_templates[$a_files[$i]]['target'], $this->a_templates[$a_files[$i]]['template']);
    }

    $s_template = $this->a_templates[$a_files[$i_amount]]['template'];
    foreach ($this->a_includes AS $s_file => $include) {
      $s_template = str_replace('<include src="' . $include['name'] . '"/>', $include['template'], $s_template);
    }
    $this->s_template = $s_template;
    
    $this->checkBlocks();
    $this->parseBlocks();
    
    $this->parseIf();
    
    $this->s_template = preg_replace('/\{([a-z0-9_\-\[\]\'"]+)\}/si','<?php echo($${1}); ?>',$this->s_template);
  }

  protected function importChildTemplate($s_parentKey, $s_target, $s_childTemplate) {
    $s_template = $this->a_templates[$s_parentKey]['template'];

    $s_template = str_replace('{' . $s_target . '}', $s_childTemplate, $s_template);

    $this->a_templates[$s_parentKey]['template'] = $s_template;
  }
  
  /**
   * Checks the blocks
   *
   * @throws \TemplateException If the blocks are invalid
   */
  protected function checkBlocks() {
    $i_start = preg_match_all('#<block#', $this->s_template, $a_matches);
    $i_end = preg_match_all('#</block>#', $this->s_template, $a_matches);

    if ($i_start > $i_end) {
      throw new \TemplateException("Template validation error : number of <block> is bigger than the number of </block>.");
    } else
    if ($i_end > $i_start) {
      throw new \TemplateException("Template validation error : number of </block> is bigger than the number of <block>.");
    } else
    if ($i_start == 0)
      return;
  }
  
  protected function parseBlocks(){
    if( preg_match_all('#<block\s+\{([a-zA-Z0-9_\-]+)\}\s*>#si', $this->s_template, $a_matches) ){
      foreach($a_matches[1] AS $s_block){
	$this->writeBlock($s_block);
      }
    }
  }
  
  /**
   * Writes the block with the given key
   *
   * @param string $s_key
   *            The block key
   */
  protected function writeBlock($s_key) {
    /* Get block */
    $s_search = '<block {' . $s_key . '}>';

    $i_pos = stripos($this->s_template, $s_search);
    if ($i_pos === false) {
      throw new \TemplateException('Notice : Call to undefined template block ' . $s_key . '.');
      return;
    }

    /* Find end */
    $i_end = stripos($this->s_template, '</block>', $i_pos);

    /* Check for between blocks */
    $i_pos2 = stripos($this->s_template, '<block', $i_pos + 1);
    $i_extra = 0;
    while ($i_pos2 !== false && ($i_pos2 < $i_end)) {
      $i_pos2 = stripos($this->s_template, '<block', $i_pos2 + 1);
      $i_extra ++;
    }

    for ($i = $i_extra; $i > 0; $i --) {
      $i_end = stripos($this->s_template, '</block>', $i_end + 1);
    }
    
    $s_template = $this->s_template;
    $i_start = ($i_pos+strlen($s_search));
    $s_block = substr($s_template, $i_start,($i_end-$i_start));
    $s_blockNew = preg_replace('/\{([a-z0-9_\-]+)\}/si','<?php echo($item["${1}"]); ?>',$s_block);
    $s_blockNew = str_replace(['echo($item["NIV"])','echo($item["shared_style_dir"])','echo($item["style_dir"])'],
      ['echo($NIV)','echo($shared_style_dir)','echo($style_dir)'],$s_blockNew);
    
    $s_template = substr_replace($s_template, '<?php } ?>',$i_end,8);
    $s_template = substr_replace($s_template,$s_blockNew,$i_start,strlen($s_block));
    $s_template = substr_replace($s_template, '<?php foreach( $'.$s_key.' AS $item ){ ?>', $i_pos,strlen($s_search));
  
    $this->s_template = $s_template;
  }

  /**
   * Writes the values to the given keys on the given template
   *
   * @param array $a_keys            
   * @param array $a_values            
   * @param string $s_template
   *            The template to parse
   * @return string parsed template
   */
  public function writeTemplate($a_keys, $a_values, $s_template) {
    \youconix\core\Memory::type('array', $a_keys);
    \youconix\core\Memory::type('array', $a_values);
    \youconix\core\Memory::type('string', $s_template);

    $i_number = count($a_keys);
    for ($i = 0; $i < $i_number; $i ++) {
      if (substr($a_keys[$i], 0, 1) != '{' && substr($a_keys[$i], - 1) != '}') {
	$a_keys[$i] = '{' . $a_keys[$i] . '}';
      }
    }

    return str_replace($a_keys, $a_values, $s_template);
  }

  /**
   * Writes the if blocks
   *
   * @throws \TemplateException If the template is invalid
   */
  protected function parseIf() {
    $i_start = preg_match_all('#<if#', $this->s_template, $a_matches);
    $i_end = preg_match_all('#</if>#', $this->s_template, $a_matches);

    if ($i_start > $i_end) {
      throw new \TemplateException("Template validation error : number of &lt;if&gt; is bigger than the number of &lt;/if>.");
    } else
    if ($i_end > $i_start) {
      throw new \TemplateException("Template validation error : number of &lt;/if&gt; is bigger than the number of &lt;if&gt;.");
    }

    $this->s_template = preg_replace('/<elseif\s*\{([a-z0-9_\-]+)\}\s*>/si', '<?php elseif( $${1} ){ ?>', $this->s_template);
    $this->s_template = preg_replace('/<if\s*\{([a-z0-9_\-]+)\}\s*>/si', '<?php if( $${1} ){ ?>', $this->s_template);
    $this->s_template = preg_replace('/<\/if>[\s\n]+<else>/si', '<?php } else { ?>', $this->s_template);
    $this->s_template = str_replace(['</if>','</elseif>','<else>','</else>'],['<?php } ?>','<?php } ?>','<?php else { ?>','<?php } ?>'],$this->s_template);
  }
}
