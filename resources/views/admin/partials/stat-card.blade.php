@php
    $colors = [
        'cyan' => 'from-cyan-500 to-blue-600',
        'emerald' => 'from-emerald-500 to-teal-600',
        'amber' => 'from-amber-500 to-orange-600',
        'violet' => 'from-violet-500 to-purple-600',
        'rose' => 'from-rose-500 to-pink-600',
        'indigo' => 'from-indigo-500 to-blue-600',
    ];
    $gradient = $colors[$color ?? 'cyan'] ?? $colors['cyan'];
@endphp
<div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-sm hover:shadow-md transition-shadow duration-300">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $value }}</p>
            @if(isset($change))
                <p class="text-xs mt-1 {{ str_starts_with($change, '+') ? 'text-emerald-600' : 'text-slate-500' }}">{{ $change }}</p>
            @endif
        </div>
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center shadow-lg">
            {!! $icon !!}
        </div>
    </div>
</div>
