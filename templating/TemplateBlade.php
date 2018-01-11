<?php

namespace youconix\core\templating;

class TemplateBlade extends \youconix\core\templating\TemplateParent
{

  /**
   * Loads the templates
   * 
   * @param string $s_template
   * @return string
   */
  protected function loadTemplates($s_template)
  {
    if (strpos($s_template, '@extends') !== false) {
      $a_matches = null;

      preg_match('/@extends\s*([a-z0-9_\-\.\/]+)/si', $s_template, $a_matches);
      $s_parent = $a_matches[1];

      if (substr($s_parent, 0, 1) !== '/') {
        $s_parent = $this->s_templateDir.$s_parent;
      } else {
        $s_parent = $_SERVER['DOCUMENT_ROOT'].$s_parent;
      }

      $s_parentTemplate = $this->fileHandler->readFile($s_parent);
      
      $s_template = $this->importChildTemplate($s_parentTemplate, $s_template);
      return $this->loadTemplates($s_template);
    }
      
    $a_includes = null;
    if (preg_match_all("/@include\('([a-z0-9_\-\.\/]+)'\)/si", $s_template,
            $a_includes)) {
      if (is_array($a_includes[1])) {
        $a_includes = $a_includes[1];
      } else {
        $a_includes = [$a_includes[1]];
      }

      foreach ($a_includes AS $s_name) {
        $s_include = $s_name;
        if (substr($s_include, 0, 1) !== '/') {
          $s_include = $this->s_templateDir.$s_include;
        } else {
          $s_include = $_SERVER['DOCUMENT_ROOT'].$s_include;
        }

        $s_includeContent = $this->fileHandler->readFile($s_include);
        $s_template = str_replace('@include(\''.$s_name.'\')', $s_includeContent, $s_template);
      }
      
      return $this->loadTemplates($s_template);
    }
    
    return $s_template;
  }

  protected function importChildTemplate($s_parentTemplate, $s_childTemplate)
  {
    $a_yields = null;
    preg_match_all('/@yield\(\'([a-z0-9_\-\.\/]+)\'\)/si', $s_parentTemplate,
        $a_yields);

    if (is_array($a_yields[1])) {
      $a_yields = $a_yields[1];
    } else {
      $a_yields = [$a_yields][1];
    }

    foreach ($a_yields AS $s_yield) {
      $a_block = null;

      if (strpos($s_childTemplate, "@section('".$s_yield."')") !== false) {
	$search = "@section('".$s_yield."')";
	$start = strpos($s_childTemplate, $search);
	$end = strpos($s_childTemplate, '@endsection', $start);
	if ($end === false) {
	  throw new \TemplateException('Missing @endsection for '.$search.'.');
	}
	
	$start += strlen($search);
	
	$block = substr($s_childTemplate, $start, ($end-$start));
	
	$s_parentTemplate = str_replace("@yield('".$s_yield."')", $block,
            $s_parentTemplate);
      } else if (preg_match("/@section\('".$s_yield."'\s*,\s*'(.*)'\)/",
              $s_childTemplate, $a_block)) {
        $s_parentTemplate = str_replace("@yield('".$s_yield."')", $a_block[1],
            $s_parentTemplate);
      }
    }

    return $s_parentTemplate;
  }

  protected function parse()
  {
    $s_template = $this->fileHandler->readFile($this->s_file);
    $s_template = $this->loadTemplates($s_template);
    $s_template = preg_replace('/@yield\([^)]+\)/s', '', $s_template);
    
    $s_template = $this->parseIf($s_template);
    $s_template = $this->parseLoop($s_template, 'foreach');
    $s_template = $this->parseLoop($s_template, 'for');
    $s_template = $this->parseLoop($s_template, 'while');

    $s_template = $this->parseFields($s_template);

    $this->s_template = $s_template;
    $this->parsePaths();
  }

  /**
   * 
   * @param string $s_template
   * @return string
   */
  protected function parseIf($s_template)
  {
    $s_template = str_replace(['@else','@endif'],['<?php } else { ?>','<?php } ?>'],$s_template);
    $s_template = $this->parseIfStatements('if', $s_template);
    $s_template = $this->parseIfStatements('elseif', $s_template);
    
    return $s_template;
  }
  
  /**
   * 
   * @param string $s_statement
   * @param string $s_template
   * @return string
   * @throws \TemplateException
   */
  protected function parseIfStatements($s_statement, $s_template) {
    $i_pos = strpos($s_template, '@'.$s_statement.'(');
    if ( $i_pos === false) {
      return $s_template;
    }
    
    $i_open = 1;
    $i_start = $i_pos;
    $i_length = strlen($s_template);
    $i_pos += strlen('@'.$s_statement.'(');
    while(true){
      if ($s_template[$i_pos] == '(') {
        $i_open++;
      }
      else if($s_template[$i_pos] == ')') {
        $i_open--;
      }
      
      $i_pos++;
      
      if ($i_open == 0) {
        break;
      }
      
      if ($i_pos > $i_length) {
        throw new \TemplateException('Invalid template. Check if-statements.');
      }
    }
    
    $s_part = substr($s_template,$i_start, ($i_pos-$i_start));
    $s_replace = str_replace('@'.$s_statement.'(', '<?php '.$s_statement.'(',$s_part);
    $s_replace = substr_replace($s_replace, '){ ?>', -1);
    $s_template = str_replace($s_part, $s_replace, $s_template);
    
    return $this->parseIfStatements($s_statement, $s_template);
  }

  protected function parseLoop($s_template, $s_key)
  {
    $s_search = '@'.$s_key.'(';
    $i_length = strlen($s_search);

    $i_pos = strpos($s_template, $s_search);
    while ($i_pos !== false) {
      $i_end = strpos($s_template, ')'.PHP_EOL, $i_pos);

      $s_template = substr_replace($s_template, '){ ?>', $i_end, 1);
      $s_template = substr_replace($s_template, '<?php '.$s_key.'(', $i_pos,
          $i_length);

      $i_pos = strpos($s_template, $s_search, $i_pos);
    }
    $s_template = str_replace('@end'.$s_key, '<?php } ?>', $s_template);

    return $s_template;
  }

  protected function parseFields($s_template)
  {
    $s_template = preg_replace('/{!!\s(.*?)(?=\s!!})/si', '<?php echo( ${1} ); ?>', $s_template);
    $s_template = preg_replace('/{{\s(.*?)(?=\s}})/si', '<?php echo( nl2br(htmlentities(${1})) ); ?>', $s_template);
    $s_template = preg_replace('( }}| !!})','', $s_template);
    
    return $s_template;
  }

  /**
   * Displays the if part with the given key
   *
   * @param string $s_key
   *            The key in template
   */
  public function displayPart($s_key)
  {
    trigger_error('Not implemented for blade templates');
  }

  /**
   * Writes the values to the given keys on the given template
   * @deprecated
   *
   * @param array $a_keys            
   * @param array $a_values            
   * @param string $s_template
   *            The template to parse
   * @return string parsed template
   */
  public function writeTemplate($a_keys, $a_values, $s_template)
  {
    trigger_error('Not implemented for blade templates');
    return $s_template;
  }

  protected function defaultFields()
  {
    parent::defaultFields();

    foreach ($this->a_parser AS $key => $value) {
      if(is_object($value) ){
        continue;
      }
      $this->a_parser[$key] = str_replace(
          ['{{ $LEVEL }}', '{{ $style_dir }}', '{{ $shared_style_dir }}', '{{ $NIV }}'],
          [LEVEL, $this->getStylesDir(), $this->config->getSharedStylesDir(), NIV],
          $value);
    }
  }
}
