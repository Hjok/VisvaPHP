<?php
    class Visvalingam
    {
        /**
         * 
         * The data must be an array containing arrays A
         * A must have (at least) two elements : the abscissa values and the ordinate values
         * When getting priorityed data an other element will be added : the priority of each point
         *
         */
        
        
        private $data; //The original data
        private $priorityedData; //The data once priorityed, ordered by priority
        private $unorderedpriorityedData; //The data priorityed but ordered by abscissa values
        private $x; //Name of the abscissa key
        private $y; //Name of the ordinate key
        private $priority; //Name of the priority key
        private $typeX; //Type of the abscissa values (currently date or numeric)
        
        /**
         * A Static function that return the amount of points you need
         * @param array $data an array containing the points
         * @param int $amount the number of points you want to get
         * @param bool $ordered (optional) wether you want the points ordered by priority values or not (if not they will be ordered by abscissa values). Default is false.
         * @param string|int $x (optional) key of the abscissa values in each element of your data, default is 'x'
         * @param string|int $y (optional) key of the ordinate values in each element of your data, default is 'y'
         * @param string|int $priority (optional) key of the priority value in each element of data returned, default is 'priority'
         * @return array an array containing the filtered points
         */
        public static function filterPoints($data, $amount, $ordered=false, $x='x', $y='y', $priority='priority')
        {
            $Vis=new self($data, $x, $y, $priority);
            if($ordered)
                return $Vis->getOrderedData($amount);
            return $Vis->getData($amount);
        }
        
        /**
         * The constructor take a set of data and priority it
         * @param array $data each element of $data must be an array containing an abscissa and an ordinate value, the abscissa value can be a numeric value, or a DateTime or a string formated to construct a DateTime
         * @param string|int $x (optional) key of the abscissa values in each element of your data, default is 'x'
         * @param string|int $y (optional) key of the ordinate values in each element of your data, default is 'y'
         * @param string|int $priority (optional) key of the priority value in each element of data returned, default is 'priority'
         * @param bool $check (optional) whether the constructor should check data integrity or not. DO NOT set it to false unless you are really sure what you're doing and you really want to spare some ms
         */
        function __construct($data, $x='x', $y='y', $priority='priority', $check=true)
        {
            $this->data=$data;
            $this->x=$x;
            $this->y=$y;
            $this->priority=$priority;
            
            if($check)
                $this->checkDataIntegrity();
            else
            {
                if(is_string($this->data[0][$this->x]))
                    $this->typeX='dateString';
                //If their are DateTime objects
                elseif((gettype($this->data[0][$this->x]) =='object' && get_class($this->data[0][$this->x]) =='DateTime'))
                    $this->typeX='date';
                elseif(is_numeric($this->data[0][$this->x]))
                    $this->typeX='number';
                else
                    throw new Exception($this->x . ' values are neither numeric nor DateTime or string');
            }

            $this->priorityData();
            $this->unorderedpriorityedData=null;
        }
        
        /**
         * Get some points selected by priority and ordered by abscissa values
         * DO NOT use this function if you intend to filter data on runtime but only for direct drawing
         * @param int $size (optional) number of points to return, if negative return all points, if >3 return 3 points, default is -1
         * @return array containing the points ordered by abscissa values
         *
         */
        public function getData($size=-1)
        {
            if($size>0)
            {
                $return=array_slice($this->priorityData, $size>2 ? -$size : -3);
                return $this->orderByX($return);
            }
            if(!$this->unorderedpriorityedData)
                $this->orderByX();
            return $this->unorderedpriorityedData;
        }
        
        /**
         * Get some points selected and ordered by priority
         * USE this function if you intend to filter data on runtime, NOT for direct drawing
         * @param int $size (optional) number of points to return, if negative return all points, if >3 return 3 points, default is -1
         * @return array containing the points ordered by abscissa values
         *
         */
        public function getOrderedData($size=-1)
        {
            if($size>0)
                return array_slice($this->priorityData, $size>2 ? -$size : -3);
            return $this->priorityData;
        }
        
        /**
         * Attribute a priority to each point according to V-W's algorithm
         * 
         */
        private function priorityData()
        {
            //If there is less than three points, we can't use the algorithm
            if(count($this->data)<3)
            {
                foreach ($this->data as &$d)
                {
                    $d[$this->priority]=1;
                }
            }
            else
            {
                $maxArea=0;
                $i=0;
                $max=count($this->data)-1;
                $triangles = array();
                $this->priorityData=array();
                for($i=0; $i<=$max; $i++)
                {
                    //We assign to each point the value of the area of his triangle
                    if($i>0 && $i<$max)
                    {
        
                        $triangle=array_slice($this->data, $i-1, 3);
                        $triangle[1][$this->priority]=$this->area($triangle);
                        $triangles[]=$triangle;
                    }
                    else
                    {
                        //For first and last points we assign a default value
                        $triangle[1]=$this->data[$i];
                        $triangle[1][$this->priority]=-1;
                        $triangles[]=$triangle;
                    }
                }
                $max=count($triangles)-1;
                //We assign to each point the values of triangles of his neighbors
                for($i=1; $i<$max; $i++)
                {
                    $triangles[$i][0]=$triangles[$i-1][1];
                    $triangles[$i][2]=$triangles[$i+1][1];
                
                }
                $max=count($triangles);
                //While there is more than two points, we keep eliminating
                while($max>2)
                {
                    //We seek for point with lowest area
                    $toErase=[1,$triangles[1][1][$this->priority]];
        
                    for($i=1; $i<$max-1; $i++)
                    {
                        if($triangles[$i][1][$this->priority]<=$toErase[1])
                            $toErase=[$i,$triangles[$i][1][$this->priority]];
                    }
                    //If the point has a lower area than the previous eliminated point
                    //We assign to this point the priority of previous point+1
                    if($toErase[1]<=$maxArea)
                        $triangles[$toErase[0]][1][$this->priority]=$maxArea+1;
                        
                    $maxArea=$triangles[$toErase[0]][1][$this->priority];
                    $this->priorityData[]=$triangles[$toErase[0]][1];
                    //We delete point from array
                    array_splice($triangles, $toErase[0], 1);
                    $max=count($triangles);
                    
                    //And we update area of his neighbors
                    if($toErase[0]>1)
                    {
                        $triangles[$toErase[0]-1][2]=$triangles[$toErase[0]][1];
                        $triangles[$toErase[0]-1][1][$this->priority]=$this->area($triangles[$toErase[0]-1]);
                    }
                    if($toErase[0]<$max-1)
                    {
                        $triangles[$toErase[0]][0]=$triangles[$toErase[0]-1][1];
                        $triangles[$toErase[0]][1][$this->priority]=$this->area($triangles[$toErase[0]]);
                    }
                }
                //Finally we add the greatest priority to first and last points
                $triangles[1][1][$this->priority]=$maxArea+1;
                $this->priorityData[]=$triangles[1][1];
                $triangles[0][1][$this->priority]=$maxArea+2;
                $this->priorityData[]=$triangles[0][1];
                

            }
        }
        
        /**
         * This function compute the area of the triangle of a given pint
         * @param array $t an array containing the point and his neighbors
         */
        private function area($t)
        {
            switch ($this->typeX)
            {
                case 'dateString':
                    try {
                        $d0=new DateTime($t[0][$this->x]);
                        $d1=new DateTime($t[1][$this->x]);
                        $d2=new DateTime($t[2][$this->x]);
                        return abs(($t[2][$this->y]-$t[0][$this->y])*($d1->getTimestamp()-$d0->getTimestamp())-($t[1][$this->y]-$t[0][$this->y])*($d2->getTimestamp()-$d0->getTimestamp()));
                    }
                    catch(Exception $e)
                    {
                        throw new Exception('Problem met when trying to build DateTime from a ' . $this->x . 'value : ' . $e->getMessage());
                    }
                    break;
                case 'date':
                    return abs(($t[2][$this->y]-$t[0][$this->y])*($t[1][$this->x]->getTimestamp()-$t[0][$this->x]->getTimestamp())-($t[1][$this->y]-$t[0][$this->y])*($t[2][$this->x]->getTimestamp()-$t[0][$this->x]->getTimestamp()));
                    break;
                case 'number':
                    return abs(($t[2][$this->y]-$t[0][$this->y])*($t[1][$this->x]-$t[0][$this->x])-($t[1][$this->y]-$t[0][$this->y])*($t[2][$this->x]-$t[0][$this->x]));
                    break;
            }
        }
        /**
         * This function order the data set by his abscissa values
         * @param array $data (optional) : the data to order, if none order the priorityData and stock it in unorderedpriorityedData
         */
        private function orderByX($data=null)
        {
            $toOrder=$data ? $data : $this->priorityData;
            
            $x=$this->x;
            switch ($this->typeX)
            {
                case 'dateString':
                    try
                    {
                        usort($toOrder, function($a, $b) use ($x)
                        {
                            $a=new DateTime($a[$x]);
                            $b=new DateTime($b[$x]);
                            if($a->getTimestamp()>$b->getTimestamp())
                                return 1;
                            return -1;
                        });
                        
                    }
                    catch(Exception $e)
                    {
                        throw new Exception('Problem met when trying to build DateTime from a ' . $this->x . 'value : ' . $e->getMessage());
                    }
                    
                    break;
                case 'date':
                    try
                    {
                        usort($toOrder, function($a, $b) use ($x)
                        {
                            if($a[$x]->getTimestamp()>$b[$x]->getTimestamp())
                                return 1;
                            return -1;
                        });
                    }
                    catch(Exception $e)
                    {
                        throw new Exception('Problem met when trying to build DateTime from a ' . $this->x . 'value : ' . $e->getMessage());
                    }
                    break;
                case 'number':
                        usort($toOrder, function($a, $b) use ($x)
                        {
                            if($a[$x]>$b[$x])
                                return 1;
                            return -1;
                        });
                    break;
            }
            if($data)
                return $toOrder;
            $this->unorderedpriorityedData=$toOrder;
        }
        
        /**
         * Check data integrity, throw error if data is not what the algorithm is excepting
         *
         */
        private function checkDataIntegrity()
        {
            $this->typeX=null;
            foreach($this->data as $d)
            {
                //If it's the first iteration, we determine typeX
                if(!$this->typeX)
                {
                    if(is_string($d[$this->x]))
                        $this->typeX='dateString';
                    //If their are DateTime objects
                    elseif((gettype($d[$this->x]) =='object' && get_class($d[$this->x]) =='DateTime'))
                        $this->typeX='date';
                    elseif(is_numeric($d[$this->x]))
                        $this->typeX='number';
                    else
                        throw new Exception('First ' . $this->x . ' value is neither numeric nor DateTime or string');
                }
                else
                {
                    switch ($this->typeX)
                    {
                        case 'dateString':
                            if(!is_string($d[$this->x]))
                                throw new Exception('An ' . $this->x . ' value \(' . serialize($d[$this->x]) . '\) is of type ' . gettype($d[$this->x]) . ' whereas other were strings');
                            break;
                        case 'date':
                            if(gettype($d[$this->x]) !='object' || get_class($d[$this->x]) !='DateTime')
                                throw new Exception('An ' . $this->x . ' value (' . serialize($d[$this->x]) . ') is of type ' . gettype($d[$this->x]) . ' whereas other were DateTime');
                            break;
                        case 'number':
                            if(!is_numeric($d[$this->x]))
                                throw new Exception('An ' . $this->x . ' value (' . serialize($d[$this->x]) . ') is of type ' . gettype($d[$this->x]) . ' whereas other were numerics');
                            break;
                            
                    }
                }
                if(!is_numeric($d[$this->y]))
                    throw new Exception('An ' . $this->y . ' value (' . serialize($d[$this->y]) . ') is of type ' . gettype($d[$this->y]) . ' instead of being numeric');
            }
        }
    }
?>