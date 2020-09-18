<?php

namespace App\Http\Controllers\Api\Classes;


class Dijkstra
{
    /** @var integer[][] The graph, where $graph[node1][node2]=cost */
    protected $graph;
    /** @var int[] Distances from the source node to each other node */
    protected $distance;
    /** @var string[][] The previous node(s) in the path to the current node */
    protected $previous;
    /** @var int[] Nodes which have yet to be processed */
    protected $queue;
    /** @var int[] Nodes paths available which have  */
    protected $availble_paths;    
    /**
     * @param integer[][] $graph
    */

    public function __construct($graph)
    {
        $this->graph = $graph;
    }

    /**
     * Process the next (i.e. closest) entry in the queue.
     *
     * @param string[] $exclude A list of nodes to exclude - for calculating next-shortest paths.
     *
     * @return void
     */

    protected function processNextNodeInQueueChepeast(array $exclude)
    {            
        // Process the closest vertex
        $closest = array_search(min($this->queue), $this->queue);
        if (!empty($this->graph[$closest]) && !in_array($closest, $exclude))
        {
            foreach ($this->graph[$closest] as $neighbor => $cost)
            {
                if (isset($this->distance[$neighbor]))
                {

                    if ($this->distance[$closest] + $cost['price'] < $this->distance[$neighbor])
                    {
                        // A shorter path was found
                        $this->distance[$neighbor]      = $this->distance[$closest] + $cost['price'];
                        $this->previous[$neighbor]      = array($cost);
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[$neighbor]= $cost;
                    }
                    elseif ($this->distance[$closest] + $cost['price'] === $this->distance[$neighbor])
                    {
                        // An equally short path was found
                        $this->previous[$neighbor][]    = $cost;
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[$neighbor]= $cost;
                    }
                }
            }
        }

        unset($this->queue[$closest]);
    }


    /**
     * Extract all the paths from $source to $target as arrays of nodes.
     *
     * @param string $target The starting node (working backwards)
     *
     * @return string[][] One or more shortest paths, each represented by a list of nodes
     */
    protected function extractCheapestPaths($target)
    {
        $paths                    = array(array($target));

        for ($key = 0; ( isset($paths[$key]) && is_array($paths[$key]) ) ? array_key_exists(0, $paths[$key] ) : isset($paths[$key]); ++$key)
        {
            $path = $paths[$key];

            $neighbors = (is_array($path[0]) ) ? $this->previous[ $path[0]['origin_city'] ]   : $this->previous[$path[0]] ;

            if(  !empty($neighbors) ) {
                foreach ($neighbors as $previous) {
                    $copy       = $path;
                    array_unshift($copy, $previous);
                    $paths[]    = $copy;
                }
                unset($paths[$key]);
            }
        }
        $last_path= end($paths);

        array_pop($last_path);

        $coastChepeastPath = $this->getTotalPriceChepeastPath($last_path);

        return array(
            'total price'    => $coastChepeastPath['total_price'],
            'total duration' => $this->hoursandmins($coastChepeastPath['num_minutes'], $format = '%02d Hours, %02d Minutes'),
            'schedule'       => $last_path
        );
    }


    private function getTotalPriceChepeastPath($last_path)
    {
        $total_price        = 0;
        $num_minutes        = 0;

        foreach ( $last_path as  $key => $path_value) {
            
            if(is_array($path_value)  && !empty($path_value) ){

                $total_price += $path_value['price'];
            }
        }

        if(is_array($last_path)  && !empty($last_path) ) {

            $num_minutes = $this->getMinutesAsCoast(date_create(end($last_path)['landing_time']), date_create($last_path[0]['takeoff_time']) );
        }

        return array('total_price'=> $total_price, 'num_minutes'=>$num_minutes);
    }//end function get Total Price Chepeast Path


    protected function processNextNodeInQueueFastest(array $exclude)
    {            
        // Process the closest vertex
        $closest = array_search(min($this->queue), $this->queue);

        if (!empty($this->graph[$closest]) && !in_array($closest, $exclude))
        {
            foreach ($this->graph[$closest] as $neighbor => $cost)
            {
                //add new voast to get all paths available
                $cost['!*_$_11_@_*!'] = 0;
                if (isset($this->distance[$neighbor]))
                {
                    if ($this->distance[$closest] + $cost['!*_$_11_@_*!'] < $this->distance[$neighbor])
                    {
                        // A shorter path was found
                        $this->distance[$neighbor]      = $this->distance[$closest] +$cost['!*_$_11_@_*!'];
                        array_pop($cost);
                        $this->previous[$neighbor]      = array($cost);
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[]= $cost;
                    }
                    elseif ($this->distance[$closest] + $cost['!*_$_11_@_*!'] === $this->distance[$neighbor])
                    {
                        // An equally short path was found
                        array_pop($cost);
                        $this->previous[$neighbor][]    = $cost;
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[]= $cost;
                    }
                }
            }
        }
        unset($this->queue[$closest]);
    }



    protected function extractFastestPaths($target)
    {
        $paths          = array(array($target));

        for ($key = 0; ( isset($paths[$key]) && is_array($paths[$key]) ) ? array_key_exists(0, $paths[$key] ) : isset($paths[$key]); ++$key)
        {
            $path = $paths[$key];

            $neighbors = (is_array($path[0]) ) ? $this->previous[ $path[0]['origin_city'] ]   : $this->previous[$path[0]] ;

            if(  !empty($neighbors) ) {
                foreach ($neighbors as $previous)
                {
                    $copy       = $path;
                    array_unshift($copy, $previous);
                    $paths[]    = $copy;
                }
                unset($paths[$key]);
            }
        }

        $minimumTimeDuration= $this->getMinimumTimeDurationInPaths($paths);

        array_pop($paths[$minimumTimeDuration['fastest_key']]);

        return array(
            'total price'       => $this->getTotalPriceFastestPath($paths[$minimumTimeDuration['fastest_key']]),
            'total duration'    => $this->hoursandmins($minimumTimeDuration['minimum_minutes'], $format = '%02d Hours, %02d Minutes'),
            'schedule'          => $paths[$minimumTimeDuration['fastest_key']] 
        );

    }


    private function getMinimumTimeDurationInPaths($paths)
    {
        $num_minutes          = 0;
        $arr_num_minutes      = array();

        foreach ($paths as  $key => $path_value) {
            array_pop($path_value);

            if(is_array($path_value)  && !empty($path_value) ){
                $num_minutes = $this->getMinutesAsCoast(date_create(end($path_value)['landing_time']), date_create($path_value[0]['takeoff_time']) );
            }
            $arr_num_minutes[$key] = $num_minutes;
        }
        $fastest_key = array_search(min($arr_num_minutes),$arr_num_minutes );

        return array('fastest_key'=> $fastest_key, 'minimum_minutes'=> min($arr_num_minutes));
    }//end function get Key Minimum Time Duration In Paths



    private function getTotalPriceFastestPath($path)
    {
        $total_price     = 0;

        foreach ($path as $key => $value) {
            $total_price += $path[$key]['price'];
        }
        return $total_price;
    }//end function get Total Price Fastest Path



    /**
     * Calculate the shortest path through a a graph, from $source to $target.
     *
     * @param string   $source  The starting node
     * @param string   $target  The ending node
     * @param string[] $exclude A list of nodes to exclude - for calculating next-shortest paths.
     *
     * @return string[][] Zero or more shortest paths, each represented by a list of nodes
     */
    public function shortestPaths($source, $target, array $exclude = array())
    {
        // The shortest distance to all nodes starts with infinity...
        $this->distance = array_fill_keys(array_keys($this->graph), INF);
        // ...except the start node
        $this->distance[$source] = 0;
        // The previously visited nodes
        $this->previous = array_fill_keys(array_keys($this->graph), array());
        // Process all nodes in order
        $this->queue = array($source => 0);
        if(request()->type == 0){
            while (!empty($this->queue))
            {
                $this->processNextNodeInQueueChepeast($exclude);
            }
        }
        if(request()->type == 1){
            while (!empty($this->queue))
            {
                $this->processNextNodeInQueueFastest($exclude);
            }
        }   
        if ($source === $target)
        {
            // A null path
            return array(array($source));
        } 
        elseif (empty($this->previous[$target]))
        {
            // No path between $source and $target
            return array();
        }
        else
        {
            // One or more paths were found between $source and $targets
            if(request()->type == 0){
                return $this->extractCheapestPaths($target);
            }
            if(request()->type == 1){
                return $this->extractFastestPaths($target);
            }
        }
    }

    private function getMinutesAsCoast($landing_time,$takeoff_time)
    {
        $take_time   = date_diff($landing_time,$takeoff_time);
        $minutes     = $take_time->days * 24 * 60;
        $minutes    += $take_time->h * 60;
        $minutes    += $take_time->i;

        return $minutes;
    }//end function get get Minutes As Coast for nodes


    //convert number of minites to h : m 
    private function hoursandmins($time, $format = '%02d:%02d')
    {
        if ($time < 1) {
            return;
        }
        $hours = floor($time / 60);
        $minutes = ($time % 60);
        return sprintf($format, $hours, $minutes);
    }
}