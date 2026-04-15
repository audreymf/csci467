<?php

function sendToExternalSystem($quoteId, $finalAmount) {
    // Fake external API response for testing
    return [
        'processing_date' => date('Y-m-d'),
        'commission_rate' => 10
    ];
}
