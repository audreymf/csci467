<?php
function sendToExternalSystem($quoteId, $amount) {
    return [
        'processing_date' => date('Y-m-d'),
        'commission_rate' => 5.0
    ];
}
?>
