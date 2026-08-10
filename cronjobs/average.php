<?php
ini_set('max_execution_time', '600');
define("BASEPATH", TRUE);
require $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
require $_SERVER["DOCUMENT_ROOT"]."/app/init.php";
$smmapi = new SMMApi();

// Get all services
$services = $conn->prepare("SELECT * FROM services");
$services->execute(array());
$services = $services->fetchAll(PDO::FETCH_ASSOC);

foreach($services as $service):
    // Get current datetime for this service's update
    $currentDateTime = date('Y-m-d H:i:s');
    
    // Get 2 latest completed orders for this service
    $orders = $conn->prepare("SELECT * FROM orders 
                            INNER JOIN services ON services.service_id = orders.service_id 
                            WHERE orders.order_status = :status 
                            AND orders.service_id = :id 
                            ORDER BY orders.order_id DESC 
                            LIMIT 2");
    $orders->execute(array(
        "status" => "completed",
        "id" => $service["service_id"]
    ));
    $orders = $orders->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($orders) > 0) {
        $totalSeconds = 0;
        $orderCount = 0;
        
        foreach($orders as $order):
            $date1 = $order["order_create"];
            $date2 = $order["last_check"];
            
            $timestamp1 = strtotime($date1);
            $timestamp2 = strtotime($date2);
            
            $diffSeconds = abs($timestamp2 - $timestamp1);
            $totalSeconds += $diffSeconds;
            $orderCount++;
        endforeach;
        
        $averageSeconds = $totalSeconds / $orderCount;
        
        // Calculate hours, minutes, seconds
        $hours = floor($averageSeconds / 3600);
        $remainingSeconds = $averageSeconds % 3600;
        $minutes = floor($remainingSeconds / 60);
        $seconds = round($remainingSeconds % 60);
        
        // Build time parts array
        $timeParts = [];
        if ($hours > 0) {
            $timeParts[] = $hours . ' hour' . ($hours != 1 ? 's' : '');
        }
        if ($minutes > 0) {
            $timeParts[] = $minutes . ' minute' . ($minutes != 1 ? 's' : '');
        }
        if ($seconds > 0 || count($timeParts) == 0) {
            $timeParts[] = $seconds . ' second' . ($seconds != 1 ? 's' : '');
        }
        
        // Format the time string
        if (count($timeParts) > 1) {
            $lastPart = array_pop($timeParts);
            $average = implode(' and ', $timeParts) . ' and ' . $lastPart;
        } else {
            $average = $timeParts[0];
        }
        
        // Update service record with average time and last updated timestamp
        $update = $conn->prepare("UPDATE services SET time = :time, last_updated = :last_updated WHERE service_id = :id");
        $update->execute(array(
            "id" => $service["service_id"],
            "time" => $average,
            "last_updated" => $currentDateTime
        ));
        
        echo "Service ID: {$service['service_id']} - Average Time: $average (Last Updated: $currentDateTime)<br>";
    } else {
        // Update with last updated timestamp even when no orders found
        $update = $conn->prepare("UPDATE services SET time = :time, last_updated = :last_updated WHERE service_id = :id");
        $update->execute(array(
            "id" => $service["service_id"],
            "time" => "No data available",
            "last_updated" => $currentDateTime
        ));
        
        echo "Service ID: {$service['service_id']} - No completed orders found (Last Updated: $currentDateTime)<br>";
    }
endforeach;

// Add final timestamp showing when the script completed
echo "<hr>Script completed at: " . date('Y-m-d H:i:s');