<?php

namespace youconix\core\templating;

class TemplateBlade extends \youconix\core\templating\TemplateParent
{

  /**
   * Loads the templates
   */
  protected function loadTemplates($s_file)
  {
    $file = $this->fileHandler->getFile($s_file);
    $s_template = $this->fileHandler->readFileObject($file);
    $i_changed = $file->getCTime();

    $this->a_templates[$s_file] = ['changed' => $i_changed, 'template' => $s_template];

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

        $include = $this->fileHandler->getFile($s_include);
        $s_includeContent = $this->fileHandler->readFileObject($include);
        $i_changed = $include->getCTime();
        $this->a_includes[$s_include] = ['name' => $s_name, 'changed' => $i_changed,
            'template' => $s_includeContent];
      }
    }

    if (strpos($s_template, '@extends') !== false) {
      $a_matches = null;

      preg_match('/@extends\s*([a-z0-9_\-\.\/]+)/si', $s_template, $a_matches);
      $s_template = str_replace($a_matches[0], '', $s_template);
      $this->a_templates[$s_file]['template'] = $s_template;

      $s_parent = $a_matches[1];

      if (substr($s_parent, 0, 1) !== '/') {
        $s_parent = $this->s_templateDir.$s_parent;
      } else {
        $s_parent = $_SERVER['DOCUMENT_ROOT'].$s_parent;
      }

      $this->loadTemplates($s_parent);
    }
  }

  protected function importChildTemplate($s_parentKey, $s_childTemplate)
  {
    $s_template = $this->a_templates[$s_parentKey]['template'];

    $a_yields = null;
    preg_match_all('/@yield\(\'([a-z0-9_\-\.\/]+)\'\)/si', $s_template,
        $a_yields);

    if (is_array($a_yields[1])) {
      $a_yields = $a_yields[1];
    } else {
      $a_yields = [$a_yields][1];
    }

    foreach ($a_yields AS $s_yield) {
      $a_block = null;

      if (strpos($s_childTemplate, "@section('".$s_yield."')") !== false) {
        preg_match("/@section\('".$s_yield."'\)(.*)@endsection/s",
            $s_childTemplate, $a_block);
        $s_template = str_replace("@yield('".$s_yield."')", $a_block[1],
            $s_template);
      } else if (preg_match("/@section\('".$s_yield."'\s*,\s*'(.*)'\)/",
              $s_childTemplate, $a_block)) {
        $s_template = str_replace("@yield('".$s_yield."')", $a_block[1],
            $s_template);
      }
    }

    $this->a_templates[$s_parentKey]['template'] = $s_template;
  }

  protected function parse()
  {
    $a_files = array_keys($this->a_templates);
    $i_amount = (count($a_files) - 1);

    for ($i = ($i_amount - 1); $i >= 0; $i--) {
      $this->importChildTemplate($a_files[$i_amount],
          $this->a_templates[$a_files[$i]]['template']);
    }

    $s_template = $this->a_templates[$a_files[$i_amount]]['template'];
    foreach ($this->a_includes AS $s_file => $include) {
      $s_template = str_replace("@include('".$include['name']."')",
          $include['template'], $s_template);
    }

    $s_template = $this->parseIf($s_template);
    $s_template = $this->parseLoop($s_template, 'foreach');
    $s_template = $this->parseLoop($s_template, 'for');
    $s_template = $this->parseLoop($s_template, 'while');

    $s_template = $this->parseFields($s_template);

    $this->s_template = $s_template;
  }

  protected function parseIf($s_template)
  {
    $s_template = str_replace(['@else','@endif'],['<?php } else { ?>','<?php } ?>'],$s_template);
    $s_template = preg_replace('/@if\(([a-zA-Z0-9\-_\$!><\(\)\s=\'"\[\]&\|]+)\)/si','<?php if( $1 ){ ?>',$s_template);
    $s_template = preg_replace('/@elseif\(([a-zA-Z0-9\-_\$!=><\(\)\s\'"\[\]&\|]+)\)/si','<?php elseif( $1 ){ ?>',$s_template);
    
    return $s_template;
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
    $s_template = preg_replace('/@{{\s{1}(\$?[\$a-zA-Z0-9\-_><\[\]\'"\(\)]+)\s{1}}}/si',
        '@|| ${1} ||', $s_template);
    $s_template = preg_replace('/{!!\s{1}(\$?[\$a-zA-Z0-9\-_><\[\]\'"\(\)]+)\s{1}!!}/si',
        '<?php echo( ${1} ); ?>', $s_template);
    $s_template = preg_replace('/{{\s{1}([\a-zA-Z0-9\-_\(\)]*\$[a-zA-Z0-9\-_><\[\]\'"\(\)]+[\)]*)\s{1}}}/si',
        '<?php echo( nl2br(htmlentities(${1})) ); ?>', $s_template);
    $s_template = preg_replace('/@\|\|\s{1}(\$[a-zA-Z0-9\-_><\[\]\'"\(\)]+)\s{1}\|\|/si',
        '{{ ${1} }}', $s_template);

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