<?php

namespace App\Http\Controllers\Api\Flights;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\FlightResouce;
use App\Http\Controllers\Api\Traits\ApiResponse;
use App\Http\Controllers\Api\Classes\Dijkstra;
use App\Models\Flight;
use Carbon\Carbon;

class SearchController extends Controller
{
	use ApiResponse;

	public function search(Request $request)
	{
		$validation = $this->validation($request);
    	if ($validation instanceof Response)
    	{
			return $validation;
		}

		//get all nodes in array you must visit all nodes first.
		$flights_origin 	= Flight::pluck('origin_city')->toArray();
		$flights_dest 		= Flight::pluck('destination_city')->toArray();
 		$get_all_nodes 		= array_unique(array_merge($flights_origin,$flights_dest));

		$all_points 		= $this->getAllNodesGraph($get_all_nodes);

		//get all paths available
		$algorithm	= new Dijkstra($all_points);
		$path 		= $algorithm->shortestPaths($request->origin_city , $request->destination_city );

		$message = (empty($path)) ?
					'No Flights Yet.' :
					$request->type == 1 ? 
					'Fastest Flight path...' :
					'Cheapest Flight path...';

		return $this->apiResponse($path, $message, 200);
	}//end function search



	private function validation($request)
	{
		return $this->apiValidation($request, [
			'origin_city'		=>'required|exists:flights,origin_city',
			'destination_city'	=>'required|exists:flights,destination_city',
			'type'				=>'required|in:0,1'
		]);
	}//end function validation


	//get all nodes available with destination graph
	private function getAllNodesGraph($get_all_nodes)
	{
		$all_points 		= array();
		$all_points_dest 	= array();
		$today 				= Carbon::now();

		//draw the graph of nodes as example $graph
		foreach ($get_all_nodes as $key => $node)
		{
			$all_destinations = Flight::where('origin_city',$node)->where('takeoff_time','>=',$today)->where('landing_time','>=',$today)->get(['id','origin_city','destination_city','takeoff_time','landing_time','price']);

			foreach ($all_destinations as $key => $destination)
			{
				$all_points_dest[ $destination->destination_city ] =$destination->toArray();
			}

			$all_points[$node]	= $all_points_dest;
			$all_points_dest 	= array();
		}

		return $all_points;
	}//end function get all nodes available with destination graph

}