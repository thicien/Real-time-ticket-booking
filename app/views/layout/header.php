<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bus Ticket Booking System</title>

    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>

</head>

<body class="bg-gray-50">

<!-- NAVBAR -->
<nav class="bg-white shadow-sm fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        <!-- Logo -->
        <a href="index.php" class="text-2xl font-bold text-blue-600">
            BusBooking
        </a>

        <!-- Menu -->
        <div class="hidden md:flex space-x-8 text-gray-700 font-medium">
            <a href="index.php" class="hover:text-blue-600">Home</a>
            <a href="#" class="hover:text-blue-600">Routes</a>
            <a href="#" class="hover:text-blue-600">Contact</a>
        </div>

        <!-- Login Button -->
        <a href="login.php" 
           class="px-5 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
           Login
        </a>
    </div>
</nav>

<!-- Space for navbar -->
<div class="pt-20"></div>
