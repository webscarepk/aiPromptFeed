@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-gray-800 border-gray-700 text-gray-100 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm placeholder-gray-500']) }}>
