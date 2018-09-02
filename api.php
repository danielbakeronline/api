<?php

require_once('class.php');

header("Content-Type:application/json");



// Makes sure that it is a POST request

if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('The request method has to be POST!');
}
 
// Makes sure that the content type of the POST request has been set to application/json

$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    throw new Exception('Content type must be: application/json');
}
 
// Receives the RAW post data

$content = trim(file_get_contents("php://input"));
 
// Attempts to convert the json request to a usable format

$decoded = json_decode($content, true);
 
// If json_decode fails, then the JSON is invalid

if(!is_array($decoded)){
    throw new Exception('Received content contained invalid JSON!');
} 



$categories = new Categories;

$qty_counter = 0;
$counter = 0;
$total_price = 0;
$sandwich_counter = 1;
$snack_counter = 1;
$drink_counter = 1;

/*

// Just added for testing - START

$decoded = Array(

    'products' => Array(
    "0" => Array
        (
            'product_id' => 0,
            'qty' => 2
        ),    
    "1" => Array
        (
            'product_id' => 3,
            'qty' => 2  
        ),    
    "2" => Array
        (
            'product_id' => 8,
            'qty' => 1    
        ),

    "3" => Array
        (
            'product_id' => 2,
            'qty' => 6    
        )
    )
);

*/

// Just added for testing - END



// Gets all of the product information

$json_source_file = file_get_contents("products.json");
$json_source_array = json_decode($json_source_file,true);

// Gets the product ids and quanities from the request

$all_product_ids = array_column($decoded['products'], 'product_id');
$all_qtys = array_column($decoded['products'], 'qty');

// Creates an array of each individual product requested

foreach(array_combine($all_product_ids, $all_qtys) as $value => $tally){
    
    $key_location = array_search($value, array_column($json_source_array['products'], 'id'));
    
    // Add multiple entries when there are multiples of a product

    while ($qty_counter < $tally) {
        $category_price[$counter]['category'] = $json_source_array['products'][$key_location]['category'];
        $category_price[$counter]['price'] = $json_source_array['products'][$key_location]['price'];
        
        $qty_counter += 1;
        $counter += 1;
    } 
    
    $qty_counter = 0;
}

// Sorts all of the the products in descending price order. This means that the customer will
// get the best deal and  the grand total will be the same independent of the order that it
// has been added to the shopping basket

usort($category_price, 'order_by_price');

function order_by_price($a, $b) {
    return $b['price'] > $a['price'] ? 1 : -1;
}   

// Identifies the amount of meal deals

$sandwich_count = $categories->find_category_amount($category_price, 'category', 'sandwich');
$drink_count = $categories->find_category_amount($category_price, 'category', 'drink');
$snack_count = $categories->find_category_amount($category_price, 'category', 'snack');
$meal_deal_count = min($sandwich_count, $drink_count, $snack_count);

// Groups the non-meal deal products together

foreach($category_price as $category_prices) {
    if($category_prices['category'] == 'sandwich' AND $sandwich_counter <= $meal_deal_count) {
        $sandwich_counter += 1;
    }
    elseif($category_prices['category'] == 'snack' AND $snack_counter <= $meal_deal_count){
        $snack_counter += 1;
    }
    elseif($category_prices['category'] == 'drink' AND $drink_counter <= $meal_deal_count){
        $drink_counter += 1;
    }
    else {
        $remaining_category_price[$qty_counter] = $category_prices;
        $qty_counter += 1;
    }
}

$individual_products_total = 0;

// Calculates the total price of non-meal deal products

foreach($remaining_category_price as $remaing_category_prices){
    $individual_products_total += $remaing_category_prices['price'];
}

// Calculates the final total

$total_price = $individual_products_total + ($meal_deal_count * 3);

$total = floatval(number_format($total_price, 2,'.',''));

// Formats the final total into json and sends it off

$total_array = Array (
    'total' => $total,
);

echo json_encode($total_array);

?>