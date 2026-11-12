import markdownit from 'markdown-it';
import hljs from 'highlight.js';
import { Alpine } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import completeMarkdown from '../completeMarkdown.js';

// When a turn ends, its stored message replaces the streaming one and picks the reveal up from here.
let unfinishedReveal = null;

export default function assistantMessage(message, labels, isStreaming = false) {
    const markdown = createMarkdown(labels.copy);

    return {
        message,
        lastEventType: null,
        showThinking: false,
        copied: false,
        revealedLength: 0,
        isRevealing: false,

        init() {
            loadHighlightTheme(this.$flux.dark);

            this.$watch('$flux.dark', (isDark) => loadHighlightTheme(isDark));

            if (isStreaming) {
                this.stopListening = this.$wire.$interceptMessage(({ onStream }) => {
                    onStream(({ json }) => this.receive(json.content));
                });
            } else if (this.continuesUnfinishedReveal()) {
                this.continueReveal();
            } else {
                this.paint(markdown.render(this.message.content));
            }
        },

        destroy() {
            this.stopListening?.();
        },

        receive(event) {
            switch (event.type) {
                case 'stream_start':
                    if (this.message.content.trim() !== '') {
                        this.message.content += '\n\n';
                    }
                    break;
                case 'text_delta':
                    this.message.content += event.delta;
                    break;
                case 'reasoning_delta':
                    this.message.thinking += event.delta;
                    break;
                case 'tool_call':
                    this.message.toolCalls.push({
                        id: event.tool_id,
                        name: event.tool_name,
                        arguments: event.arguments,
                    });
                    break;
                case 'tool_result':
                    this.message.toolCalls = this.message.toolCalls.map((toolCall) =>
                        toolCall.id === event.tool_id
                            ? { ...toolCall, result: event.result, failed: !event.successful }
                            : toolCall,
                    );
                    break;
            }

            this.lastEventType = event.type;

            this.reveal();
        },

        continuesUnfinishedReveal() {
            return (
                unfinishedReveal !== null &&
                performance.now() - unfinishedReveal.at < 1000 &&
                this.message.content.length > unfinishedReveal.text.length &&
                this.message.content.startsWith(unfinishedReveal.text)
            );
        },

        continueReveal() {
            this.paint(wrapWords(markdown.render(completeMarkdown(unfinishedReveal.text))));
            this.revealedLength = unfinishedReveal.text.length;

            requestAnimationFrame(() => {
                if (this.$el.isConnected) {
                    this.$refs.reply.dataset.reveal = '';
                    this.reveal();
                }
            });
        },

        reveal() {
            if (this.isRevealing) {
                return;
            }

            this.isRevealing = true;

            const revealMore = () => {
                const text = this.message.content;
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

                this.paint(wrapWords(markdown.render(isFinished ? revealed : completeMarkdown(revealed))));

                unfinishedReveal = { text: revealed, at: performance.now() };

                requestAnimationFrame(revealMore);
            };

            requestAnimationFrame(revealMore);
        },

        paint(html) {
            const reply = this.$refs.reply.cloneNode(false);

            reply.innerHTML = html;

            Alpine.morph(this.$refs.reply, reply);
        },

        async copy() {
            await navigator.clipboard.writeText(this.message.content);

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

        get thinkingHtml() {
            return markdown.render(this.message.thinking);
        },

        formatToolResult(result) {
            return typeof result === 'string' ? result : JSON.stringify(result);
        },

        isWriting() {
            return ['text_delta', 'text_end', 'stream_end'].includes(this.lastEventType);
        },

        status() {
            const runningTool = this.message.toolCalls.findLast((toolCall) => !('result' in toolCall));

            return runningTool ? labels.runningTool.replace(':tool', runningTool.name) : labels.thinking;
        },

        latestThought() {
            if (!['reasoning_start', 'reasoning_delta'].includes(this.lastEventType)) {
                return '';
            }

            return this.message.thinking
                .trim()
                .split('\n')
                .at(-1)
                .replace(/[*_`#>]/g, '')
                .trim();
        },
    };
}

function createMarkdown(copyLabel) {
    const markdown = markdownit({
        html: false,
        breaks: true,
        linkify: true,
        typographer: true,
        highlight: (code, language) => {
            if (language && hljs.getLanguage(language)) {
                try {
                    return hljs.highlight(code, { language }).value;
                } catch (error) {
                    console.warn('Highlight.js error:', error);
                }
            }

            return `<pre><code class="hljs">${markdown.utils.escapeHtml(code)}</code></pre>`;
        },
    });

    markdown.linkify.set({ fuzzyEmail: false });

    const renderFence = markdown.renderer.rules.fence;

    markdown.renderer.rules.fence = (tokens, index, options, env, self) => {
        const language = markdown.utils.escapeHtml(tokens[index].info.trim().split(/\s+/)[0] || 'text');

        return `<div class="code-block"><div class="code-block-header"><span>${language}</span><button type="button" data-copy-code>${markdown.utils.escapeHtml(copyLabel)}</button></div>${renderFence(tokens, index, options, env, self)}</div>`;
    };

    return markdown;
}

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
