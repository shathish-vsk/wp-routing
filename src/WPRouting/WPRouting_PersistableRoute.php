<?php

/**
 * Class WPRouting_PersistableRoute
 *
 * An extension of the WPRouting_Route class to allow meta information about a route to be persisted to the database.
 */
class WPRouting_PersistableRoute extends WPRouting_Route
{
    const PARENT_CLASS = 'WPRouting_Route';
    
    /**
     * All the meta information about all the routes will be stored to an array like value in the database. This is the `option_name`.
     */
    const OPTION_ID = '__wp_routing_routes_meta';
    const ROUTE_PERSISTED_VALUES_FILTER = 'WP_Routing_PersistableRoute_persist_route';
    
    /**
     * @var null|tad_Option An instance of the options wrapper class.
     */
    protected static $option = null;
    
    /**
     * @param tad_FunctionsAdapterInterface $f
     * @param tad_Option $option
     */
    public function __construct(tad_FunctionsAdapterInterface $f = null, tad_Option $option = null)
    {
        $this->maybeInitStaticHelper();
        if (is_null($option)) {
            $option = tad_Option::on(self::OPTION_ID);
        }
        $this->option = $option;
        parent::__construct($f);
    }
    
    public static function set($key, $value = null)
    {
        self::$
        {
            $key
        } = $value;
    }
    
    /**
     * Override of the parent method to hook in the route generation process at a class level (in place of using the WP hook).
     *
     * @param string $routeId
     * @param array $args
     */
    protected static function actOnRoute($routeId, Array $args)
    {
        
        // if the route should not be persisted return
        if (!isset($args['shouldBePersisted']) or !$args['shouldBePersisted']) {
            return;
        }
        
        // if the route title is not set return
        if (!isset($args['title']) or !is_string($args['title'])) {
            return;
        }
        
        // if the route permalink is not set return
        if (!isset($args['permalink']) or !is_string($args['permalink']) or !preg_match("/[\\/\\w]*/ui", $args['permalink'])) {
            return;
        }
        
        $routeArgs = array(
            'title' => $args['title'],
            'permalink' => $args['permalink']
        );
        
        // allow plugins to hook into persisted arguments
        if (function_exists('apply_filters')) {
            $routeArgs = apply_filters(self::ROUTE_PERSISTED_VALUES_FILTER, $routeArgs, $routeId);
        }
        
        // persist the route using the id as the key and storing the title and the permalink
        if (is_array($routeArgs)) {
            self::$option->setValue($routeId, $routeArgs);
        }
    }
    
    /**
     * Sugar method to set the `shouldBePersisted` meta for a route.
     *
     * @return WPRouting_PersistableRoute $this
     */
    public function shouldBePersisted($shouldBePersisted = null)
    {
        $this->args['shouldBePersisted'] = is_bool($shouldBePersisted) ? $shouldBePersisted : true;
        return $this;
    }
    
    /**
     * Sets the `permalink` key for the route starting from the path.
     *
     * A `path` specified in the route like `/^hello$/` will set the route permalink to `hello`.
     *
     * @param $patterns
     */
    protected function replacePatterns($patterns)
    {
        
        // call WPRouting_Route::replacePatterns
        parent::replacePatterns($patterns);
        
        // set the permalink to something like path
        // do not use the '/'
        if (!isset($this->args['path'])) {
            return;
        }
        $this->args['permalink'] = rtrim(ltrim($this->args['path'], '/^') , '$/');
    }
    
    protected function maybeInitStaticHelper()
    {
        if (is_null(tad_Static::getClassExtending(self::PARENT_CLASS))) {
            tad_Static::setClassExtending(self::PARENT_CLASS, __CLASS__);
        }
    }
    public function willBePersisted(){
        $shouldBePersisted = isset($this->args['shouldBePersisted']) && !empty($this->args['shouldBePersisted']);
        return $shouldBePersisted ? $shouldBePersisted : false;
    }
}