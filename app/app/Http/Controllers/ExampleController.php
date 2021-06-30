<?php

namespace App\Http\Controllers;

class ExampleController extends Controller
{
    private $items;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->middleware('auth:api');

        $this->items = array();
        for($i = 0; $i<10; $i++) {
            $item = array(
                'id' => $i,
                'name' => "item-" . $i
            );
            $this->items[] = $item;
        }

    }

    public function all()
    {
        // Test database connection
        try {
            $this->items = app('db')->select("SELECT * FROM test");
            $i = count($this->items);
            $this->items[] = [ 'id' => $i, 'name' => 'DBO With PDO connected!'];
        } catch (\Exception $e) {
            die("Could not connect to the database.  Please check your configuration. error:" . $e );
        }
        return response()->json($this->items);
    }

    public function get($id)
    {
        $found_key = array_search($id, array_column($this->items, 'id'));
        return response()->json($this->items[$found_key]);
    }
}
