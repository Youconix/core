<?php

/**
 * Map service
 * Contains the calls for the google maps API
 *
 * @author:		Rachelle Scheijen <rachelle.scheijen@unixerius.nl>
 * @copyright	The au pair BV	2013
 * @since     1.0
 */
class Service_Map extends Service
{

    private $s_api;

    private $s_template = 'maps';

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        $this->s_api = '<script src="https://maps.googleapis.com/maps/api/js?sensor=false"></script>
		<script src="{NIV}js/maps.js"></script>
		<script src="{NIV}js/infobox.js"></script>';
        $this->s_api .= '<p id="jsNotice">Please enable javascript</p>
		<script type="text/javascript">
	    <!--
	    document.getElementById("jsNotice").style.display	= "none";
	    if( typeof google == "undefined" ){
			alert("Please allow googleapis.com for the map.");
		}
		
		//-->
		</script>';
    }

    /**
     * Displays the full map
     *
     * @param string $s_template
     *            content div id
     * @param string $s_function
     *            mouse listener callback
     */
    public function displayFullMap($s_template, $s_function)
    {
        $s_output = $this->s_api . '
	    <script type="text/javascript">
	    <!--
	    function initialize(){
	    	mapsWrapper.initialize("' . $s_template . '",13.234745, 17.581376,4);
	    	mapsWrapper.setMouseListener("' . $s_function . '");
	    }
	    if( (typeof google != "undefined") && (typeof mapsWrapper != "undefined") ){
	    	google.maps.event.addDomListener(window, "load", initialize);
	    }
	    //-->
    	</script>';
        
        return $s_output;
    }

    /**
     * Displays the edible full map
     *
     * @param string $s_template
     *            content div id
     * @param string $s_function
     *            mouse listener callback
     * @param string $s_x            
     * @param string $s_y            
     */
    public function displayEditFullMap($s_template, $s_function, $s_x, $s_y)
    {
        $s_output = $this->s_api . '
	    <script type="text/javascript">
	    <!--
	    function initialize(){
	    	mapsWrapper.initialize("' . $s_template . '",' . $s_x . ', ' . $s_y . ',12);
	    	mapsWrapper.setMarker(' . $s_x . ',' . $s_y . ');
	    	mapsWrapper.setMouseListener("' . $s_function . '");
	    }	    
	    if( typeof google != "undefined" ){
	    	google.maps.event.addDomListener(window, "load", initialize);
	    }
	    //-->
    	</script>';
        
        return $s_output;
    }

    /**
     * Displays the map
     *
     * @param string $s_template
     *            div id
     * @param string $s_x            
     * @param string $s_y            
     */
    public function displayMap($s_template, $s_x, $s_y)
    {
        $s_output = $this->s_api . '
	    <script type="text/javascript">
	    <!--
	    function initialize(){
	    	mapsWrapper.initialize("' . $s_template . '",' . $s_x . ', ' . $s_y . ',12);
	    	mapsWrapper.setMarker(' . $s_x . ',' . $s_y . ');
	    }
	    if( typeof google != "undefined" ){
	    	google.maps.event.addDomListener(window, "load", initialize);   	
	    }
	    //-->
    	</script>';
        
        return $s_output;
    }

    /**
     * Displays a map with the given location
     *
     * @param string $s_template
     *            div id
     * @param string $s_location
     *            name
     */
    public function displayMapLocation($s_template, $s_location)
    {
        $s_output = $this->s_api . '
	    <script type="text/javascript">
	    <!--
	    function initialize(){
	    	mapsWrapper.initializeLocation("' . $s_template . '","' . $s_location . '");
	    }
	    if( typeof google != "undefined" ){
	    	google.maps.event.addDomListener(window, "load", initialize);
	    }
	    //-->
    	</script>';
        
        return $s_output;
    }
}