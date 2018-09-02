<?php

class Products {

    private $sandwich_counter = 1;
    private $snack_counter = 1;
    private $drink_counter = 1;
    private $qty_counter;
    private $individual_products_total;
    private $counter = 0;
    private $count;


    // Gets all of the product information

    public function get_all_product_information () {
        $this->json_source_file = file_get_contents("products.json");
        $this->json_source_array = json_decode($this->json_source_file,true);

    }

    // Creates an array of each individual product requested

    public function create_individual_product_list ($all_product_ids, $all_qtys) {
        
        foreach(array_combine($all_product_ids, $all_qtys) as $value => $tally){
    
            $key_location = array_search($value, array_column($this->json_source_array['products'], 'id'));
            
            // Add multiple entries when there are multiples of a product
        
            while ($this->qty_counter < $tally) {
                $this->category_price[$this->counter]['category'] = $this->json_source_array['products'][$key_location]['category'];
                $this->category_price[$this->counter]['price'] = $this->json_source_array['products'][$key_location]['price'];
                
                $this->qty_counter += 1;
                $this->counter += 1;
            } 
            
            $this->qty_counter = 0;
        }

    }

    // Identifies the amount of meal deals

    public function calculate_meal_deals_quantity() {

        $this->sandwich_count = $this->find_category_amount($this->category_price, 'category', 'sandwich');
        $this->drink_count = $this->find_category_amount($this->category_price, 'category', 'drink');
        $this->snack_count = $this->find_category_amount($this->category_price, 'category', 'snack');
        $this->meal_deal_count = min($this->sandwich_count, $this->drink_count, $this->snack_count);

    }

    // Identifies the amount of the particular category

    private function find_category_amount($arrays, $key, $search) {
        
        $count = 0;
     
        foreach($arrays as $object) {
            if(is_object($object)) {
               $object = get_object_vars($object);
            }
     
            if(array_key_exists($key, $object) && $object[$key] == $search) $this->count++;
        }
     
        return $this->count;

    }

    // Groups the non-meal deal products together

    public function remove_meal_deals() {
        
        foreach($this->category_price as $category_prices) {
            
            if($category_prices['category'] == 'sandwich' AND $this->sandwich_counter <= $this->meal_deal_count) {
                $this->sandwich_counter += 1;
            }
            elseif($category_prices['category'] == 'snack' AND $this->snack_counter <= $this->meal_deal_count){
                $this->snack_counter += 1;
            }
            elseif($category_prices['category'] == 'drink' AND $this->drink_counter <= $this->meal_deal_count){
                $this->drink_counter += 1;
            }
            else {
                
                $this->remaining_category_price[$this->qty_counter] = $category_prices;
                $this->qty_counter += 1;
                
            }
        } 
    }

    // Calculates the total price of non-meal deal products
    
    public function individual_products_total() {

        foreach($this->remaining_category_price as $remaining_category_prices){
            $this->individual_products_total += $remaining_category_prices['price'];
        }

    }

    // Calculates the final total
    
    public function calculate_grand_total() {

        $this->total_price = $this->individual_products_total + ($this->meal_deal_count * 3);

        $this->total = floatval(number_format($this->total_price, 2,'.',''));

        // Formats the final total into json format

        return $total_array = Array (
            'total' => $this->total,
        );

    }

}

?>