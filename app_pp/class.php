<?php

class Categories {
    
    // Identifies the amount of the particular category

    public function find_category_amount($arrays, $key, $search) {
        
        $count = 0;
     
        foreach($arrays as $object) {
            if(is_object($object)) {
               $object = get_object_vars($object);
            }
     
            if(array_key_exists($key, $object) && $object[$key] == $search) $count++;
        }
     
        return $count;

    }

    
}

?>