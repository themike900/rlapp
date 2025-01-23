<div>
    <label for="{{ $name }}" class="block font-medium text-gray-700">{{ ucfirst($name) }}
    <select
        id="{{ $name }}"
        name="{{ $name }}"
        class="mt-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        <option value="">Bitte wählen</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    </label>
</div>
