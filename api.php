<?php

require_once('class.php');

header("Content-Type:application/json");

$products = new Products;

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

// If the request is missing keys, then it is invalid

if (!isset($decoded['products'][0]['product_id']) || (!isset($decoded['products'][0]['qty'])))  {
    throw new Exception('The JSON file is missing the correct keys');
}


// Gets all of the product information

$products->get_all_product_information();

// Gets the product ids and quanities from the request

$all_product_ids = array_column($decoded['products'], 'product_id');
$all_qtys = array_column($decoded['products'], 'qty');

// Creates an array of each individual product requested

$products->create_individual_product_list($all_product_ids, $all_qtys);

// Sorts all of the the products in descending price order. This means that the customer will
// get the best deal and  the grand total will be the same independent of the order that it
// has been added to the shopping basket

usort($products->category_price, 'order_by_price');

function order_by_price($a, $b) {
    return $b['price'] > $a['price'] ? 1 : -1;
}    

// Identifies the amount of meal deals

$products->calculate_meal_deals_quantity();

// Groups the non-meal deal products together

$products->remove_meal_deals();

// Calculates the total price of non-meal deal products

$products->individual_products_total();

// Sends off the the final total

echo json_encode($products->calculate_grand_total());

?>