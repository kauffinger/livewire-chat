import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import markdownProcessor from './components/markdownProcessor.js';

// After a new build, wire:navigate runs the new bundle next to the one an open tab already started.
if (window.livewireHasStarted) {
    window.location.reload();
} else {
    window.livewireHasStarted = true;

    Alpine.data('markdownProcessor', markdownProcessor);

    Livewire.start();
}
