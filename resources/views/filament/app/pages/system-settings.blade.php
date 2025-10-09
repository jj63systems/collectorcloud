<x-filament-panels::page>
    <div class="space-y-6">
        {{ $this->content }}
    </div>
</x-filament-panels::page>


<script>
    window.addEventListener('confirm-unlock', (event) => {
        const {field, message} = event.detail;
        const input = document.querySelector(`[name="${field}"]`);

        if (!input) {
            alert('Could not find input field: ' + field);
            return;
        }

        if (confirm(message)) {
            input.removeAttribute('disabled');
            input.focus();
        }
    });
</script>
