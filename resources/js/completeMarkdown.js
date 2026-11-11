/**
 * completeMarkdown – closes or hides the markdown a reply is still in the
 * middle of writing, so half of a bold word, link or table never shows as
 * raw syntax while the reply streams.
 */
export default function completeMarkdown(text) {
    if (isInsideCodeFence(text)) {
        return text;
    }

    const lastBlankLine = text.lastIndexOf('\n\n');
    const blockStart = lastBlankLine === -1 ? 0 : lastBlankLine + 2;

    return (
        text.slice(0, blockStart) +
        completeTable(dropDanglingMarker(closeInlineMarkup(hidePartialLink(text.slice(blockStart)))))
    );
}

function isInsideCodeFence(text) {
    return (text.match(/^ {0,3}(```|~~~)/gm) || []).length % 2 === 1;
}

function hidePartialLink(block) {
    return block.replace(/!?\[([^\]\n]*)\](\([^)\n]*)?$/, '$1').replace(/!?\[([^\]\n]*)$/, '$1');
}

function closeInlineMarkup(block) {
    block = closeMarker(block, '`', count(block, /`/g));

    const prose = block.replace(/`[^`\n]*`/g, '');

    block = closeMarker(block, '**', count(prose, /\*\*/g));
    block = closeMarker(block, '~~', count(prose, /~~/g));

    return closeMarker(block, '*', count(prose.replace(/\*\*/g, ''), /(?<=\S)\*|\*(?=\S)/g));
}

function closeMarker(block, marker, occurrences) {
    if (occurrences % 2 === 0) {
        return block;
    }

    return block.endsWith(marker) ? block.slice(0, -marker.length) : block + marker;
}

function count(text, pattern) {
    return (text.match(pattern) || []).length;
}

function dropDanglingMarker(block) {
    return block.replace(/(^|\n)[ \t]*([-=]+|[*+]|\d+[.)]|#{1,6}|>|\||`+|~+)[ \t]*$/, '');
}

function completeTable(block) {
    const [header, , ...rows] = block.split('\n');

    if (!header.trimStart().startsWith('|') || rows.length > 0) {
        return block;
    }

    const columns = header
        .trim()
        .replace(/^\||\|$/g, '')
        .split('|').length;

    return `${header.trimEnd().endsWith('|') ? header : `${header} |`}\n|${' --- |'.repeat(columns)}`;
}
