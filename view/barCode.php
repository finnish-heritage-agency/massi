<?php
$text = "HK6000_1553";
$tmp = base64_encode(file_get_contents(WEBROOT . "/rest/barCodeGenerator.php?type=jpg&text=HK6000_1553&code=TYPE_CODE_128_B"));
echo '<img src="data:image/jpeg;base64, ' . $tmp . '" alt="$text"/><br/>';
?>
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Viivakoodigeneraattori</h1>
</div>

