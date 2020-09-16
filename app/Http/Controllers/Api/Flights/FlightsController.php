<?php

namespace App\Http\Controllers\Api\Flights;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\FlightResouce;
use App\Http\Controllers\Api\Traits\ApiResponse;
use App\Models\Flight;
use Carbon\Carbon;

class FlightsController extends Controller
{
	use ApiResponse;

	//start function store flight's info
    public function store(Request $request)
    {
    	//validataton
    	$validation = $this->validation($request);
    	if ($validation instanceof Response)
    	{
			return $validation;
		}

		$data = $request->all();

		$data['date_diff_in_min']	= $this->getMinutesAsCoast(date_create($request->landing_time), date_create($request->takeoff_time));

		//create new flight's
		$flight = $this->createFlight($data);       

		if ($flight)
		{
			return $this->createdResponse(new FlightResouce($flight),'Flight Successfully Created.');
		}

       	return $this->unknowError();
    }//end function store flight's info

    //start function to create flight's info
	private function createFlight($data)
	{
		return Flight::create([
						'origin_city'		=>$data['origin_city'],
						'destination_city'	=>$data['destination_city'],
						'price'				=>$data['price'],
						'takeoff_time'		=>$data['takeoff_time'],
						'landing_time'		=>$data['landing_time'],
						'date_diff_in_min'	=>$data['date_diff_in_min']
				]);  
	}//end function create flight's info

	private function validation($request)
	{
    	$today = Carbon::now();
		return $this->apiValidation($request, [
			'origin_city'		=>'required|string|max:50|min:3',
			'destination_city'	=>'required|string|max:50|min:3',
			'price'				=>'required|regex:/^\d*(\.\d{2})?$/',
			'takeoff_time'		=>'required|date_format:Y-m-d H:i:s|date|after_or_equal:'.$today,
			'landing_time'		=>'required|date_format:Y-m-d H:i:s|date|after:takeoff_time'
		]);
	}//end function validation

	private function getMinutesAsCoast($landing_time,$takeoff_time)
	{
		$take_time 		= date_diff($landing_time,$takeoff_time);
		$minutes 		= $take_time->days * 24 * 60;
		$minutes 		+= $take_time->h * 60;
		$minutes 		+= $take_time->i;

		return $minutes;
	}//end function get Minutes As Coast for nodes

}