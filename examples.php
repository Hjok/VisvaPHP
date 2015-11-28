<?php

    //File inclusion
    require_once 'Visvalingam.php';
    
    echo '<table border="5"    width="100%"   cellpadding="4" cellspacing="3"><tr><th colspan="3"><br /><h3>Some examples of points priorityed using Visvalingam</h3></th>';
    echo '<tr><th colspan="3">(Uncomment indicated lines in examples.php to see more examples)</th>';
    //Let's create a fake set of data
    $dataDate=array();
    for($i=0; $i<200; $i++)
    {
        //Here  abcissa values are dates
        $dataDate[$i][0]=date('c', 1446637647+3600*$i);
        $dataDate[$i][1]=rand(0,100);
    }
    //Get the result using the static function
    //Note that we asked here for the 20 points with highest priority, ordered by priority
    try
    {
        $r=Visvalingam::filterPoints($dataDate, 20, true, 0, 1);
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
    //Uncomment following lines to see result
    
    echo '<tr><th colspan="3"><br /><h4>20 Points of first dataset with highest priority, ordered by priority</h4></th> <tr><th>Abcissa</th><th>Ordinate</th> <th>priority</th></tr>';
    foreach($r as $o) {
        echo '<tr><td>' . $o[0] . '</td><td>' . $o[1] . '</td><td>' . $o["priority"] . '</td></tr>';
      }/**/
    
    
    
    //We transform the data set :  abcissa values arent string anymore but DateTime
    for($i=0; $i<200; $i++)
    {
        $dataDate[$i][0]=new DateTime($dataDate[$i][0]);
    }
    
    try
    {
        //Create a instance of Visvalingam after the data set
       $vis=new Visvalingam($dataDate, 0, 1, 2);
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
   
   
   
    //Get the 30 highest priority points, ordered by their  abcissa values
    $rOrderedByX=$vis->getData(30);
    ///Uncomment following lines to see result
    
    /*echo '<tr><th colspan="3"><br /><h4>30 Points of first dataset with highest priority, ordered by abcissa</h4></th> <tr><th>Abcissa</th><th>Ordinate</th> <th>priority</th></tr>';
    foreach($rOrderedByX as $o) {
        echo '<tr><td>' . $o[0]->format('Y-m-d H:i:s') . '</td><td>' . $o[1] . '</td><td>' . $o[2] . '</td></tr>';
      }/**/
    
    
    //Get the 30 highest priority points, ordered by their priority values
    $rOrderedBypriority=$vis->getOrderedData(15);
    //Uncomment following lines to see result
    
    /*echo '<tr><th colspan="3"><br /><h4>15 Points of first dataset with highest priority, ordered by priority</h4></th> <tr><th>Abcissa</th><th>Ordinate</th> <th>priority</th></tr>';
    foreach($rOrderedBypriority as $o) {
        echo '<tr><td>' . $o[0]->format('Y-m-d H:i:s') . '</td><td>' . $o[1] . '</td><td>' . $o[2] . '</td></tr>';
      }/**/
    
    
    //Now let's try with an other set using numeric values for  abcissa and having  abcissa and ordinate keys named 'x' and 'y', so we don't have to pass them as arguments
    $dataNumeric=array();
    for($i=0; $i<300; $i++)
    {
        $dataNumeric[$i]['x']=$i;
        $dataNumeric[$i]['y']=rand(0,100);
    }
    //Get the result using the static function
    //Note that we asked here for the 20 points with highest priority, ordered by abcissa
    try
    {
        $r=Visvalingam::filterPoints($dataNumeric, 20);
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
    //Uncomment following lines to see result
    
    /*echo '<tr><th colspan="3"><br /><h4>20 Points of first dataset with highest priority, ordered by abcissa</h4></th> <tr><th>Abcissa</th><th>Ordinate</th> <th>priority</th></tr>';
    foreach($r as $o) {
        echo '<tr><td>' . $o["x"] . '</td><td>' . $o["y"] . '</td><td>' . $o["priority"] . '</td></tr>';
      }
    echo '</table>';/**/
    
    
    echo '</table>';
    


?>