<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

<div class="hidden p-6 bg-white shadow-md rounded-md">
    <h1 class="text-2xl font-bold text-center mb-4">Rechnung</h1>

    <p class="text-gray-700"><strong>Name:</strong> Max Mustermann</p>
    <p class="text-gray-700"><strong>Datum:</strong> {{ now()->format('d.m.Y') }}</p>

    <table class="w-full border-collapse border border-gray-300 mt-4">
        <thead>
        <tr class="bg-gray-100">
            <th class="border border-gray-300 px-4 py-2 text-left">Artikel</th>
            <th class="border border-gray-300 px-4 py-2 text-center">Menge</th>
            <th class="border border-gray-300 px-4 py-2 text-right">Preis</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class="border border-gray-300 px-4 py-2">Produkt A</td>
            <td class="border border-gray-300 px-4 py-2 text-center">2</td>
            <td class="border border-gray-300 px-4 py-2 text-right">20,00 €</td>
        </tr>
        <tr>
            <td class="border border-gray-300 px-4 py-2">Produkt B</td>
            <td class="border border-gray-300 px-4 py-2 text-center">1</td>
            <td class="border border-gray-300 px-4 py-2 text-right">10,00 €</td>
        </tr>
        </tbody>
    </table>

    <p class="text-right font-bold mt-4">Gesamt: 30,00 €</p>

    <footer class="text-center text-gray-500 text-sm mt-6">
        Danke fuer Ihren Einkauf!
    </footer>
</div>
