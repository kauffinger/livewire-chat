/**
 * streamedMarkdown – watches a Livewire `wire:stream` target and
 * converts its growing innerText into rendered, highlighted Markdown.
 *
 * Usage in Blade:
 *   <p wire:stream="answer" x-ref="raw" class="hidden"></p>
 *   <article wire:ignore x-html="html"></article>
 */
import markdownit from 'markdown-it'
import hljs      from 'highlight.js'
import 'highlight.js/styles/github.css'

export default function streamedMarkdown () {
    return {
        md: null,
        html: '',          // rendered output for <article x-html="html">

        init () {
            // Configure markdown-it to match Vue component options
            this.md = markdownit({
                html: false,  // Disable HTML to prevent <?php interpretation issues
                breaks: true,
                linkify: true,
                typographer: true,
                highlight: (str, lang) => {
                    
                    if (lang && hljs.getLanguage(lang)) {
                        try {
                            return hljs.highlight(str, { language: lang }).value
                        } catch (err) {
                            console.warn('Highlight.js error:', err)
                        }
                    }
                    return `<pre><code class="hljs">${str}</code></pre>`
                }
            })

            // Configure linkify to disable fuzzy email detection (matching Vue component)
            this.md.linkify.set({ fuzzyEmail: false })

            // first paint
            this.render()

            // re-render whenever Livewire mutates the wire:stream element
            new MutationObserver(() => this.render())
                .observe(this.$refs.raw, {
                    childList: true,
                    characterData: true,
                    subtree: true
                })
        },

        render () {
            let content = this.$refs.raw.innerText
            
            this.html = this.md.render(content)
        },

    }
}
