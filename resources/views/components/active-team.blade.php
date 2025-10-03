@if ($team)
    <div class="fi-sidebar-nav-item">
        <a
            href="{{ route('filament.app.pages.team-select') }}"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium
                   rounded-lg border border-primary-200 dark:border-primary-800
                   bg-primary-50 dark:bg-primary-900/30
                   text-primary-700 dark:text-primary-200
                   hover:bg-primary-100 dark:hover:bg-primary-800
                   transition"
        >
            <x-heroicon-o-users class="w-5 h-5"/> {{-- same size as Filament nav icons --}}
            <span>Current: {{ $team }}</span>
        </a>
    </div>
@endif
