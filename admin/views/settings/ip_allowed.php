<style>
/* Row */
.container .row{
 background-color:#ffffff;
}

/* Accordion Content */
.col-md-8 .panel .panel-body{
 background-color:#ffffff !important;
}

/* New */
#new_ip{
 display: flex;
    align-items: center;
    background-color: #e9ecef; /* Light input background */
    border-radius: 8px;
    padding: 12px;
    color: #333; /* Dark text color */
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    min-height: 50px;
}


body{
 font-family:'Inter', sans-serif;
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            background-color:rgba(30,30,30,0) !important;
            color: #e0e0e0 !important ; /* Light gray text */
}
/* Accordion Content */
.col-md-8 .panel .panel-body{
 width: 100%;
     border-style:none;
   
    background-color: #2b2b2b;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
}
.container .panel{
 border-style:none !important;
 border-width:0px !important;
 border-top-left-radius:12px;
 border-top-right-radius:12px;
 border-bottom-left-radius:12px;
 border-bottom-right-radius:12px;
}
/* Label */
.panel-body form label{
 font-weight:600;
 font-size:16px;
}

/* Heading */
.panel .panel-body h4{
 font-weight:600;
 font-size:16px;
}


/* List Item */
.panel-body ul li{
 padding-bottom:10px;
 padding-top:10px;
 padding-left:10px;
 padding-right:10px;
 
}

/* Button */
.panel-body form .btn{
    
 background: linear-gradient(145deg, #007bff, #0056b3); /* Light gradient */
    color: #ffffff;
    padding: 14px 28px;
    font-size: 14px;
    font-weight: 500;
    border: none;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: background 0.3s ease, transform 0.2s ease;
}
  .panel-body form .btn:hover {
    background: linear-gradient(145deg, #0056b3, #003c82);
    transform: translateY(-2px);
}
/* Button */
.panel-body ul .btn-danger{
 background: linear-gradient(145deg, #c71f1f, #e04d4d); /* Gradient background for a professional look */
  color: #ffffff; /* Text color */
  position:relative;
 left:10px;
  font-size: 14px; /* Font size */
  font-weight: 500; /* Font weight */
  border: none; /* Remove default border */
  border-radius: 5px; /* Slightly rounded corners */
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3); /* Subtle shadow for depth */
  transition: background 0.3s ease, transform 0.2s ease; /* Smooth transition for hover effects */
}
.panel-body ul .btn-danger:hover {
  background: linear-gradient(145deg, #e04d4d, #f76c6c); /* Darker gradient on hover */
  transform: translateY(-2px); /* Slight lift effect */
}
</style>
<div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
      <?php
      $file = 'admin_security.php';  // Path to the file where $authorized_ips is located
      $message = '';
      
      // Function to get the current $authorized_ips array from admin_security.php
      function getAuthorizedIPs($file) {
          $content = file_get_contents($file);
          preg_match('/\$authorized_ips\s*=\s*\[(.*?)\];/s', $content, $matches);
          if (isset($matches[1])) {
              $ips = array_map('trim', explode(',', $matches[1]));
              return array_filter(array_map(function($ip) {
                  return trim($ip, "'\" ");
              }, $ips));
          }
          return [];
      }

      // Function to update the $authorized_ips array in admin_security.php
      function updateAuthorizedIPs($file, $ips) {
          $ipsArray = array_map(function($ip) {
              return "'$ip'";
          }, $ips);
          $newContent = "\$authorized_ips = [" . implode(', ', $ipsArray) . "];";
          
          // Read the current file content
          $content = file_get_contents($file);
          // Replace the $authorized_ips array in the file
          $newFileContent = preg_replace('/\$authorized_ips\s*=\s*\[.*?\];/s', $newContent, $content);
          
          // Write the updated content back to the file
          file_put_contents($file, $newFileContent);
      }

      // Add new IP
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_ip"])) {
          $newIp = filter_var($_POST["new_ip"], FILTER_VALIDATE_IP);
          if ($newIp) {
              $existingIPs = getAuthorizedIPs($file);
              if (!in_array($newIp, $existingIPs, true)) {
                  $existingIPs[] = $newIp;
                  updateAuthorizedIPs($file, $existingIPs);
                  $message = "IP Address added successfully.";
              } else {
                  $message = "IP Address already exists.";
              }
          } else {
              $message = "Invalid IP Address.";
          }
      }

      // Delete IP
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_ip"])) {
          $deleteIp = $_POST["delete_ip"];
          $existingIPs = getAuthorizedIPs($file);
          if (($key = array_search($deleteIp, $existingIPs)) !== false) {
              unset($existingIPs[$key]);
              updateAuthorizedIPs($file, $existingIPs);
              $message = "IP Address deleted successfully.";
          } else {
              $message = "IP Address not found.";
          }
      }

      

      // Display existing IPs and delete option
      $existingIPs = getAuthorizedIPs($file);
      echo "<h4 style='color:black'>Existing IP Address :</h4>";
      if (!empty($existingIPs)) {
          echo "<ul>";
          foreach ($existingIPs as $ip) {
              echo "<li style='color:black'>" . htmlspecialchars($ip) . " 
              <form method='post' action='' style='display:inline;'>
                <input type='hidden' name='delete_ip' value='" . htmlspecialchars($ip) . "'/>
                <button type='submit' class='btn btn-danger btn-xs'>Delete</button>
              </form>
              </li>";
          }
          echo "</ul>";
      } else {
          echo "<p>No IP addresses found.</p>";
      }
      ?>

      
      <form action="" method="post">
        <div class="form-group">
          <label for="new_ip" style="color:black">Add a new IP Address :</label>
          <input type="text" class="form-control" id="new_ip" name="new_ip" required>
        </div>
        <button type="submit" class="btn btn-default">Submit</button>
      </form>
    </div>
  </div>
</div>
