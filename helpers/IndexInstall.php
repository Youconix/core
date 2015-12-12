<?php
namespace youconix\core\helpers;

class IndexInstall extends Helper implements \Display
{

    protected $s_content = '';

    public function __construct(\Output $template)
    {
        $template->setCssLink('<link rel="stylesheet" href="{STYLE_DIR}/css/installIndex.css">');
    }

    public function generate()
    {
        $this->title();
        
        $this->installCompleted();
        
        $this->gettingStarted();
        
        $this->maintenance();
        
        return $this->s_content;
    }

    private function title()
    {
        $this->s_content .= '<h1>Youconix framework</h1>
		';
    }

    private function installCompleted()
    {
        $this->s_content .= '<section id="installCompleted">
			<p>Youconix framework is a modern framework designed for AJAX-usage with modern techniques like MVC, interfaces, namespaces, dependency injection and unit-testing through the entire core.</p>
				
			<p>You have the freedom the use the code however you want. The framework is to serve you instead of forcing you to a certain way. You can use BaselogicClass for all your pages without any problem.
				But if you only want to core without a GUI? No problem, just call Memory directly and go ahead.</p>
				
			<p>Code on the way you want, but remember : If the framework does not like your commands, it will throw an exception.</p>
		</section>
		';
    }

    private function gettingStarted()
    {
        $this->s_content .= '<section id="gettingStarted">
			<h2>Getting started</h2>
				
			<p><a href="http://framework.youconix.nl/2/wiki/controllers">Creating new pages</a> Each page you visit trough the browser has his own controller and own access rights.</p> 
			<p><a href="http://framework.youconix.nl/2/wiki/models">Creating new models</a> The database access should run trough controllers. A controller is a class that maintains the data and only this class knows how the tables are called.</p>
				
			<p><a href="http://framework.youconix.nl/2/wiki/overrides">Overriding framework classes</a> It is possible to automatically override the framework libaries. The code will not even notice it until you cast it to your own object!</p>
		</section>
		';
    }

    private function maintenance()
    {
        $this->s_content .= '<section id="maintenance">
			<h2>External information</h2>
				
			<ul>
				<li><a href="http://nl.wikipedia.org/wiki/Dependency_injection">Dependency injection</a></li>
				<li><a href="http://nl3.php.net/manual/en/language.exceptions.php">Exceptions</a></li>
				<li><a href="http://php.net/manual/en/language.oop5.interfaces.php">Interface</a></li>
				<li><a href="http://nl3.php.net/manual/en/language.namespaces.php">Namespaces</a></li>				
				<li><a href="https://phpunit.de/">Unit testing</a></li>
				
			</ul>
		</section>';
    }
}