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

function loadHighlightTheme(isDark) {
    let link = document.head.querySelector('link[data-highlight-theme]');

    if (!link) {
        link = document.createElement('link');
        link.rel = 'stylesheet';
        link.setAttribute('data-highlight-theme', 'dynamic');
        document.head.appendChild(link);
    }

    const themeUrl = isDark ? '/css/highlight/github-dark.css' : '/css/highlight/github-light.css';

    if (link.getAttribute('href') !== themeUrl) {
        link.href = themeUrl;
    }
}

export default function markdownProcessor() {
    return {
        md: null,
        html: '', // rendered output for <article x-html="html">
        // Chunk type data
        streamData: {
            text: '',
            thinking: '',
            meta: '',
            toolCalls: [],
            currentChunkType: 'text',
        },
        thinkingHtml: '',
        showThinking: false,
        copied: false,

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
                    return `<pre><code class="hljs">${this.md.utils.escapeHtml(str)}</code></pre>`;
                },
            });

            this.md.linkify.set({ fuzzyEmail: false });

            loadHighlightTheme(this.$flux.dark);

            this.$watch('$flux.dark', (isDark) => loadHighlightTheme(isDark));

            // first paint
            this.render();

            // re-render whenever Livewire mutates the wire:stream element
            new MutationObserver(() => this.render()).observe(this.$refs.raw, {
                childList: true,
                characterData: true,
                subtree: true,
            });
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

        async copy() {
            await navigator.clipboard.writeText(this.streamData.text);

            this.copied = true;

            setTimeout(() => (this.copied = false), 2000);
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

        isWaiting() {
            return this.html.length === 0 && !this.hasThinking() && !this.hasToolCalls();
        },

        isCurrentlyThinking() {
            return this.streamData.currentChunkType === 'reasoning_delta';
        },

        isCurrentlyUsingTools() {
            return (
                this.streamData.currentChunkType === 'tool_call' || this.streamData.currentChunkType === 'tool_result'
            );
        },
    };
}
