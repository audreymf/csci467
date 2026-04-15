<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Plant Repair Services - HQ</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .nav { background: #333; padding: 10px 20px; }
        .nav a { color: #fff; text-decoration: none; margin-right: 20px; font-size: 14px; }
        .nav a:hover { text-decoration: underline; }
        .nav .brand { color: #fff; font-weight: bold; margin-right: 30px; }
        .content { padding: 20px; max-width: 1000px; margin: 0 auto; }
        h2 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: #fff; margin-top: 10px; }
        th { background: #333; color: #fff; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        tr:hover td { background: #f9f9f9; }
        a.btn { padding: 5px 12px; color: #fff; text-decoration: none; border-radius: 3px; font-size: 13px; }
        .btn-blue { background: #0066cc; }
        .btn-green { background: #28a745; }
        .flash { padding: 10px 15px; border-radius: 3px; margin-bottom: 15px; }
        .flash.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .flash.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .form-box { background: #fff; padding: 20px; max-width: 500px; border: 1px solid #ddd; border-radius: 4px; }
        label { display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px; }
        select, input[type="number"], input[type="text"], textarea {
            width: 100%; padding: 8px; margin-bottom: 14px; border: 1px solid #ccc;
            border-radius: 3px; font-size: 14px; box-sizing: border-box;
        }
        button[type="submit"] { background: #333; color: #fff; border: none; padding: 9px 24px; border-radius: 3px; font-size: 14px; cursor: pointer; }
        button[type="submit"]:hover { background: #555; }
        .info-box { background: #e8f0fe; border-left: 4px solid #0066cc; padding: 10px 15px; margin-bottom: 15px; font-size: 14px; }
        .tabs { margin-bottom: 15px; }
        .tabs a { display: inline-block; padding: 6px 16px; margin-right: 5px; background: #ddd; color: #333; text-decoration: none; border-radius: 3px; font-size: 13px; }
        .tabs a.active, .tabs a:hover { background: #333; color: #fff; }
    </style>
</head>
<body>
 
<div class="nav">
    <span class="brand">Plant Repair Services - HQ</span>
    <a href="review_quotes.php">Quote Review</a>
    <a href="sanction_list.php">Sanction Quotes</a>
    <a href="process_orders.php">Process Orders</a>
</div>
 
<div class="content">
 