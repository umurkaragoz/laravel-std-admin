<?php

namespace Umurkaragoz\StdAdmin;

use App\Http\Controllers\Controller;
use Request;
use Route;

class StdAdminController extends Controller
{
    use ManagesDeletables;
    use GeneratesIndexes;

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
    public function __construct($config = [])
    {
        module()->parseCurrentRoute(array_get($config, 'level', 0));

        module()->extendConfig($config);
    }

    /* ------------------------------------------------------------------------------------------------------------------------------ UTILITIES -+- */

    /* ----------------------------------------------------------------------------------------------------------------------- generate headers -+- */
    /**
     * Generate headers for index and trashed sections using given column list
     *
     * @param array $columns
     *
     * @return array
     */
    private function generateHeaders($columns)
    {
        $headers = [];

        foreach ($columns as $column) {
            $headers[] = module()->trans("attributes.$column");
        }

        return $headers;
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
    protected function jsonResponse($status = true, $message = '', $data = null, $code = null)
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
