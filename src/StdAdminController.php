<?php

namespace Umurkaragoz\StdAdmin;

use App\Http\Controllers\Controller;
use Request;
use Route;

class StdAdminController extends Controller
{
    use ManagesDeletables;
    
    protected $opts = [];
    
    // current module.
    protected $module;
    // current section.
    protected $section;
    // method which will be required for next action. e.g. POST in 'edit', which will be required for 'create'.
    protected $method;
    // current action.
    protected $action;
    // current model's id. For actions which process single item: create, edit, delete, restore.
    protected $id;
    // current model instance. For actions which process single item: create, edit, delete, restore.
    protected $item;
    
    // helper object containing useful route urls for current section.
    protected $routeUrl;
    // helper object containing useful route names for current section.
    protected $routeName;
    
    // is action == 'edit'.
    protected $editing;
    // is action == 'create'.
    protected $creating;
    
    /**
     * Generates useful variables about current route and action.
     *
     * @param int $level module level
     */
    public function __construct($level = 0)
    {
        // check the action
        $editing = Request::segment($level + 4) == 'edit';
        $creating = Request::segment($level + 3) == 'create';
        
        // generate module path for the route
        $module = '';
        for ($i = 0; $i < $level + 1; $i++) {
            $module .= Request::segment($i + 1) . '.';
        }
        
        $section = Request::segment($level + 2);
        $method = $editing ? 'put' : 'post';
        $action = $editing ? 'update' : 'store';
        $id = $editing ? Request::segment($level + 3) : false;
        
        // construct editing action links
        if ($editing) {
            $action = route("$module$section.$action", $id);
        } else if ($creating) {
            $action = route("$module$section.$action");
        }
        
        // share data with child controllers
        $this->module = $module;
        $this->section = $section;
        $this->editing = $editing;
        $this->creating = $creating;
        $this->method = $method;
        $this->action = $action;
        $this->id = $id;
        
        $routeUrl = (object)[
            'create'  => $this->sectionRoute('create'),
            'index'   => $this->sectionRoute('index'),
            'trashed' => $this->sectionRoute('trashed'),
        ];
        
        $routeName = (object)[
            'create'  => $this->sectionRoute('create', true),
            'index'   => $this->sectionRoute('index', true),
            'edit'    => $this->sectionRoute('edit', true),
            'destroy' => $this->sectionRoute('destroy', true),
            'trashed' => $this->sectionRoute('trashed', true),
            'restore' => $this->sectionRoute('restore', true),
        ];
        
        $this->routeUrl = $routeUrl;
        $this->routeName = $routeName;
        
        // set default options
        $this->setDefaultOptions();
        
        // boot native traits
        $this->bootNativeTraits();
        
        // share data with views
        view()->share(compact('section', 'routeUrl', 'routeName', 'editing', 'creating', 'method', 'action'));
    }
    
    private function bootNativeTraits()
    {
        $this->bootManagesDeletablesTrait();
    }
    
    private function setDefaultOptions()
    {
        $this->opts_set([
            // SomeModel::class
            'model'             => ":model-map.$this->section",
            // 'Model'
            'model-name'        => 'Model',
            // 'Models'
            'model-name-plural' => 'Models',
            'item-name-attr'    => 'name',
            // map sections to models so you do not have to supply ':model'. 'users' => User::class.
            'model-map'         => []
        ]);
    }
    
    /* -------------------------------------------------------------------------------------------------------------------------------- OPTIONS -+- */
    
    /* ------------------------------------------------------------------------------------------------------------------------------- opts set -+- */
    /**
     * Replaces given key on the options. Extends options array when only one array parameter is supplied.
     *
     * @param mixed|array $key   Key in options array to update its value.
     * @param mixed       $value Array of options to extend the defaults with.
     *
     */
    protected function opts_set($key, $value = false)
    {
        if ($value) {
            // replace the array key if both key and value is given.
            array_set($this->opts, $key, $value);
        } else {
            // extend the whole array if only one parameter given.
            $this->opts = array_replace_recursive($this->opts, $key);
        }
    }
    
    /* ----------------------------------------------------------------------------------------------------------------------------------- opts -+- */
    /**
     * Get a value from section options. Dot notation can be used for nested keys.
     *
     * @param string $key     Key to retrieve, dot notation can be used.
     * @param bool   $default Default key to return in case of no match.
     *
     * @return mixed|null
     */
    protected function opts_get($key, $default = false)
    {
        $value = array_get($this->opts, $key, $default);
        
        $value = $this->optsResolveLinks($value, $default);
        
        // return the result.
        return $value;
    }
    
    private function optsResolveLinks($value)
    {
        $item = $this->item;
        
        // replace variables/inner links.
        $newValue = preg_replace_callback('|:([A-z:._-]*)|', function($matches) use ($item) {
            $raw = $matches[0];
            $key = $matches[1];
            
            // retrieve the value from item attributes.
            if (starts_with($key, 'item.')) {
                // to get a high IMDB score, we shall allow links in links. Linkception. Resolve links in the link.
                $key = (strpos($key, ':') !== false) ? $this->optsResolveLinks($key) : $key;
                
                return object_get($item, substr($key, 5), $raw);
            }
            
            // retrieve the value from opts.
            $value = array_get($this->opts, $key, $raw);
            
            // update retrieved option.
            $this->opts_set($key, $value);
            
            return $value;
        }, $value);
        
        // re-process the value if it has changed, return it if it has not.
        return $value == $newValue ? $value : $this->optsResolveLinks($newValue);
    }
    
    /* ------------------------------------------------------------------------------------------------------------------------------ UTILITIES -+- */
    
    /* ---------------------------------------------------------------------------------------------------------------------------- section Url -+- */
    /**
     * Generates routes for given action of the current section.
     *
     * @param string $action      REST action to generate route for.
     * @param bool   $returnRoute Return route name instead of url.
     *
     * @return bool|string
     */
    protected function sectionRoute($action, $returnRoute = false)
    {
        $routeName = "$this->module$this->section.$action";
        
        if (Route::has($routeName)) {
            return $returnRoute ? $routeName : route($routeName);
        }
        
        return false;
    }
    
    /* -------------------------------------------------------------------------------------------------------------------------- json Response -+- */
    /**
     * (somewhat) JSend compliant json response.
     * Only difference with JSend is we have 'message' in every request as a generic info about the process.
     *
     * @see https://labs.omniti.com/labs/jsend/wiki
     *
     * @param bool|string $status bool|success|fail|error
     * @param string      $message
     * @param bool        $data
     * @param bool        $code
     * @param bool        $data
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    private function jsonResponse($status = true, $message = '', $data = null, $code = null)
    {
        $status = $status === true ? 'success' : ($status === false ? 'error' : $status);
        
        switch ($status) {
            // All went well, and (usually) some data was returned.
            case 'success':
                return response()->json([
                    'status'  => 'success',
                    'message' => $message,
                    'data'    => $data
                ]);
            break;
            // There was a problem with the data submitted, or some pre-condition of the API call wasn't satisfied
            case 'fail':
                return response()->json([
                    'status'  => 'fail',
                    'message' => $message,
                    'data'    => $data
                ]);
            break;
            // An error occurred in processing the request, i.e. an exception was thrown.
            case 'error':
                return response()->json([
                    'status'  => 'error',
                    'message' => $message,
                    'code'    => $code,
                    'data'    => $data
                ]);
            break;
            default:
                throw new \Exception('json response status may either be success, fail or error.', 500);
            break;
        }
    }
}
