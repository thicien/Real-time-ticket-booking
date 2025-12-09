<!-- HERO SECTION -->
<section class="bg-blue-600 py-20">
    <div class="max-w-6xl mx-auto px-6 text-center text-white">
        <h1 class="text-4xl md:text-5xl font-bold leading-tight">
            Book Your Bus Tickets Easily & Quickly
        </h1>
        <p class="mt-4 text-lg opacity-90">
            Find the best bus routes, compare prices and reserve your seat instantly.
        </p>
    </div>
</section>

<!-- SEARCH BOX -->
<section class="max-w-4xl mx-auto -mt-10">
    <div class="bg-white shadow-lg p-8 rounded-xl">

        <form action="search.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="block mb-1 font-semibold">From</label>
                <input type="text" name="from" required
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 font-semibold">To</label>
                <input type="text" name="to" required
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block mb-1 font-semibold">Date</label>
                <input type="date" name="date" id="travelDate" required
                    class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="flex items-end">
                <button 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition">
                    Search Buses
                </button>
            </div>

        </form>

    </div>
</section>

<!-- FEATURED BUSES -->
<section class="max-w-6xl mx-auto mt-16 px-6">
    <h2 class="text-2xl font-bold mb-6">Popular Routes</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Card -->
        <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-xl transition">
            <h3 class="text-xl font-bold text-gray-800">Kigali → Huye</h3>
            <p class="text-gray-600 mt-2">Daily trips | 2h 20min</p>
            <p class="mt-3 font-semibold text-blue-600">From RWF 2,500</p>
        </div>

        <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-xl transition">
            <h3 class="text-xl font-bold text-gray-800">Kigali → Musanze</h3>
            <p class="text-gray-600 mt-2">Every 30 minutes | 2h</p>
            <p class="mt-3 font-semibold text-blue-600">From RWF 3,000</p>
        </div>

        <div class="bg-white shadow-md rounded-xl p-6 hover:shadow-xl transition">
            <h3 class="text-xl font-bold text-gray-800">Kigali → Rusizi</h3>
            <p class="text-gray-600 mt-2">Daily trips | 5h 30min</p>
            <p class="mt-3 font-semibold text-blue-600">From RWF 6,000</p>
        </div>

    </div>
</section>

<!-- SMALL JS VALIDATION -->
<script>
document.getElementById('travelDate').min = new Date().toISOString().split("T")[0];
</script>
