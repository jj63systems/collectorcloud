<x-filament-panels::page>
    {{-- Page content --}}

    {{ $this->form }}


</x-filament-panels::page>


<script>
    document.addEventListener('run-analysis', () => {
        Livewire.dispatch('runAnalysis');
    });
</script>

