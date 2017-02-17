<?php


$array = array('a','f','c','b','e','h','j','i','g');
print_r(maopao_fun($array));
    function maopao_fun($array){
        
        $count = count($array);
        for($i=0;$i<$count;$i++){
            for($j=$count-1;$j>$i;$j--){
                if($array[$j] > $array[$j-1]){
                    $tmp = $array[$j];
                    $array[$j] = $array[$j-1];
                    $array[$j-1] = $tmp;
                }
            }
        }
        return $array;
    }

?>