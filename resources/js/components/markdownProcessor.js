/**
 * streamedMarkdown – watches a Livewire `wire:stream` target and
 * converts its growing innerText into rendered, highlighted Markdown.
 *
 * Usage in Blade:
 *   <p wire:stream="answer" x-ref="raw" class="hidden"></p>
 *   <article wire:ignore x-html="html"></article>
 */
import markdownit from 'markdown-it';
import hljs from 'highlight.js';

export default function markdownProcessor() {
    return {
        md: null,
        html: '', // rendered output for <article x-html="html">
        currentThemeLink: null, // track the current theme link element
        // Chunk type data
        streamData: {
            text: '',
            thinking: '',
            meta: '',
            toolCalls: [],
            toolResults: [],
            currentChunkType: 'text',
        },
        thinkingHtml: '',
        showThinking: false,

        init() {
            this.md = markdownit({
                html: false,
                breaks: true,
                linkify: true,
                typographer: true,
                highlight: (str, lang) => {
                    if (lang && hljs.getLanguage(lang)) {
                        try {
                            return hljs.highlight(str, { language: lang }).value;
                        } catch (err) {
                            console.warn('Highlight.js error:', err);
                        }
                    }
                    return `<pre><code class="hljs">${str}</code></pre>`;
                },
            });

            this.md.linkify.set({ fuzzyEmail: false });

            this.loadHighlightTheme();

            // Watch for dark mode changes and update theme accordingly
            this.$watch('$flux.dark', () => {
                this.loadHighlightTheme();
            });

            // first paint
            this.render();

            // re-render whenever Livewire mutates the wire:stream element
            new MutationObserver(() => this.render()).observe(this.$refs.raw, {
                childList: true,
                characterData: true,
                subtree: true,
            });
        },

        loadHighlightTheme() {
            // Remove existing theme if it exists
            if (this.currentThemeLink) {
                this.currentThemeLink.remove();
                this.currentThemeLink = null;
            }

            const isDark = this.$flux?.dark || false;

            const themeUrl = isDark ? '/css/highlight/github-dark.css' : '/css/highlight/github-light.css';

            // Create and append new theme link
            this.currentThemeLink = document.createElement('link');
            this.currentThemeLink.rel = 'stylesheet';
            this.currentThemeLink.href = themeUrl;
            this.currentThemeLink.setAttribute('data-highlight-theme', 'dynamic');

            document.head.appendChild(this.currentThemeLink);
        },

        render() {
            let content = this.$refs.raw.innerText.trim();

            // Skip empty content
            if (!content) {
                return;
            }

            const jsonData = JSON.parse(content);
            if (jsonData && typeof jsonData === 'object') {
                this.streamData = { ...this.streamData, ...jsonData };

                // Render main text content
                this.html = this.md.render(this.streamData.text || '');

                // Render thinking content if available
                if (this.streamData.thinking) {
                    this.thinkingHtml = this.md.render(this.streamData.thinking);
                }
            }
        },

        toggleThinking() {
            this.showThinking = !this.showThinking;
        },

        hasThinking() {
            return this.streamData.thinking && this.streamData.thinking.trim().length > 0;
        },

        hasToolCalls() {
            return this.streamData.toolCalls && this.streamData.toolCalls.length > 0;
        },

        hasToolResults() {
            return this.streamData.toolResults && this.streamData.toolResults.length > 0;
        },

        isCurrentlyThinking() {
            return this.streamData.currentChunkType === 'thinking_delta';
        },

        isCurrentlyUsingTools() {
            return (
                this.streamData.currentChunkType === 'tool_call' || this.streamData.currentChunkType === 'tool_result'
            );
        },
    };
}
