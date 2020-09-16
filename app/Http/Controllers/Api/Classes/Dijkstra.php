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
                        $this->previous[$neighbor]      = array($closest);
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[$neighbor]= $cost;
                    }
                    elseif ($this->distance[$closest] + $cost['price'] === $this->distance[$neighbor])
                    {
                        // An equally short path was found
                        $this->previous[$neighbor][]    = $closest;
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
        $array_path                 = array();
        $paths                      = array(array($target));
        $schedule                   = array();
        $total_price                = 0;
        $total_duration             = 0;

        for ($key = 0; isset($paths[$key]); ++$key)
        {
            $path = $paths[$key];

            if (!empty($this->previous[$path[0]])) {
                foreach ($this->previous[$path[0]] as $previous)
                {
                    $copy       = $path;
                    array_unshift($copy, $previous);
                    $paths[]    = $copy;
                }
                unset($paths[$key]);
            }
        }

        foreach (end($paths) as $key => $value)
        {
            if($key == 0) continue;
            array_push($schedule, $this->availble_paths[$value]);
            $total_duration     += $minutes = $this->getMinutesAsCoast(date_create($this->availble_paths[$value]['landing_time']), date_create($this->availble_paths[$value]['takeoff_time']));

            $total_price        += $this->availble_paths[$value]['price'];
        }
        $total_duration_as_day_hour= $this->hoursandmins($total_duration, $format = '%02d Hours, %02d Minutes');
        return array('total price' => $total_price, 'total duration' => $total_duration_as_day_hour, 'schedule' => $schedule );
    }

    protected function processNextNodeInQueueFastest(array $exclude)
    {            
        // Process the closest vertex
        $closest = array_search(min($this->queue), $this->queue);
        if (!empty($this->graph[$closest]) && !in_array($closest, $exclude))
        {
            foreach ($this->graph[$closest] as $neighbor => $cost)
            {
                if (isset($this->distance[$neighbor]))
                {
                    $minutes = $this->getMinutesAsCoast(date_create($cost['landing_time']), date_create($cost['takeoff_time']));
                    if ($this->distance[$closest] + $minutes < $this->distance[$neighbor])
                    {
                        // A shorter path was found
                        $this->distance[$neighbor]      = $this->distance[$closest] + $minutes;
                        $this->previous[$neighbor]      = array($closest);
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[$neighbor]= $cost;
                        
                    }
                    elseif ($this->distance[$closest] + $minutes === $this->distance[$neighbor])
                    {
                        // An equally short path was found
                        $this->previous[$neighbor][]    = $closest;
                        $this->queue[$neighbor]         = $this->distance[$neighbor];
                        $this->availble_paths[$neighbor]= $cost;
                    }
                }
            }
        }
        unset($this->queue[$closest]);
    }


    protected function extractFastestPaths($target)
    {
        $array_path                 = array();
        $paths                      = array(array($target));
        $schedule                   = array();
        $total_price                = 0;
        $total_duration             = 0;

        for ($key = 0; isset($paths[$key]); ++$key)
        {
            $path = $paths[$key];

            if (!empty($this->previous[$path[0]])) {
                foreach ($this->previous[$path[0]] as $previous)
                {
                    $copy       = $path;
                    array_unshift($copy, $previous);
                    $paths[]    = $copy;
                }
                unset($paths[$key]);
            }
        }
        foreach (end($paths) as $key => $value)
        {
            if($key == 0) continue;
            array_push($schedule, $this->availble_paths[$value]);

            $total_duration    += $minutes = $this->getMinutesAsCoast(date_create($this->availble_paths[$value]['landing_time']), date_create($this->availble_paths[$value]['takeoff_time']));

            $total_price       += $this->availble_paths[$value]['price'];
        }
        $total_duration_as_day_hour= $this->hoursandmins($total_duration, $format = '%02d Hours, %02d Minutes');
        return array('total price' => $total_price, 'total duration' => $total_duration_as_day_hour,  'schedule' => $schedule );
    }

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