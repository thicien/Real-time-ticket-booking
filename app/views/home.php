<?php include __DIR__ . '/layout/header.php'; ?>

<!-- Hero Section -->
<section class="bg-blue-600 text-white py-24 px-6">
    <div class="max-w-5xl mx-auto text-center">
        <h1 class="text-4xl font-bold mb-4">Book Kigali Bus Tickets Online</h1>
        <p class="text-lg opacity-90 mb-6">Fast, reliable and paperless bus booking for Kigali city routes</p>

        <!-- Search Form -->
        <form action="/search" method="GET" class="bg-white p-6 rounded-lg shadow-md flex flex-col md:flex-row gap-4 max-w-3xl mx-auto">
            <input type="text" name="from" placeholder="From" class="border rounded p-3 w-full">
            <input type="text" name="to" placeholder="To" class="border rounded p-3 w-full">
            <input type="date" name="date" class="border rounded p-3 w-full">
            <button class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 w-full md:w-auto">Search</button>
        </form>
    </div>
</section>

<!-- Featured Buses -->
<section class="py-16 px-6">
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold mb-6">Popular Kigali Routes</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- Sample Card -->
            <div class="border rounded-xl shadow hover:shadow-lg p-5">
                <img src="/assets/images/bus1.jpg" class="rounded-lg mb-4" alt="Bus">
                <h3 class="text-xl font-semibold">Kimironko → Nyabugogo</h3>
                <p class="text-gray-600 text-sm mb-3">Every 30 minutes • RWF 500</p>
                <a href="/search?from=Kimironko&to=Nyabugogo" 
                   class="text-blue-600 font-semibold hover:underline">Book Now →</a>
            </div>

            <div class="border rounded-xl shadow hover:shadow-lg p-5">
                <img src="/assets/images/bus2.jpg" class="rounded-lg mb-4" alt="Bus">
                <h3 class="text-xl font-semibold">Kicukiro → Downtown</h3>
                <p class="text-gray-600 text-sm mb-3">Every 20 minutes • RWF 400</p>
                <a href="/search?from=Kicukiro&to=Downtown" 
                   class="text-blue-600 font-semibold hover:underline">Book Now →</a>
            </div>

            <div class="border rounded-xl shadow hover:shadow-lg p-5">
                <img src="/assets/images/bus3.jpg" class="rounded-lg mb-4" alt="Bus">
                <h3 class="text-xl font-semibold">Remera → Nyamirambo</h3>
                <p class="text-gray-600 text-sm mb-3">Every 45 minutes • RWF 600</p>
                <a href="/search?from=Remera&to=Nyamirambo" 
                   class="text-blue-600 font-semibold hover:underline">Book Now →</a>
            </div>

        </div>
    </div>
</section>

<?php include __DIR__ . '/layout/footer.php'; ?>
