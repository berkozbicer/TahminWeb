@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <h1 class="text-2xl font-bold mb-4">İletişim</h1>

    <p class="text-gray-600 mb-6">Her türlü soru, destek veya iş birliği talepleriniz için aşağıdaki formu doldurabilirsiniz.</p>

    <form method="post" action="{{ route('contact.submit') }}" class="space-y-4 bg-white p-6 rounded-lg shadow">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700">İsim</label>
            <input type="text" name="name" value="{{ old('name') }}" required
+                   class="mt-1 block w-full border-gray-200 rounded-md shadow-sm" />
            @error('name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">E-posta</label>
            <input type="email" name="email" value="{{ old('email') }}" required
+                   class="mt-1 block w-full border-gray-200 rounded-md shadow-sm" />
            @error('email')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Mesaj</label>
            <textarea name="message" rows="6" required class="mt-1 block w-full border-gray-200 rounded-md shadow-sm">{{ old('message') }}</textarea>
            @error('message')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
        </div>

        <div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded font-semibold">Gönder</button>
        </div>
    </form>
</div>
@endsection
