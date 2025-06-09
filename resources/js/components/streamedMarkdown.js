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

export default function streamedMarkdown () {
    return {
        md: null,
        html: '',          // rendered output for <article x-html="html">
        currentThemeLink: null, // track the current theme link element

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

            // Load initial theme based on current dark mode state
            this.loadHighlightTheme()

            // Watch for dark mode changes and update theme accordingly
            this.$watch('$flux.dark', () => {
                this.loadHighlightTheme()
            })

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

        loadHighlightTheme() {
            // Remove existing theme if it exists
            if (this.currentThemeLink) {
                this.currentThemeLink.remove()
                this.currentThemeLink = null
            }

            // Determine which theme to use based on dark mode
            const isDark = this.$flux?.dark || false
            
            // Use local CSS files from public directory
            const themeUrl = isDark 
                ? '/css/highlight/github-dark.css'
                : '/css/highlight/github-light.css'

            console.log('Loading highlight theme:', { isDark, themeUrl })

            // Create and append new theme link
            this.currentThemeLink = document.createElement('link')
            this.currentThemeLink.rel = 'stylesheet'
            this.currentThemeLink.href = themeUrl
            this.currentThemeLink.setAttribute('data-highlight-theme', 'dynamic')
            
            document.head.appendChild(this.currentThemeLink)
        },

        render () {
            let content = this.$refs.raw.innerText
            
            this.html = this.md.render(content)
        },

    }
}
