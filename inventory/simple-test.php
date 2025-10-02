<?php
session_start();
$_SESSION['user_id'] = 1;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple API Test</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Simple API Test</h1>
    <p>Session ID: <?php echo session_id(); ?></p>
    <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
    
    <button onclick="testAPI()">Test API</button>
    <div id="result"></div>
    
    <script>
    function testAPI() {
        $.ajax({
            url: 'api/get-inventory-items.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#result').html('<p style="color: green;">✅ Success! Found ' + response.inventory_items.length + ' items</p>');
                    console.log('Items:', response.inventory_items);
                } else {
                    $('#result').html('<p style="color: red;">❌ Error: ' + response.message + '</p>');
                }
            },
            error: function(xhr, status, error) {
                $('#result').html('<p style="color: red;">❌ AJAX Error: ' + error + '</p>');
                console.error('AJAX Error:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
            }
        });
    }
    </script>
</body>
</html>
