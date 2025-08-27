<x-filament-panels::page>
    {{-- Page content --}}

    <h2> hello</h2>


    {{ $this->form }}


</x-filament-panels::page>


<script>
    document.addEventListener('run-analysis', () => {
        Livewire.dispatch('runAnalysis');
    });
</script>

