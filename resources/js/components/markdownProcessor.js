/**
 * streamedMarkdown – watches a Livewire `wire:stream` target and
 * converts its growing innerText into rendered, highlighted Markdown.
 *
 * Usage in Blade:
 *   <p wire:stream="answer" x-ref="raw" class="hidden"></p>
 *   <article wire:ignore x-ref="reply"></article>
 */
import markdownit from 'markdown-it';
import hljs from 'highlight.js';
import { Alpine } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import completeMarkdown from '../completeMarkdown.js';

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

let unfinishedReveal = null;

export default function markdownProcessor(labels, isStreaming = false) {
    return {
        md: null,
        html: '',
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
        revealedLength: 0,
        isRevealing: false,

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

            const renderFence = this.md.renderer.rules.fence;

            this.md.renderer.rules.fence = (tokens, index, options, env, self) => {
                const language = this.md.utils.escapeHtml(tokens[index].info.trim().split(/\s+/)[0] || 'text');
                const copyLabel = this.md.utils.escapeHtml(labels.copy);

                return `<div class="code-block"><div class="code-block-header"><span>${language}</span><button type="button" data-copy-code>${copyLabel}</button></div>${renderFence(tokens, index, options, env, self)}</div>`;
            };

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

                // Render thinking content if available
                if (this.streamData.thinking) {
                    this.thinkingHtml = this.md.render(this.streamData.thinking);
                }

                if (isStreaming) {
                    this.reveal();
                } else if (this.continuesUnfinishedReveal()) {
                    this.paint(wrapWords(this.md.render(completeMarkdown(unfinishedReveal.text))));
                    this.revealedLength = unfinishedReveal.text.length;

                    requestAnimationFrame(() => {
                        if (this.$el.isConnected) {
                            this.$refs.reply.dataset.reveal = '';
                            this.reveal();
                        }
                    });
                } else {
                    this.paint(this.md.render(this.streamData.text || ''));
                }
            }
        },

        continuesUnfinishedReveal() {
            return (
                unfinishedReveal !== null &&
                performance.now() - unfinishedReveal.at < 1000 &&
                this.streamData.text.length > unfinishedReveal.text.length &&
                this.streamData.text.startsWith(unfinishedReveal.text)
            );
        },

        reveal() {
            if (this.isRevealing) {
                return;
            }

            this.isRevealing = true;

            const revealMore = () => {
                const text = this.streamData.text;
                const remaining = text.length - this.revealedLength;

                if (remaining <= 0 || !this.$el.isConnected) {
                    this.isRevealing = false;

                    if (remaining <= 0) {
                        unfinishedReveal = null;
                    }

                    return;
                }

                this.revealedLength += matchMedia('(prefers-reduced-motion: reduce)').matches
                    ? remaining
                    : Math.ceil(remaining / 10);

                if (isFirstHalfOfSurrogatePair(text.charCodeAt(this.revealedLength - 1))) {
                    this.revealedLength++;
                }

                const revealed = text.slice(0, this.revealedLength);
                const isFinished = !isStreaming && revealed.length === text.length;

                this.paint(wrapWords(this.md.render(isFinished ? revealed : completeMarkdown(revealed))));

                unfinishedReveal = { text: revealed, at: performance.now() };

                requestAnimationFrame(revealMore);
            };

            requestAnimationFrame(revealMore);
        },

        paint(html) {
            const reply = this.$refs.reply.cloneNode(false);

            reply.innerHTML = html;

            Alpine.morph(this.$refs.reply, reply);

            this.html = html;
        },

        async copy() {
            await navigator.clipboard.writeText(this.streamData.text);

            this.copied = true;

            setTimeout(() => (this.copied = false), 2000);
        },

        async copyCode(event) {
            const button = event.target.closest('[data-copy-code]');

            if (!button) {
                return;
            }

            await navigator.clipboard.writeText(button.closest('.code-block').querySelector('code').innerText);

            button.textContent = labels.copied;

            setTimeout(() => (button.textContent = labels.copy), 2000);
        },

        formatToolResult(result) {
            return typeof result === 'string' ? result : JSON.stringify(result);
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

        isWriting() {
            return ['text_delta', 'text_end', 'stream_end'].includes(this.streamData.currentChunkType);
        },

        status() {
            const runningTool = this.streamData.toolCalls.findLast((toolCall) => !('result' in toolCall));

            return runningTool ? labels.runningTool.replace(':tool', runningTool.name) : labels.thinking;
        },

        latestThought() {
            if (!['reasoning_start', 'reasoning_delta'].includes(this.streamData.currentChunkType)) {
                return '';
            }

            return this.streamData.thinking
                .trim()
                .split('\n')
                .at(-1)
                .replace(/[*_`#>]/g, '')
                .trim();
        },
    };
}

function isFirstHalfOfSurrogatePair(charCode) {
    return charCode >= 0xd800 && charCode <= 0xdbff;
}

function wrapWords(html) {
    const template = document.createElement('template');

    template.innerHTML = html;

    const walker = document.createTreeWalker(template.content, NodeFilter.SHOW_TEXT);
    const textNodes = [];

    while (walker.nextNode()) {
        if (!walker.currentNode.parentElement?.closest('.code-block, pre')) {
            textNodes.push(walker.currentNode);
        }
    }

    textNodes.forEach((textNode) => {
        const words = document.createDocumentFragment();

        textNode.nodeValue.split(/(\s+)/).forEach((part) => {
            if (part.trim() === '') {
                words.append(part);

                return;
            }

            const word = document.createElement('span');

            word.className = 'stream-word';
            word.textContent = part;
            words.append(word);
        });

        textNode.replaceWith(words);
    });

    return template.innerHTML;
}
