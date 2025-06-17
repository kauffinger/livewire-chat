import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import streamedMarkdown from './components/streamedMarkdown.js';

Alpine.data('streamedMarkdown', streamedMarkdown);

Livewire.start();
