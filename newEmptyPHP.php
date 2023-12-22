
<?php
$firstName = "Wei";
$lastName = "Gong";
?>
</head>
<body>
<h2>User Registration</h2>
<?php
print <<<Mark
<p>Welcome $firstName $lastName! </p>
<form action="acknowledge.html" method="post">
First Name: <input type="text" name="first_name" id="first_name" value = "$firstName"><br/>
Last Name: <input type ="text" name="last_name" id="last_name" value = "$lastName"><br/>
<br/>
<input type ="submit" name = "submit_button" value="Submit" />
</form>
Mark;
?>
</body>
</html><?php

/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/EmptyPHP.php to edit this template
 */

