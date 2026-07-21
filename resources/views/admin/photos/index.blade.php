@extends('layouts.admin')

@section('title', 'Job Photos')
@section('page-title', 'Job Photos')
@section('page-subtitle', 'Gallery of before/after job photos')

@section('content')
<div class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-4">
        @forelse($photos as $photo)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden hover:shadow-md transition group">
            <div class="relative aspect-square">
                <img src="{{ asset('storage/'.$photo->file_path) }}" alt="{{ $photo->type }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition">
                    <div class="absolute bottom-0 left-0 right-0 p-3">
                        @if($photo->booking)
                        <a href="{{ route('admin.bookings.show', $photo->booking) }}" class="text-xs text-white font-medium hover:underline block truncate">
                            {{ $photo->booking->booking_number }}
                        </a>
                        @endif
                        <p class="text-xs text-slate-300 truncate">{{ $photo->provider?->name }}</p>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 flex items-center justify-between bg-slate-50">
                <span class="text-xs font-medium text-slate-600 capitalize">{{ $photo->type }}</span>
                <span class="text-xs text-slate-400">{{ $photo->uploaded_at?->format('d M') ?? $photo->created_at->format('d M') }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl border border-slate-200/80 shadow-sm p-12 text-center text-slate-500">
            No photos uploaded yet
        </div>
        @endforelse
    </div>

    @if($photos->hasPages())
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm px-6 py-4">{{ $photos->links() }}</div>
    @endif
</div>
@endsection
