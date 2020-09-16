<?php

use Illuminate\Database\Seeder;
use App\Models\Flight;

class FlightsTableDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('saves a flight’s information.');
        
        Flight::create([
            'origin_city'		=>'cairo',
            'destination_city'	=>'dubai',
            'price'				=>3000,
            'takeoff_time'		=>date_create('2020-09-27 17:22:02'),
            'landing_time'		=>date_create('2020-09-28 17:22:02'),
            'date_diff_in_min'	=>$this->getMinutesAsCoast(date_create('2020-09-27 17:22:02'), date_create('2020-09-28 17:22:02'))
        ]);
        $this->command->info('Creating a flight from cairo to dubai The flight
        price is 3000 EGP from 2020-09-27 17:22:02 to 2020-09-28 17:22:02 ');
        Flight::create([
            'origin_city'		=>'dubai',
            'destination_city'	=>'tokyo',
            'price'				=>6000,
            'takeoff_time'		=>date_create('2020-09-28 10:22:02'),
            'landing_time'		=>date_create('2020-09-28 17:22:02'),
            'date_diff_in_min'	=>$this->getMinutesAsCoast(date_create('2020-09-28 10:22:02'), date_create('2020-09-28 17:22:02'))
        ]);
        $this->command->info('Creating flight from dubai to tokyo The flight
        price is 6000 EGP from 2020-09-28 10:22:02 to 2020-09-28 17:22:02 ');
        Flight::create([
            'origin_city'		=>'cairo',
            'destination_city'	=>'tokyo',
            'price'				=>12000,
            'takeoff_time'		=>date_create('2020-09-28 08:22:02'),
            'landing_time'		=>date_create('2020-09-28 22:22:02'),
            'date_diff_in_min'	=>$this->getMinutesAsCoast(date_create('2020-09-28 08:22:02'), date_create('2020-09-28 22:22:02'))
        ]);
        $this->command->info('Creating flight from cairo to tokyo The flight
        price is 12000 EGP from 2020-09-28 08:22:02 to 2020-09-28 22:22:02 ');
    }

    private function getMinutesAsCoast($landing_time,$takeoff_time)
	{
		$take_time 		= date_diff($landing_time,$takeoff_time);
		$minutes 		= $take_time->days * 24 * 60;
		$minutes 		+= $take_time->h * 60;
		$minutes 		+= $take_time->i;

		return $minutes;
	}//end function get Minutes As Coast for nodes
}
