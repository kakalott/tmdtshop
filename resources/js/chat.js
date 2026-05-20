const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrf && window.axios) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrf;
}

const panel = document.getElementById('chat-panel');
const toggle = document.getElementById('chat-toggle');
const closeBtn = document.getElementById('chat-close');
const form = document.getElementById('chat-form');
const input = document.getElementById('chat-input');
const messagesEl = document.getElementById('chat-messages');
const sendBtn = document.getElementById('chat-send');

let historyLoaded = false;
let sending = false;

function escapeHtml(text) {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function escapeAttr(text) {
    return escapeHtml(text).replace(/'/g, '&#39;');
}

function isSafeUrl(url) {
    if (!url || typeof url !== 'string') {
        return false;
    }
    if (url.startsWith('/')) {
        return /^\/[a-zA-Z0-9/_\-.?=&%#]*$/.test(url);
    }
    try {
        const parsed = new URL(url);
        return parsed.protocol === 'http:' || parsed.protocol === 'https:';
    } catch {
        return false;
    }
}

function isSameLocalHost(hostname) {
    return hostname === 'localhost'
        || hostname === '127.0.0.1'
        || hostname === '::1'
        || hostname === window.location.hostname;
}

function normalizeUrl(url) {
    try {
        const parsed = new URL(url, window.location.origin);
        if (isSameLocalHost(parsed.hostname) || parsed.origin === window.location.origin) {
            return new URL(parsed.pathname + parsed.search + parsed.hash, window.location.origin).href;
        }
        return parsed.href;
    } catch {
        return url;
    }
}

function resolveUrl(url) {
    if (url.startsWith('/')) {
        return new URL(url, window.location.origin).href;
    }
    return normalizeUrl(url);
}

function formatAssistantContent(content) {
    let text = escapeHtml(content);

    // In đậm **text**
    text = text.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

    text = text.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, (_, alt, url) => {
        const resolved = normalizeUrl(url.trim());
        if (!isSafeUrl(url.trim()) && !isSafeUrl(resolved)) {
            return escapeHtml(`![${alt}](${url})`);
        }
        const src = escapeAttr(resolved);
        const altText = escapeAttr(alt || 'Sản phẩm');
        return `<img src="${src}" alt="${altText}" class="chat-msg__img" loading="lazy">`;
    });

    text = text.replace(/\[([^\]]+)\]\(([^)]+)\)/g, (_, label, url) => {
        let raw = url.trim();
        const productMatch = raw.match(/(\/product\/\d+)/);
        if (productMatch) {
            raw = productMatch[1];
        } else {
            const absoluteProduct = raw.match(/https?:\/\/[^)\s]+\/product\/\d+/);
            if (absoluteProduct) {
                try {
                    raw = new URL(absoluteProduct[0]).pathname;
                } catch {
                    raw = absoluteProduct[0];
                }
            }
        }
        const resolved = resolveUrl(raw);
        if (!isSafeUrl(raw) && !isSafeUrl(resolved)) {
            return escapeHtml(`[${label}](${url})`);
        }
        const href = escapeAttr(resolved);
        const linkLabel = escapeHtml(label);
        return `<a href="${href}" class="chat-msg__link" target="_blank" rel="noopener">${linkLabel}</a>`;
    });

    return text.replace(/\n/g, '<br>');
}

function appendMessage(role, content) {
    const div = document.createElement('div');
    div.className = `chat-msg chat-msg--${role === 'user' ? 'user' : 'assistant'}`;

    if (role === 'assistant') {
        div.innerHTML = formatAssistantContent(content);
    } else {
        div.textContent = content;
    }

    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function setTyping(show) {
    const existing = document.getElementById('chat-typing');
    if (!show) {
        existing?.remove();
        return;
    }
    if (existing) return;
    const div = document.createElement('div');
    div.id = 'chat-typing';
    div.className = 'chat-msg chat-msg--typing';
    div.textContent = 'Đang trả lời...';
    messagesEl.appendChild(div);
    messagesEl.scrollTop = messagesEl.scrollHeight;
}

function openPanel() {
    panel.hidden = false;
    toggle.setAttribute('aria-expanded', 'true');
    if (!historyLoaded) {
        loadHistory();
    }
    input.focus();
}

function closePanel() {
    panel.hidden = true;
    toggle.setAttribute('aria-expanded', 'false');
}

async function loadHistory() {
    try {
        const { data } = await window.axios.get('/chat/history');
        const list = data.messages || [];
        if (list.length === 0) {
            appendMessage(
                'assistant',
                'Xin chào! Tôi có thể giúp bạn tra sản phẩm, giá, tồn kho và đơn hàng (nếu đã đăng nhập).'
            );
        } else {
            list.forEach((m) => appendMessage(m.role, m.content));
        }
        historyLoaded = true;
    } catch {
        appendMessage('assistant', 'Không tải được lịch sử chat. Bạn vẫn có thể gửi tin nhắn mới.');
        historyLoaded = true;
    }
}

toggle?.addEventListener('click', () => {
    if (panel.hidden) {
        openPanel();
    } else {
        closePanel();
    }
});

closeBtn?.addEventListener('click', closePanel);

form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text || sending) return;

    sending = true;
    sendBtn.disabled = true;
    appendMessage('user', text);
    input.value = '';
    setTyping(true);

    try {
        const { data } = await window.axios.post('/chat/message', { message: text });
        setTyping(false);
        appendMessage('assistant', data.reply || 'Không nhận được phản hồi.');
    } catch (err) {
        setTyping(false);
        const msg =
            err.response?.status === 429
                ? 'Bạn gửi quá nhanh, vui lòng đợi vài giây.'
                : 'Không gửi được tin nhắn. Vui lòng thử lại.';
        appendMessage('assistant', msg);
    } finally {
        sending = false;
        sendBtn.disabled = false;
        input.focus();
    }
});
