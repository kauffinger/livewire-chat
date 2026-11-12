import markdownit from 'markdown-it';
import hljs from 'highlight.js';
import { Alpine } from '../../../vendor/livewire/livewire/dist/livewire.esm';
import completeMarkdown from '../completeMarkdown.js';

const markdown = createMarkdown();

// When a turn ends, its stored message replaces the streaming one and picks the reveal up from here.
let unfinishedReveal = null;

export default function assistantMessage(message, labels, isStreaming = false) {
    const render = (text) => markdown.render(text, { copyLabel: labels.copy });

    return {
        message,
        activity: null,
        showReasoning: false,
        copied: false,
        revealedLength: 0,
        isRevealing: false,

        init() {
            if (isStreaming) {
                this.stopListening = this.$wire.$interceptMessage(({ onStream, onFinish }) => {
                    // Earlier messages load in a request of their own, which can finish while a reply streams.
                    let hasStreamed = false;

                    onStream(({ json }) => {
                        hasStreamed = true;

                        this.receive(json.content);
                    });

                    onFinish(() => {
                        if (hasStreamed) {
                            this.clear();
                        }
                    });
                });
            } else if (this.continuesUnfinishedReveal()) {
                this.continueReveal();
            } else {
                this.paint(render(this.message.content));
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
                    return;
                case 'text_delta':
                    this.message.content += event.delta;
                    break;
                case 'reasoning_delta':
                    this.message.reasoning += event.delta;
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
                default:
                    return;
            }

            this.activity = event.type;

            this.reveal();
        },

        clear() {
            Object.assign(this.message, { content: '', reasoning: '', toolCalls: [] });
            Object.assign(this, { activity: null, showReasoning: false, revealedLength: 0 });

            this.paint('');
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
            this.paint(wrapWords(render(completeMarkdown(unfinishedReveal.text))));
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

                this.paint(wrapWords(render(isFinished ? revealed : completeMarkdown(revealed))));

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

        get reasoningHtml() {
            return render(this.message.reasoning);
        },

        formatToolResult(result) {
            return typeof result === 'string' ? result : JSON.stringify(result);
        },

        isWriting() {
            return this.activity === 'text_delta';
        },

        status() {
            const runningTool = this.message.toolCalls.findLast((toolCall) => !('result' in toolCall));

            return runningTool ? labels.runningTool.replace(':tool', runningTool.name) : labels.thinking;
        },

        latestThought() {
            if (this.activity !== 'reasoning_delta') {
                return '';
            }

            return this.message.reasoning
                .trim()
                .split('\n')
                .at(-1)
                .replace(/[*_`#>]/g, '')
                .trim();
        },
    };
}

function createMarkdown() {
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

        return `<div class="code-block"><div class="code-block-header"><span>${language}</span><button type="button" data-copy-code>${markdown.utils.escapeHtml(env.copyLabel)}</button></div>${renderFence(tokens, index, options, env, self)}</div>`;
    };

    return markdown;
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
