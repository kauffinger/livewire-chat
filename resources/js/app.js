import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import markdownProcessor from './components/markdownProcessor.js';

Alpine.data('markdownProcessor', markdownProcessor);

Livewire.start();
