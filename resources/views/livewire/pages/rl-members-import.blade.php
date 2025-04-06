    <div>

        <!-- Header -->
        <div class="bg-white p-4 shadow rounded-lg">
            <p class="text-2xl font-bold mb-0">Mitglieder-Import von MeinVerein</p>
        </div>

        <div class="flex py-4 space-x-2">
            <main class="bg-white p-4 rounded-lg shadow-lg flex-1">

                @if(session('success'))
                    <div>{{ session('success') }}</div>
                 @endif

                <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input id="file-upload" type="file" name="file" class="cursor-pointer space-x-2 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <button type="submit" class="px-4 py-2 border text-white bg-blue-600 rounded-md">Import Excel</button>
                </form>
            </main>
        </div>


    </div>
