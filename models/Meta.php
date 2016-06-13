<?php
namespace youconix\core\models;

/**
 * Description of Meta
 *
 * @author rachelle
 */
class Meta {
    /**
     *
     * @var \Config
     */
    protected $config;
    
    /**
     *
     * @var \Builder
     */
    protected $builder;
    
    /**
     *
     * @var \Output
     */
    protected $output;
    
    protected $s_title;
    
    protected $s_keywords;
    
    protected $s_description;
    
    public function __construct(\Config $config,\Builder $builder,\Output $output){
        $this->config = $config;
        $this->builder = $builder;
        $this->output = $output;
    }
    
    public function write(){
        $this->parse();
        
        $this->setTitle();
        $this->setKeywords();
        $this->setDescription();
    }
    
    protected function parse(){
        $s_page = $this->config->getPage();
        
        $this->builder->select('meta','title,keywords,description')->getWhere()->bindString('page',$s_page);
        $database = $this->builder->getResult();
        if( $database->num_rows() > 0 ){
            $data = $database->fetch_object();
            
            $this->s_title = $data[0]->title;
            $this->s_keywords = $data[0]->keywords;
            $this->s_description = $data[0]->description;
        }
    }
    
    protected function setTitle(){
        if( !is_null($this->s_title) ){
            $this->output->set('title', '<title>'.$this->s_title.'</title>');
        }
    }
    
    protected function setKeywords(){
        if( !is_null($this->s_keywords) ){
            $this->output->set('keywords','<meta name="keywords" content="'.$this->s_keywords.'"/>');
        }
    }
    
    protected function setDescription(){
        if( !is_null($this->s_description) ){
            $this->output->set('description','<meta name="description" content="'.$this->s_description.'"/>');
        }
    }
}
