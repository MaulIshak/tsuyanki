
/**
 * Parse text for Anki-style media tags.
 * Supports:
 * - [sound:filename.mp3]
 * - <img src="filename.jpg">
 */
export const parseMedia = (text) => {
    const media = [];
    if (!text) return media;

    // Audio: [sound:filename]
    const audioRegex = /\[sound:(.*?)\]/g;
    let match;
    while ((match = audioRegex.exec(text)) !== null) {
        media.push({
            type: 'audio',
            src: match[1],
            tag: match[0],
            index: match.index
        });
    }

    // Images: <img src="filename">
    const imgRegex = /<img[^>]+src=["'](.*?)["'][^>]*>/g;
    while ((match = imgRegex.exec(text)) !== null) {
        media.push({
            type: 'image',
            src: match[1],
            tag: match[0],
            index: match.index
        });
    }

    return media;
}

export const deserializeContent = (text) => {
    if (!text) return '';
    // Convert [sound:src] to <audio src="resolvedUrl(src)"></audio>
    // But wait, the src in [sound:src] is often a filename. 
    // We need to resolve it to full URL for the editor to play it.
    // AND we need to store the original filename to convert back? 
    // Tiptap AudioNode stores 'src'.

    let html = text;

    // Audio [sound:...]
    html = html.replace(/\[sound:(.*?)\]/g, (match, src) => {
        const url = resolveMediaUrl(src);
        // We store original src in data-original-src to convert back easily
        return `<audio src="${url}" data-original-src="${src}" controls></audio>`;
    });

    // Images <img src="...">
    // Anki images often are <img src="filename">.
    // Browser needs full URL.
    html = html.replace(/<img[^>]+src=["'](.*?)["'][^>]*>/g, (match, src) => {
        const url = resolveMediaUrl(src);
        // If it's already a full URL, resolveMediaUrl handles it (mostly).
        return `<img src="${url}" data-original-src="${src}">`;
    });

    return html;
}

export const serializeContent = (html) => {
    if (!html) return '';

    // Helper to strip storage URL
    const baseUrl = import.meta.env.VITE_API_BASE_URL ? new URL(import.meta.env.VITE_API_BASE_URL).origin : '';
    const storageUrl = `${baseUrl}/storage/`; // e.g. http://127.0.0.1:8000/storage/

    const div = document.createElement('div');
    div.innerHTML = html;

    const audios = div.querySelectorAll('audio');
    audios.forEach(audio => {
        const originalSrc = audio.getAttribute('data-original-src');
        let src = originalSrc || audio.getAttribute('src');

        // Strip absolute storage URL to make it relative/portable
        if (src && src.startsWith(storageUrl)) {
            src = src.replace(storageUrl, '');
            // If the result still has 'media/' prefix, keep it? 
            // Anki standard is just filename.
            // But our system seems to use 'media/filename' or just 'filename' depending on resolve.
            // resolveMediaUrl adds 'media/' if no slash.
            // If we strip 'storage/media/foo.mp3' -> 'media/foo.mp3'.
            // resolveMediaUrl('media/foo.mp3') -> 'storage/media/foo.mp3'. Correct.
        }

        const textNode = document.createTextNode(`[sound:${src}]`);
        audio.parentNode.replaceChild(textNode, audio);
    });

    const imgs = div.querySelectorAll('img');
    imgs.forEach(img => {
        const originalSrc = img.getAttribute('data-original-src');
        let src = originalSrc || img.getAttribute('src');

        if (src && src.startsWith(storageUrl)) {
            src = src.replace(storageUrl, '');
        }

        if (originalSrc) {
            // If data-original-src existed, use it (likely the clean filename)
            img.setAttribute('src', originalSrc);
            img.removeAttribute('data-original-src');
        } else {
            // Reconstruct tag with cleaned src
            // We can't easily replace the tag attribute in string while keeping others without parsing.
            // But here we are operating on DOM.
            // For imgs, we need to convert to <img src="filename"> string for Anki?
            // Or just kept as <img>?
            // Anki uses <img src="filename"> text in the field.
            // So we should update the src attribute of the node.

            img.setAttribute('src', src);
            img.removeAttribute('data-original-src');
        }
    });

    return div.innerHTML;
}

export const resolveMediaUrl = (src) => {
    if (src.startsWith('http')) return src;

    // Check if it's a relative path from storage
    // If it was uploaded via MediaController, it might be "media/hash.ext"
    // If it was imported from Anki, it might be "filename.ext" (flat in media folder)

    // We assume the base media URL is /storage/
    // If src contains '/', assume it's like 'media/file.ext'
    // If not, assume it's in 'media/' (Anki style)

    const baseUrl = import.meta.env.VITE_API_BASE_URL ? new URL(import.meta.env.VITE_API_BASE_URL).origin : '';
    const storageUrl = `${baseUrl}/storage`;

    if (src.includes('/')) {
        return `${storageUrl}/${src}`;
    } else {
        return `${storageUrl}/media/${src}`;
    }
}
